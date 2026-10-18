<?php

namespace App\Http\Controllers\Web\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class SuperadminUserController extends Controller
{
    /**
     * Display a listing of all users across the system.
     */
    public function index(Request $request): View
    {
        $search = (string) $request->query('search', '');
        $status = (string) $request->query('status', 'all');

        $query = User::with(['orgs', 'profile'])->orderBy('created_at', 'desc');

        // Search by name or email
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                    ->orWhere('email', 'ilike', "%{$search}%");
            });
        }

        // Filter by status / type
        if ($status !== '' && $status !== 'all') {
            if ($status === 'active') {
                $query->where(function ($q) {
                    $q->where('status', 'active')->orWhereNull('status');
                });
            } elseif ($status === 'suspended') {
                $query->where('status', 'suspended');
            } elseif ($status === 'demo') {
                $query->where('is_demo', true);
            } elseif ($status === 'superadmin') {
                $query->where('is_superadmin', true);
            }
        }

        $counts = [
            'all' => User::count(),
            'active' => User::where(function ($q) {
                $q->where('status', 'active')->orWhereNull('status');
            })->count(),
            'suspended' => User::where('status', 'suspended')->count(),
            'demo' => User::where('is_demo', true)->count(),
            'superadmin' => User::where('is_superadmin', true)->count(),
        ];

        $users = $query->paginate(15)->withQueryString();

        return view('superadmin.users.index', compact('users', 'search', 'status', 'counts'));
    }

    /**
     * Toggle superadmin rights for the given user.
     */
    public function toggleSuperadmin(Request $request, string $id): RedirectResponse
    {
        $targetUser = User::findOrFail($id);

        if ($targetUser->id === Auth::id() && $targetUser->is_superadmin) {
            return back()->with('error', 'Anda tidak boleh melucutkan hak Superadmin akaun anda sendiri.');
        }

        $targetUser->is_superadmin = ! $targetUser->is_superadmin;
        $targetUser->save();

        $actionText = $targetUser->is_superadmin ? 'dilantik sebagai Superadmin' : 'dilucutkan daripada Superadmin';

        return back()->with('success', "Pengguna {$targetUser->name} telah berjaya {$actionText}.");
    }

    /**
     * Update user account status (active / suspended).
     */
    public function updateStatus(Request $request, string $id): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'string', 'in:active,suspended'],
        ]);

        $targetUser = User::findOrFail($id);

        if ($targetUser->id === Auth::id() && $validated['status'] === 'suspended') {
            return back()->with('error', 'Anda tidak boleh menggantung akaun anda sendiri.');
        }

        $targetUser->status = $validated['status'];
        $targetUser->save();

        $statusText = $validated['status'] === 'active' ? 'diaktifkan' : 'digantung';

        return back()->with('success', "Akaun {$targetUser->name} telah berjaya {$statusText}.");
    }
}
