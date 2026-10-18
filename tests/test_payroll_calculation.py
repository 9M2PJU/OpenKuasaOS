"""Unit tests for Malaysian Statutory Payroll Calculation (KWSP / SOCSO / EIS / PCB)."""

import pytest
from openkuasa_backend.services.payroll import MalaysianPayrollEngine


@pytest.fixture
def engine():
    return MalaysianPayrollEngine()


def test_kwsp_contributions_for_salary_up_to_5000(engine):
    """Test KWSP employee rate 11% and employer 13% for salary <= RM 5,000."""
    salary = 4000.00
    epf = engine.calculate_epf(salary, 11.00)

    # Employee 11%: 4000 * 0.11 = 440.00
    assert epf["employee"] == 440.00

    # Employer 13%: 4000 * 0.13 = 520.00
    assert epf["employer"] == 520.00
    assert epf["employer_rate"] == 13.00


def test_kwsp_contributions_for_salary_above_5000(engine):
    """Test KWSP employer rate drops to 12% for salary > RM 5,000."""
    salary = 6000.00
    epf = engine.calculate_epf(salary, 11.00)

    # Employee 11%: 6000 * 0.11 = 660.00
    assert epf["employee"] == 660.00

    # Employer 12%: 6000 * 0.12 = 720.00
    assert epf["employer"] == 720.00
    assert epf["employer_rate"] == 12.00


def test_socso_contributions_and_statutory_cap(engine):
    """Test SOCSO contributions and cap at RM 6,000."""
    # For RM 3,000 salary
    socso3k = engine.calculate_socso(3000.00)
    assert socso3k["employee"] > 10.00
    assert socso3k["employer"] > 40.00

    # For RM 6,000 cap
    socso6k = engine.calculate_socso(6000.00)
    assert socso6k["employee"] == 29.75
    assert socso6k["employer"] == 104.15

    # For RM 10,000 (must be capped at RM 6,000 rate)
    socso10k = engine.calculate_socso(10000.00)
    assert socso10k["employee"] == 29.75
    assert socso10k["employer"] == 104.15


def test_eis_contributions_and_statutory_cap(engine):
    """Test EIS contributions and cap at RM 6,000."""
    # For RM 6,000 cap (0.2% = 11.90)
    eis6k = engine.calculate_eis(6000.00)
    assert eis6k["employee"] == 11.90
    assert eis6k["employer"] == 11.90

    # For RM 12,000 (must be capped at RM 6,000 rate)
    eis12k = engine.calculate_eis(12000.00)
    assert eis12k["employee"] == 11.90
    assert eis12k["employer"] == 11.90


def test_pcb_tax_deduction_progressive_scale(engine):
    """Test PCB monthly tax deduction calculation."""
    # Low salary (RM 2,000) under personal tax relief
    # Chargeable income is 0 -> PCB must be 0.00
    pcb_low = engine.calculate_pcb(2000.00, 220.00)
    assert pcb_low == 0.00

    # High salary (RM 10,000 / mo = RM 120,000 / year)
    pcb_high = engine.calculate_pcb(10000.00, 1100.00)
    assert pcb_high > 500.00


def test_full_payslip_calculation(engine):
    """Test full payslip breakdown calculation."""
    salary = 4500.00
    payslip = engine.calculate_payslip(salary)

    assert payslip["gross_pay"] == 4500.00
    assert payslip["epf_employee"] == 495.00  # 11%
    assert payslip["epf_employer"] == 585.00  # 13%
    assert payslip["socso_employee"] > 0.00
    assert payslip["eis_employee"] > 0.00
    assert payslip["net_pay"] > 0.00

    # Assert net pay = gross - employee statutory deductions
    expected_net = round(
        payslip["gross_pay"]
        - payslip["epf_employee"]
        - payslip["socso_employee"]
        - payslip["eis_employee"]
        - payslip["pcb_amount"],
        2,
    )
    assert payslip["net_pay"] == expected_net
