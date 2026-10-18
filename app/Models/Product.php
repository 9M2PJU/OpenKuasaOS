<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model
{
    use BelongsToTenant, HasFactory, HasUuids;

    protected $table = 'products';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'org_id',
        'name',
        'sku',
        'category',
        'price',
        'cost_price',
        'stock',
        'description',
        'usp',
        'target_audience',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'cost_price' => 'decimal:2',
            'stock' => 'integer',
        ];
    }

    public function org(): BelongsTo
    {
        return $this->belongsTo(Org::class, 'org_id');
    }
}
