<?php

namespace App\Models\Traits;

use App\Models\Org;
use App\Models\Scopes\TenantScope;
use App\Services\Tenancy\OrganizationContext;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToTenant
{
    /**
     * Boot the trait and register global tenant scope & creating event.
     */
    public static function bootBelongsToTenant(): void
    {
        static::addGlobalScope(new TenantScope());

        static::creating(function ($model) {
            if (empty($model->org_id) && OrganizationContext::getId() !== null) {
                $model->org_id = OrganizationContext::getId();
            }
        });
    }

    /**
     * Get the tenant organization this entity belongs to.
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Org::class, 'org_id');
    }
}
