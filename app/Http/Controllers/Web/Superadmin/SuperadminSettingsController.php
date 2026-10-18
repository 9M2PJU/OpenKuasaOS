<?php

namespace App\Http\Controllers\Web\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use App\Services\Superadmin\SuperadminMetricsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\View\View;

class SuperadminSettingsController extends Controller
{
    public function __construct(
        protected SuperadminMetricsService $metricsService
    ) {}

    /**
     * Display system settings and server diagnostics.
     */
    public function index(): View
    {
        $health = $this->metricsService->getSystemHealth();
        $registrationEnabled = (bool) SystemSetting::get('registration_enabled', true);
        $instanceName = (string) SystemSetting::get('instance_name', 'OpenKuasa Self-Hosted');
        $ownerUserId = (string) SystemSetting::get('owner_user_id', '');

        return view('superadmin.system.index', compact(
            'health',
            'registrationEnabled',
            'instanceName',
            'ownerUserId'
        ));
    }

    /**
     * Update system settings.
     */
    public function update(Request $request): mixed
    {
        $validated = $request->validate([
            'registration_enabled' => ['sometimes', 'boolean'],
            'instance_name' => ['sometimes', 'string', 'max:255'],
        ]);

        if ($request->has('registration_enabled')) {
            SystemSetting::set(
                'registration_enabled',
                $request->boolean('registration_enabled'),
                'Membolehkan atau menutup pendaftaran awam pada login/register'
            );
        }

        if ($request->has('instance_name')) {
            SystemSetting::set(
                'instance_name',
                $validated['instance_name'],
                'Nama instalasi pelayan OpenKuasa'
            );
        }

        if ($request->expectsJson() || $request->is('api/*') || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Tetapan sistem berjaya dikemaskini.',
                'settings' => [
                    'registration_enabled' => SystemSetting::get('registration_enabled'),
                    'instance_name' => SystemSetting::get('instance_name'),
                ],
            ]);
        }

        return back()->with('success', 'Tetapan sistem berjaya disimpan.');
    }

    /**
     * Clear system cache (optimize:clear).
     */
    public function clearCache(Request $request): mixed
    {
        Artisan::call('optimize:clear');
        $output = Artisan::output();

        if ($request->expectsJson() || $request->is('api/*') || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Cache sistem berjaya dikosongkan sepenuhnya.',
                'output' => $output,
            ]);
        }

        return back()->with('success', 'Cache sistem berjaya dikosongkan sepenuhnya.');
    }
}
