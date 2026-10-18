@php
    $isMalay = app()->getLocale() === 'ms';
@endphp

@extends('layouts.app', ['title' => 'Lekir — Pengambilan Pekerja & Temuduga'])

@section('content')
<div class="space-y-6 pb-12">
    <!-- Header with Breadcrumb & Quick Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-slate-200 dark:border-slate-800 pb-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center justify-center w-8 h-8 rounded-xl bg-[#CA6A36]/10 text-[#CA6A36] dark:bg-[#CA6A36]/20 font-bold text-sm">
                    🎯
                </span>
                <h1 class="text-xl font-bold tracking-tight text-slate-900 dark:text-white">
                    Lekir — {{ $isMalay ? 'Pusat Pengambilan Pekerja & Temuduga' : 'Hiring & Recruitment Dashboard' }}
                </h1>
            </div>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                {{ $isMalay ? 'Pantau aliran calon pemohon, prestasi saluran sumber, ejen perekrut AI, dan jadual temu duga aktif.' : 'Track hiring pipeline, applicant sources, AI recruiter crew, and upcoming interviews.' }}
            </p>
        </div>

        <!-- Quick Navigation -->
        <div class="flex items-center gap-2 flex-wrap">
            <a href="{{ route('hire.jobs') }}" class="px-3.5 py-2 rounded-xl bg-[#CA6A36] hover:bg-[#b85a28] text-white text-xs font-semibold shadow-sm transition flex items-center gap-1.5">
                <span>➕</span>
                <span>{{ $isMalay ? 'Iklan Jawatan' : 'Open Jobs' }}</span>
            </a>
            <a href="{{ route('hire.applications') }}" class="px-3.5 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700/60 text-xs font-semibold text-slate-700 dark:text-slate-200 transition">
                {{ $isMalay ? 'Senarai Pemohon' : 'Applications' }}
            </a>
            <a href="{{ route('hire.interviews') }}" class="px-3.5 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700/60 text-xs font-semibold text-slate-700 dark:text-slate-200 transition">
                {{ $isMalay ? 'Jadual Temuduga' : 'Interviews' }}
            </a>
        </div>
    </div>

    <!-- 1. Top KPI Cards Row -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- 1. Open roles (Orange Highlighted Card) -->
        <div class="bg-[#CA6A36] text-white rounded-2xl p-5 shadow-sm flex flex-col justify-between transition hover:shadow-md">
            <span class="text-xs font-medium text-white/90">Open roles</span>
            <span class="text-3xl sm:text-4xl font-extrabold tracking-tight mt-3">{{ $openRolesCount }}</span>
        </div>

        <!-- 2. Applications -->
        <div class="bg-white dark:bg-[#101726] border border-slate-200 dark:border-[#1F293D] rounded-2xl p-5 shadow-sm flex flex-col justify-between transition hover:border-slate-300 dark:hover:border-slate-700">
            <span class="text-xs font-medium text-slate-500 dark:text-slate-400">Applications</span>
            <span class="text-3xl sm:text-4xl font-extrabold text-slate-900 dark:text-white tracking-tight mt-3">{{ $totalApplications }}</span>
        </div>

        <!-- 3. Interviews · next 7 days -->
        <div class="bg-white dark:bg-[#101726] border border-slate-200 dark:border-[#1F293D] rounded-2xl p-5 shadow-sm flex flex-col justify-between transition hover:border-slate-300 dark:hover:border-slate-700">
            <span class="text-xs font-medium text-slate-500 dark:text-slate-400">Interviews &middot; next 7 days</span>
            <span class="text-3xl sm:text-4xl font-extrabold text-slate-900 dark:text-white tracking-tight mt-3">{{ $interviewsNext7Days }}</span>
        </div>

        <!-- 4. Offers out -->
        <div class="bg-white dark:bg-[#101726] border border-slate-200 dark:border-[#1F293D] rounded-2xl p-5 shadow-sm flex flex-col justify-between transition hover:border-slate-300 dark:hover:border-slate-700">
            <span class="text-xs font-medium text-slate-500 dark:text-slate-400">Offers out</span>
            <span class="text-3xl sm:text-4xl font-extrabold text-slate-900 dark:text-white tracking-tight mt-3">{{ $offersOutCount }}</span>
        </div>
    </div>

    <!-- 2. Analytics Charts Row (Applications over time & Applications by source) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5">
        <!-- 2.1 Applications over time (Last 8 weeks) -->
        <div class="lg:col-span-8 bg-white dark:bg-[#101726] border border-slate-200 dark:border-[#1F293D] rounded-2xl p-6 shadow-sm flex flex-col justify-between">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-8 h-8 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-amber-500">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white">Applications over time</h3>
                    <span class="text-[11px] text-slate-400">Last 8 weeks</span>
                </div>
            </div>

            <!-- Canvas Container -->
            <div class="relative w-full h-64 mt-2">
                <canvas id="applicationsOverTimeChart"></canvas>
            </div>

            <!-- Bottom Custom Legend -->
            <div class="flex items-center justify-center gap-6 mt-4 pt-2 text-xs font-medium text-slate-400 border-t border-slate-100 dark:border-slate-800/60">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-sm bg-[#CA6A36]"></span>
                    <span class="text-slate-700 dark:text-slate-300">Applied</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-sm bg-[#00c5bb]"></span>
                    <span class="text-slate-700 dark:text-slate-300">Shortlisted</span>
                </div>
            </div>
        </div>

        <!-- 2.2 Applications by source Donut Chart -->
        <div class="lg:col-span-4 bg-white dark:bg-[#101726] border border-slate-200 dark:border-[#1F293D] rounded-2xl p-6 shadow-sm flex flex-col justify-between">
            <div class="flex items-center gap-3 mb-2">
                <div class="w-8 h-8 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-amber-500">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"/></svg>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white">Applications by source</h3>
                </div>
            </div>

            <!-- Donut Container with Centered Count -->
            <div class="relative w-full h-56 flex items-center justify-center my-auto">
                <canvas id="applicationsBySourceChart"></canvas>
                <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
                    <span class="text-2xl font-black text-slate-900 dark:text-white tracking-tight">{{ $totalApplications }}</span>
                    <span class="text-[10px] text-slate-400 font-medium">applications</span>
                </div>
            </div>

            <!-- Source Legend -->
            <div class="grid grid-cols-2 gap-2 mt-4 pt-3 text-[11px] font-medium text-slate-400 border-t border-slate-100 dark:border-slate-800/60">
                <div class="flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-sm bg-[#CA6A36] flex-shrink-0"></span>
                    <span class="text-slate-700 dark:text-slate-300 truncate">JobStreet</span>
                </div>
                <div class="flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-sm bg-[#06B6D4] flex-shrink-0"></span>
                    <span class="text-slate-700 dark:text-slate-300 truncate">LinkedIn</span>
                </div>
                <div class="flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-sm bg-[#EAB308] flex-shrink-0"></span>
                    <span class="text-slate-700 dark:text-slate-300 truncate">Referral</span>
                </div>
                <div class="flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-sm bg-[#E05260] flex-shrink-0"></span>
                    <span class="text-slate-700 dark:text-slate-300 truncate">Careers page</span>
                </div>
            </div>
        </div>
    </div>

    <!-- 3. Middle Section: Pipeline, AI Recruiters, Offer-accept rate -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        <!-- 3.1 Hiring Pipeline (Applied -> Hired) -->
        <div class="bg-white dark:bg-[#101726] border border-slate-200 dark:border-[#1F293D] rounded-2xl p-6 shadow-sm flex flex-col justify-between">
            <div>
                <div class="flex items-center gap-3 mb-5">
                    <div class="w-8 h-8 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-amber-500">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-900 dark:text-white">Hiring pipeline</h3>
                        <span class="text-[11px] text-slate-400">Applied &rarr; hired</span>
                    </div>
                </div>

                <div class="space-y-3.5">
                    @foreach($pipeline as $stage)
                    <div class="flex items-center justify-between text-xs">
                        <span class="w-20 text-slate-400 text-right pr-3 text-[11px]">{{ $stage['label'] }}</span>
                        <div class="flex-1 flex justify-start">
                            <div class="{{ $stage['color'] }} text-white font-bold py-1 px-3 rounded-xl text-center text-xs shadow-sm transition-all" style="width: {{ $stage['width'] }}; min-width: 38px;">
                                {{ $stage['count'] }}
                            </div>
                        </div>
                        <span class="w-12 text-slate-400 text-right pl-2 text-[11px]">{{ $stage['percentage'] }}%</span>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- 3.2 AI Recruiters (Your always-on crew) -->
        <div class="bg-white dark:bg-[#101726] border border-slate-200 dark:border-[#1F293D] rounded-2xl p-6 shadow-sm flex flex-col justify-between">
            <div>
                <div class="flex items-center gap-3 mb-5">
                    <div class="w-8 h-8 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-amber-500">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-900 dark:text-white">AI recruiters</h3>
                        <span class="text-[11px] text-slate-400">Your always-on crew</span>
                    </div>
                </div>

                <div class="space-y-3">
                    @foreach($aiRecruiters as $agent)
                    <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 dark:bg-[#0c1220] border border-slate-100 dark:border-slate-800/60">
                        <div class="flex items-center gap-2.5">
                            @if($agent['active'])
                            <span class="w-2 h-2 rounded-full bg-emerald-500 shadow-[0_0_8px_rgba(16,185,129,0.7)] animate-pulse"></span>
                            @else
                            <span class="w-2 h-2 rounded-full bg-slate-400"></span>
                            @endif
                            <span class="text-xs font-semibold text-slate-800 dark:text-slate-200">{{ $agent['name'] }}</span>
                        </div>
                        <span class="text-xs font-medium {{ $agent['active'] ? 'text-slate-400' : 'text-slate-500' }}">
                            {{ $agent['status'] }}
                        </span>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- 3.3 Offer-accept rate -->
        <div class="bg-white dark:bg-[#101726] border border-slate-200 dark:border-[#1F293D] rounded-2xl p-6 shadow-sm flex flex-col justify-between">
            <div>
                <div class="flex items-center gap-3 mb-5">
                    <div class="w-8 h-8 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-amber-500">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-900 dark:text-white">Offer-accept rate</h3>
                        <span class="text-[11px] text-slate-400">Last 90 days</span>
                    </div>
                </div>

                <div class="py-14 text-center my-auto flex flex-col items-center justify-center">
                    <span class="text-xs font-medium text-slate-400 dark:text-slate-500">Not available yet</span>
                </div>
            </div>
        </div>
    </div>

    <!-- 4. Bottom Row: Upcoming interviews & Top candidates -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
        <!-- 4.1 Upcoming interviews -->
        <div class="bg-white dark:bg-[#101726] border border-slate-200 dark:border-[#1F293D] rounded-2xl p-6 shadow-sm">
            <div class="flex items-center justify-between mb-5">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-amber-500">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-900 dark:text-white">Upcoming interviews</h3>
                        <span class="text-[11px] text-slate-400">Next on your calendar</span>
                    </div>
                </div>
                <a href="{{ route('hire.interviews') }}" class="text-xs font-semibold text-[#CA6A36] hover:underline">
                    {{ $isMalay ? 'Kalendar' : 'View all' }} &rarr;
                </a>
            </div>

            <div class="space-y-3">
                @foreach($upcomingInterviews as $interview)
                <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-[#0c1220] border border-slate-100 dark:border-slate-800/60 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 shadow-[0_0_8px_rgba(16,185,129,0.7)] flex-shrink-0"></span>
                        <div class="w-8 h-8 rounded-full bg-[#CA6A36]/15 text-[#CA6A36] dark:bg-[#CA6A36]/30 dark:text-amber-300 font-bold text-xs flex items-center justify-center flex-shrink-0">
                            {{ $interview['initials'] }}
                        </div>
                        <div>
                            <span class="block text-xs font-bold text-slate-900 dark:text-white">{{ $interview['name'] }}</span>
                            <span class="block text-[11px] text-slate-400">{{ $interview['role'] }}</span>
                        </div>
                    </div>
                    <div class="text-right flex-shrink-0">
                        <span class="block text-xs font-medium text-slate-800 dark:text-slate-200">{{ $interview['date'] }}</span>
                        <span class="block text-[11px] text-slate-400 mt-0.5">{{ $interview['type'] }}</span>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <!-- 4.2 Top candidates -->
        <div class="bg-white dark:bg-[#101726] border border-slate-200 dark:border-[#1F293D] rounded-2xl p-6 shadow-sm">
            <div class="flex items-center justify-between mb-5">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-amber-500">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-900 dark:text-white">Top candidates</h3>
                        <span class="text-[11px] text-slate-400">Highest rated</span>
                    </div>
                </div>
                <a href="{{ route('hire.applications') }}" class="text-xs font-semibold text-[#CA6A36] hover:underline">
                    {{ $isMalay ? 'Semua Calon' : 'All Candidates' }} &rarr;
                </a>
            </div>

            <div class="space-y-3">
                @foreach($topCandidates as $candidate)
                <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-[#0c1220] border border-slate-100 dark:border-slate-800/60 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full bg-[#CA6A36]/15 text-[#CA6A36] dark:bg-[#CA6A36]/30 dark:text-amber-300 font-bold text-xs flex items-center justify-center flex-shrink-0">
                            {{ $candidate['initials'] }}
                        </div>
                        <div>
                            <span class="block text-xs font-bold text-slate-900 dark:text-white">{{ $candidate['name'] }}</span>
                            <span class="block text-[11px] text-slate-400">{{ $candidate['role'] }}</span>
                        </div>
                    </div>
                    <div class="flex-shrink-0">
                        <span class="px-2.5 py-1 rounded-lg text-xs font-bold bg-emerald-950/30 text-emerald-400 border border-emerald-500/30 flex items-center gap-1 shadow-sm">
                            <span class="text-xs">&star;</span>
                            <span>{{ $candidate['rating'] }}</span>
                        </span>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

<!-- Chart.js Library & Initialization Script -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const chartJs = window.Chart;
    if (!chartJs) return;

    chartJs.defaults.color = '#94a3b8';
    chartJs.defaults.font.family = "Instrument Sans, system-ui, -apple-system, sans-serif";

    // 1. Applications over time (Smooth Spline Area Chart)
    const lineCtx = document.getElementById('applicationsOverTimeChart');
    if (lineCtx) {
        const ctx2d = lineCtx.getContext('2d');

        // Copper/Terracotta gradient
        const gradOrange = ctx2d.createLinearGradient(0, 0, 0, 240);
        gradOrange.addColorStop(0, 'rgba(202, 106, 54, 0.35)');
        gradOrange.addColorStop(1, 'rgba(202, 106, 54, 0.00)');

        // Teal gradient
        const gradTeal = ctx2d.createLinearGradient(0, 0, 0, 240);
        gradTeal.addColorStop(0, 'rgba(0, 197, 187, 0.15)');
        gradTeal.addColorStop(1, 'rgba(0, 197, 187, 0.00)');

        new chartJs(lineCtx, {
            type: 'line',
            data: {
                labels: @json($weeklyLabels),
                datasets: [
                    {
                        label: 'Applied',
                        data: @json($appliedWeeklyData),
                        borderColor: '#CA6A36',
                        backgroundColor: gradOrange,
                        fill: true,
                        tension: 0.45,
                        borderWidth: 2.5,
                        pointRadius: 0,
                        pointHoverRadius: 5,
                        pointHoverBackgroundColor: '#CA6A36',
                        pointHoverBorderColor: '#ffffff',
                    },
                    {
                        label: 'Shortlisted',
                        data: @json($shortlistedWeeklyData),
                        borderColor: '#00c5bb',
                        backgroundColor: gradTeal,
                        fill: true,
                        tension: 0.45,
                        borderWidth: 2,
                        pointRadius: 0,
                        pointHoverRadius: 5,
                        pointHoverBackgroundColor: '#00c5bb',
                        pointHoverBorderColor: '#ffffff',
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
                        displayColors: true,
                        callbacks: {
                            label: function(ctx) {
                                return ctx.dataset.label + ': ' + ctx.parsed.y + ' pemohon';
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        border: { display: false },
                        ticks: {
                            color: '#94a3b8',
                            font: { size: 11, weight: '500' }
                        }
                    },
                    y: {
                        display: false,
                        grid: { display: false },
                        border: { display: false }
                    }
                }
            }
        });
    }

    // 2. Applications by source (Donut Chart)
    const donutCtx = document.getElementById('applicationsBySourceChart');
    if (donutCtx) {
        new chartJs(donutCtx, {
            type: 'doughnut',
            data: {
                labels: @json($sourceData['labels']),
                datasets: [
                    {
                        data: @json($sourceData['counts']),
                        backgroundColor: @json($sourceData['colors']),
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
});
</script>
@endsection
