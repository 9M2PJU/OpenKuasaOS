"""Tests for Superadmin authorization, user status enforcement, and system metrics."""

import pytest
from openkuasa_backend.exceptions import AccountSuspendedException, SuperadminUnauthorizedException
from openkuasa_backend.services.superadmin import SuperadminService, SystemSettingStore, UserModel


@pytest.fixture
def service():
    s = SuperadminService()
    # Demo Account
    s.add_user(
        UserModel(
            id="usr-demo-1",
            name="Ahmad Zulkifli",
            email="admin@openkuasa.my",
            is_superadmin=False,
            is_demo=True,
            status="active",
        )
    )
    # Superadmin Owner Account
    s.add_user(
        UserModel(
            id="usr-owner-2",
            name="Maui Sabily",
            email="admin@sabily.info",
            is_superadmin=True,
            is_demo=False,
            status="active",
        )
    )
    # Regular User
    s.add_user(
        UserModel(
            id="usr-reg-3",
            name="Regular Staff",
            email="staff@example.com",
            is_superadmin=False,
            is_demo=False,
            status="active",
        )
    )
    s.add_organization("org-1", "Rimba Ventures Sdn Bhd", "usr-demo-1")
    s.add_organization("org-2", "Sabily Enterprise", "usr-owner-2")
    return s


def test_superadmin_access_granted(service):
    """Superadmin is granted access to the superadmin control plane."""
    owner = service.users["usr-owner-2"]
    # Should not raise
    service.authorize_superadmin(owner)


def test_demo_user_blocked_from_superadmin(service):
    """Demo user is forbidden from superadmin access."""
    demo = service.users["usr-demo-1"]
    with pytest.raises(SuperadminUnauthorizedException):
        service.authorize_superadmin(demo)


def test_regular_user_blocked_from_superadmin(service):
    """Regular tenant user is forbidden from superadmin access."""
    regular = service.users["usr-reg-3"]
    with pytest.raises(SuperadminUnauthorizedException):
        service.authorize_superadmin(regular)


def test_suspended_user_cannot_authenticate(service):
    """Suspended user is rejected with AccountSuspendedException upon login."""
    owner = service.users["usr-owner-2"]
    service.set_user_status(owner, "usr-reg-3", "suspended")

    with pytest.raises(AccountSuspendedException) as exc:
        service.authenticate_user("staff@example.com")
    assert "digantung" in str(exc.value)


def test_superadmin_can_toggle_roles(service):
    """Superadmin can promote or demote user roles."""
    owner = service.users["usr-owner-2"]
    updated = service.toggle_superadmin_role(owner, "usr-reg-3")
    assert updated.is_superadmin is True

    updated_again = service.toggle_superadmin_role(owner, "usr-reg-3")
    assert updated_again.is_superadmin is False


def test_superadmin_metrics_and_trends(service):
    """Metrics calculation correctly differentiates real vs demo users."""
    owner = service.users["usr-owner-2"]
    metrics = service.get_metrics(owner)
    assert metrics["total_users"] == 3
    assert metrics["real_users"] == 2
    assert metrics["demo_users"] == 1
    assert metrics["total_organizations"] == 2


def test_superadmin_impersonation_lifecycle(service):
    """Superadmin can impersonate organization and safely leave."""
    owner = service.users["usr-owner-2"]
    session = service.impersonate_organization(owner, "org-1")
    assert session["current_org_id"] == "org-1"
    assert session["impersonated_by"] == owner.id

    service.leave_impersonation()
    assert service.impersonation_session is None


def test_system_settings_key_value_store():
    """System settings get, set, and default operations."""
    SystemSettingStore.reset()
    assert SystemSettingStore.get("registration_enabled") is True
    assert SystemSettingStore.get("instance_name") == "OpenKuasaOS Self-Hosted"

    SystemSettingStore.set("registration_enabled", False)
    assert SystemSettingStore.get("registration_enabled") is False
    assert SystemSettingStore.get("non_existent_key", "default_val") == "default_val"
