@extends('layouts.app', ['title' => 'Kasturi — CRM & AI Saluran Jualan'])

@section('content')
<div class="space-y-6 pb-16">
    <!-- Header with Breadcrumbs & Action Buttons -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-[#00b8d4] animate-pulse"></span>
                <span class="text-xs uppercase tracking-wider font-bold text-slate-500 dark:text-slate-400">Kasturi AI Sales Engine</span>
            </div>
            <h1 class="text-2xl font-black tracking-tight text-slate-900 dark:text-white mt-1">
                Kasturi &bull; CRM & AI Automasi Saluran Jualan
            </h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                Pengurusan saluran urus niaga jualan, penjejakan kontak pelanggan dan pemantauan prestasi 6 ejen AI pintar dari pangkalan data.
            </p>
        </div>
        <div class="flex items-center gap-2.5 flex-wrap">
            <a href="{{ route('crm.products') }}" class="px-3.5 py-2 rounded-xl border border-indigo-500/30 bg-indigo-500/10 hover:bg-indigo-500/20 text-xs font-semibold text-indigo-600 dark:text-indigo-400 shadow-sm transition flex items-center gap-1.5">
                <span>📦</span>
                <span>Katalog Produk</span>
            </a>
            <a href="{{ route('crm.calendar') }}" class="px-3.5 py-2 rounded-xl border border-cyan-500/30 bg-cyan-500/10 hover:bg-cyan-500/20 text-xs font-semibold text-cyan-400 shadow-sm transition flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <span>Kalendar Temujanji</span>
            </a>
            <a href="{{ route('crm.contacts') }}" class="px-3.5 py-2 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 hover:bg-slate-50 dark:hover:bg-slate-800/80 text-xs font-semibold text-slate-700 dark:text-slate-300 shadow-sm transition">
                Buku Kontak
            </a>
            <a href="{{ route('crm.deals') }}" class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold shadow-sm transition flex items-center gap-1.5">
                <span>Papan Deals</span>
                <span>&rarr;</span>
            </a>
        </div>
    </div>

    <!-- ==============================================
         ROW 1: TOP 4 KPI CARDS (Matching Screenshot)
         ============================================== -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Card 1: Active agents (Dark Teal/Cyan Hero Card) -->
        <div class="rounded-2xl p-5 bg-[#175263] border border-cyan-800/50 text-white shadow-lg shadow-cyan-900/20 flex flex-col justify-between min-h-[120px] relative overflow-hidden">
            <div>
                <span class="text-xs font-semibold text-cyan-200/90">Active agents</span>
                <div class="flex items-center gap-2.5 mt-2">
                    <span class="text-4xl font-black tracking-tight text-white">{{ $activeAgents }} / {{ $totalAgents }}</span>
                    <span class="px-2 py-0.5 rounded-full text-xs font-bold bg-[#0c313c] text-cyan-300 border border-cyan-600/40">
                        +1
                    </span>
                </div>
            </div>
            <!-- Bottom Sparkline Wave -->
            <div class="mt-4 relative h-6 w-full opacity-70">
                <svg viewBox="0 0 100 24" class="w-full h-full" fill="none">
                    <path d="M0,18 Q20,15 40,16 T75,13 T100,16" stroke="rgba(10, 30, 40, 0.7)" stroke-width="2.5" stroke-linecap="round"/>
                </svg>
            </div>
        </div>

        <!-- Card 2: Tasks automated -->
        <div class="rounded-2xl p-5 bg-[#121927] border border-slate-800/80 text-white shadow-sm flex flex-col justify-between min-h-[120px] relative overflow-hidden">
            <div>
                <span class="text-xs font-medium text-slate-400">Tasks automated</span>
                <div class="flex items-center gap-2.5 mt-2">
                    <span class="text-3xl sm:text-4xl font-black tracking-tight text-white">{{ number_format($tasksAutomated) }}</span>
                    <span class="px-2 py-0.5 rounded-full text-xs font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                        +22%
                    </span>
                </div>
            </div>
            <!-- Bottom Sparkline -->
            <div class="mt-4 relative h-6 w-full">
                <svg viewBox="0 0 100 24" class="w-full h-full" fill="none">
                    <path d="M0,20 Q40,18 70,14 T100,6" stroke="#00b8d4" stroke-width="2" stroke-linecap="round"/>
                </svg>
            </div>
        </div>

        <!-- Card 3: Hours saved -->
        <div class="rounded-2xl p-5 bg-[#121927] border border-slate-800/80 text-white shadow-sm flex flex-col justify-between min-h-[120px] relative overflow-hidden">
            <div>
                <span class="text-xs font-medium text-slate-400">Hours saved</span>
                <div class="flex items-center gap-2.5 mt-2">
                    <span class="text-3xl sm:text-4xl font-black tracking-tight text-white">{{ $hoursSaved }}h</span>
                    <span class="px-2 py-0.5 rounded-full text-xs font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                        +11%
                    </span>
                </div>
            </div>
            <!-- Bottom Sparkline -->
            <div class="mt-4 relative h-6 w-full">
                <svg viewBox="0 0 100 24" class="w-full h-full" fill="none">
                    <path d="M0,18 Q50,17 75,15 T100,12" stroke="#10b981" stroke-width="2" stroke-linecap="round"/>
                </svg>
            </div>
        </div>

        <!-- Card 4: Avg success rate -->
        <div class="rounded-2xl p-5 bg-[#121927] border border-slate-800/80 text-white shadow-sm flex flex-col justify-between min-h-[120px] relative overflow-hidden">
            <div>
                <span class="text-xs font-medium text-slate-400">Avg success rate</span>
                <div class="flex items-center gap-2.5 mt-2">
                    <span class="text-3xl sm:text-4xl font-black tracking-tight text-white">{{ $successRate }}%</span>
                    <span class="px-2 py-0.5 rounded-full text-xs font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                        +2pt
                    </span>
                </div>
            </div>
            <!-- Bottom Progress Line -->
            <div class="mt-4 w-full h-1.5 bg-slate-800/80 rounded-full overflow-hidden">
                <div class="h-full bg-gradient-to-r from-blue-500 to-[#00b8d4] rounded-full" style="width: {{ $successRate }}%"></div>
            </div>
        </div>
    </div>

    <!-- ==============================================
         ROW 2: CHARTS (Automated vs manual & Automation coverage)
         ============================================== -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Card 5 (2 Cols): Automated vs manual -->
        <div class="lg:col-span-2 rounded-2xl p-6 bg-[#121927] border border-slate-800/80 text-white shadow-sm flex flex-col justify-between">
            <div>
                <div class="flex items-start gap-3">
                    <div class="w-8 h-8 rounded-lg bg-cyan-500/10 border border-cyan-500/20 flex items-center justify-center text-[#00b8d4] shrink-0">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-sm font-bold text-white tracking-tight">Automated vs manual</h2>
                        <p class="text-xs text-slate-400 mt-0.5">Tasks handled &middot; last 8 weeks</p>
                    </div>
                </div>

                <!-- Line Chart Canvas -->
                <div class="mt-6 relative h-64 w-full">
                    <canvas id="automatedVsManualChart"></canvas>
                </div>
            </div>

            <!-- Custom Legend -->
            <div class="mt-4 flex items-center justify-center gap-6 pt-3 border-t border-slate-800/60 text-xs font-semibold">
                <div class="flex items-center gap-2 text-slate-300">
                    <span class="w-3 h-3 rounded-sm bg-[#00b8d4]"></span>
                    <span>Automated</span>
                </div>
                <div class="flex items-center gap-2 text-slate-300">
                    <span class="w-3 h-3 rounded-sm bg-[#f59e0b]"></span>
                    <span>Manual</span>
                </div>
            </div>
        </div>

        <!-- Card 6 (1 Col): Automation coverage -->
        <div class="rounded-2xl p-6 bg-[#121927] border border-slate-800/80 text-white shadow-sm flex flex-col justify-between">
            <div>
                <div class="flex items-start gap-3">
                    <div class="w-8 h-8 rounded-lg bg-cyan-500/10 border border-cyan-500/20 flex items-center justify-center text-[#00b8d4] shrink-0">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-sm font-bold text-white tracking-tight">Automation coverage</h2>
                        <p class="text-xs text-slate-400 mt-0.5">Pipeline on autopilot</p>
                    </div>
                </div>

                <!-- Large Circular Progress Ring Gauge -->
                <div class="flex flex-col items-center justify-center my-6">
                    <div class="relative w-48 h-48 flex items-center justify-center">
                        @php
                            $coverageCircumference = 2 * 3.14159 * 62;
                            $coverageOffset = $coverageCircumference - ($automationCoverage / 100) * $coverageCircumference;
                        @endphp
                        <svg class="w-full h-full transform -rotate-90" viewBox="0 0 160 160">
                            <!-- Background Track Circle -->
                            <circle cx="80" cy="80" r="62" stroke="#1e293b" stroke-width="14" fill="transparent"/>
                            <!-- Progress Arc in Cyan -->
                            <circle cx="80" cy="80" r="62"
                                stroke="#00b8d4"
                                stroke-width="14"
                                stroke-dasharray="{{ $coverageCircumference }}"
                                stroke-dashoffset="{{ $coverageOffset }}"
                                stroke-linecap="round"
                                fill="transparent"
                                class="transition-all duration-1000 ease-out"/>
                        </svg>
                        <!-- Center Text -->
                        <div class="absolute inset-0 flex flex-col items-center justify-center text-center">
                            <span class="text-3xl font-black text-white tracking-tight">{{ $automationCoverage }}%</span>
                            <span class="text-xs text-slate-400 font-medium">covered</span>
                        </div>
                    </div>
                </div>
            </div>

            <p class="text-[11px] text-slate-400 text-center border-t border-slate-800/60 pt-3">
                {{ $automationCoverage }}% urus niaga pipeline dinilai & dikuasakan secara automatik oleh ejen pintar tanpa bebanan manual.
            </p>
        </div>
    </div>

    <!-- ==============================================
         ROW 3: TASKS BY AGENT & AGENT STRENGTHS
         ============================================== -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Card 7: Tasks by agent (Horizontal Bars) -->
        <div class="rounded-2xl p-6 bg-[#121927] border border-slate-800/80 text-white shadow-sm space-y-5">
            <div class="flex items-start gap-3">
                <div class="w-8 h-8 rounded-lg bg-cyan-500/10 border border-cyan-500/20 flex items-center justify-center text-[#00b8d4] shrink-0">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                </div>
                <div>
                    <h2 class="text-sm font-bold text-white tracking-tight">Tasks by agent</h2>
                    <p class="text-xs text-slate-400 mt-0.5">Completed this month</p>
                </div>
            </div>

            <!-- Horizontal Bars for the 6 Agents -->
            @php
                $maxTaskCount = !empty($tasksByAgent) ? max(array_column($tasksByAgent, 'count')) : 480;
                $maxTaskCount = $maxTaskCount ?: 1;
            @endphp
            <div class="space-y-4 pt-2">
                @foreach($tasksByAgent as $agentItem)
                @php
                    $barPct = round(($agentItem['count'] / $maxTaskCount) * 100);
                @endphp
                <div class="flex items-center gap-4">
                    <div class="w-36 text-right shrink-0">
                        <span class="text-xs font-semibold text-slate-300 block truncate">{{ $agentItem['name'] }}</span>
                    </div>
                    <div class="flex-1 h-7 bg-slate-900/80 rounded-md overflow-hidden relative flex items-center">
                        <div class="h-full bg-[#00b8d4] rounded-md transition-all duration-700" style="width: {{ $barPct }}%"></div>
                        <span class="absolute left-3 text-[11px] font-bold text-slate-900 mix-blend-screen">
                            {{ number_format($agentItem['count']) }}
                        </span>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Card 8: Agent strengths (Pentagon Radar Chart) -->
        <div class="rounded-2xl p-6 bg-[#121927] border border-slate-800/80 text-white shadow-sm flex flex-col justify-between">
            <div>
                <div class="flex items-start gap-3">
                    <div class="w-8 h-8 rounded-lg bg-cyan-500/10 border border-cyan-500/20 flex items-center justify-center text-[#00b8d4] shrink-0">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-sm font-bold text-white tracking-tight">Agent strengths</h2>
                        <p class="text-xs text-slate-400 mt-0.5">Speed &middot; accuracy &middot; volume</p>
                    </div>
                </div>

                <!-- Radar Chart Canvas -->
                <div class="mt-4 relative h-64 w-full flex items-center justify-center">
                    <canvas id="agentStrengthsChart"></canvas>
                </div>
            </div>

            <div class="mt-4 flex items-center justify-center gap-6 pt-3 border-t border-slate-800/60 text-xs text-slate-400">
                <span>Skor purata keupayaan sistem: <strong class="text-white">90.2%</strong></span>
            </div>
        </div>
    </div>

    <!-- ==============================================
         ROW 4: SALES PIPELINE DEALS & STAGES OVERVIEW
         ============================================== -->
    <div class="space-y-4 pt-4 border-t border-slate-800/80">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
            <div>
                <h2 class="text-base font-bold text-slate-900 dark:text-white tracking-tight">Saluran Transaksi Jualan (CRM Pipeline)</h2>
                <p class="text-xs text-slate-500">Ringkasan urus niaga sebenar dari pangkalan data berserta status penukaran.</p>
            </div>
            <a href="{{ route('crm.deals') }}" class="text-xs font-semibold text-indigo-600 dark:text-indigo-400 hover:underline flex items-center gap-1">
                Buka Papan Kanban Penuh &rarr;
            </a>
        </div>

        <!-- 3 Pipeline Value Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="bg-[#121927] border border-slate-800/80 rounded-2xl p-5 shadow-sm text-white">
                <span class="block text-xs font-medium text-slate-400">Nilai Saluran Jualan Semasa</span>
                <span class="block text-2xl font-black text-indigo-400 tracking-tight mt-1">
                    RM {{ number_format($totalPipeline, 2) }}
                </span>
                <span class="block text-[11px] text-slate-500 mt-1">Urus niaga aktif dalam perbincangan</span>
            </div>

            <div class="bg-[#121927] border border-slate-800/80 rounded-2xl p-5 shadow-sm text-white">
                <span class="block text-xs font-medium text-slate-400">Jumlah Jualan Berjaya Ditutup (Won)</span>
                <span class="block text-2xl font-black text-emerald-400 tracking-tight mt-1">
                    RM {{ number_format($wonPipeline, 2) }}
                </span>
                <span class="block text-[11px] text-slate-500 mt-1">Status Berjaya (Closed-Won)</span>
            </div>

            <div class="bg-[#121927] border border-slate-800/80 rounded-2xl p-5 shadow-sm text-white">
                <span class="block text-xs font-medium text-slate-400">Jumlah Urus Niaga & Kontak</span>
                <span class="block text-2xl font-black text-white tracking-tight mt-1">
                    {{ $dealsCount }} Urus Niaga
                </span>
                <span class="block text-[11px] text-slate-500 mt-1">{{ count($contacts) }} profil kontak pelanggan</span>
            </div>
        </div>

        <!-- Deals by Stages Preview Grid -->
        <div class="bg-[#121927] border border-slate-800/80 rounded-2xl p-6 shadow-sm text-white">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-400 block mb-4">Pecahan Urus Niaga Mengikut Peringkat</span>
            <div class="grid grid-cols-2 md:grid-cols-6 gap-3">
                @foreach($stages as $key => $name)
                @php
                    $stageDeals = $deals->where('stage_id', $key);
                    $stageSum = $stageDeals->sum('amount');
                @endphp
                <div class="p-3.5 rounded-xl border border-slate-800 bg-slate-900/60">
                    <span class="block text-[10px] font-bold uppercase text-slate-400 truncate">{{ $name }}</span>
                    <span class="block text-lg font-bold text-white mt-1">{{ $stageDeals->count() }}</span>
                    <span class="block text-[11px] text-slate-400 mt-0.5">RM {{ number_format($stageSum, 0) }}</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

<!-- ==============================================
     CHART.JS SCRIPTS (Automated vs manual & Radar)
     ============================================== -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    const chartJs = window.Chart;
    if (!chartJs) return;

    // 1. Automated vs manual (Line Chart)
    const lineCtx = document.getElementById('automatedVsManualChart');
    if (lineCtx) {
        new chartJs(lineCtx, {
            type: 'line',
            data: {
                labels: @json($weeklyLabels),
                datasets: [
                    {
                        label: 'Automated',
                        data: @json($weeklyAutomated),
                        borderColor: '#00b8d4',
                        backgroundColor: function(context) {
                            const chart = context.chart;
                            const {ctx, chartArea} = chart;
                            if (!chartArea) return 'rgba(0, 184, 212, 0.2)';
                            const gradient = ctx.createLinearGradient(0, chartArea.top, 0, chartArea.bottom);
                            gradient.addColorStop(0, 'rgba(0, 184, 212, 0.25)');
                            gradient.addColorStop(1, 'rgba(0, 184, 212, 0.0)');
                            return gradient;
                        },
                        borderWidth: 2.5,
                        fill: true,
                        tension: 0.35,
                        pointRadius: 3,
                        pointBackgroundColor: '#00b8d4',
                        pointHoverRadius: 5,
                    },
                    {
                        label: 'Manual',
                        data: @json($weeklyManual),
                        borderColor: '#f59e0b',
                        backgroundColor: function(context) {
                            const chart = context.chart;
                            const {ctx, chartArea} = chart;
                            if (!chartArea) return 'rgba(245, 158, 11, 0.15)';
                            const gradient = ctx.createLinearGradient(0, chartArea.top, 0, chartArea.bottom);
                            gradient.addColorStop(0, 'rgba(245, 158, 11, 0.2)');
                            gradient.addColorStop(1, 'rgba(245, 158, 11, 0.0)');
                            return gradient;
                        },
                        borderWidth: 2.5,
                        fill: true,
                        tension: 0.35,
                        pointRadius: 3,
                        pointBackgroundColor: '#f59e0b',
                        pointHoverRadius: 5,
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#0f172a',
                        titleColor: '#f8fafc',
                        bodyColor: '#cbd5e1',
                        borderColor: '#334155',
                        borderWidth: 1,
                        padding: 10,
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: {
                            color: '#94a3b8',
                            font: { size: 11, weight: '500' }
                        }
                    },
                    y: {
                        display: false,
                        grid: { display: false },
                    }
                }
            }
        });
    }

    // 2. Agent strengths (Pentagon Radar Chart)
    const radarCtx = document.getElementById('agentStrengthsChart');
    if (radarCtx) {
        new chartJs(radarCtx, {
            type: 'radar',
            data: {
                labels: @json($agentStrengths['labels']),
                datasets: [
                    {
                        data: @json($agentStrengths['data']),
                        backgroundColor: 'rgba(148, 163, 184, 0.45)',
                        borderColor: 'rgba(203, 213, 225, 0.8)',
                        borderWidth: 1.5,
                        pointBackgroundColor: '#94a3b8',
                        pointBorderColor: '#fff',
                        pointRadius: 3,
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#0f172a',
                        titleColor: '#f8fafc',
                        bodyColor: '#cbd5e1',
                        borderColor: '#334155',
                        borderWidth: 1,
                        padding: 8,
                    }
                },
                scales: {
                    r: {
                        min: 0,
                        max: 100,
                        ticks: { display: false },
                        grid: {
                            color: 'rgba(255, 255, 255, 0.06)'
                        },
                        angleLines: {
                            color: 'rgba(255, 255, 255, 0.08)'
                        },
                        pointLabels: {
                            color: '#94a3b8',
                            font: {
                                size: 11,
                                weight: '600'
                            }
                        }
                    }
                }
            }
        });
    }
});
</script>
@endsection
