<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FinanceLedgerAccount extends Model
{
    use BelongsToTenant, HasFactory, HasUuids;

    protected $table = 'finance_ledger_accounts';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'org_id',
        'code',
        'name',
        'type', // asset, liability, equity, revenue, expense
        'parent_id',
        'balance',
    ];

    protected function casts(): array
    {
        return [
            'balance' => 'decimal:2',
        ];
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(FinanceLedgerAccount::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(FinanceLedgerAccount::class, 'parent_id');
    }

    public function journalLines(): HasMany
    {
        return $this->hasMany(FinanceJournalLine::class, 'ledger_account_id');
    }
}
