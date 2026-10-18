<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Org extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'orgs';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'name',
        'slug',
        'logo_url',
    ];

    public function members(): HasMany
    {
        return $this->hasMany(OrgMember::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(FinanceInvoice::class);
    }

    public function accounts(): HasMany
    {
        return $this->hasMany(FinanceAccount::class);
    }

    public function employees(): HasMany
    {
        return $this->hasMany(HrEmployee::class);
    }

    public function deals(): HasMany
    {
        return $this->hasMany(CrmDeal::class);
    }

    public function jobs(): HasMany
    {
        return $this->hasMany(HireJob::class);
    }

    public function smtpSetting(): HasOne
    {
        return $this->hasOne(OrgSmtpSetting::class, 'org_id');
    }
}
