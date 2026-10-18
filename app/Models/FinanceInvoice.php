<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FinanceInvoice extends Model
{
    use BelongsToTenant, HasFactory, HasUuids;

    protected $table = 'finance_invoices';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'org_id',
        'customer_id',
        'quotation_id',
        'customer_name',
        'customer_email',
        'customer_phone',
        'invoice_number',
        'status', // draft, posted, paid, void
        'issue_date',
        'due_date',
        'subtotal',
        'sst_rate',
        'sst_amount',
        'total_amount',
        'amount_paid',
        'e_invoice_uuid',
        'e_invoice_status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'issue_date' => 'date',
            'due_date' => 'date',
            'subtotal' => 'decimal:2',
            'sst_rate' => 'decimal:2',
            'sst_amount' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'amount_paid' => 'decimal:2',
        ];
    }

    public function lines(): HasMany
    {
        return $this->hasMany(FinanceInvoiceLine::class, 'invoice_id');
    }

    public function receipts(): HasMany
    {
        return $this->hasMany(FinanceReceipt::class, 'invoice_id');
    }

    public function quotation(): BelongsTo
    {
        return $this->belongsTo(FinanceQuotation::class, 'quotation_id');
    }
}
