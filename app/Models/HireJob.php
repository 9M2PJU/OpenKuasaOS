<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class HireJob extends Model
{
    use BelongsToTenant, HasFactory, HasUuids;

    protected $table = 'hire_jobs';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'org_id',
        'title',
        'slug',
        'department',
        'employment_type',
        'description',
        'requirements',
        'salary_min',
        'salary_max',
        'status', // published, draft, closed
    ];

    protected $casts = [
        'salary_min' => 'decimal:2',
        'salary_max' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::creating(function (HireJob $job) {
            if (empty($job->slug) && !empty($job->title)) {
                $base = Str::slug($job->title);
                $job->slug = $base . '-' . substr(bin2hex(random_bytes(3)), 0, 5);
            }
        });
    }

    public function org(): BelongsTo
    {
        return $this->belongsTo(Org::class, 'org_id');
    }

    public function applications(): HasMany
    {
        return $this->hasMany(HireApplication::class, 'job_id');
    }

    public function getPublicApplyUrlAttribute(): string
    {
        return route('careers.show', $this->slug ?: $this->id);
    }
}
