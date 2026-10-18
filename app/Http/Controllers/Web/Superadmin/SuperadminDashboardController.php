<?php

namespace App\Http\Controllers\Web\Superadmin;

use App\Http\Controllers\Controller;
use App\Services\Superadmin\SuperadminMetricsService;
use Illuminate\View\View;

class SuperadminDashboardController extends Controller
{
    public function __construct(
        protected SuperadminMetricsService $metricsService
    ) {}

    /**
     * Display the superadmin master dashboard.
     */
    public function index(): View
    {
        $breakdown = $this->metricsService->getUsersBreakdown();
        $totalOrgs = $this->metricsService->getTotalOrganizationsCount();
        $activeUsers = $this->metricsService->getActiveUsers7DaysCount();
        $health = $this->metricsService->getSystemHealth();
        $trends = $this->metricsService->getRegistrationTrends(14);
        $recentSignups = $this->metricsService->getRecentSignups(5);

        return view('superadmin.dashboard', compact(
            'breakdown',
            'totalOrgs',
            'activeUsers',
            'health',
            'trends',
            'recentSignups'
        ));
    }
}
