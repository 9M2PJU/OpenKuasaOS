<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HireApplication extends Model
{
    use BelongsToTenant, HasFactory, HasUuids;

    protected $table = 'hire_applications';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'org_id',
        'job_id',
        'candidate_id',
        'candidate_name',
        'email',
        'phone',
        'cv_file_url',
        'stage', // applied, screening, interview, offered, hired, rejected
        'status',
    ];

    public function job(): BelongsTo
    {
        return $this->belongsTo(HireJob::class, 'job_id');
    }

    public function candidate(): BelongsTo
    {
        return $this->belongsTo(HireCandidate::class, 'candidate_id');
    }

    public function interviews(): HasMany
    {
        return $this->hasMany(HireInterview::class, 'application_id');
    }
}
