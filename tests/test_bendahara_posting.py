"""Unit tests for Bendahara General Ledger posting and validation."""

import pytest
from openkuasa_backend.exceptions import UnbalancedJournalException
from openkuasa_backend.services.finance import BendaharaPostingService


@pytest.fixture
def service():
    return BendaharaPostingService()


def test_balanced_journal_passes_validation(service):
    """Test that balanced debits and credits pass validation without exception."""
    lines = [
        {"debit": 1080.00, "credit": 0.00},
        {"debit": 0.00, "credit": 1000.00},
        {"debit": 0.00, "credit": 80.00},
    ]
    # Should not raise exception
    service.validate_journal_balance(lines)


def test_unbalanced_journal_throws_exception(service):
    """Test that unbalanced debits and credits raise UnbalancedJournalException."""
    lines = [
        {"debit": 1000.00, "credit": 0.00},
        {"debit": 0.00, "credit": 950.00},  # Off by RM 50.00
    ]
    with pytest.raises(UnbalancedJournalException) as exc_info:
        service.validate_journal_balance(lines)
    assert "tidak seimbang" in str(exc_info.value)


def test_sequential_invoice_numbering(service):
    """Test atomic sequential generation of invoice numbers: INV-YYYY-XXXX."""
    org_id = "org-uuid-001"
    inv1 = service.generate_next_invoice_number(org_id)
    inv2 = service.generate_next_invoice_number(org_id)
    inv3 = service.generate_next_invoice_number(org_id)

    assert inv1 != inv2
    assert inv2 != inv3
    assert inv1.endswith("0001")
    assert inv2.endswith("0002")
    assert inv3.endswith("0003")


def test_sequential_receipt_numbering(service):
    """Test atomic sequential generation of receipt numbers: RCP-YYYY-XXXX."""
    org_id = "org-uuid-001"
    rcp1 = service.generate_next_receipt_number(org_id)
    rcp2 = service.generate_next_receipt_number(org_id)

    assert rcp1 != rcp2
    assert rcp1.endswith("0001")
    assert rcp2.endswith("0002")
