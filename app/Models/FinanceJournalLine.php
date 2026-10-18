<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinanceJournalLine extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'finance_journal_lines';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'journal_id',
        'ledger_account_id',
        'description',
        'debit',
        'credit',
    ];

    protected function casts(): array
    {
        return [
            'debit' => 'decimal:2',
            'credit' => 'decimal:2',
        ];
    }

    public function journal(): BelongsTo
    {
        return $this->belongsTo(FinanceJournal::class, 'journal_id');
    }

    public function ledgerAccount(): BelongsTo
    {
        return $this->belongsTo(FinanceLedgerAccount::class, 'ledger_account_id');
    }
}
