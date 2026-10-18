<?php

namespace App\Services\Finance;

use App\Exceptions\UnbalancedJournalException;
use App\Models\FinanceAccount;
use App\Models\FinanceExpense;
use App\Models\FinanceInvoice;
use App\Models\FinanceInvoiceLine;
use App\Models\FinanceJournal;
use App\Models\FinanceJournalLine;
use App\Models\FinanceLedgerAccount;
use App\Models\FinanceQuotation;
use App\Models\FinanceQuotationLine;
use App\Models\FinanceReceipt;
use Exception;
use Illuminate\Support\Facades\DB;

class BendaharaPostingService
{
    /**
     * Get or create standard system chart of accounts for the organization.
     */
    public function getOrCreateLedgerAccount(string $orgId, string $code, string $name, string $type): FinanceLedgerAccount
    {
        return FinanceLedgerAccount::withoutGlobalScopes()
            ->firstOrCreate(
                ['org_id' => $orgId, 'code' => $code],
                ['name' => $name, 'type' => $type, 'balance' => 0.00]
            );
    }

    /**
     * Generate an atomic sequential invoice number for the organization.
     */
    public function generateNextInvoiceNumber(string $orgId): string
    {
        $year = now()->format('Y');
        $prefix = "INV-{$year}-";

        // Fetch highest number with lock
        $latest = FinanceInvoice::withoutGlobalScopes()
            ->where('org_id', $orgId)
            ->where('invoice_number', 'LIKE', "{$prefix}%")
            ->lockForUpdate()
            ->orderByRaw('LENGTH(invoice_number) DESC, invoice_number DESC')
            ->value('invoice_number');

        $nextNumber = 1;
        if ($latest && preg_match('/INV-\d{4}-(\d+)/', $latest, $matches)) {
            $nextNumber = ((int) $matches[1]) + 1;
        }

        return sprintf('%s%04d', $prefix, $nextNumber);
    }

    /**
     * Generate an atomic sequential receipt number for the organization.
     */
    public function generateNextReceiptNumber(string $orgId): string
    {
        $year = now()->format('Y');
        $prefix = "RCP-{$year}-";

        $latest = FinanceReceipt::withoutGlobalScopes()
            ->where('org_id', $orgId)
            ->where('receipt_number', 'LIKE', "{$prefix}%")
            ->lockForUpdate()
            ->orderByRaw('LENGTH(receipt_number) DESC, receipt_number DESC')
            ->value('receipt_number');

        $nextNumber = 1;
        if ($latest && preg_match('/RCP-\d{4}-(\d+)/', $latest, $matches)) {
            $nextNumber = ((int) $matches[1]) + 1;
        }

        return sprintf('%s%04d', $prefix, $nextNumber);
    }

    /**
     * Generate an atomic sequential payment voucher number for the organization.
     */
    public function generateNextVoucherNumber(string $orgId): string
    {
        $year = now()->format('Y');
        $prefix = "PV-{$year}-";

        $latest = FinanceExpense::withoutGlobalScopes()
            ->where('org_id', $orgId)
            ->where('voucher_number', 'LIKE', "{$prefix}%")
            ->lockForUpdate()
            ->orderByRaw('LENGTH(voucher_number) DESC, voucher_number DESC')
            ->value('voucher_number');

        $nextNumber = 1;
        if ($latest && preg_match('/PV-\d{4}-(\d+)/', $latest, $matches)) {
            $nextNumber = ((int) $matches[1]) + 1;
        }

        return sprintf('%s%04d', $prefix, $nextNumber);
    }

    /**
     * Generate an atomic sequential quotation number for the organization.
     */
    public function generateNextQuotationNumber(string $orgId): string
    {
        $year = now()->format('Y');
        $prefix = "QT-{$year}-";

        $latest = FinanceQuotation::withoutGlobalScopes()
            ->where('org_id', $orgId)
            ->where('quotation_number', 'LIKE', "{$prefix}%")
            ->lockForUpdate()
            ->orderByRaw('LENGTH(quotation_number) DESC, quotation_number DESC')
            ->value('quotation_number');

        $nextNumber = 1;
        if ($latest && preg_match('/QT-\d{4}-(\d+)/', $latest, $matches)) {
            $nextNumber = ((int) $matches[1]) + 1;
        }

        return sprintf('%s%04d', $prefix, $nextNumber);
    }

