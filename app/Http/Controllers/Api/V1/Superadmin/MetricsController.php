<?php

namespace App\Http\Controllers\Api\V1\Superadmin;

use App\Http\Controllers\Concerns\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Superadmin\SuperadminMetricsService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MetricsController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected SuperadminMetricsService $metricsService
    ) {}

    /**
     * Get system telemetry metrics in JSON format.
     */
    public function __invoke(Request $request): JsonResponse
    {
        $health = $this->metricsService->getSystemHealth();
        $totalUsers = $this->metricsService->getTotalUsersCount();
        $totalOrgs = $this->metricsService->getTotalOrganizationsCount();

        $cutoff24h = Carbon::now()->subHours(24);
        $activeUsers24h = User::where('updated_at', '>=', $cutoff24h)
            ->orWhere('created_at', '>=', $cutoff24h)
            ->count();

        return response()->json([
            'success' => true,
            'data' => [
                'total_users' => $totalUsers,
                'total_orgs' => $totalOrgs,
                'active_users_24h' => max($activeUsers24h, 1),
                'storage_mb' => $health['storage']['storage_mb'] ?? 0,
            ],
        ]);
    }
}
