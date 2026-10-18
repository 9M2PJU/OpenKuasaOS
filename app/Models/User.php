<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, HasRoles, HasUuids, Notifiable;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'name',
        'email',
        'password',
        'locale',
        'is_superadmin',
        'is_demo',
        'status',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_superadmin' => 'boolean',
            'is_demo' => 'boolean',
        ];
    }

    public function isSuperadmin(): bool
    {
        return (bool) $this->is_superadmin;
    }

    public function isDemo(): bool
    {
        return (bool) $this->is_demo;
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function scopeSuperadmins($query)
    {
        return $query->where('is_superadmin', true);
    }

    public function scopeDemoUsers($query)
    {
        return $query->where('is_demo', true);
    }

    public function scopeRealUsers($query)
    {
        return $query->where('is_demo', false);
    }

    public function profile(): HasOne
    {
        return $this->hasOne(Profile::class);
    }

    public function orgMembers(): HasMany
    {
        return $this->hasMany(OrgMember::class);
    }

    public function employee(): HasOne
    {
        return $this->hasOne(HrEmployee::class);
    }

    public function orgs(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Org::class, 'org_members', 'user_id', 'org_id')
            ->withPivot('role');
    }
}