    /**
     * Convert an accepted quotation directly into a customer invoice.
     */
    public function convertQuotationToInvoice(FinanceQuotation $quotation, array $options = []): FinanceInvoice
    {
        return DB::transaction(function () use ($quotation, $options) {
            $lockedQuotation = FinanceQuotation::withoutGlobalScopes()
                ->where('id', $quotation->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedQuotation->status === 'converted' && $lockedQuotation->converted_invoice_id) {
                return FinanceInvoice::withoutGlobalScopes()->findOrFail($lockedQuotation->converted_invoice_id);
            }

            $issueDate = $options['issue_date'] ?? now()->toDateString();
            $dueDate = $options['due_date'] ?? now()->addDays(30)->toDateString();

            // Create Invoice
            $invoice = FinanceInvoice::withoutGlobalScopes()->create([
                'org_id' => $lockedQuotation->org_id,
                'customer_id' => $lockedQuotation->customer_id,
                'quotation_id' => $lockedQuotation->id,
                'customer_name' => $lockedQuotation->customer_name,
                'customer_email' => $lockedQuotation->customer_email,
                'customer_phone' => $lockedQuotation->customer_phone,
                'issue_date' => $issueDate,
                'due_date' => $dueDate,
                'status' => 'draft',
                'subtotal' => $lockedQuotation->subtotal,
                'sst_rate' => $lockedQuotation->sst_rate,
                'sst_amount' => $lockedQuotation->sst_amount,
                'total_amount' => $lockedQuotation->total_amount,
                'amount_paid' => 0.00,
                'e_invoice_status' => 'none',
                'notes' => $lockedQuotation->notes,
            ]);

            // Copy lines
            foreach ($lockedQuotation->lines as $line) {
                FinanceInvoiceLine::withoutGlobalScopes()->create([
                    'org_id' => $lockedQuotation->org_id,
                    'invoice_id' => $invoice->id,
                    'description' => $line->description,
                    'quantity' => $line->quantity,
                    'unit_price' => $line->unit_price,
                    'total' => $line->total,
                ]);
            }

            // Update Quotation status
            $lockedQuotation->status = 'converted';
            $lockedQuotation->converted_invoice_id = $invoice->id;
            $lockedQuotation->save();

            // Optionally post invoice immediately
            if (!empty($options['post_immediately'])) {
                $invoice = $this->postInvoice($invoice);
            }

            return $invoice;
        });
    }

