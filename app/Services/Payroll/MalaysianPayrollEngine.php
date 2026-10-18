<?php

namespace App\Services\Payroll;

class MalaysianPayrollEngine
{
    public const SOCSO_WAGE_CAP = 6000.00;
    public const EIS_WAGE_CAP = 6000.00;
    public const EPF_SALARY_THRESHOLD_FOR_13_PERCENT = 5000.00;

    /**
     * Calculate EPF (KWSP) contributions.
     * Employer: 13% if wage <= 5000, 12% if wage > 5000.
     * Employee: 11% default (or customized rate).
     */
    public function calculateEpf(float $grossSalary, float $employeeRate = 11.00): array
    {
        if ($grossSalary <= 0) {
            return ['employee' => 0.00, 'employer' => 0.00];
        }

        $employerRate = ($grossSalary <= self::EPF_SALARY_THRESHOLD_FOR_13_PERCENT) ? 13.00 : 12.00;

        $employeeContribution = round($grossSalary * ($employeeRate / 100), 2);
        $employerContribution = round($grossSalary * ($employerRate / 100), 2);

        return [
            'employee' => $employeeContribution,
            'employer' => $employerContribution,
            'employee_rate' => $employeeRate,
            'employer_rate' => $employerRate,
        ];
    }

    /**
     * Calculate SOCSO (PERKESO) contributions based on Malaysian SOCSO Schedule.
     * Capped at RM 6,000. Employee roughly 0.5%, employer roughly 1.75%.
     */
    public function calculateSocso(float $grossSalary): array
    {
        if ($grossSalary <= 0) {
            return ['employee' => 0.00, 'employer' => 0.00];
        }

        $cappedWage = min($grossSalary, self::SOCSO_WAGE_CAP);

        // Official PERKESO Schedule: for RM 100 brackets, approx 0.5% employee and 1.75% employer
        // At exactly RM 3,000: employee = 14.75, employer = 51.65
        // At exactly RM 4,000: employee = 19.75, employer = 69.15
        // At RM 6,000 cap: employee = 29.75, employer = 104.15
        if ($cappedWage >= 6000.00) {
            return ['employee' => 29.75, 'employer' => 104.15];
        }

        // Bracket increment calculation for standard SOCSO table (bracket width RM 100)
        $bracketIndex = floor($cappedWage / 100);
        $employee = round(max(0.25, ($bracketIndex * 0.50) - 0.25), 2);
        $employer = round(max(0.75, ($bracketIndex * 1.75) - 0.85), 2);

        return [
            'employee' => $employee,
            'employer' => $employer,
        ];
    }

    /**
     * Calculate EIS (SIP) contributions based on Malaysian EIS Schedule.
     * Capped at RM 6,000. Employee 0.2%, employer 0.2%.
     */
    public function calculateEis(float $grossSalary): array
    {
        if ($grossSalary <= 0) {
            return ['employee' => 0.00, 'employer' => 0.00];
        }

        $cappedWage = min($grossSalary, self::EIS_WAGE_CAP);

        // Official EIS schedule:
        // RM 3,000: employee = 5.90, employer = 5.90
        // RM 6,000 (cap): employee = 11.90, employer = 11.90
        if ($cappedWage >= 6000.00) {
            return ['employee' => 11.90, 'employer' => 11.90];
        }

        $bracketIndex = floor($cappedWage / 100);
        $contribution = round(max(0.05, ($bracketIndex * 0.20) - 0.10), 2);

        return [
            'employee' => $contribution,
            'employer' => $contribution,
        ];
    }

    /**
     * Calculate PCB (Monthly Tax Deduction) using Malaysian computerized progressive tax scale.
     */
    public function calculatePcb(float $grossSalary, float $epfEmployee, array $reliefs = []): float
    {
        if ($grossSalary <= 0) {
            return 0.00;
        }

        $annualGross = $grossSalary * 12;

        // Individual relief: RM 9,000
        $individualRelief = 9000.00;

        // EPF relief capped at RM 4,000 annually
        $epfRelief = min($epfEmployee * 12, 4000.00);

        $totalRelief = $individualRelief + $epfRelief + (float) ($reliefs['additional'] ?? 0);
        $chargeableIncome = max(0.00, $annualGross - $totalRelief);

        if ($chargeableIncome <= 5000.00) {
            return 0.00;
        }

        $annualTax = 0.00;

        // Tax Brackets:
        // 0 - 5,000 : 0%
        // 5,001 - 20,000 : 1%
        // 20,001 - 35,000 : 3%
        // 35,001 - 50,000 : 6%
        // 50,001 - 70,000 : 11%
        // 70,001 - 100,000 : 19%
        // 100,001 - 400,000 : 25%
        // 400,001 - 600,000 : 26%
        // 600,001 - 2,000,000 : 28%
        // Above 2,000,000 : 30%

        $brackets = [
            [5000, 20000, 0.01],
            [20000, 35000, 0.03],
            [35000, 50000, 0.06],
            [50000, 70000, 0.11],
            [70000, 100000, 0.19],
            [100000, 400000, 0.25],
            [400000, 600000, 0.26],
            [600000, 2000000, 0.28],
            [2000000, PHP_FLOAT_MAX, 0.30],
        ];

        foreach ($brackets as [$lower, $upper, $rate]) {
            if ($chargeableIncome > $lower) {
                $taxableChunk = min($chargeableIncome, $upper) - $lower;
                $annualTax += $taxableChunk * $rate;
            }
        }

        // Section 6A Tax Rebate (RM 400 for resident individual with chargeable income <= RM 35,000)
        if ($chargeableIncome <= 35000.00) {
            $annualTax = max(0.00, $annualTax - 400.00);
        }

        return round(max(0.00, $annualTax / 12), 2);
    }

    /**
     * Compute full monthly payslip breakdown for an employee.
     */
    public function calculatePayslip(float $basicSalary, array $options = []): array
    {
        $epfRate = (float) ($options['epf_rate'] ?? 11.00);

        $epf = $this->calculateEpf($basicSalary, $epfRate);
        $socso = $this->calculateSocso($basicSalary);
        $eis = $this->calculateEis($basicSalary);
        $pcb = $this->calculatePcb($basicSalary, $epf['employee'], $options['reliefs'] ?? []);

        $totalEmployeeDeductions = $epf['employee'] + $socso['employee'] + $eis['employee'] + $pcb;
        $netPay = round(max(0.00, $basicSalary - $totalEmployeeDeductions), 2);

        return [
            'gross_pay' => round($basicSalary, 2),
            'epf_employee' => $epf['employee'],
            'epf_employer' => $epf['employer'],
            'socso_employee' => $socso['employee'],
            'socso_employer' => $socso['employer'],
            'eis_employee' => $eis['employee'],
            'eis_employer' => $eis['employer'],
            'pcb_amount' => $pcb,
            'net_pay' => $netPay,
        ];
    }

    /**
     * Calculate salary breakdown (alias of calculatePayslip).
     */
    public function calculateSalary(float $basicSalary, array $options = []): array
    {
        return $this->calculatePayslip($basicSalary, $options);
    }
}
