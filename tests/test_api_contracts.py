"""Unit tests for RESTful API response contracts and error payloads."""

import pytest


def format_api_success(data=None, meta=None, message="Operasi berjaya."):
    """Format standardized API success response according to SPEC Section 4."""
    payload = {
        "success": True,
        "data": data if data is not None else {},
        "message": message,
    }
    if meta is not None:
        payload["meta"] = meta
    return payload


def format_api_error(code: str, message: str, details: dict = None):
    """Format standardized API error response according to SPEC Section 4."""
    return {
        "success": False,
        "error": {
            "code": code,
            "message": message,
            "details": details or {},
        },
    }


def test_api_success_contract():
    """Verify standard success response structure."""
    res = format_api_success(
        data={"invoice_id": "inv-001"},
        meta={"page": 1, "per_page": 20, "total": 1},
        message="Invois berjaya diambil.",
    )
    assert res["success"] is True
    assert "data" in res
    assert "meta" in res
    assert res["meta"]["total"] == 1
    assert res["message"] == "Invois berjaya diambil."


def test_api_validation_error_contract():
    """Verify standard HTTP 422 validation failure error structure."""
    res = format_api_error(
        code="VALIDATION_FAILED",
        message="Data yang dimasukkan tidak sah.",
        details={"due_date": ["The due date cannot be before the invoice date."]},
    )
    assert res["success"] is False
    assert res["error"]["code"] == "VALIDATION_FAILED"
    assert res["error"]["message"] == "Data yang dimasukkan tidak sah."
    assert "due_date" in res["error"]["details"]
    assert "The due date cannot be before the invoice date." in res["error"]["details"]["due_date"]