    /**
     * Post a draft invoice, assign sequence number, and create double-entry journal.
     */
    public function postInvoice(FinanceInvoice $invoice): FinanceInvoice
    {
        return DB::transaction(function () use ($invoice) {
            $lockedInvoice = FinanceInvoice::withoutGlobalScopes()
                ->where('id', $invoice->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedInvoice->status !== 'draft') {
                throw new Exception("Invois dengan status [{$lockedInvoice->status}] tidak boleh dipos.");
            }

            // Generate sequential invoice number if not set
            if (empty($lockedInvoice->invoice_number)) {
                $lockedInvoice->invoice_number = $this->generateNextInvoiceNumber($lockedInvoice->org_id);
            }

            // Ensure ledger accounts exist
            $arAccount = $this->getOrCreateLedgerAccount($lockedInvoice->org_id, '1200', 'Akaun Belum Terima (AR)', 'asset');
            $salesAccount = $this->getOrCreateLedgerAccount($lockedInvoice->org_id, '4000', 'Hasil Jualan', 'revenue');
            $sstAccount = $this->getOrCreateLedgerAccount($lockedInvoice->org_id, '2200', 'SST Kena Bayar', 'liability');

            $totalAmount = (float) $lockedInvoice->total_amount;
            $subtotal = (float) $lockedInvoice->subtotal;
            $sstAmount = (float) $lockedInvoice->sst_amount;

            // Journal lines payload
            $lines = [
                [
                    'ledger_account_id' => $arAccount->id,
                    'description' => "Penghutang Invois {$lockedInvoice->invoice_number}",
                    'debit' => $totalAmount,
                    'credit' => 0.00,
                ],
                [
                    'ledger_account_id' => $salesAccount->id,
                    'description' => "Hasil Jualan Invois {$lockedInvoice->invoice_number}",
                    'debit' => 0.00,
                    'credit' => $subtotal,
                ],
            ];

            if ($sstAmount > 0) {
                $lines[] = [
                    'ledger_account_id' => $sstAccount->id,
                    'description' => "Cukai SST Invois {$lockedInvoice->invoice_number}",
                    'debit' => 0.00,
                    'credit' => $sstAmount,
                ];
            }

            // Validate balanced ledger
            $this->validateJournalBalance($lines);

            // Create Journal
            $journal = FinanceJournal::withoutGlobalScopes()->create([
                'org_id' => $lockedInvoice->org_id,
                'reference_type' => 'invoice',
                'reference_id' => $lockedInvoice->id,
                'reference_number' => $lockedInvoice->invoice_number,
                'entry_date' => $lockedInvoice->issue_date,
                'description' => "Pos Invois {$lockedInvoice->invoice_number}",
                'status' => 'posted',
            ]);

            foreach ($lines as $line) {
                FinanceJournalLine::create([
                    'journal_id' => $journal->id,
                    'ledger_account_id' => $line['ledger_account_id'],
                    'description' => $line['description'],
                    'debit' => $line['debit'],
                    'credit' => $line['credit'],
                ]);
            }

            $lockedInvoice->status = 'posted';
            $lockedInvoice->save();

            return $lockedInvoice;
        });
    }

