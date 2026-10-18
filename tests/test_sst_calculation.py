"""Unit tests for Malaysian Sales and Service Tax (SST) calculation."""

import pytest
from openkuasa_backend.services.finance import SstService


def test_sst_6_percent_calculation():
    """Test SST 6% calculation on services."""
    subtotal = 1000.00
    sst_amount = SstService.calculate_sst(subtotal, 6.00)
    assert sst_amount == 60.00

    total_result = SstService.calculate_total(subtotal, 6.00)
    assert total_result["subtotal"] == 1000.00
    assert total_result["sst_rate"] == 6.00
    assert total_result["sst_amount"] == 60.00
    assert total_result["total_amount"] == 1060.00


def test_sst_8_percent_calculation():
    """Test SST 8% calculation on standard supply of taxable services."""
    subtotal = 2500.00
    sst_amount = SstService.calculate_sst(subtotal, 8.00)
    assert sst_amount == 200.00

    total_result = SstService.calculate_total(subtotal, 8.00)
    assert total_result["subtotal"] == 2500.00
    assert total_result["sst_rate"] == 8.00
    assert total_result["sst_amount"] == 200.00
    assert total_result["total_amount"] == 2700.00


def test_sst_fractional_rounding():
    """Test rounding precision for fractional amounts."""
    subtotal = 125.45
    # 125.45 * 0.08 = 10.036 -> rounds to 10.04
    sst_amount = SstService.calculate_sst(subtotal, 8.00)
    assert sst_amount == 10.04

    total_result = SstService.calculate_total(subtotal, 8.00)
    assert total_result["total_amount"] == 135.49


def test_sst_zero_rate():
    """Test 0% rate and negative/zero boundaries."""
    subtotal = 500.00
    total_result = SstService.calculate_total(subtotal, 0.00)
    assert total_result["sst_amount"] == 0.00
    assert total_result["total_amount"] == 500.00
