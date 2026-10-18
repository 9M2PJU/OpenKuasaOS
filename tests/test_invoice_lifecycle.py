"""Feature tests for Full Invoice Lifecycle (Draft -> Post -> Pay -> Void)."""

import pytest
from openkuasa_backend.services.invoice import InvoiceService


@pytest.fixture
def invoice_service():
    service = InvoiceService()
    service.create_account("acc-1", "org-1", "Maybank Berhad", 10000.00)
    return service


def test_full_invoice_lifecycle_draft_post_pay(invoice_service):
    """Test full cycle: Draft creation -> Post -> Pay."""
    org_id = "org-1"
    lines = [
        {"description": "Langganan OpenKuasa OS Tahunan", "quantity": 1, "unit_price": 10000.00},
        {"description": "Pemasangan & Latihan Kakitangan", "quantity": 2, "unit_price": 1000.00},
    ]

    # 1. DRAFT: Subtotal 12000, 8% SST = 960, Total = 12960
    inv = invoice_service.create_draft(
        invoice_id="inv-101",
        org_id=org_id,
        customer_name="Syarikat Pelanggan Maju Jaya",
        issue_date="2026-10-12",
        due_date="2026-10-26",
        lines=lines,
        sst_rate=8.00,
    )

    assert inv["status"] == "draft"
    assert inv["subtotal"] == 12000.00
    assert inv["sst_amount"] == 960.00
    assert inv["total_amount"] == 12960.00
    assert inv["amount_paid"] == 0.00

    # 2. POST: Lock invoice, generate sequence number, post balanced journal
    posted_inv = invoice_service.post_invoice("inv-101")
    assert posted_inv["status"] == "posted"
    assert posted_inv["invoice_number"].startswith("INV-")
    assert len(invoice_service.journals) == 1
    post_journal = invoice_service.journals[0]
    assert post_journal["status"] == "posted"

    # Verify journal balancing: debits == credits
    total_debit = sum(l["debit"] for l in post_journal["lines"])
    total_credit = sum(l["credit"] for l in post_journal["lines"])
    assert total_debit == 12960.00
    assert total_credit == 12960.00

    # 3. PAY: Record payment receipt
    pay_res = invoice_service.record_payment(
        invoice_id="inv-101",
        account_id="acc-1",
        amount=12960.00,
        payment_date="2026-10-15",
    )

    assert pay_res["invoice"]["status"] == "paid"
    assert pay_res["invoice"]["amount_paid"] == 12960.00
    assert pay_res["invoice"]["balance"] == 0.00
    assert invoice_service.accounts["acc-1"]["balance"] == 22960.00


def test_cannot_pay_unposted_invoice(invoice_service):
    """Attempting to record payment on draft invoice must raise ValueError."""
    lines = [{"quantity": 1, "unit_price": 500.00}]
    invoice_service.create_draft("inv-draft", "org-1", "Cust", "2026-10-12", "2026-10-20", lines)

    with pytest.raises(ValueError) as exc:
        invoice_service.record_payment("inv-draft", "acc-1", 500.00, "2026-10-15")
    assert "Hanya invois yang telah dipos" in str(exc.value)


def test_cannot_void_invoice_with_payments(invoice_service):
    """Invoices with recorded payments cannot be voided directly."""
    lines = [{"quantity": 1, "unit_price": 500.00}]
    invoice_service.create_draft("inv-paid", "org-1", "Cust", "2026-10-12", "2026-10-20", lines)
    invoice_service.post_invoice("inv-paid")
    invoice_service.record_payment("inv-paid", "acc-1", 500.00, "2026-10-15")

    with pytest.raises(ValueError) as exc:
        invoice_service.void_invoice("inv-paid", "Salah maklumat")
    assert "mempunyai bayaran" in str(exc.value)


def test_void_posted_invoice_reverses_journal(invoice_service):
    """Voiding a posted unpaid invoice reverses the ledger journal."""
    lines = [{"quantity": 1, "unit_price": 1000.00}]
    invoice_service.create_draft("inv-void", "org-1", "Cust", "2026-10-12", "2026-10-20", lines, sst_rate=8.00)
    invoice_service.post_invoice("inv-void")

    assert len(invoice_service.journals) == 1
    voided = invoice_service.void_invoice("inv-void", "Customer cancellation")

    assert voided["status"] == "void"
    assert len(invoice_service.journals) == 2
    reversal = invoice_service.journals[1]
    assert reversal["reference_type"] == "invoice_void"
    total_debit = sum(l["debit"] for l in reversal["lines"])
    total_credit = sum(l["credit"] for l in reversal["lines"])
    assert total_debit == 1080.00
    assert total_credit == 1080.00
