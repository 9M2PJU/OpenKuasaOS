<?php

namespace App\Http\Controllers\Web\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\Org;
use App\Services\Superadmin\ImpersonationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SuperadminOrgController extends Controller
{
    public function __construct(
        protected ImpersonationService $impersonationService
    ) {}

    /**
     * Display a listing of all tenant organizations.
     */
    public function index(Request $request): View
    {
        $search = (string) $request->query('search', '');

        $query = Org::with(['members.user'])
            ->withCount(['members', 'employees', 'invoices'])
            ->orderBy('created_at', 'desc');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                    ->orWhere('slug', 'ilike', "%{$search}%");
            });
        }

        $organizations = $query->paginate(15)->withQueryString();
        $totalOrganizations = Org::count();

        return view('superadmin.organizations.index', compact('organizations', 'search', 'totalOrganizations'));
    }

    /**
     * Impersonate an organization context for technical support.
     */
    public function impersonate(string $id): RedirectResponse
    {
        $org = Org::findOrFail($id);

        $this->impersonationService->impersonate($org);

        return redirect()->route('dashboard')
            ->with('info', "Anda kini sedang melihat paparan organisasi: {$org->name} (Mod Bantuan Superadmin).");
    }

    /**
     * Leave impersonation and return to the superadmin master console.
     */
    public function leave(): RedirectResponse
    {
        $this->impersonationService->leave();

        return redirect()->route('superadmin.organizations.index')
            ->with('success', 'Anda telah berjaya keluar dari mod bantuan organisasi.');
    }

    public function leaveImpersonation(): RedirectResponse
    {
        return $this->leave();
    }
}
