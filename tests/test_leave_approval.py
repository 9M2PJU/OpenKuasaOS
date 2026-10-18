"""Feature tests for HR Leave Approval Service."""

import pytest
from openkuasa_backend.exceptions import InsufficientLeaveBalanceException, LeaveOverlapException
from openkuasa_backend.services.leave import LeaveApprovalService


@pytest.fixture
def leave_service():
    return LeaveApprovalService()


@pytest.fixture
def employee():
    return {
        "id": "emp-101",
        "org_id": "org-1",
        "full_name": "Siti Aminah",
        "annual_leave_balance": 14.0,
        "medical_leave_balance": 14.0,
    }


def test_leave_request_creation(leave_service, employee):
    """Test standard leave submission."""
    req = leave_service.request_leave(
        employee=employee,
        leave_type="annual",
        start_date="2026-11-01",
        end_date="2026-11-03",
        reason="Cuti Rehat",
    )
    assert req["status"] == "pending"
    assert req["days_count"] == 3


def test_leave_overlap_rejection(leave_service, employee):
    """Submitting leave that overlaps with existing pending/approved leave must be rejected."""
    leave_service.request_leave(
        employee=employee,
        leave_type="annual",
        start_date="2026-11-01",
        end_date="2026-11-05",
    )

    with pytest.raises(LeaveOverlapException):
        leave_service.request_leave(
            employee=employee,
            leave_type="annual",
            start_date="2026-11-03",
            end_date="2026-11-07",
        )


def test_insufficient_leave_balance_rejection(leave_service, employee):
    """Requesting more days than available balance must raise InsufficientLeaveBalanceException."""
    employee["annual_leave_balance"] = 2.0

    with pytest.raises(InsufficientLeaveBalanceException) as exc:
        leave_service.request_leave(
            employee=employee,
            leave_type="annual",
            start_date="2026-12-01",
            end_date="2026-12-05",  # 5 days
        )
    assert "tidak mencukupi" in str(exc.value)


def test_leave_approval_deducts_quota(leave_service, employee):
    """Approving leave updates status and deducts employee leave balance."""
    req = leave_service.request_leave(
        employee=employee,
        leave_type="annual",
        start_date="2026-11-10",
        end_date="2026-11-12",  # 3 days
    )
    assert employee["annual_leave_balance"] == 14.0

    approved = leave_service.approve_leave(req, employee, approver_id="mgr-01")
    assert approved["status"] == "approved"
    assert approved["approver_id"] == "mgr-01"
    assert employee["annual_leave_balance"] == 11.0


def test_leave_rejection(leave_service, employee):
    """Rejecting leave sets status to rejected with reason."""
    req = leave_service.request_leave(
        employee=employee,
        leave_type="annual",
        start_date="2026-11-20",
        end_date="2026-11-21",
    )
    rejected = leave_service.reject_leave(req, reason="Kekurangan kakitangan bertugas.")
    assert rejected["status"] == "rejected"
    assert rejected["rejection_reason"] == "Kekurangan kakitangan bertugas."
    assert employee["annual_leave_balance"] == 14.0
