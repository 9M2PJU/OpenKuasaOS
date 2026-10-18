<?php

namespace App\Services\Superadmin;

use App\Models\Org;
use App\Services\Tenancy\OrganizationContext;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class ImpersonationService
{
    /**
     * Start impersonating an organization context for superadmin support.
     */
    public function impersonate(Org $org): void
    {
        $user = Auth::user();
        if (! $user || ! $user->isSuperadmin()) {
            abort(403, 'Hanya Superadmin boleh mengaktifkan mod bantuan organisasi.');
        }

        // Save original org context if not already stored
        if (! Session::has('original_org_id')) {
            $originalOrgId = Session::get('current_org_id') ?? OrganizationContext::getId();
            Session::put('original_org_id', $originalOrgId);
        }

        Session::put('impersonated_org_id', $org->id);
        Session::put('impersonated_by', $user->id);
        Session::put('impersonated_org_name', $org->name);
        Session::put('current_org_id', $org->id);

        OrganizationContext::set($org);
    }

    /**
     * Stop impersonating and return to superadmin's original organization.
     */
    public function leave(): ?string
    {
        $originalOrgId = Session::get('original_org_id');

        Session::forget('impersonated_org_id');
        Session::forget('impersonated_by');
        Session::forget('impersonated_org_name');
        Session::forget('original_org_id');

        if ($originalOrgId) {
            Session::put('current_org_id', $originalOrgId);
            OrganizationContext::setId($originalOrgId);
        } else {
            $user = Auth::user();
            $firstOrg = $user?->orgs()->first();
            if ($firstOrg) {
                Session::put('current_org_id', $firstOrg->id);
                OrganizationContext::set($firstOrg);
            }
        }

        return $originalOrgId;
    }

    /**
     * Check if current session is in impersonation mode.
     */
    public function isImpersonating(): bool
    {
        return Session::has('impersonated_org_id') && Auth::check() && Auth::user()->isSuperadmin();
    }

    /**
     * Get the impersonated organization if currently active.
     */
    public function getImpersonatedOrg(): ?Org
    {
        if (! $this->isImpersonating()) {
            return null;
        }

        $orgId = Session::get('impersonated_org_id');
        return $orgId ? Org::find($orgId) : null;
    }
}
