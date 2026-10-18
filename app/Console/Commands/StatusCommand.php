<?php

namespace App\Console\Commands;

use App\Models\Org;
use App\Models\User;
use App\Services\Superadmin\SuperadminMetricsService;
use Illuminate\Console\Command;

class StatusCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'openkuasa:status';

    /**
     * The console command description.
     */
    protected $description = 'Papar ringkasan status sistem, Superadmin semasa, dan metrik pelayan';

    /**
     * Execute the console command.
     */
    public function handle(SuperadminMetricsService $metricsService): int
    {
        $this->info('====================================================');
        $this->info('           OPENKUASA OS - STATUS PELAYAN             ');
        $this->info('====================================================');

        $health = $metricsService->getSystemHealth();
        $breakdown = $metricsService->getUsersBreakdown();
        $totalOrgs = Org::count();

        $this->line("Versi PHP      : " . $health['php_version']);
        $this->line("Versi Laravel  : " . $health['laravel_version']);
        $this->line("Pangkalan Data : " . $health['database']['version'] . ' (' . ($health['database']['connected'] ? 'Bersambung' : 'Ralat Sambungan') . ')');
        $this->line("Storan Bebas   : " . $health['storage']['free_formatted']);
        $this->line("Enjin Tuah AI  : " . ($health['ai']['ready'] ? 'Sedia (' . $health['ai']['model'] . ')' : 'Tidak Aktif'));

        $this->newLine();
        $this->info('--- Metrik Pengguna & Organisasi ---');
        $this->line("Jumlah Pengguna : {$breakdown['total']} (Pemilik Sebenar: {$breakdown['real']}, Demo: {$breakdown['demo']})");
        $this->line("Jumlah Syarikat : {$totalOrgs}");

        $this->newLine();
        $this->info('--- Senarai Superadmin Semasa ---');
        $superadmins = User::where('is_superadmin', true)->get();
        if ($superadmins->isEmpty()) {
            $this->warn('Tiada Superadmin berdaftar dalam sistem!');
        } else {
            foreach ($superadmins as $admin) {
                $this->line("- [ID: {$admin->id}] {$admin->name} <{$admin->email}> (Status: {$admin->status})");
            }
        }

        $this->newLine();
        $this->info('--- Status Akaun Demo ---');
        $demoUsers = User::where('is_demo', true)->get();
        foreach ($demoUsers as $demo) {
            $this->line("- {$demo->name} <{$demo->email}> [DEMO MODE]");
        }

        return Command::SUCCESS;
    }
}
