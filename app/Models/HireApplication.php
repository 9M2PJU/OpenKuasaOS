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
        'birth_date',
        'address',
        'cv_file_url',
        'cover_letter',
        'stage', // applied, screening, interview, offered, hired, rejected
        'status',
        'ai_score',
        'ai_status', // layak, pertimbangan, tidak_layak
        'ai_analysis',
        'ai_reviewed_at',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'ai_score' => 'integer',
        'ai_analysis' => 'array',
        'ai_reviewed_at' => 'datetime',
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

    public function getAgeAttribute(): ?int
    {
        return $this->birth_date ? $this->birth_date->age : null;
    }

    public function getAiStatusLabelAttribute(): string
    {
        return match ($this->ai_status) {
            'layak' => 'Layak',
            'pertimbangan' => 'Pertimbangan',
            'tidak_layak' => 'Tidak Layak',
            default => 'Belum Disaring',
        };
    }

    public function getAiBadgeClassAttribute(): string
    {
        return match ($this->ai_status) {
            'layak' => 'bg-emerald-950/40 text-emerald-400 border-emerald-500/30',
            'pertimbangan' => 'bg-amber-950/40 text-amber-400 border-amber-500/30',
            'tidak_layak' => 'bg-rose-950/40 text-rose-400 border-rose-500/30',
            default => 'bg-slate-800/80 text-slate-400 border-slate-700/50',
        };
    }
}
