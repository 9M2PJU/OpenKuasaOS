@php
    $isMalay = app()->getLocale() === 'ms';
@endphp

@extends('layouts.app', ['title' => 'Lekir — Calon Pemohon Pekerjaan'])

@section('content')
<div class="space-y-6 pb-12">
    <!-- Header with Breadcrumb & Quick Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-slate-200 dark:border-slate-800 pb-4">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('hire.index') }}" class="text-xs text-indigo-600 dark:text-indigo-400 font-semibold hover:underline">
                    &larr; Lekir Hire
                </a>
                <span class="text-slate-400 text-xs">/</span>
                <span class="text-xs text-slate-500 font-medium">Permohonan</span>
            </div>
            <h1 class="text-xl font-bold tracking-tight text-slate-900 dark:text-white mt-1">
                Lekir — Calon Pemohon Pekerjaan
            </h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                {{ $isMalay ? 'Peringkat penilaian calon resume, saringan temuduga dan pengeluaran tawaran kerja.' : 'Track candidates through screening, interviews, and hiring pipeline.' }}
            </p>
        </div>

        <!-- Quick Navigation -->
        <div class="flex items-center gap-2 flex-wrap">
            <a href="{{ route('hire.index') }}" class="px-3.5 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700/60 text-xs font-semibold text-slate-700 dark:text-slate-200 transition">
                &larr; {{ $isMalay ? 'Dashboard Lekir' : 'Lekir Dashboard' }}
            </a>
            <a href="{{ route('hire.jobs') }}" class="px-3.5 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700/60 text-xs font-semibold text-slate-700 dark:text-slate-200 transition">
                {{ $isMalay ? 'Iklan Jawatan' : 'Jobs' }}
            </a>
            <a href="{{ route('hire.interviews') }}" class="px-3.5 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700/60 text-xs font-semibold text-slate-700 dark:text-slate-200 transition">
                {{ $isMalay ? 'Jadual Temuduga' : 'Interviews' }}
            </a>
        </div>
    </div>

    <!-- 1. Top KPI Metric Cards Row -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- 1. Total applications (Solid Copper Highlight Card) -->
        <div class="bg-[#CA6A36] text-white rounded-2xl p-5 shadow-sm flex flex-col justify-between transition hover:shadow-md">
            <span class="text-xs font-medium text-white/90">Total applications</span>
            <span class="text-3xl sm:text-4xl font-extrabold tracking-tight mt-3">{{ $totalApplications }}</span>
        </div>

        <!-- 2. New -->
        <div class="bg-white dark:bg-[#101726] border border-slate-200 dark:border-[#1F293D] rounded-2xl p-5 shadow-sm flex flex-col justify-between transition hover:border-slate-300 dark:hover:border-slate-700">
            <span class="text-xs font-medium text-slate-500 dark:text-slate-400">New</span>
            <span class="text-3xl sm:text-4xl font-extrabold text-slate-900 dark:text-white tracking-tight mt-3">{{ $newCount }}</span>
        </div>

        <!-- 3. In review -->
        <div class="bg-white dark:bg-[#101726] border border-slate-200 dark:border-[#1F293D] rounded-2xl p-5 shadow-sm flex flex-col justify-between transition hover:border-slate-300 dark:hover:border-slate-700">
            <span class="text-xs font-medium text-slate-500 dark:text-slate-400">In review</span>
            <span class="text-3xl sm:text-4xl font-extrabold text-slate-900 dark:text-white tracking-tight mt-3">{{ $inReviewCount }}</span>
        </div>

        <!-- 4. Rejected -->
        <div class="bg-white dark:bg-[#101726] border border-slate-200 dark:border-[#1F293D] rounded-2xl p-5 shadow-sm flex flex-col justify-between transition hover:border-slate-300 dark:hover:border-slate-700">
            <span class="text-xs font-medium text-slate-500 dark:text-slate-400">Rejected</span>
            <span class="text-3xl sm:text-4xl font-extrabold text-slate-900 dark:text-white tracking-tight mt-3">{{ $rejectedCount }}</span>
        </div>
    </div>

    <!-- 2. Analytics Charts Row (By status Donut & Applications by job Horizontal Bar) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5">
        <!-- 2.1 By status Donut Chart (approx 38% width) -->
        <div class="lg:col-span-5 bg-white dark:bg-[#101726] border border-slate-200 dark:border-[#1F293D] rounded-2xl p-6 shadow-sm flex flex-col justify-between">
            <div class="flex items-center gap-3 mb-2">
                <div class="w-8 h-8 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-amber-500">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white">By status</h3>
                    <span class="text-[11px] text-slate-400">Current application mix</span>
                </div>
            </div>

            <!-- Donut Canvas Container with Centered Count -->
            <div class="relative w-full h-56 flex items-center justify-center my-auto">
                <canvas id="applicationsByStatusChart"></canvas>
                <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
                    <span class="text-2xl font-black text-slate-900 dark:text-white tracking-tight">{{ $totalApplications }}</span>
                    <span class="text-[10px] text-slate-400 font-medium">applications</span>
                </div>
            </div>

            <!-- Status Legend matching screenshot -->
            <div class="flex flex-wrap items-center justify-center gap-x-4 gap-y-2 mt-4 pt-3 text-[11px] font-medium text-slate-400 border-t border-slate-100 dark:border-slate-800/60">
                <div class="flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-sm bg-[#CA6A36] flex-shrink-0"></span>
                    <span class="text-slate-700 dark:text-slate-300">New</span>
                </div>
                <div class="flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-sm bg-[#00c5bb] flex-shrink-0"></span>
                    <span class="text-slate-700 dark:text-slate-300">In review</span>
                </div>
                <div class="flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-sm bg-[#eab308] flex-shrink-0"></span>
                    <span class="text-slate-700 dark:text-slate-300">Shortlisted</span>
                </div>
                <div class="flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-sm bg-[#e05260] flex-shrink-0"></span>
                    <span class="text-slate-700 dark:text-slate-300">Rejected</span>
                </div>
            </div>
        </div>

        <!-- 2.2 Applications by job Horizontal Bar Chart (approx 62% width) -->
        <div class="lg:col-span-7 bg-white dark:bg-[#101726] border border-slate-200 dark:border-[#1F293D] rounded-2xl p-6 shadow-sm flex flex-col justify-between">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-8 h-8 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-amber-500">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white">Applications by job</h3>
                    <span class="text-[11px] text-slate-400">Top roles</span>
                </div>
            </div>

            <!-- Horizontal Bar Canvas Container -->
            <div class="relative w-full h-64 mt-2">
                <canvas id="applicationsByJobChart"></canvas>
            </div>
        </div>
    </div>

    <!-- 3. Recent Applications Table (Most recent first) -->
    <div class="bg-white dark:bg-[#101726] border border-slate-200 dark:border-[#1F293D] rounded-2xl shadow-sm overflow-hidden">
        <!-- Section Header -->
        <div class="p-6 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-amber-500">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2 2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white">Recent applications</h3>
                    <span class="text-[11px] text-slate-400">Most recent first</span>
                </div>
            </div>

            <span class="px-2.5 py-1 rounded-full text-[11px] font-semibold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300">
                {{ count($sampleApplications) }} Calon Terkini
            </span>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-slate-100 dark:border-slate-800/80 text-slate-400 font-semibold text-[11px]">
                        <th class="py-3.5 pl-6 pr-4">Candidate</th>
                        <th class="py-3.5 px-4">Job</th>
                        <th class="py-3.5 px-4">Source</th>
                        <th class="py-3.5 px-4">Status</th>
                        <th class="py-3.5 pr-6 text-right">Applied</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100/70 dark:divide-slate-800/50">
                    <!-- 1. Real applications from DB if created by user -->
                    @foreach($applications as $dbApp)
                        @php
                            $init = strtoupper(substr($dbApp->candidate_name ?? 'C', 0, 1));
                            $isActive = in_array($dbApp->stage, ['applied', 'screening', 'interview', 'offered', 'hired']);
                            $isRejected = ($dbApp->stage === 'rejected');
                        @endphp
                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition">
                            <td class="py-4 pl-6 pr-4">
                                <div class="flex items-center gap-3">
                                    @if($isActive)
                                    <span class="w-2 h-2 rounded-full bg-emerald-500 shadow-[0_0_8px_rgba(16,185,129,0.7)] flex-shrink-0"></span>
                                    @else
                                    <span class="w-2 h-2 rounded-full bg-slate-400 flex-shrink-0"></span>
                                    @endif

                                    <div class="w-7 h-7 rounded-full bg-[#CA6A36]/15 text-[#CA6A36] dark:bg-[#CA6A36]/30 dark:text-amber-300 font-bold text-[11px] flex items-center justify-center flex-shrink-0">
                                        {{ $init }}
                                    </div>
                                    <span class="font-semibold text-slate-900 dark:text-white truncate max-w-[180px]">
                                        {{ $dbApp->candidate_name }}
                                    </span>
                                </div>
                            </td>
                            <td class="py-4 px-4 text-slate-700 dark:text-slate-300 font-medium">
                                {{ $dbApp->job->title ?? 'Jawatan' }}
                            </td>
                            <td class="py-4 px-4">
                                <span class="inline-flex px-2.5 py-1 rounded-md text-[11px] font-medium bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 border border-slate-200/60 dark:border-slate-700/60">
                                    Portal Web
                                </span>
                            </td>
                            <td class="py-4 px-4">
                                @if($dbApp->stage === 'hired' || $dbApp->stage === 'offered' || $dbApp->stage === 'interview')
                                <span class="inline-flex px-2.5 py-1 rounded-md text-[11px] font-semibold bg-emerald-950/40 text-emerald-400 border border-emerald-500/30">
                                    Shortlisted
                                </span>
                                @elseif($dbApp->stage === 'screening')
                                <span class="inline-flex px-2.5 py-1 rounded-md text-[11px] font-semibold bg-cyan-950/40 text-cyan-400 border border-cyan-500/30">
                                    In review
                                </span>
                                @elseif($dbApp->stage === 'rejected')
                                <span class="inline-flex px-2.5 py-1 rounded-md text-[11px] font-medium bg-slate-800/80 text-slate-400 border border-slate-700/50">
                                    Rejected
                                </span>
                                @else
                                <span class="inline-flex px-2.5 py-1 rounded-md text-[11px] font-semibold bg-amber-950/40 text-amber-400 border border-amber-500/30">
                                    New
                                </span>
                                @endif
                            </td>
                            <td class="py-4 pr-6 text-right text-slate-400 text-xs font-medium whitespace-nowrap">
                                {{ $dbApp->created_at ? $dbApp->created_at->format('d M Y') : '13 Oct 2026' }}
                            </td>
                        </tr>
                    @endforeach

                    <!-- 2. Sample Data Rows (Matching Screenshot Pixel-for-Pixel) -->
                    @foreach($sampleApplications as $candidate)
                    <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition">
                        <!-- Candidate -->
                        <td class="py-4 pl-6 pr-4">
                            <div class="flex items-center gap-3">
                                @if($candidate['is_active'])
                                <span class="w-2 h-2 rounded-full bg-emerald-500 shadow-[0_0_8px_rgba(16,185,129,0.7)] flex-shrink-0"></span>
                                @else
                                <span class="w-2 h-2 rounded-full bg-slate-400 flex-shrink-0"></span>
                                @endif

                                <div class="w-7 h-7 rounded-full bg-[#CA6A36]/15 text-[#CA6A36] dark:bg-[#CA6A36]/30 dark:text-amber-300 font-bold text-[11px] flex items-center justify-center flex-shrink-0">
                                    {{ $candidate['initial'] }}
                                </div>
                                <span class="font-semibold text-slate-900 dark:text-white truncate max-w-[180px]">
                                    {{ $candidate['candidate_name'] }}
                                </span>
                            </div>
                        </td>

                        <!-- Job -->
                        <td class="py-4 px-4 text-slate-700 dark:text-slate-300 font-medium">
                            {{ $candidate['job_title'] }}
                        </td>

                        <!-- Source -->
                        <td class="py-4 px-4">
                            <span class="inline-flex px-2.5 py-1 rounded-md text-[11px] font-medium bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 border border-slate-200/60 dark:border-slate-700/60">
                                {{ $candidate['source'] }}
                            </span>
                        </td>

                        <!-- Status -->
                        <td class="py-4 px-4">
                            @if($candidate['status_type'] === 'shortlisted')
                            <span class="inline-flex px-2.5 py-1 rounded-md text-[11px] font-semibold bg-emerald-950/40 text-emerald-400 border border-emerald-500/30">
                                Shortlisted
                            </span>
                            @elseif($candidate['status_type'] === 'new')
                            <span class="inline-flex px-2.5 py-1 rounded-md text-[11px] font-semibold bg-amber-950/40 text-amber-400 border border-amber-500/30">
                                New
                            </span>
                            @elseif($candidate['status_type'] === 'in_review')
                            <span class="inline-flex px-2.5 py-1 rounded-md text-[11px] font-semibold bg-cyan-950/40 text-cyan-400 border border-cyan-500/30">
                                In review
                            </span>
                            @else
                            <span class="inline-flex px-2.5 py-1 rounded-md text-[11px] font-medium bg-slate-800/80 text-slate-400 border border-slate-700/50">
                                Rejected
                            </span>
                            @endif
                        </td>

                        <!-- Applied Date -->
                        <td class="py-4 pr-6 text-right text-slate-400 text-xs font-medium whitespace-nowrap">
                            {{ $candidate['applied_date'] }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Chart.js Scripts Initialization -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const chartJs = window.Chart;
    if (!chartJs) return;

    chartJs.defaults.color = '#94a3b8';
    chartJs.defaults.font.family = "Instrument Sans, system-ui, -apple-system, sans-serif";

    // 1. By status (Donut Chart)
    const donutCtx = document.getElementById('applicationsByStatusChart');
    if (donutCtx) {
        new chartJs(donutCtx, {
            type: 'doughnut',
            data: {
                labels: @json($statusChartData['labels']),
                datasets: [
                    {
                        data: @json($statusChartData['counts']),
                        backgroundColor: @json($statusChartData['colors']),
                        borderColor: '#101726',
                        borderWidth: 2,
                        hoverOffset: 4,
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '72%',
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#0f172a',
                        titleColor: '#f8fafc',
                        bodyColor: '#cbd5e1',
                        borderColor: '#334155',
                        borderWidth: 1,
                        padding: 10,
                        displayColors: true,
                        callbacks: {
                            label: function(ctx) {
                                const total = ctx.dataset.data.reduce((a, b) => a + b, 0);
                                const val = ctx.parsed;
                                const pct = Math.round((val / total) * 100);
                                return ' ' + ctx.label + ': ' + val + ' (' + pct + '%)';
                            }
                        }
                    }
                }
            }
        });
    }

    // 2. Applications by job (Horizontal Bar Chart in solid cyan/teal)
    const barCtx = document.getElementById('applicationsByJobChart');
    if (barCtx) {
        new chartJs(barCtx, {
            type: 'bar',
            data: {
                labels: @json($jobRolesData['labels']),
                datasets: [
                    {
                        data: @json($jobRolesData['counts']),
                        backgroundColor: '#00c5bb',
                        hoverBackgroundColor: '#20ddd3',
                        borderRadius: 4,
                        borderSkipped: false,
                        barThickness: 20,
                    }
                ]
            },
            options: {
                indexAxis: 'y',
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
                        callbacks: {
                            label: function(ctx) {
                                return ' ' + ctx.parsed.x + ' pemohon';
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        display: false,
                        grid: { display: false },
                        border: { display: false }
                    },
                    y: {
                        grid: { display: false },
                        border: { display: false },
                        ticks: {
                            color: '#94a3b8',
                            font: { size: 11, weight: '500' },
                            padding: 8,
                            callback: function(value, index, values) {
                                const label = this.getLabelForValue(value);
                                return label;
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
