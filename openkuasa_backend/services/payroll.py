"""Malaysian Statutory Payroll Engine (KWSP / SOCSO / EIS / PCB)."""

import math
from typing import Any, Dict, Optional


class MalaysianPayrollEngine:
    """Computes statutory contributions under Malaysian Employment Act regulations."""

    SOCSO_WAGE_CAP: float = 6000.00
    EIS_WAGE_CAP: float = 6000.00
    EPF_SALARY_THRESHOLD_FOR_13_PERCENT: float = 5000.00

    def calculate_epf(self, gross_salary: float, employee_rate: float = 11.00) -> Dict[str, float]:
        """
        Calculate EPF (KWSP) contributions.
        Employer: 13% if wage <= RM 5,000; 12% if wage > RM 5,000.
        Employee: 11% default.
        """
        if gross_salary <= 0:
            return {"employee": 0.00, "employer": 0.00, "employee_rate": employee_rate, "employer_rate": 13.00}

        employer_rate = 13.00 if gross_salary <= self.EPF_SALARY_THRESHOLD_FOR_13_PERCENT else 12.00

        employee_contrib = round(gross_salary * (employee_rate / 100.0), 2)
        employer_contrib = round(gross_salary * (employer_rate / 100.0), 2)

        return {
            "employee": employee_contrib,
            "employer": employer_contrib,
            "employee_rate": employee_rate,
            "employer_rate": employer_rate,
        }

    def calculate_socso(self, gross_salary: float) -> Dict[str, float]:
        """
        Calculate SOCSO (PERKESO) contributions based on Malaysian SOCSO Schedule.
        Capped at RM 6,000.
        """
        if gross_salary <= 0:
            return {"employee": 0.00, "employer": 0.00}

        capped_wage = min(gross_salary, self.SOCSO_WAGE_CAP)

        if capped_wage >= 6000.00:
            return {"employee": 29.75, "employer": 104.15}

        bracket_index = math.floor(capped_wage / 100.0)
        employee = round(max(0.25, (bracket_index * 0.50) - 0.25), 2)
        employer = round(max(0.75, (bracket_index * 1.75) - 0.85), 2)

        return {
            "employee": employee,
            "employer": employer,
        }

    def calculate_eis(self, gross_salary: float) -> Dict[str, float]:
        """
        Calculate EIS (SIP) contributions based on Malaysian EIS Schedule.
        Capped at RM 6,000.
        """
        if gross_salary <= 0:
            return {"employee": 0.00, "employer": 0.00}

        capped_wage = min(gross_salary, self.EIS_WAGE_CAP)

        if capped_wage >= 6000.00:
            return {"employee": 11.90, "employer": 11.90}

        bracket_index = math.floor(capped_wage / 100.0)
        contribution = round(max(0.05, (bracket_index * 0.20) - 0.10), 2)

        return {
            "employee": contribution,
            "employer": contribution,
        }

    def calculate_pcb(
        self, gross_salary: float, epf_employee: float, reliefs: Optional[Dict[str, Any]] = None
    ) -> float:
        """
        Calculate PCB (Monthly Tax Deduction) using Malaysian computerized progressive tax scale.
        """
        if gross_salary <= 0:
            return 0.00

        annual_gross = gross_salary * 12.0
        individual_relief = 9000.00
        epf_relief = min(epf_employee * 12.0, 4000.00)

        additional_relief = float((reliefs or {}).get("additional", 0.0))
        total_relief = individual_relief + epf_relief + additional_relief
        chargeable_income = max(0.00, annual_gross - total_relief)

        if chargeable_income <= 5000.00:
            return 0.00

        brackets = [
            (5000.0, 20000.0, 0.01),
            (20000.0, 35000.0, 0.03),
            (35000.0, 50000.0, 0.06),
            (50000.0, 70000.0, 0.11),
            (70000.0, 100000.0, 0.19),
            (100000.0, 400000.0, 0.25),
            (400000.0, 600000.0, 0.26),
            (600000.0, 2000000.0, 0.28),
            (2000000.0, float("inf"), 0.30),
        ]

        annual_tax = 0.00
        for lower, upper, rate in brackets:
            if chargeable_income > lower:
                taxable_chunk = min(chargeable_income, upper) - lower
                annual_tax += taxable_chunk * rate

        # Section 6A Tax Rebate (RM 400 for resident individual with chargeable income <= RM 35,000)
        if chargeable_income <= 35000.00:
            annual_tax = max(0.00, annual_tax - 400.00)

        return round(max(0.00, annual_tax / 12.0), 2)

    def calculate_payslip(self, basic_salary: float, options: Optional[Dict[str, Any]] = None) -> Dict[str, float]:
        """Compute full monthly payslip breakdown for an employee."""
        options = options or {}
        epf_rate = float(options.get("epf_rate", 11.00))

        epf = self.calculate_epf(basic_salary, epf_rate)
        socso = self.calculate_socso(basic_salary)
        eis = self.calculate_eis(basic_salary)
        pcb = self.calculate_pcb(basic_salary, epf["employee"], options.get("reliefs"))

        total_employee_deductions = epf["employee"] + socso["employee"] + eis["employee"] + pcb
        net_pay = round(max(0.00, basic_salary - total_employee_deductions), 2)

        return {
            "gross_pay": round(basic_salary, 2),
            "epf_employee": epf["employee"],
            "epf_employer": epf["employer"],
            "socso_employee": socso["employee"],
            "socso_employer": socso["employer"],
            "eis_employee": eis["employee"],
            "eis_employer": eis["employer"],
            "pcb_amount": pcb,
            "net_pay": net_pay,
        }
