<?php

namespace App\Services\Superadmin;

use App\Models\AgentConfig;
use App\Models\Org;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class SuperadminMetricsService
{
    /**
     * Get total users count.
     */
    public function getTotalUsersCount(): int
    {
        return User::count();
    }

    /**
     * Get user count breakdown (total, real, demo).
     */
    public function getUsersBreakdown(): array
    {
        $total = User::count();
        $demo = User::where('is_demo', true)->count();
        $real = User::where('is_demo', false)->count();

        return [
            'total' => $total,
            'real' => $real,
            'demo' => $demo,
        ];
    }

    /**
     * Get total organizations count.
     */
    public function getTotalOrganizationsCount(): int
    {
        return Org::count();
    }

    /**
     * Get active users count within the last 7 days.
     */
    public function getActiveUsers7DaysCount(): int
    {
        $cutoff = Carbon::now()->subDays(7);

        // Check users updated or created in last 7 days, or session activity
        $activeFromUsers = User::where('updated_at', '>=', $cutoff)
            ->orWhere('created_at', '>=', $cutoff)
            ->count();

        // Also check sessions table if present
        $activeSessions = 0;
        try {
            $activeSessions = DB::table('sessions')
                ->where('last_activity', '>=', $cutoff->timestamp)
                ->distinct('user_id')
                ->count('user_id');
        } catch (\Throwable $e) {
            // Sessions table might not have user_id indexed or empty
        }

        return max($activeFromUsers, $activeSessions, 1);
    }

    /**
     * Get daily registration trends for the given number of days.
     */
    public function getRegistrationTrends(int $days = 14): array
    {
        $startDate = Carbon::now()->subDays($days - 1)->startOfDay();
        $endDate = Carbon::now()->endOfDay();

        $rawCounts = User::where('created_at', '>=', $startDate)
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->pluck('count', 'date')
            ->toArray();

        $labels = [];
        $data = [];
        $trends = [];

        for ($i = $days - 1; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->format('Y-m-d');
            $displayDate = Carbon::now()->subDays($i)->format('d M');
            $count = (int) ($rawCounts[$date] ?? 0);

            $labels[] = $displayDate;
            $data[] = $count;
            $trends[] = [
                'date' => $date,
                'label' => $displayDate,
                'count' => $count,
            ];
        }

        return [
            'labels' => $labels,
            'data' => $data,
            'trends' => $trends,
            'total_in_period' => array_sum($data),
        ];
    }

    /**
     * Get recent user signups.
     */
    public function getRecentSignups(int $limit = 5): Collection
    {
        return User::with(['orgs', 'profile'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get system health parameters.
     */
    public function getSystemHealth(): array
    {
        // 1. Database connection
        $dbConnected = false;
        $dbVersion = 'PostgreSQL';
        try {
            DB::connection()->getPdo();
            $dbConnected = true;
            $pdoVersion = DB::select('select version() as ver');
            if (! empty($pdoVersion)) {
                $dbVersion = $pdoVersion[0]->ver ?? 'PostgreSQL 16';
                if (preg_match('/PostgreSQL\s+([0-9.]+)/i', $dbVersion, $m)) {
                    $dbVersion = 'PostgreSQL ' . $m[1];
                }
            }
        } catch (\Throwable $e) {
            $dbConnected = false;
        }

        // 2. Storage / Disk
        $diskPath = storage_path();
        $freeBytes = @disk_free_space($diskPath) ?: 0;
        $totalBytes = @disk_total_space($diskPath) ?: 1;
        $usedBytes = max(0, $totalBytes - $freeBytes);
        $usedPercentage = round(($usedBytes / $totalBytes) * 100, 1);
        $storageOk = $freeBytes > (50 * 1024 * 1024); // at least 50MB free
        $storageFormatted = round($freeBytes / (1024 * 1024 * 1024), 2) . ' GB Bebas';

        // 3. Tuah AI status
        $tuahConfig = AgentConfig::where('agent_name', 'Tuah')->first();
        $tuahActive = $tuahConfig ? (bool) $tuahConfig->is_active : true;
        $tuahModel = $tuahConfig ? ($tuahConfig->model ?? $tuahConfig->ollama_model ?? 'Tuah Engine') : 'Ollama / Gemma';

        // 4. Server info
        $uptime = $this->getSystemUptime();
        $serverIp = $_SERVER['SERVER_ADDR'] ?? gethostbyname(gethostname()) ?? '127.0.0.1';

        return [
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'database' => [
                'driver' => config('database.default'),
                'connected' => $dbConnected,
                'version' => $dbVersion,
            ],
            'storage' => [
                'ok' => $storageOk,
                'free_formatted' => $storageFormatted,
                'used_percentage' => $usedPercentage,
                'storage_mb' => round($usedBytes / (1024 * 1024), 2),
            ],
            'ai' => [
                'ready' => $tuahActive,
                'name' => 'Tuah AI',
                'model' => $tuahModel,
            ],
            'server_ip' => $serverIp,
            'uptime' => $uptime,
        ];
    }

    /**
     * Get system uptime if available.
     */
    protected function getSystemUptime(): string
    {
        if (is_readable('/proc/uptime')) {
            $str = file_get_contents('/proc/uptime');
            $num = floatval(explode(' ', $str)[0]);
            $days = floor($num / 86400);
            $hours = floor(($num % 86400) / 3600);
            $mins = floor(($num % 3600) / 60);

            $parts = [];
            if ($days > 0) $parts[] = "{$days} hari";
            if ($hours > 0) $parts[] = "{$hours} jam";
            $parts[] = "{$mins} minit";

            return implode(', ', $parts);
        }

        return 'Aktif';
    }
}
