"""Finance and Bendahara double-entry accounting services."""

import re
from datetime import datetime
from typing import Any, Dict, List, Optional
from openkuasa_backend.exceptions import UnbalancedJournalException


class SstService:
    """Malaysian Sales and Service Tax (SST) calculation service."""

    @staticmethod
    def calculate_sst(subtotal: float, sst_rate: float) -> float:
        """Calculate SST amount given subtotal and SST rate percentage."""
        if sst_rate <= 0 or subtotal <= 0:
            return 0.00
        return round(subtotal * (sst_rate / 100.0), 2)

    @classmethod
    def calculate_total(cls, subtotal: float, sst_rate: float) -> Dict[str, float]:
        """Calculate total amount including SST."""
        sst_amount = cls.calculate_sst(subtotal, sst_rate)
        total = round(subtotal + sst_amount, 2)
        return {
            "subtotal": round(subtotal, 2),
            "sst_rate": round(sst_rate, 2),
            "sst_amount": sst_amount,
            "total_amount": total,
        }


class BendaharaPostingService:
    """Double-entry general ledger posting and sequential numbering service."""

    def __init__(self):
        self._seq_cache: Dict[str, int] = {}

    def generate_next_invoice_number(
        self, org_id: str, existing_numbers: Optional[List[str]] = None
    ) -> str:
        """Generate an atomic sequential invoice number: INV-{YEAR}-{XXXX}."""
        year = datetime.now().year
        prefix = f"INV-{year}-"

        max_seq = 0
        if existing_numbers:
            for no in existing_numbers:
                m = re.match(rf"INV-{year}-(\d+)", no)
                if m:
                    max_seq = max(max_seq, int(m.group(1)))

        cache_key = f"inv_{org_id}_{year}"
        current = max(self._seq_cache.get(cache_key, 0), max_seq)
        next_seq = current + 1
        self._seq_cache[cache_key] = next_seq

        return f"{prefix}{next_seq:04d}"

    def generate_next_receipt_number(
        self, org_id: str, existing_numbers: Optional[List[str]] = None
    ) -> str:
        """Generate an atomic sequential receipt number: RCP-{YEAR}-{XXXX}."""
        year = datetime.now().year
        prefix = f"RCP-{year}-"

        max_seq = 0
        if existing_numbers:
            for no in existing_numbers:
                m = re.match(rf"RCP-{year}-(\d+)", no)
                if m:
                    max_seq = max(max_seq, int(m.group(1)))

        cache_key = f"rcp_{org_id}_{year}"
        current = max(self._seq_cache.get(cache_key, 0), max_seq)
        next_seq = current + 1
        self._seq_cache[cache_key] = next_seq

        return f"{prefix}{next_seq:04d}"

    def validate_journal_balance(self, lines: List[Dict[str, Any]]) -> None:
        """Validate that total debits equal total credits within 2 decimal places."""
        total_debit = sum(float(line.get("debit", 0.0)) for line in lines)
        total_credit = sum(float(line.get("credit", 0.0)) for line in lines)

        total_debit = round(total_debit, 2)
        total_credit = round(total_credit, 2)

        if abs(total_debit - total_credit) > 0.001:
            raise UnbalancedJournalException(
                f"Baki transaksi lejar tidak seimbang: Debit (RM {total_debit:.2f}) != Kredit (RM {total_credit:.2f})."
            )
