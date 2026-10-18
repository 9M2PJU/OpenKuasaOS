"""Invoice lifecycle service."""

from typing import Any, Dict, List, Optional
from openkuasa_backend.exceptions import UnbalancedJournalException
from openkuasa_backend.services.finance import BendaharaPostingService, SstService


class InvoiceService:
    """Manages full lifecycle of invoices: draft -> post -> pay -> void."""

    def __init__(self, posting_service: Optional[BendaharaPostingService] = None):
        self.posting_service = posting_service or BendaharaPostingService()
        self.invoices: Dict[str, Dict[str, Any]] = {}
        self.journals: List[Dict[str, Any]] = []
        self.receipts: List[Dict[str, Any]] = []
        self.accounts: Dict[str, Dict[str, Any]] = {}

    def create_account(self, account_id: str, org_id: str, name: str, balance: float) -> Dict[str, Any]:
        acc = {
            "id": account_id,
            "org_id": org_id,
            "name": name,
            "balance": round(balance, 2),
        }
        self.accounts[account_id] = acc
        return acc

    def create_draft(
        self,
        invoice_id: str,
        org_id: str,
        customer_name: str,
        issue_date: str,
        due_date: str,
        lines: List[Dict[str, Any]],
        sst_rate: float = 8.00,
    ) -> Dict[str, Any]:
        """Create a new draft invoice."""
        if due_date < issue_date:
            raise ValueError("The due date cannot be before the invoice date.")

        subtotal = sum(float(line.get("quantity", 1)) * float(line.get("unit_price", 0)) for line in lines)
        totals = SstService.calculate_total(subtotal, sst_rate)

        inv = {
            "id": invoice_id,
            "org_id": org_id,
            "invoice_number": None,
            "customer_name": customer_name,
            "issue_date": issue_date,
            "due_date": due_date,
            "lines": lines,
            "subtotal": totals["subtotal"],
            "sst_rate": totals["sst_rate"],
            "sst_amount": totals["sst_amount"],
            "total_amount": totals["total_amount"],
            "amount_paid": 0.00,
            "balance": totals["total_amount"],
            "status": "draft",
        }
        self.invoices[invoice_id] = inv
        return inv

    def post_invoice(self, invoice_id: str) -> Dict[str, Any]:
        """Post a draft invoice: assign INV-YYYY-XXXX, create balanced double-entry journal."""
        inv = self.invoices.get(invoice_id)
        if not inv:
            raise ValueError(f"Invoice {invoice_id} not found.")
        if inv["status"] != "draft":
            raise ValueError(f"Invois dengan status [{inv['status']}] tidak boleh dipos.")

        existing_numbers = [
            i["invoice_number"]
            for i in self.invoices.values()
            if i.get("org_id") == inv["org_id"] and i.get("invoice_number")
        ]
        inv_no = self.posting_service.generate_next_invoice_number(inv["org_id"], existing_numbers)
        inv["invoice_number"] = inv_no

        # Double-entry ledger: Debit AR, Credit Sales, Credit SST
        total = inv["total_amount"]
        subtotal = inv["subtotal"]
        sst = inv["sst_amount"]

        journal_lines = [
            {"account": "1200 - AR", "debit": total, "credit": 0.00},
            {"account": "4000 - Sales", "debit": 0.00, "credit": subtotal},
        ]
        if sst > 0:
            journal_lines.append({"account": "2200 - SST", "debit": 0.00, "credit": sst})

        self.posting_service.validate_journal_balance(journal_lines)

        journal = {
            "id": f"jrnl-{len(self.journals) + 1}",
            "org_id": inv["org_id"],
            "reference_type": "invoice",
            "reference_id": inv["id"],
            "reference_number": inv_no,
            "lines": journal_lines,
            "status": "posted",
        }
        self.journals.append(journal)

        inv["status"] = "posted"
        return inv

    def record_payment(
        self, invoice_id: str, account_id: str, amount: float, payment_date: str
    ) -> Dict[str, Any]:
        """Record a customer payment against a posted invoice."""
        inv = self.invoices.get(invoice_id)
        if not inv:
            raise ValueError(f"Invoice {invoice_id} not found.")
        if inv["status"] not in ["posted", "paid"]:
            raise ValueError("Hanya invois yang telah dipos boleh menerima bayaran.")

        account = self.accounts.get(account_id)
        if not account:
            raise ValueError(f"Account {account_id} not found.")

        existing_rcps = [r["receipt_number"] for r in self.receipts if r.get("org_id") == inv["org_id"]]
        rcp_no = self.posting_service.generate_next_receipt_number(inv["org_id"], existing_rcps)

        receipt = {
            "id": f"rcp-{len(self.receipts) + 1}",
            "org_id": inv["org_id"],
            "invoice_id": invoice_id,
            "account_id": account_id,
            "receipt_number": rcp_no,
            "amount": round(amount, 2),
            "payment_date": payment_date,
        }
        self.receipts.append(receipt)

        # Update account balance
        account["balance"] = round(account["balance"] + amount, 2)

        # Update invoice paid & balance
        inv["amount_paid"] = round(inv["amount_paid"] + amount, 2)
        inv["balance"] = max(0.00, round(inv["total_amount"] - inv["amount_paid"], 2))
        if inv["amount_paid"] >= inv["total_amount"]:
            inv["status"] = "paid"

        # Journal: Debit Bank, Credit AR
        journal_lines = [
            {"account": f"1000 - Bank ({account['name']})", "debit": amount, "credit": 0.00},
            {"account": "1200 - AR", "debit": 0.00, "credit": amount},
        ]
        self.posting_service.validate_journal_balance(journal_lines)

        journal = {
            "id": f"jrnl-{len(self.journals) + 1}",
            "org_id": inv["org_id"],
            "reference_type": "receipt",
            "reference_id": receipt["id"],
            "reference_number": rcp_no,
            "lines": journal_lines,
            "status": "posted",
        }
        self.journals.append(journal)

        return {"invoice": inv, "receipt": receipt}

    def void_invoice(self, invoice_id: str, reason: str) -> Dict[str, Any]:
        """Void an invoice, reversing journal if previously posted."""
        inv = self.invoices.get(invoice_id)
        if not inv:
            raise ValueError(f"Invoice {invoice_id} not found.")

        if inv["amount_paid"] > 0:
            raise ValueError("Invois yang telah mempunyai bayaran tidak boleh dibatalkan secara terus.")

        if inv["status"] == "posted":
            # Reverse entry: Debit Sales, Debit SST, Credit AR
            lines = [
                {"account": "4000 - Sales", "debit": inv["subtotal"], "credit": 0.00},
                {"account": "1200 - AR", "debit": 0.00, "credit": inv["total_amount"]},
            ]
            if inv["sst_amount"] > 0:
                lines.insert(1, {"account": "2200 - SST", "debit": inv["sst_amount"], "credit": 0.00})

            self.posting_service.validate_journal_balance(lines)
            journal = {
                "id": f"jrnl-{len(self.journals) + 1}",
                "org_id": inv["org_id"],
                "reference_type": "invoice_void",
                "reference_id": inv["id"],
                "reference_number": inv["invoice_number"],
                "lines": lines,
                "status": "void",
            }
            self.journals.append(journal)

        inv["status"] = "void"
        inv["void_reason"] = reason
        return inv
