@extends('layouts.app', ['title' => 'Lekiu — Pengurusan HR & Kakitangan'])

@section('content')
<div class="space-y-6 pb-16">
    <!-- Header & Action Bar -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-blue-500 animate-pulse"></span>
                <span class="text-xs uppercase tracking-wider font-bold text-slate-500 dark:text-slate-400">Lekiu People & Attendance Engine</span>
            </div>
            <h1 class="text-2xl font-black tracking-tight text-slate-900 dark:text-white mt-1">
                Lekiu &bull; Pengurusan Sumber Manusia & Kakitangan
            </h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                Papan pemuka kehadiran harian, rekod cuti berkanun, agihan jabatan, dan automasi operasi kakitangan masa nyata.
            </p>
        </div>
        <div class="flex items-center gap-2.5 flex-wrap">
            <a href="{{ route('hire.index') }}" class="px-3.5 py-2 rounded-xl border border-purple-500/30 bg-purple-500/10 hover:bg-purple-500/20 text-xs font-semibold text-purple-600 dark:text-purple-400 shadow-sm transition flex items-center gap-1.5">
                <span>💼</span>
                <span>Pengambilan Staf</span>
            </a>
            <a href="{{ route('people.attendance') }}" class="px-3.5 py-2 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 hover:bg-slate-50 dark:hover:bg-slate-800/80 text-xs font-semibold text-slate-700 dark:text-slate-300 shadow-sm transition">
                Punch Card
            </a>
            <a href="{{ route('people.leave') }}" class="px-3.5 py-2 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 hover:bg-slate-50 dark:hover:bg-slate-800/80 text-xs font-semibold text-slate-700 dark:text-slate-300 shadow-sm transition">
                Cuti
            </a>
            <a href="{{ route('people.employees') }}" class="px-3.5 py-2 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 hover:bg-slate-50 dark:hover:bg-slate-800/80 text-xs font-semibold text-slate-700 dark:text-slate-300 shadow-sm transition">
                Direktori
            </a>
            <a href="{{ route('people.payroll') }}" class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold shadow-sm transition flex items-center gap-1.5">
                <span>Gaji (Payroll)</span>
                <span>&rarr;</span>
            </a>
        </div>
    </div>

    <!-- ==============================================
         ROW 1: TOP 4 KPI CARDS (Matching Screenshot)
         ============================================== -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Card 1: Headcount (Hero Blue Card) -->
        <div class="relative overflow-hidden rounded-2xl p-5 bg-gradient-to-br from-[#2563eb] to-[#1d4ed8] text-white shadow-lg shadow-blue-500/20 flex flex-col justify-between min-h-[115px]">
            <div>
                <span class="text-xs font-semibold text-blue-100">Headcount</span>
                <span class="block text-4xl font-black tracking-tight mt-1">{{ $headcount }}</span>
            </div>
            <!-- Bottom subtle underline -->
            <div class="mt-4 w-full h-1 bg-black/20 rounded-full"></div>
        </div>

        <!-- Card 2: Clocked in so far today -->
        <div class="rounded-2xl p-5 bg-[#121927] border border-slate-800/80 text-white shadow-sm flex flex-col justify-between min-h-[115px]">
            <span class="text-xs font-medium text-slate-400">Clocked in so far today</span>
            <span class="block text-3xl sm:text-4xl font-black tracking-tight text-white mt-1">{{ $clockedInToday }}</span>
            <div class="mt-4 flex items-center gap-1.5 text-[11px] text-emerald-400 font-medium">
                <span>&bull;</span> <span>{{ round(($clockedInToday / ($headcount ?: 1)) * 100) }}% kehadiran staf aktif</span>
            </div>
        </div>

        <!-- Card 3: On leave today -->
        <div class="rounded-2xl p-5 bg-[#121927] border border-slate-800/80 text-white shadow-sm flex flex-col justify-between min-h-[115px]">
            <span class="text-xs font-medium text-slate-400">On leave today</span>
            <span class="block text-3xl sm:text-4xl font-black tracking-tight text-white mt-1">{{ $onLeaveToday }}</span>
            <div class="mt-4 flex items-center gap-1.5 text-[11px] text-amber-400 font-medium">
                <span>&bull;</span> <span>Cuti sakit & cuti tahunan diluluskan</span>
            </div>
        </div>

        <!-- Card 4: Pending approvals -->
        <div class="rounded-2xl p-5 bg-[#121927] border border-slate-800/80 text-white shadow-sm flex flex-col justify-between min-h-[115px]">
            <span class="text-xs font-medium text-slate-400">Pending approvals</span>
            <span class="block text-3xl sm:text-4xl font-black tracking-tight text-white mt-1">{{ $pendingApprovals }}</span>
            <div class="mt-4 flex items-center gap-1.5 text-[11px] text-rose-400 font-medium">
                <span>&bull;</span> <span>Menunggu semakan pengurus</span>
            </div>
        </div>
    </div>

    <!-- ==============================================
         ROW 2: CHARTS (Headcount over time & By department)
         ============================================== -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Card 5 (2 Cols): Headcount over time -->
        <div class="lg:col-span-2 rounded-2xl p-6 bg-[#121927] border border-slate-800/80 text-white shadow-sm flex flex-col justify-between">
            <div>
                <div class="flex items-start gap-3">
                    <div class="w-8 h-8 rounded-lg bg-blue-500/10 border border-blue-500/20 flex items-center justify-center text-blue-400 shrink-0">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-sm font-bold text-white tracking-tight">Headcount over time</h2>
                        <p class="text-xs text-slate-400 mt-0.5">Last 8 months, by join date</p>
                    </div>
                </div>

                <!-- Line Chart Canvas -->
                <div class="mt-6 relative h-60 w-full">
                    <canvas id="headcountOverTimeChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Card 6 (1 Col): By department (Donut) -->
        <div class="rounded-2xl p-6 bg-[#121927] border border-slate-800/80 text-white shadow-sm flex flex-col justify-between">
            <div>
                <div class="flex items-start gap-3">
                    <div class="w-8 h-8 rounded-lg bg-cyan-500/10 border border-cyan-500/20 flex items-center justify-center text-cyan-400 shrink-0">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-sm font-bold text-white tracking-tight">By department</h2>
                    </div>
                </div>

                <!-- Donut Chart Container -->
                <div class="mt-4 relative h-48 w-full flex items-center justify-center">
                    <canvas id="byDepartmentChart"></canvas>
                    <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
                        <span class="text-2xl font-black text-white">{{ $headcount }}</span>
                        <span class="text-[11px] text-slate-400 font-medium">staff</span>
                    </div>
                </div>
            </div>

            <!-- Custom Legend (Matching Screenshot) -->
            <div class="mt-4 flex flex-wrap items-center justify-center gap-x-4 gap-y-1.5 pt-3 border-t border-slate-800/60 text-[11px] font-semibold text-slate-300">
                <div class="flex items-center gap-1.5">
                    <span class="w-2.5 h-2.5 rounded-sm bg-[#3b82f6]"></span>
                    <span>Sales</span>
                </div>
                <div class="flex items-center gap-1.5">
                    <span class="w-2.5 h-2.5 rounded-sm bg-[#00b8d4]"></span>
                    <span>Operations</span>
                </div>
                <div class="flex items-center gap-1.5">
                    <span class="w-2.5 h-2.5 rounded-sm bg-[#f59e0b]"></span>
                    <span>Finance</span>
                </div>
                <div class="flex items-center gap-1.5">
                    <span class="w-2.5 h-2.5 rounded-sm bg-[#ef4444]"></span>
                    <span>Management</span>
                </div>
                <div class="flex items-center gap-1.5">
                    <span class="w-2.5 h-2.5 rounded-sm bg-[#64748b]"></span>
                    <span>Marketing</span>
                </div>
            </div>
        </div>
    </div>

    <!-- ==============================================
         ROW 3: LEAVE BY TYPE, CLOCKED IN SO FAR & HR AI AGENTS
         ============================================== -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Card 7: Leave by type -->
        <div class="rounded-2xl p-6 bg-[#121927] border border-slate-800/80 text-white shadow-sm space-y-5">
            <div class="flex items-start gap-3">
                <div class="w-8 h-8 rounded-lg bg-cyan-500/10 border border-cyan-500/20 flex items-center justify-center text-cyan-400 shrink-0">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                    </svg>
                </div>
                <div>
                    <h2 class="text-sm font-bold text-white tracking-tight">Leave by type</h2>
                    <p class="text-xs text-slate-400 mt-0.5">Approved days this month</p>
                </div>
            </div>

            <!-- Horizontal Bars in Cyan (Annual, Emergency, Medical) -->
            @php
                $maxLeaveDays = max($leaveByType['annual'], $leaveByType['emergency'], $leaveByType['medical'], 1);
            @endphp
            <div class="space-y-4 pt-2">
                <!-- Annual leave -->
                <div>
                    <div class="flex items-center justify-between text-xs mb-1.5">
                        <span class="text-slate-400 font-medium">Annual leave</span>
                    </div>
                    <div class="h-9 w-full bg-slate-900/80 rounded-md overflow-hidden relative">
                        <div class="h-full bg-[#00b8d4] rounded-md transition-all duration-700" style="width: {{ round(($leaveByType['annual'] / $maxLeaveDays) * 100) }}%"></div>
                    </div>
                </div>

                <!-- Emergency leave -->
                <div>
                    <div class="flex items-center justify-between text-xs mb-1.5">
                        <span class="text-slate-400 font-medium">Emergency leave</span>
                    </div>
                    <div class="h-9 w-full bg-slate-900/80 rounded-md overflow-hidden relative">
                        <div class="h-full bg-[#00b8d4] rounded-md transition-all duration-700" style="width: {{ round(($leaveByType['emergency'] / $maxLeaveDays) * 100) }}%"></div>
                    </div>
                </div>

                <!-- Medical leave -->
                <div>
                    <div class="flex items-center justify-between text-xs mb-1.5">
                        <span class="text-slate-400 font-medium">Medical leave</span>
                    </div>
                    <div class="h-9 w-full bg-slate-900/80 rounded-md overflow-hidden relative">
                        <div class="h-full bg-[#00b8d4] rounded-md transition-all duration-700" style="width: {{ round(($leaveByType['medical'] / $maxLeaveDays) * 100) }}%"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card 8: Clocked in so far -->
        <div class="rounded-2xl p-6 bg-[#121927] border border-slate-800/80 text-white shadow-sm flex flex-col justify-between">
            <div>
                <div class="flex items-start gap-3">
                    <div class="w-8 h-8 rounded-lg bg-blue-500/10 border border-blue-500/20 flex items-center justify-center text-blue-400 shrink-0">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-sm font-bold text-white tracking-tight">Clocked in so far</h2>
                        <p class="text-xs text-slate-400 mt-0.5">Present or late today, not yet a rate</p>
                    </div>
                </div>

                <!-- Giant Number (Matching Screenshot) -->
                <div class="my-8">
                    <span class="text-6xl font-black text-white tracking-tight block">{{ $clockedInToday }}</span>
                </div>
            </div>

            <div class="pt-3 border-t border-slate-800/60 text-xs text-slate-400 flex items-center justify-between">
                <span>Jumlah pekerja aktif: <strong class="text-white">{{ $headcount }} orang</strong></span>
                <a href="{{ route('people.attendance') }}" class="text-blue-400 font-semibold hover:underline">Semak log &rarr;</a>
            </div>
        </div>

        <!-- Card 9: HR AI agents (Your always-on crew) -->
        <div class="rounded-2xl p-6 bg-[#121927] border border-slate-800/80 text-white shadow-sm space-y-4">
            <div class="flex items-start gap-3">
                <div class="w-8 h-8 rounded-lg bg-indigo-500/10 border border-indigo-500/20 flex items-center justify-center text-indigo-400 shrink-0">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                </div>
                <div>
                    <h2 class="text-sm font-bold text-white tracking-tight">HR AI agents</h2>
                    <p class="text-xs text-slate-400 mt-0.5">Your always-on crew</p>
                </div>
            </div>

            <!-- List of 4 HR Agents -->
            <div class="divide-y divide-slate-800/60 pt-1">
                @foreach($hrAgents as $agent)
                <div class="py-3 first:pt-0 last:pb-0 flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <span class="w-2 h-2 rounded-full {{ $agent['active'] ? 'bg-emerald-400' : 'bg-slate-500' }}"></span>
                        <span class="text-xs font-semibold text-white">{{ $agent['name'] }}</span>
                    </div>
                    <span class="text-xs font-medium {{ $agent['active'] ? 'text-slate-300' : 'text-slate-500' }}">
                        {{ $agent['status'] }}
                    </span>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- ==============================================
         ROW 4: ACTIVITY CARDS (On leave today, Pending approvals & Birthdays)
         ============================================== -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Card 10: On leave today (Matching Screenshot) -->
        <div class="rounded-2xl p-6 bg-[#121927] border border-slate-800/80 text-white shadow-sm space-y-4">
            <div class="flex items-start gap-3">
                <div class="w-8 h-8 rounded-lg bg-cyan-500/10 border border-cyan-500/20 flex items-center justify-center text-cyan-400 shrink-0">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                    </svg>
                </div>
                <div>
                    <h2 class="text-sm font-bold text-white tracking-tight">On leave today</h2>
                    <p class="text-xs text-slate-400 mt-0.5">{{ $today->format('j M') }}</p>
                </div>
            </div>

            <!-- On Leave Person Card (Lim Wei Jie) -->
            <div class="p-3.5 rounded-xl border border-slate-800/80 bg-slate-900/60 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-full bg-slate-800 border border-slate-700 flex items-center justify-center text-xs font-bold text-slate-200">
                        LWJ
                    </div>
                    <div>
                        <h3 class="text-xs font-bold text-white">
                            {{ $primaryOnLeave->employee->full_name ?? 'Lim Wei Jie' }}
                        </h3>
                        <p class="text-[11px] text-slate-400">
                            {{ ucfirst($primaryOnLeave->leave_type ?? 'medical') }} leave
                        </p>
                    </div>
                </div>
                <span class="text-xs font-medium text-slate-400">
                    {{ $today->format('j M') }}
                </span>
            </div>
        </div>

        <!-- Card 11: Pending approvals (Matching Screenshot) -->
        <div class="rounded-2xl p-6 bg-[#121927] border border-slate-800/80 text-white shadow-sm space-y-4">
            <div class="flex items-start gap-3">
                <div class="w-8 h-8 rounded-lg bg-blue-500/10 border border-blue-500/20 flex items-center justify-center text-blue-400 shrink-0">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                </div>
                <div>
                    <h2 class="text-sm font-bold text-white tracking-tight">Pending approvals</h2>
                    <p class="text-xs text-slate-400 mt-0.5">Awaiting your action</p>
                </div>
            </div>

            <!-- Pending Request Card (Tan Mei Ling) -->
            <div class="p-3.5 rounded-xl border border-slate-800/80 bg-slate-900/60 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 shrink-0"></span>
                    <div>
                        <h3 class="text-xs font-bold text-white">
                            {{ $primaryPending->employee->full_name ?? 'Tan Mei Ling' }}
                        </h3>
                        <p class="text-[11px] text-slate-400">
                            {{ (int) ($primaryPending->days_count ?? 1) }} day &middot; {{ \Carbon\Carbon::parse($primaryPending->start_date ?? '2026-10-17')->format('j M') }}
                        </p>
                    </div>
                </div>
                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-indigo-500/20 text-indigo-300 border border-indigo-500/30">
                    {{ ucfirst($primaryPending->leave_type ?? 'emergency') }} leave
                </span>
            </div>
        </div>

        <!-- Card 12: Birthdays & anniversaries (Matching Screenshot) -->
        <div class="rounded-2xl p-6 bg-[#121927] border border-slate-800/80 text-white shadow-sm space-y-4">
            <div class="flex items-start gap-3">
                <div class="w-8 h-8 rounded-lg bg-indigo-500/10 border border-indigo-500/20 flex items-center justify-center text-indigo-400 shrink-0">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </div>
                <div>
                    <h2 class="text-sm font-bold text-white tracking-tight">Birthdays & anniversaries</h2>
                    <p class="text-xs text-slate-400 mt-0.5">In the next 30 days</p>
                </div>
            </div>

            <!-- Birthday Event Card (Nurul Huda - 23 Oct) -->
            <div class="p-3.5 rounded-xl border border-slate-800/80 bg-slate-900/60 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-blue-500/10 border border-blue-500/20 flex items-center justify-center text-blue-400">
                        🎂
                    </div>
                    <div>
                        <h3 class="text-xs font-bold text-white">Nurul Huda</h3>
                        <p class="text-[11px] text-slate-400">Birthday</p>
                    </div>
                </div>
                <span class="text-xs font-medium text-slate-400">
                    23 Oct
                </span>
            </div>
        </div>
    </div>
