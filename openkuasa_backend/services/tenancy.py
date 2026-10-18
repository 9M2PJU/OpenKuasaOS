"""Multi-Tenancy Context & Data Isolation Service."""

from typing import Any, Dict, List, Optional
from openkuasa_backend.exceptions import TenantIsolationException


class OrganizationContext:
    """Thread-local / request-scoped Organization context."""

    _current_org_id: Optional[str] = None

    @classmethod
    def set(cls, org_id: str) -> None:
        cls._current_org_id = org_id

    @classmethod
    def get(cls) -> Optional[str]:
        return cls._current_org_id

    @classmethod
    def clear(cls) -> None:
        cls._current_org_id = None


class MultiTenantStore:
    """Tenant-scoped data repository enforcing zero data leakage between organizations."""

    def __init__(self):
        self._memberships: Dict[str, List[str]] = {}  # user_id -> [org_id, ...]
        self._items: Dict[str, Dict[str, Any]] = {}   # item_id -> item

    def add_membership(self, user_id: str, org_id: str) -> None:
        if user_id not in self._memberships:
            self._memberships[user_id] = []
        if org_id not in self._memberships[user_id]:
            self._memberships[user_id].append(org_id)

    def is_user_in_org(self, user_id: str, org_id: str) -> bool:
        return org_id in self._memberships.get(user_id, [])

    def store_item(self, item_id: str, org_id: str, data: Dict[str, Any]) -> Dict[str, Any]:
        record = dict(data)
        record["id"] = item_id
        record["org_id"] = org_id
        self._items[item_id] = record
        return record

    def list_items_for_org(self, org_id: str, user_id: str) -> List[Dict[str, Any]]:
        """List all items belonging strictly to org_id, verifying user membership."""
        if not self.is_user_in_org(user_id, org_id):
            raise TenantIsolationException(f"User {user_id} is not authorized for organization {org_id}.")

        return [item for item in self._items.values() if item.get("org_id") == org_id]

    def get_item_for_org(self, item_id: str, org_id: str, user_id: str) -> Optional[Dict[str, Any]]:
        """Get an item by id strictly scoped to org_id."""
        if not self.is_user_in_org(user_id, org_id):
            raise TenantIsolationException(f"User {user_id} is not authorized for organization {org_id}.")

        item = self._items.get(item_id)
        if not item or item.get("org_id") != org_id:
            return None
        return item
