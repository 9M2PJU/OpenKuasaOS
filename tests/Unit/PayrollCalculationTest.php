<?php

namespace Tests\Unit;

use App\Services\Payroll\MalaysianPayrollEngine;
use PHPUnit\Framework\TestCase;

class PayrollCalculationTest extends TestCase
{
    protected MalaysianPayrollEngine $engine;

    protected function setUp(): void
    {
        parent::setUp();
        $this->engine = new MalaysianPayrollEngine();
    }

    /**
     * Test KWSP employee rate 11% and employer 13% for salary <= RM 5,000.
     */
    public function test_kwsp_contributions_for_salary_up_to_5000(): void
    {
        $salary = 4000.00;
        $epf = $this->engine->calculateEpf($salary, 11.00);

        // Employee 11%: 4000 * 0.11 = 440.00
        $this->assertEquals(440.00, $epf['employee']);

        // Employer 13%: 4000 * 0.13 = 520.00
        $this->assertEquals(520.00, $epf['employer']);
        $this->assertEquals(13.00, $epf['employer_rate']);
    }

    /**
     * Test KWSP employer rate drops to 12% for salary > RM 5,000.
     */
    public function test_kwsp_contributions_for_salary_above_5000(): void
    {
        $salary = 6000.00;
        $epf = $this->engine->calculateEpf($salary, 11.00);

        // Employee 11%: 6000 * 0.11 = 660.00
        $this->assertEquals(660.00, $epf['employee']);

        // Employer 12%: 6000 * 0.12 = 720.00
        $this->assertEquals(720.00, $epf['employer']);
        $this->assertEquals(12.00, $epf['employer_rate']);
    }

    /**
     * Test SOCSO contributions and cap at RM 6,000.
     */
    public function test_socso_contributions_and_statutory_cap(): void
    {
        // For RM 3,000 salary
        $socso3k = $this->engine->calculateSocso(3000.00);
        $this->assertGreaterThan(10.00, $socso3k['employee']);
        $this->assertGreaterThan(40.00, $socso3k['employer']);

        // For RM 6,000 cap
        $socso6k = $this->engine->calculateSocso(6000.00);
        $this->assertEquals(29.75, $socso6k['employee']);
        $this->assertEquals(104.15, $socso6k['employer']);

        // For RM 10,000 (must be capped at RM 6,000 rate)
        $socso10k = $this->engine->calculateSocso(10000.00);
        $this->assertEquals(29.75, $socso10k['employee']);
        $this->assertEquals(104.15, $socso10k['employer']);
    }

    /**
     * Test EIS contributions and cap at RM 6,000.
     */
    public function test_eis_contributions_and_statutory_cap(): void
    {
        // For RM 6,000 cap (0.2% = 11.90)
        $eis6k = $this->engine->calculateEis(6000.00);
        $this->assertEquals(11.90, $eis6k['employee']);
        $this->assertEquals(11.90, $eis6k['employer']);

        // For RM 12,000 (must be capped at RM 6,000 rate)
        $eis12k = $this->engine->calculateEis(12000.00);
        $this->assertEquals(11.90, $eis12k['employee']);
        $this->assertEquals(11.90, $eis12k['employer']);
    }

    /**
     * Test PCB monthly tax deduction calculation.
     */
    public function test_pcb_tax_deduction_progressive_scale(): void
    {
        // Low salary (RM 2,000) under personal tax relief (RM 9,000 / 12 = 750 + epf)
        // Chargeable income is 0 -> PCB must be 0.00
        $pcbLow = $this->engine->calculatePcb(2000.00, 220.00);
        $this->assertEquals(0.00, $pcbLow);

        // High salary (RM 10,000 / mo = RM 120,000 / year)
        // Chargeable income: 120k - 9k - 4k = 107k
        $pcbHigh = $this->engine->calculatePcb(10000.00, 1100.00);
        $this->assertGreaterThan(500.00, $pcbHigh);
    }

    /**
     * Test full payslip breakdown calculation.
     */
    public function test_full_payslip_calculation(): void
    {
        $salary = 4500.00;
        $payslip = $this->engine->calculatePayslip($salary);

        $this->assertEquals(4500.00, $payslip['gross_pay']);
        $this->assertEquals(495.00, $payslip['epf_employee']); // 11%
        $this->assertEquals(585.00, $payslip['epf_employer']); // 13%
        $this->assertGreaterThan(0.00, $payslip['socso_employee']);
        $this->assertGreaterThan(0.00, $payslip['eis_employee']);
        $this->assertGreaterThan(0.00, $payslip['net_pay']);

        // Assert net pay = gross - employee statutory deductions
        $expectedNet = round($payslip['gross_pay'] - $payslip['epf_employee'] - $payslip['socso_employee'] - $payslip['eis_employee'] - $payslip['pcb_amount'], 2);
        $this->assertEquals($expectedNet, $payslip['net_pay']);
    }
}
