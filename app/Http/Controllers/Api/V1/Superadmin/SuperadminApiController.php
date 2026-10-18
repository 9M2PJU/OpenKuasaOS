<?php

namespace App\Http\Controllers\Api\V1\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\Org;
use App\Models\User;
use App\Services\Superadmin\SuperadminMetricsService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SuperadminApiController extends Controller
{
    public function __construct(
        protected SuperadminMetricsService $metricsService
    ) {}

    /**
     * Get system metrics JSON for telemetry and headless monitoring.
     */
    public function metrics(Request $request): JsonResponse
    {
        $health = $this->metricsService->getSystemHealth();
        $cutoff24h = Carbon::now()->subHours(24);
        $active24h = User::where('updated_at', '>=', $cutoff24h)->count();

        return response()->json([
            'success' => true,
            'data' => [
                'total_users' => User::count(),
                'total_orgs' => Org::count(),
                'active_users_24h' => max(1, $active24h),
                'storage_mb' => $health['storage']['storage_mb'] ?? 0,
                'breakdown' => $this->metricsService->getUsersBreakdown(),
                'health' => [
                    'php_version' => $health['php_version'],
                    'laravel_version' => $health['laravel_version'],
                    'database_connected' => $health['database']['connected'],
                    'ai_ready' => $health['ai']['ready'],
                ],
            ],
        ]);
    }
}