    /**
     * Record a payment receipt for an invoice.
     */
    public function recordPayment(FinanceInvoice $invoice, FinanceAccount $account, float $amount, array $meta = []): FinanceReceipt
    {
        return DB::transaction(function () use ($invoice, $account, $amount, $meta) {
            $lockedInvoice = FinanceInvoice::withoutGlobalScopes()
                ->where('id', $invoice->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (!in_array($lockedInvoice->status, ['posted', 'paid'])) {
                throw new Exception("Hanya invois yang telah dipos boleh menerima bayaran.");
            }

            $receiptNumber = $this->generateNextReceiptNumber($lockedInvoice->org_id);
            $paymentDate = $meta['payment_date'] ?? now()->toDateString();

            $receipt = FinanceReceipt::withoutGlobalScopes()->create([
                'org_id' => $lockedInvoice->org_id,
                'invoice_id' => $lockedInvoice->id,
                'account_id' => $account->id,
                'receipt_number' => $receiptNumber,
                'amount' => $amount,
                'payment_date' => $paymentDate,
                'payment_method' => $meta['payment_method'] ?? 'bank_transfer',
                'reference' => $meta['reference'] ?? null,
                'notes' => $meta['notes'] ?? null,
            ]);

            // Update Account balance
            $account->increment('balance', $amount);

            // Update Invoice amount paid & status
            $newPaid = (float) $lockedInvoice->amount_paid + $amount;
            $lockedInvoice->amount_paid = $newPaid;
            if ($newPaid >= (float) $lockedInvoice->total_amount) {
                $lockedInvoice->status = 'paid';
            }
            $lockedInvoice->save();

            // Journal Entry: Debit Bank/Cash, Credit Accounts Receivable
            $bankLedger = $this->getOrCreateLedgerAccount($lockedInvoice->org_id, '1000', 'Akaun Bank & Tunai', 'asset');
            $arLedger = $this->getOrCreateLedgerAccount($lockedInvoice->org_id, '1200', 'Akaun Belum Terima (AR)', 'asset');

            $lines = [
                [
                    'ledger_account_id' => $bankLedger->id,
                    'description' => "Bayaran masuk {$receiptNumber} ke akaun {$account->name}",
                    'debit' => $amount,
                    'credit' => 0.00,
                ],
                [
                    'ledger_account_id' => $arLedger->id,
                    'description' => "Penyelesaian penghutang melalui resit {$receiptNumber}",
                    'debit' => 0.00,
                    'credit' => $amount,
                ],
            ];

            $this->validateJournalBalance($lines);

            $journal = FinanceJournal::withoutGlobalScopes()->create([
                'org_id' => $lockedInvoice->org_id,
                'reference_type' => 'receipt',
                'reference_id' => $receipt->id,
                'reference_number' => $receiptNumber,
                'entry_date' => $paymentDate,
                'description' => "Terimaan bayaran {$receiptNumber} bagi Invois {$lockedInvoice->invoice_number}",
                'status' => 'posted',
            ]);

            foreach ($lines as $line) {
                FinanceJournalLine::create([
                    'journal_id' => $journal->id,
                    'ledger_account_id' => $line['ledger_account_id'],
                    'description' => $line['description'],
                    'debit' => $line['debit'],
                    'credit' => $line['credit'],
                ]);
            }

            return $receipt;
        });
    }

    /**
     * Void an invoice, reversing ledger journals if previously posted.
     */
    public function voidInvoice(FinanceInvoice $invoice, string $reason): FinanceInvoice
    {
        return DB::transaction(function () use ($invoice, $reason) {
            $lockedInvoice = FinanceInvoice::withoutGlobalScopes()
                ->where('id', $invoice->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedInvoice->status === 'void') {
                return $lockedInvoice;
            }

            if ((float) $lockedInvoice->amount_paid > 0) {
                throw new Exception("Invois yang telah mempunyai bayaran tidak boleh dibatalkan secara terus.");
            }

            if ($lockedInvoice->status === 'posted') {
                // Reversing journal
                $arAccount = $this->getOrCreateLedgerAccount($lockedInvoice->org_id, '1200', 'Akaun Belum Terima (AR)', 'asset');
                $salesAccount = $this->getOrCreateLedgerAccount($lockedInvoice->org_id, '4000', 'Hasil Jualan', 'revenue');
                $sstAccount = $this->getOrCreateLedgerAccount($lockedInvoice->org_id, '2200', 'SST Kena Bayar', 'liability');

                $totalAmount = (float) $lockedInvoice->total_amount;
                $subtotal = (float) $lockedInvoice->subtotal;
                $sstAmount = (float) $lockedInvoice->sst_amount;

                $lines = [
                    [
                        'ledger_account_id' => $salesAccount->id,
                        'description' => "Pembalikan Hasil Invois Batal {$lockedInvoice->invoice_number}",
                        'debit' => $subtotal,
                        'credit' => 0.00,
                    ],
                    [
                        'ledger_account_id' => $arAccount->id,
                        'description' => "Pembalikan Penghutang Invois Batal {$lockedInvoice->invoice_number}",
                        'debit' => 0.00,
                        'credit' => $totalAmount,
                    ],
                ];

                if ($sstAmount > 0) {
                    $lines[] = [
                        'ledger_account_id' => $sstAccount->id,
                        'description' => "Pembalikan SST Invois Batal {$lockedInvoice->invoice_number}",
                        'debit' => $sstAmount,
                        'credit' => 0.00,
                    ];
                }

                $this->validateJournalBalance($lines);

                $journal = FinanceJournal::withoutGlobalScopes()->create([
                    'org_id' => $lockedInvoice->org_id,
                    'reference_type' => 'invoice_void',
                    'reference_id' => $lockedInvoice->id,
                    'reference_number' => $lockedInvoice->invoice_number,
                    'entry_date' => now()->toDateString(),
                    'description' => "Pembatalan Invois {$lockedInvoice->invoice_number}: {$reason}",
                    'status' => 'void',
                ]);

                foreach ($lines as $line) {
                    FinanceJournalLine::create([
                        'journal_id' => $journal->id,
                        'ledger_account_id' => $line['ledger_account_id'],
                        'description' => $line['description'],
                        'debit' => $line['debit'],
                        'credit' => $line['credit'],
                    ]);
                }
            }

            $lockedInvoice->status = 'void';
            $lockedInvoice->notes = ($lockedInvoice->notes ? $lockedInvoice->notes . "\n" : '') . "Dibatalkan: {$reason}";
            $lockedInvoice->save();

            return $lockedInvoice;
        });
    }

    /**
     * Post an expense or capital voucher, updating bank balance and general ledger.
     */
    public function postExpense(FinanceExpense $expense): FinanceExpense
    {
        return DB::transaction(function () use ($expense) {
            $lockedExpense = FinanceExpense::withoutGlobalScopes()
                ->where('id', $expense->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedExpense->status === 'void') {
                throw new Exception("Baucar yang telah dibatalkan (void) tidak boleh dipos.");
            }

            if (empty($lockedExpense->voucher_number)) {
                $lockedExpense->voucher_number = $this->generateNextVoucherNumber($lockedExpense->org_id);
            }

            $account = FinanceAccount::withoutGlobalScopes()
                ->where('id', $lockedExpense->account_id)
                ->lockForUpdate()
                ->firstOrFail();

            $totalAmount = (float) $lockedExpense->total_amount;
            $subtotal = (float) $lockedExpense->subtotal;
            $taxAmount = (float) $lockedExpense->tax_amount;

            $bankLedger = $this->getOrCreateLedgerAccount($lockedExpense->org_id, '1000', 'Tunai & Bank', 'asset');

            if ($lockedExpense->expense_category === 'equity_injection') {
                // Owner introduces capital into business bank account -> Bank increases, Equity increases
                $equityLedger = $this->getOrCreateLedgerAccount($lockedExpense->org_id, '3000', 'Modal Pemilik / Sumbangan Modal', 'equity');

                $lines = [
                    [
                        'ledger_account_id' => $bankLedger->id,
                        'description' => "Kemasukan modal pemilik ke akaun {$account->name} [{$lockedExpense->voucher_number}]",
                        'debit' => $totalAmount,
                        'credit' => 0.00,
                    ],
                    [
                        'ledger_account_id' => $equityLedger->id,
                        'description' => "Modal Pemilik ({$lockedExpense->vendor_name}) [{$lockedExpense->voucher_number}]",
                        'debit' => 0.00,
                        'credit' => $totalAmount,
                    ],
                ];

                $account->increment('balance', $totalAmount);
            } else {
                // Outflow expense / purchase / asset acquisition
                $account->decrement('balance', $totalAmount);

                $expenseDebitAmount = $subtotal > 0 ? $subtotal : ($totalAmount - $taxAmount);

                $lines = [
                    [
                        'ledger_account_id' => $lockedExpense->ledger_account_id,
                        'description' => "{$lockedExpense->description} [{$lockedExpense->voucher_number}]",
                        'debit' => $expenseDebitAmount,
                        'credit' => 0.00,
                    ],
                ];

                if ($taxAmount > 0) {
                    $taxInputLedger = $this->getOrCreateLedgerAccount($lockedExpense->org_id, '1300', 'Cukai Input SST Belum Tuntut', 'asset');
                    $lines[] = [
                        'ledger_account_id' => $taxInputLedger->id,
                        'description' => "Cukai Input SST Pembekal ({$lockedExpense->vendor_name}) [{$lockedExpense->voucher_number}]",
                        'debit' => $taxAmount,
                        'credit' => 0.00,
                    ];
                }

                $lines[] = [
                    'ledger_account_id' => $bankLedger->id,
                    'description' => "Bayaran keluar dari akaun {$account->name} kepada {$lockedExpense->vendor_name} [{$lockedExpense->voucher_number}]",
                    'debit' => 0.00,
                    'credit' => $totalAmount,
                ];
            }

            $this->validateJournalBalance($lines);

            $journal = FinanceJournal::withoutGlobalScopes()->create([
                'org_id' => $lockedExpense->org_id,
                'reference_type' => 'expense',
                'reference_id' => $lockedExpense->id,
                'reference_number' => $lockedExpense->voucher_number,
                'entry_date' => $lockedExpense->payment_date,
                'description' => "Baucar Bayaran {$lockedExpense->voucher_number} - {$lockedExpense->vendor_name} ({$lockedExpense->getCategoryLabelAttribute()})",
                'status' => 'posted',
            ]);

            foreach ($lines as $line) {
                FinanceJournalLine::create([
                    'journal_id' => $journal->id,
                    'ledger_account_id' => $line['ledger_account_id'],
                    'description' => $line['description'],
                    'debit' => $line['debit'],
                    'credit' => $line['credit'],
                ]);
            }

            $lockedExpense->status = 'posted';
            $lockedExpense->save();

            return $lockedExpense;
        });
    }

    /**
     * Void an expense voucher and reverse double-entry ledger impact.
     */
    public function voidExpense(FinanceExpense $expense, string $reason): FinanceExpense
    {
        return DB::transaction(function () use ($expense, $reason) {
            $lockedExpense = FinanceExpense::withoutGlobalScopes()
                ->where('id', $expense->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedExpense->status === 'void') {
                return $lockedExpense;
            }

            $account = FinanceAccount::withoutGlobalScopes()
                ->where('id', $lockedExpense->account_id)
                ->lockForUpdate()
                ->firstOrFail();

            $totalAmount = (float) $lockedExpense->total_amount;
            $subtotal = (float) $lockedExpense->subtotal;
            $taxAmount = (float) $lockedExpense->tax_amount;
            $bankLedger = $this->getOrCreateLedgerAccount($lockedExpense->org_id, '1000', 'Tunai & Bank', 'asset');

            if ($lockedExpense->expense_category === 'equity_injection') {
                $equityLedger = $this->getOrCreateLedgerAccount($lockedExpense->org_id, '3000', 'Modal Pemilik / Sumbangan Modal', 'equity');

                $lines = [
                    [
                        'ledger_account_id' => $equityLedger->id,
                        'description' => "Pembalikan Modal Pemilik Batal [{$lockedExpense->voucher_number}]",
                        'debit' => $totalAmount,
                        'credit' => 0.00,
                    ],
                    [
                        'ledger_account_id' => $bankLedger->id,
                        'description' => "Pembalikan Kemasukan Bank Batal [{$lockedExpense->voucher_number}]",
                        'debit' => 0.00,
                        'credit' => $totalAmount,
                    ],
                ];

                $account->decrement('balance', $totalAmount);
            } else {
                $account->increment('balance', $totalAmount);
                $expenseCreditAmount = $subtotal > 0 ? $subtotal : ($totalAmount - $taxAmount);

                $lines = [
                    [
                        'ledger_account_id' => $bankLedger->id,
                        'description' => "Pemulangan Tunai Akaun Bank Bagi Baucar Batal [{$lockedExpense->voucher_number}]",
                        'debit' => $totalAmount,
                        'credit' => 0.00,
                    ],
                    [
                        'ledger_account_id' => $lockedExpense->ledger_account_id,
                        'description' => "Pembalikan Belanja Batal [{$lockedExpense->voucher_number}]",
                        'debit' => 0.00,
                        'credit' => $expenseCreditAmount,
                    ],
                ];

                if ($taxAmount > 0) {
                    $taxInputLedger = $this->getOrCreateLedgerAccount($lockedExpense->org_id, '1300', 'Cukai Input SST Belum Tuntut', 'asset');
                    $lines[] = [
                        'ledger_account_id' => $taxInputLedger->id,
                        'description' => "Pembalikan Cukai Input SST Batal [{$lockedExpense->voucher_number}]",
                        'debit' => 0.00,
                        'credit' => $taxAmount,
                    ];
                }
            }

            $this->validateJournalBalance($lines);

            $journal = FinanceJournal::withoutGlobalScopes()->create([
                'org_id' => $lockedExpense->org_id,
                'reference_type' => 'expense_void',
                'reference_id' => $lockedExpense->id,
                'reference_number' => $lockedExpense->voucher_number,
                'entry_date' => now()->toDateString(),
                'description' => "Pembatalan Baucar {$lockedExpense->voucher_number}: {$reason}",
                'status' => 'void',
            ]);

            foreach ($lines as $line) {
                FinanceJournalLine::create([
                    'journal_id' => $journal->id,
                    'ledger_account_id' => $line['ledger_account_id'],
                    'description' => $line['description'],
                    'debit' => $line['debit'],
                    'credit' => $line['credit'],
                ]);
            }

            $lockedExpense->status = 'void';
            $lockedExpense->description = ($lockedExpense->description ? $lockedExpense->description . "\n" : '') . "Dibatalkan: {$reason}";
            $lockedExpense->save();

            return $lockedExpense;
        });
    }

    /**
     * Ensure standard LHDN-compliant chart of accounts exists for the organization.
     */
    public function seedStandardChartOfAccounts(string $orgId): void
    {
        $accounts = [
            ['code' => '1000', 'name' => 'Tunai & Bank', 'type' => 'asset'],
            ['code' => '1100', 'name' => 'Akaun Belum Terima (AR)', 'type' => 'asset'],
            ['code' => '1300', 'name' => 'Cukai Input SST Belum Tuntut', 'type' => 'asset'],
            ['code' => '1500', 'name' => 'Aset Tetap & Peralatan Modal (CAPEX)', 'type' => 'asset'],
            ['code' => '2000', 'name' => 'Akaun Belum Bayar (AP / Pembekal)', 'type' => 'liability'],
            ['code' => '2100', 'name' => 'Cukai SST Belum Bayar', 'type' => 'liability'],
            ['code' => '3000', 'name' => 'Modal Pemilik / Sumbangan Modal', 'type' => 'equity'],
            ['code' => '4000', 'name' => 'Hasil Jualan & Perkhidmatan', 'type' => 'revenue'],
            ['code' => '5000', 'name' => 'Kos Barangan Dijual & Belian Stok (COGS)', 'type' => 'expense'],
            ['code' => '5100', 'name' => 'Kos Pengangkutan Masuk (Freight-in)', 'type' => 'expense'],
            ['code' => '6000', 'name' => 'Gaji & Upah Kakitangan', 'type' => 'expense'],
            ['code' => '6010', 'name' => 'Caruman Statutori (KWSP/PERKESO/SIP)', 'type' => 'expense'],
            ['code' => '6100', 'name' => 'Sewa Premis & Pejabat', 'type' => 'expense'],
            ['code' => '6200', 'name' => 'Utiliti & Telekomunikasi (TNB/Air/Internet)', 'type' => 'expense'],
            ['code' => '6300', 'name' => 'Pemasaran & Pengiklanan', 'type' => 'expense'],
            ['code' => '6400', 'name' => 'Perjalanan, Petrol & Pengangkutan', 'type' => 'expense'],
            ['code' => '6500', 'name' => 'Bekalan & Keperluan Pejabat', 'type' => 'expense'],
            ['code' => '6600', 'name' => 'Yuran Profesional, Guaman & Perakaunan', 'type' => 'expense'],
            ['code' => '6700', 'name' => 'Pembaikan & Penyelenggaraan', 'type' => 'expense'],
            ['code' => '6800', 'name' => 'Perbelanjaan Operasi Am', 'type' => 'expense'],
            ['code' => '6900', 'name' => 'Perbelanjaan Tidak Layak Tolak Cukai', 'type' => 'expense'],
        ];

        foreach ($accounts as $acc) {
            $this->getOrCreateLedgerAccount($orgId, $acc['code'], $acc['name'], $acc['type']);
        }
    }

    /**
     * Validate that debits == credits within 2 decimal precision.
     */
    public function validateJournalBalance(array $lines): void
    {
        $totalDebit = 0.00;
        $totalCredit = 0.00;

        foreach ($lines as $line) {
            $totalDebit += (float) ($line['debit'] ?? 0);
            $totalCredit += (float) ($line['credit'] ?? 0);
        }

        $totalDebit = round($totalDebit, 2);
        $totalCredit = round($totalCredit, 2);

        if (abs($totalDebit - $totalCredit) > 0.001) {
            throw new UnbalancedJournalException("Baki transaksi lejar tidak seimbang: Debit (RM {$totalDebit}) != Kredit (RM {$totalCredit}).");
        }
    }
}
