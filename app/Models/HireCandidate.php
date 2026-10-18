<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HireCandidate extends Model
{
    use BelongsToTenant, HasFactory, HasUuids;

    protected $table = 'hire_candidates';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'org_id',
        'name',
        'email',
        'phone',
        'birth_date',
        'address',
        'notes',
    ];

    protected $casts = [
        'birth_date' => 'date',
    ];

    public function applications(): HasMany
    {
        return $this->hasMany(HireApplication::class, 'candidate_id');
    }
}
