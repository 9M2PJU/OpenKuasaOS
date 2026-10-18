"""Feature tests for Multi-Tenant Data Isolation and Security Scoping."""

import pytest
from openkuasa_backend.exceptions import TenantIsolationException
from openkuasa_backend.services.tenancy import MultiTenantStore


@pytest.fixture
def store():
    s = MultiTenantStore()
    # Org A
    s.add_membership("user-a", "org-a")
    # Org B
    s.add_membership("user-b", "org-b")
    return s


def test_organization_a_cannot_access_organization_b_invoices(store):
    """Organization B user must never see or list invoices from Organization A."""
    # Org A invoice
    store.store_item(
        item_id="inv-a-001",
        org_id="org-a",
        data={"invoice_number": "INV-A-001", "total": 5000.00, "customer": "Cust A"},
    )

    # Org B lists items
    b_items = store.list_items_for_org(org_id="org-b", user_id="user-b")
    assert len(b_items) == 0

    # User B attempts to fetch Org A's invoice directly
    item_by_b = store.get_item_for_org(item_id="inv-a-001", org_id="org-b", user_id="user-b")
    assert item_by_b is None


def test_cross_tenant_access_throws_isolation_exception(store):
    """User B attempting to query Org A directly must be rejected with TenantIsolationException."""
    store.store_item(
        item_id="inv-a-001",
        org_id="org-a",
        data={"invoice_number": "INV-A-001"},
    )

    with pytest.raises(TenantIsolationException):
        store.list_items_for_org(org_id="org-a", user_id="user-b")

    with pytest.raises(TenantIsolationException):
        store.get_item_for_org(item_id="inv-a-001", org_id="org-a", user_id="user-b")


def test_organization_a_can_access_own_invoices(store):
    """Authorized user A can correctly retrieve Org A's invoices."""
    store.store_item(
        item_id="inv-a-001",
        org_id="org-a",
        data={"invoice_number": "INV-A-001", "total": 5000.00},
    )

    a_items = store.list_items_for_org(org_id="org-a", user_id="user-a")
    assert len(a_items) == 1
    assert a_items[0]["id"] == "inv-a-001"

    item = store.get_item_for_org(item_id="inv-a-001", org_id="org-a", user_id="user-a")
    assert item is not None
    assert item["invoice_number"] == "INV-A-001"