</div>

<!-- ==============================================
     CHART.JS SCRIPTS (Headcount over time & Donut)
     ============================================== -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    const chartJs = window.Chart;
    if (!chartJs) return;

    // 1. Headcount over time (Line Area Chart)
    const lineCtx = document.getElementById('headcountOverTimeChart');
    if (lineCtx) {
        new chartJs(lineCtx, {
            type: 'line',
            data: {
                labels: @json($monthsLabels),
                datasets: [
                    {
                        label: 'Headcount',
                        data: @json($headcountTrend),
                        borderColor: '#3b82f6',
                        backgroundColor: function(context) {
                            const chart = context.chart;
                            const {ctx, chartArea} = chart;
                            if (!chartArea) return 'rgba(59, 130, 246, 0.2)';
                            const gradient = ctx.createLinearGradient(0, chartArea.top, 0, chartArea.bottom);
                            gradient.addColorStop(0, 'rgba(59, 130, 246, 0.35)');
                            gradient.addColorStop(1, 'rgba(59, 130, 246, 0.0)');
                            return gradient;
                        },
                        borderWidth: 2,
                        fill: true,
                        tension: 0.2,
                        pointRadius: 0,
                        pointHoverRadius: 4,
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
                    x: {
                        grid: { display: false },
                        ticks: {
                            color: '#64748b',
                            font: { size: 11, weight: '500' }
                        }
                    },
                    y: {
                        display: false,
                        grid: {
                            color: 'rgba(255, 255, 255, 0.05)'
                        },
                        min: 0,
                        max: 25,
                    }
                }
            }
        });
    }

    // 2. By department (Donut Chart)
    const donutCtx = document.getElementById('byDepartmentChart');
    if (donutCtx) {
        new chartJs(donutCtx, {
            type: 'doughnut',
            data: {
                labels: @json($departmentData['labels']),
                datasets: [
                    {
                        data: @json($departmentData['counts']),
                        backgroundColor: @json($departmentData['colors']),
                        borderColor: '#121927',
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
                        padding: 8,
                    }
                }
            }
        });
    }
});
</script>
@endsection
