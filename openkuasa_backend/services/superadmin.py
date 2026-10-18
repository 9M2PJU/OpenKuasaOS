"""Superadmin and Multi-Tenant Administration Service."""

from typing import Any, Dict, List, Optional
from openkuasa_backend.exceptions import AccountSuspendedException, SuperadminUnauthorizedException


class UserModel:
    """In-memory domain user model matching the Laravel users table attributes."""

    def __init__(
        self,
        id: str,
        name: str,
        email: str,
        is_superadmin: bool = False,
        is_demo: bool = False,
        status: str = "active",
    ):
        self.id = id
        self.name = name
        self.email = email
        self.is_superadmin = bool(is_superadmin)
        self.is_demo = bool(is_demo)
        self.status = status

    def is_superadmin_user(self) -> bool:
        return self.is_superadmin

    def is_demo_user(self) -> bool:
        return self.is_demo

    def is_active_user(self) -> bool:
        return self.status == "active"


class SystemSettingStore:
    """Key-value setting store matching system_settings table."""

    _settings: Dict[str, Any] = {
        "registration_enabled": True,
        "instance_name": "OpenKuasaOS Self-Hosted",
        "owner_user_id": "01a130a3-db17-73c4-8584-ea2d1dd4ea4e",
    }

    @classmethod
    def get(cls, key: str, default: Any = None) -> Any:
        return cls._settings.get(key, default)

    @classmethod
    def set(cls, key: str, value: Any) -> None:
        cls._settings[key] = value

    @classmethod
    def reset(cls) -> None:
        cls._settings = {
            "registration_enabled": True,
            "instance_name": "OpenKuasaOS Self-Hosted",
            "owner_user_id": "01a130a3-db17-73c4-8584-ea2d1dd4ea4e",
        }


class SuperadminService:
    """Superadmin guard and management service."""

    def __init__(self):
        self.users: Dict[str, UserModel] = {}
        self.organizations: Dict[str, Dict[str, Any]] = {}
        self.impersonation_session: Optional[Dict[str, Any]] = None

    def add_user(self, user: UserModel) -> None:
        self.users[user.id] = user

    def add_organization(self, org_id: str, name: str, owner_id: str) -> None:
        self.organizations[org_id] = {
            "id": org_id,
            "name": name,
            "owner_id": owner_id,
            "member_count": 1,
        }

    def authenticate_user(self, email: str) -> UserModel:
        for user in self.users.values():
            if user.email == email:
                if not user.is_active_user():
                    raise AccountSuspendedException("Akaun anda telah digantung oleh pentadbir sistem.")
                return user
        raise ValueError("Pengguna tidak dijumpai.")

    def authorize_superadmin(self, user: UserModel) -> None:
        if not user.is_superadmin:
            raise SuperadminUnauthorizedException("Akses terhad untuk Superadmin sahaja.")

    def set_user_status(self, actor: UserModel, target_user_id: str, new_status: str) -> UserModel:
        self.authorize_superadmin(actor)
        if target_user_id not in self.users:
            raise ValueError("Pengguna sasaran tidak wujud.")
        user = self.users[target_user_id]
        user.status = new_status
        return user

    def toggle_superadmin_role(self, actor: UserModel, target_user_id: str) -> UserModel:
        self.authorize_superadmin(actor)
        if target_user_id not in self.users:
            raise ValueError("Pengguna sasaran tidak wujud.")
        user = self.users[target_user_id]
        user.is_superadmin = not user.is_superadmin
        return user

    def impersonate_organization(self, actor: UserModel, org_id: str) -> Dict[str, Any]:
        self.authorize_superadmin(actor)
        if org_id not in self.organizations:
            raise ValueError("Organisasi tidak dijumpai.")
        self.impersonation_session = {
            "impersonated_by": actor.id,
            "current_org_id": org_id,
            "org": self.organizations[org_id],
        }
        return self.impersonation_session

    def leave_impersonation(self) -> None:
        self.impersonation_session = None

    def get_metrics(self, actor: UserModel) -> Dict[str, Any]:
        self.authorize_superadmin(actor)
        total_users = len(self.users)
        demo_users = sum(1 for u in self.users.values() if u.is_demo)
        real_users = total_users - demo_users
        total_orgs = len(self.organizations)
        return {
            "total_users": total_users,
            "real_users": real_users,
            "demo_users": demo_users,
            "total_organizations": total_orgs,
            "registration_enabled": SystemSettingStore.get("registration_enabled", True),
        }
