<?php

namespace Tests\Unit;

use App\Exceptions\UnbalancedJournalException;
use App\Services\Finance\BendaharaPostingService;
use PHPUnit\Framework\TestCase;

class BendaharaPostingValidationTest extends TestCase
{
    protected BendaharaPostingService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new BendaharaPostingService();
    }

    /**
     * Test that balanced debits and credits pass validation without exception.
     */
    public function test_balanced_journal_passes_validation(): void
    {
        $lines = [
            ['debit' => 1080.00, 'credit' => 0.00],
            ['debit' => 0.00, 'credit' => 1000.00],
            ['debit' => 0.00, 'credit' => 80.00],
        ];

        // Should not throw
        $this->service->validateJournalBalance($lines);
        $this->assertTrue(true);
    }

    /**
     * Test that unbalanced debits and credits throw UnbalancedJournalException.
     */
    public function test_unbalanced_journal_throws_exception(): void
    {
        $this->expectException(UnbalancedJournalException::class);

        $lines = [
            ['debit' => 1000.00, 'credit' => 0.00],
            ['debit' => 0.00, 'credit' => 950.00], // Off by RM 50.00
        ];

        $this->service->validateJournalBalance($lines);
    }
}
