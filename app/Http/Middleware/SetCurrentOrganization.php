<?php

namespace App\Http\Middleware;

use App\Models\Org;
use App\Models\OrgMember;
use App\Services\Tenancy\OrganizationContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetCurrentOrganization
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $orgId = $request->header('X-Organization-Id')
            ?? ($request->hasSession() ? $request->session()->get('current_org_id') : null)
            ?? $request->route('org_id')
            ?? $request->query('org_id');

        $user = $request->user();

        if ($user) {
            $isImpersonating = $request->hasSession() && $request->session()->has('impersonated_org_id') && $user->isSuperadmin();
            view()->share('isImpersonating', $isImpersonating);

            if ($isImpersonating) {
                $impersonatedOrgId = $request->session()->get('impersonated_org_id');
                $targetOrg = Org::find($impersonatedOrgId);
                if ($targetOrg) {
                    OrganizationContext::set($targetOrg);
                    $userOrgs = $user->orgs()->get();
                    view()->share('currentOrg', $targetOrg);
                    view()->share('userOrgs', $userOrgs);
                    view()->share('impersonatedOrg', $targetOrg);
                    return $next($request);
                }
            }

            if ($orgId) {
                $membership = OrgMember::where('org_id', $orgId)
                    ->where('user_id', $user->id)
                    ->first();

                if (!$membership) {
                    if ($request->expectsJson() || $request->is('api/*')) {
                        return response()->json([
                            'success' => false,
                            'error' => [
                                'code' => 'ORGANIZATION_FORBIDDEN',
                                'message' => 'Anda tiada akses kepada organisasi yang dipilih.',
                            ],
                        ], 403);
                    }
                    abort(403, 'Anda tiada akses kepada organisasi yang dipilih.');
                }

                OrganizationContext::setId($orgId);
                if ($request->hasSession()) {
                    $request->session()->put('current_org_id', $orgId);
                }
            } else {
                // Pick user's primary/first organization
                $membership = OrgMember::where('user_id', $user->id)->first();
                if ($membership) {
                    OrganizationContext::setId($membership->org_id);
                    if ($request->hasSession()) {
                        $request->session()->put('current_org_id', $membership->org_id);
                    }
                }
            }

            $currentOrg = OrganizationContext::get();
            if ($currentOrg) {
                $userOrgs = $user->orgs()->get();
                view()->share('currentOrg', $currentOrg);
                view()->share('userOrgs', $userOrgs);
            }
        } elseif ($orgId) {
            // For public / unauthenticated endpoints that might be scoped by org
            $orgExists = Org::where('id', $orgId)->exists();
            if ($orgExists) {
                OrganizationContext::setId($orgId);
            }
        }

        return $next($request);
    }
}
