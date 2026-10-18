<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FinanceQuotation extends Model
{
    use BelongsToTenant, HasFactory, HasUuids;

    protected $table = 'finance_quotations';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'org_id',
        'customer_id',
        'customer_name',
        'customer_email',
        'customer_phone',
        'quotation_number',
        'status', // draft, sent, accepted, rejected, converted
        'issue_date',
        'valid_until',
        'subtotal',
        'sst_rate',
        'sst_amount',
        'total_amount',
        'converted_invoice_id',
        'notes',
        'terms',
    ];

    protected function casts(): array
    {
        return [
            'issue_date' => 'date',
            'valid_until' => 'date',
            'subtotal' => 'decimal:2',
            'sst_rate' => 'decimal:2',
            'sst_amount' => 'decimal:2',
            'total_amount' => 'decimal:2',
        ];
    }

    public function lines(): HasMany
    {
        return $this->hasMany(FinanceQuotationLine::class, 'quotation_id');
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(FinanceInvoice::class, 'converted_invoice_id');
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'draft' => 'Draf',
            'sent' => 'Dihantar',
            'accepted' => 'Diterima',
            'rejected' => 'Ditolak',
            'converted' => 'Ditukar ke Invois',
            default => ucfirst($this->status),
        };
    }
}
