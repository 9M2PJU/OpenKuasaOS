<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HrAnnouncement extends Model
{
    use BelongsToTenant, HasFactory, HasUuids;

    protected $table = 'hr_announcements';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'org_id',
        'author_id',
        'author_name',
        'title',
        'content',
        'category',
        'status',
        'effective_date',
    ];

    protected function casts(): array
    {
        return [
            'effective_date' => 'date',
        ];
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    /**
     * Check if the announcement's date is in the past.
     * "Lepas tarikh" means strictly before today.
     */
    public function isPast(): bool
    {
        if (! $this->effective_date) {
            return false;
        }

        return Carbon::parse($this->effective_date)->startOfDay()->lt(Carbon::today());
    }

    /**
     * Determine whether the announcement can be edited or deleted.
     * Constraint: Cannot edit/remove if date has passed.
     */
    public function canBeModified(): bool
    {
        return ! $this->isPast();
    }
}
