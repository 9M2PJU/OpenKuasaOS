<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FinanceExpense extends Model
{
    use BelongsToTenant, HasFactory, HasUuids;

    protected $table = 'finance_expenses';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'org_id',
        'account_id',
        'ledger_account_id',
        'voucher_number',
        'expense_category',
        'tax_classification',
        'vendor_name',
        'vendor_tin',
        'vendor_brn',
        'payment_date',
        'subtotal',
        'tax_rate',
        'tax_amount',
        'total_amount',
        'payment_method',
        'reference_number',
        'e_invoice_uuid',
        'e_invoice_status',
        'status',
        'receipt_url',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'payment_date' => 'date',
            'subtotal' => 'decimal:2',
            'tax_rate' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'total_amount' => 'decimal:2',
        ];
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(FinanceAccount::class, 'account_id');
    }

    public function ledgerAccount(): BelongsTo
    {
        return $this->belongsTo(FinanceLedgerAccount::class, 'ledger_account_id');
    }

    public function journals(): HasMany
    {
        return $this->hasMany(FinanceJournal::class, 'reference_id')
            ->where('reference_type', 'expense');
    }

    public function getCategoryLabelAttribute(): string
    {
        return match ($this->expense_category) {
            'cogs' => 'Kos Barangan / Stok Niaga (COGS)',
            'opex' => 'Belanja Operasi (OPEX)',
            'capex' => 'Aset Modal (CAPEX - Elaun Modal)',
            'equity_injection' => 'Suntikan Modal Pemilik (Ekuiti)',
            'non_deductible' => 'Tidak Layak Tolak Cukai',
            default => ucfirst($this->expense_category),
        };
    }

    public function getTaxClassificationLabelAttribute(): string
    {
        return match ($this->tax_classification) {
            'cogs' => 'Kos Barangan Jualan (Seksyen 33(1))',
            'deductible' => 'Boleh Tolak Cukai Penuh (Seksyen 33(1))',
            'capital_allowance' => 'Tuntutan Elaun Modal (Jadual 3 ACP 1967)',
            'non_deductible' => 'Tidak Boleh Ditolak Cukai',
            'equity' => 'Bukan Hasil / Modal Dikecualikan Cukai',
            default => ucfirst($this->tax_classification),
        };
    }
}
