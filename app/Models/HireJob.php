<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HireJob extends Model
{
    use BelongsToTenant, HasFactory, HasUuids;

    protected $table = 'hire_jobs';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'org_id',
        'title',
        'department',
        'employment_type',
        'description',
        'status', // published, draft, closed
    ];

    public function applications(): HasMany
    {
        return $this->hasMany(HireApplication::class, 'job_id');
    }
}
