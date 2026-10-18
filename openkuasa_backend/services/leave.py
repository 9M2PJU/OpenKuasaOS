"""HR Leave Approval and Quota Tracking Service."""

from datetime import date, datetime
from typing import Any, Dict, List, Optional
from openkuasa_backend.exceptions import InsufficientLeaveBalanceException, LeaveOverlapException


class LeaveApprovalService:
    """Manages employee leave requests, date validations, and statutory quotas."""

    def __init__(self):
        self._requests: Dict[str, Dict[str, Any]] = {}
        self._seq: int = 1

    def request_leave(
        self,
        employee: Dict[str, Any],
        leave_type: str,
        start_date: str,
        end_date: str,
        reason: Optional[str] = None,
        existing_requests: Optional[List[Dict[str, Any]]] = None,
    ) -> Dict[str, Any]:
        """Submit a new leave request after checking for date overlaps and quota availability."""
        d_start = datetime.strptime(start_date, "%Y-%m-%d").date()
        d_end = datetime.strptime(end_date, "%Y-%m-%d").date()

        if d_end < d_start:
            raise ValueError("Tarikh akhir cuti tidak boleh mendahului tarikh mula.")

        days_count = (d_end - d_start).days + 1

        # Check for overlaps
        active_requests = existing_requests if existing_requests is not None else list(self._requests.values())
        for req in active_requests:
            if req.get("employee_id") == employee.get("id") and req.get("status") in ["pending", "approved"]:
                req_start = datetime.strptime(req["start_date"], "%Y-%m-%d").date()
                req_end = datetime.strptime(req["end_date"], "%Y-%m-%d").date()
                if not (d_end < req_start or d_start > req_end):
                    raise LeaveOverlapException("Terdapat permohonan cuti sedia ada yang bertindih dengan tarikh yang dipilih.")

        # Check quota balance
        if leave_type == "annual":
            bal = float(employee.get("annual_leave_balance", 0.0))
            if bal < days_count:
                raise InsufficientLeaveBalanceException(
                    f"Baki cuti tahunan ({bal} hari) tidak mencukupi untuk memohon {days_count} hari."
                )
        elif leave_type == "medical":
            bal = float(employee.get("medical_leave_balance", 0.0))
            if bal < days_count:
                raise InsufficientLeaveBalanceException(
                    f"Baki cuti sakit ({bal} hari) tidak mencukupi untuk memohon {days_count} hari."
                )

        req_id = f"leave-{self._seq}"
        self._seq += 1

        request_record = {
            "id": req_id,
            "org_id": employee.get("org_id"),
            "employee_id": employee.get("id"),
            "leave_type": leave_type,
            "start_date": start_date,
            "end_date": end_date,
            "days_count": days_count,
            "reason": reason,
            "status": "pending",
            "approver_id": None,
        }
        self._requests[req_id] = request_record
        return request_record

    def approve_leave(
        self, request_record: Dict[str, Any], employee: Dict[str, Any], approver_id: Optional[str] = None
    ) -> Dict[str, Any]:
        """Approve a pending leave request and deduct the employee's leave balance."""
        if request_record.get("status") != "pending":
            raise ValueError("Hanya permohonan cuti berstatus [pending] boleh diluluskan.")

        days = float(request_record.get("days_count", 0))
        leave_type = request_record.get("leave_type")

        if leave_type == "annual":
            bal = float(employee.get("annual_leave_balance", 0.0))
            if bal < days:
                raise InsufficientLeaveBalanceException("Baki cuti tahunan tidak mencukupi semasa kelulusan.")
            employee["annual_leave_balance"] = round(bal - days, 1)
        elif leave_type == "medical":
            bal = float(employee.get("medical_leave_balance", 0.0))
            if bal < days:
                raise InsufficientLeaveBalanceException("Baki cuti sakit tidak mencukupi semasa kelulusan.")
            employee["medical_leave_balance"] = round(bal - days, 1)

        request_record["status"] = "approved"
        request_record["approver_id"] = approver_id
        return request_record

    def reject_leave(
        self, request_record: Dict[str, Any], reason: str, approver_id: Optional[str] = None
    ) -> Dict[str, Any]:
        """Reject a pending leave request with a stated reason."""
        if request_record.get("status") != "pending":
            raise ValueError("Hanya permohonan cuti berstatus [pending] boleh ditolak.")

        request_record["status"] = "rejected"
        request_record["rejection_reason"] = reason
        request_record["approver_id"] = approver_id
        return request_record
