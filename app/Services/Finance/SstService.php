<?php

namespace App\Services\Finance;

class SstService
{
    /**
     * Calculate SST amount given subtotal and SST rate percentage.
     */
    public static function calculateSst(float $subtotal, float $sstRate): float
    {
        if ($sstRate <= 0 || $subtotal <= 0) {
            return 0.00;
        }

        return round($subtotal * ($sstRate / 100), 2);
    }

    /**
     * Calculate total with SST.
     */
    public static function calculateTotal(float $subtotal, float $sstRate): array
    {
        $sstAmount = self::calculateSst($subtotal, $sstRate);
        $total = round($subtotal + $sstAmount, 2);

        return [
            'subtotal' => round($subtotal, 2),
            'sst_rate' => round($sstRate, 2),
            'sst_amount' => $sstAmount,
            'total_amount' => $total,
        ];
    }
}
