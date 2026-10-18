<?php

namespace Tests\Unit;

use App\Services\Finance\SstService;
use PHPUnit\Framework\TestCase;

class SstCalculationTest extends TestCase
{
    /**
     * Test SST 6% calculation on services.
     */
    public function test_sst_6_percent_calculation(): void
    {
        $subtotal = 1000.00;
        $sstAmount = SstService::calculateSst($subtotal, 6.00);

        $this->assertEquals(60.00, $sstAmount);

        $totalResult = SstService::calculateTotal($subtotal, 6.00);
        $this->assertEquals(1000.00, $totalResult['subtotal']);
        $this->assertEquals(6.00, $totalResult['sst_rate']);
        $this->assertEquals(60.00, $totalResult['sst_amount']);
        $this->assertEquals(1060.00, $totalResult['total_amount']);
    }

    /**
     * Test SST 8% calculation on standard supply of taxable services.
     */
    public function test_sst_8_percent_calculation(): void
    {
        $subtotal = 2500.00;
        $sstAmount = SstService::calculateSst($subtotal, 8.00);

        $this->assertEquals(200.00, $sstAmount);

        $totalResult = SstService::calculateTotal($subtotal, 8.00);
        $this->assertEquals(2500.00, $totalResult['subtotal']);
        $this->assertEquals(8.00, $totalResult['sst_rate']);
        $this->assertEquals(200.00, $totalResult['sst_amount']);
        $this->assertEquals(2700.00, $totalResult['total_amount']);
    }

    /**
     * Test rounding precision for fractional amounts.
     */
    public function test_sst_fractional_rounding(): void
    {
        $subtotal = 125.45;
        // 125.45 * 0.08 = 10.036 -> rounds to 10.04
        $sstAmount = SstService::calculateSst($subtotal, 8.00);
        $this->assertEquals(10.04, $sstAmount);

        $totalResult = SstService::calculateTotal($subtotal, 8.00);
        $this->assertEquals(135.49, $totalResult['total_amount']);
    }

    /**
     * Test 0% rate and negative/zero boundaries.
     */
    public function test_sst_zero_rate(): void
    {
        $subtotal = 500.00;
        $totalResult = SstService::calculateTotal($subtotal, 0.00);

        $this->assertEquals(0.00, $totalResult['sst_amount']);
        $this->assertEquals(500.00, $totalResult['total_amount']);
    }
}
