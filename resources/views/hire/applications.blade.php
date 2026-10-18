@php
    $isMalay = app()->getLocale() === 'ms';
@endphp

@extends('layouts.app', ['title' => 'Lekir — Calon Pemohon Pekerjaan'])

@section('content')
<div class="space-y-6 pb-12" x-data="applicationsManager()">
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
                        <th class="py-3.5 pl-6 pr-4">Calon (Candidate)</th>
                        <th class="py-3.5 px-4">Jawatan &amp; Umur</th>
                        <th class="py-3.5 px-4">Resume / CV</th>
                        <th class="py-3.5 px-4">Saringan Kelayakan AI</th>
                        <th class="py-3.5 px-4">Peringkat (Stage)</th>
                        <th class="py-3.5 pr-6 text-right">Tindakan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100/70 dark:divide-slate-800/50">
                    <!-- 1. Real applications from DB -->
                    @foreach($applications as $dbApp)
                        @php
                            $init = strtoupper(substr($dbApp->candidate_name ?? 'C', 0, 1));
                            $isActive = in_array($dbApp->stage, ['applied', 'screening', 'interview', 'offered', 'hired']);
                        @endphp
                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition">
                            <!-- Candidate -->
                            <td class="py-4 pl-6 pr-4">
                                <div class="flex items-start gap-3">
                                    <div class="w-8 h-8 rounded-full bg-[#CA6A36]/15 text-[#CA6A36] dark:bg-[#CA6A36]/30 dark:text-amber-300 font-bold text-xs flex items-center justify-center shrink-0 mt-0.5">
                                        {{ $init }}
                                    </div>
                                    <div class="min-w-0">
                                        <span class="font-bold text-slate-900 dark:text-white block truncate max-w-[200px]">
                                            {{ $dbApp->candidate_name }}
                                        </span>
                                        <div class="text-[11px] text-slate-400 truncate">
                                            {{ $dbApp->email }} &bull; {{ $dbApp->phone }}
                                        </div>
                                        @if($dbApp->address)
                                        <div class="text-[10px] text-slate-500 truncate max-w-[200px]">
                                            📍 {{ $dbApp->address }}
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            <!-- Job & Age -->
                            <td class="py-4 px-4">
                                <span class="font-semibold text-slate-800 dark:text-slate-200 block">{{ $dbApp->job->title ?? 'Jawatan' }}</span>
                                <span class="text-[11px] text-slate-400">
                                    @if($dbApp->birth_date)
                                        {{ $dbApp->age }} tahun ({{ $dbApp->birth_date->format('d/m/Y') }})
                                    @else
                                        Umur tidak dinyatakan
                                    @endif
                                </span>
                            </td>

                            <!-- Resume / CV -->
                            <td class="py-4 px-4 whitespace-nowrap">
                                @if($dbApp->cv_file_url)
                                <a href="{{ $dbApp->cv_file_url }}" target="_blank" download class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700/60 text-[11px] font-semibold text-slate-700 dark:text-slate-200 shadow-2xs transition">
                                    <svg class="w-3.5 h-3.5 text-rose-500" fill="currentColor" viewBox="0 0 20 20"><path d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z"/></svg>
                                    <span>Buka Resume</span>
                                </a>
                                @else
                                <span class="text-[11px] text-slate-400 italic">Tiada fail</span>
                                @endif
                            </td>

                            <!-- AI Screening -->
                            <td class="py-4 px-4">
                                @if($dbApp->ai_status)
                                <div class="flex items-center gap-2">
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold border {{ $dbApp->ai_badge_class }}">
                                        <span class="w-1.5 h-1.5 rounded-full {{ $dbApp->ai_status === 'layak' ? 'bg-emerald-400 animate-pulse' : ($dbApp->ai_status === 'pertimbangan' ? 'bg-amber-400' : 'bg-rose-400') }}"></span>
                                        <span>{{ $dbApp->ai_score }}% {{ $dbApp->ai_status_label }}</span>
                                    </span>
                                    <button type="button" @click="openAiModal(@js([
                                        'id' => $dbApp->id,
                                        'candidate_name' => $dbApp->candidate_name,
                                        'job_title' => $dbApp->job->title ?? 'Jawatan',
                                        'email' => $dbApp->email,
                                        'phone' => $dbApp->phone,
                                        'birth_date' => $dbApp->birth_date?->format('d M Y'),
                                        'age' => $dbApp->age,
                                        'address' => $dbApp->address,
                                        'cv_file_url' => $dbApp->cv_file_url,
                                        'ai_score' => $dbApp->ai_score,
                                        'ai_status' => $dbApp->ai_status,
                                        'ai_status_label' => $dbApp->ai_status_label,
                                        'ai_badge_class' => $dbApp->ai_badge_class,
                                        'ai_analysis' => $dbApp->ai_analysis,
                                        'applied_date' => $dbApp->created_at ? $dbApp->created_at->format('d M Y') : '13 Oct 2026',
                                    ]))" class="p-1 rounded-lg border border-slate-200 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-500 hover:text-indigo-600 transition" title="Lihat Laporan AI">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    </button>
                                </div>
                                @else
                                <form method="POST" action="{{ route('hire.applications.ai-screen', $dbApp->id) }}">
                                    @csrf
                                    <button type="submit" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-xl bg-purple-50 dark:bg-purple-950/40 hover:bg-purple-100 dark:hover:bg-purple-900/60 text-purple-600 dark:text-purple-300 border border-purple-200/60 dark:border-purple-800/60 text-[11px] font-bold transition">
                                        <span>⚡</span>
                                        <span>Saring AI</span>
                                    </button>
                                </form>
                                @endif
                            </td>

                            <!-- Stage -->
                            <td class="py-4 px-4">
                                <form method="POST" action="{{ route('hire.applications.update-stage', $dbApp->id) }}">
                                    @csrf
                                    <select name="stage" onchange="this.form.submit()" class="px-2 py-1 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-[11px] font-semibold text-slate-700 dark:text-slate-200 cursor-pointer">
                                        @foreach($stages as $stgKey => $stgLabel)
                                        <option value="{{ $stgKey }}" {{ $dbApp->stage === $stgKey ? 'selected' : '' }}>
                                            {{ $stgLabel }}
                                        </option>
                                        @endforeach
                                    </select>
                                </form>
                            </td>

                            <!-- Actions -->
                            <td class="py-4 pr-6 text-right whitespace-nowrap">
                                <form method="POST" action="{{ route('hire.applications.ai-screen', $dbApp->id) }}" class="inline-block">
                                    @csrf
                                    <button type="submit" title="Semak Semula AI" class="px-2.5 py-1 rounded-lg border border-slate-200 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-800 text-[11px] font-semibold text-slate-600 dark:text-slate-300 transition">
                                        Semak AI ↺
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach

                    <!-- 2. Sample Data Rows with AI Screening Evaluation -->
                    @foreach($sampleApplications as $candidate)
                    <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition">
                        <!-- Candidate -->
                        <td class="py-4 pl-6 pr-4">
                            <div class="flex items-start gap-3">
                                <div class="w-8 h-8 rounded-full bg-[#CA6A36]/15 text-[#CA6A36] dark:bg-[#CA6A36]/30 dark:text-amber-300 font-bold text-xs flex items-center justify-center shrink-0 mt-0.5">
                                    {{ $candidate['initial'] }}
                                </div>
                                <div class="min-w-0">
                                    <span class="font-bold text-slate-900 dark:text-white block truncate max-w-[200px]">
                                        {{ $candidate['candidate_name'] }}
                                    </span>
                                    <div class="text-[11px] text-slate-400 truncate">
                                        {{ $candidate['email'] }} &bull; {{ $candidate['phone'] }}
                                    </div>
                                    @if(!empty($candidate['address']))
                                    <div class="text-[10px] text-slate-500 truncate max-w-[200px]">
                                        📍 {{ $candidate['address'] }}
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </td>

                        <!-- Job & Age -->
                        <td class="py-4 px-4">
                            <span class="font-semibold text-slate-800 dark:text-slate-200 block">{{ $candidate['job_title'] }}</span>
                            <span class="text-[11px] text-slate-400">
                                {{ $candidate['age'] }} tahun ({{ $candidate['birth_date'] }})
                            </span>
                        </td>

                        <!-- Resume / CV -->
                        <td class="py-4 px-4 whitespace-nowrap">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/60 text-[11px] font-semibold text-slate-600 dark:text-slate-300">
                                <svg class="w-3.5 h-3.5 text-rose-500" fill="currentColor" viewBox="0 0 20 20"><path d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z"/></svg>
                                <span>Resume.pdf</span>
                            </span>
                        </td>

                        <!-- AI Screening -->
                        <td class="py-4 px-4">
                            <div class="flex items-center gap-2">
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold border {{ $candidate['ai_badge_class'] }}">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $candidate['ai_status'] === 'layak' ? 'bg-emerald-400' : ($candidate['ai_status'] === 'pertimbangan' ? 'bg-amber-400' : 'bg-rose-400') }}"></span>
                                    <span>{{ $candidate['ai_score'] }}% {{ $candidate['ai_status_label'] }}</span>
                                </span>
                                <button type="button" @click="openAiModal(@js($candidate))" class="p-1 rounded-lg border border-slate-200 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-500 hover:text-indigo-600 transition" title="Lihat Laporan Penuh AI">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                </button>
                            </div>
                        </td>

                        <!-- Stage -->
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

                        <!-- Actions -->
                        <td class="py-4 pr-6 text-right whitespace-nowrap">
                            <button type="button" @click="openAiModal(@js($candidate))" class="px-2.5 py-1 rounded-lg border border-slate-200 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-800 text-[11px] font-semibold text-slate-600 dark:text-slate-300 transition">
                                Laporan AI 👁️
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- AI Resume Screening Breakdown Modal -->
    <div x-show="aiModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4" style="display: none;">
        <div @click.away="closeAiModal()" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl max-w-2xl w-full p-6 sm:p-7 shadow-2xl max-h-[90vh] overflow-y-auto space-y-5">
            <!-- Modal Header -->
            <div class="flex items-start justify-between gap-4 pb-4 border-b border-slate-100 dark:border-slate-800">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-purple-50 text-purple-700 dark:bg-purple-950/60 dark:text-purple-300 border border-purple-200/60">
                            ⚡ Enjin Saringan AI Lekir
                        </span>
                        <span class="text-xs text-slate-400" x-text="activeApp?.applied_date || ''"></span>
                    </div>
                    <h3 class="text-lg font-black text-slate-900 dark:text-white mt-1" x-text="'Laporan Penilaian AI: ' + (activeApp?.candidate_name || 'Calon')"></h3>
                    <p class="text-xs text-slate-500" x-text="'Jawatan Dipohon: ' + (activeApp?.job_title || 'Jawatan')"></p>
                </div>
                <button type="button" @click="closeAiModal()" class="p-2 rounded-xl text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                    ✕
                </button>
            </div>

            <!-- Score & Status Highlight Banner -->
            <template x-if="activeApp">
                <div class="space-y-4">
                    <div class="p-4 rounded-2xl flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border"
                        :class="activeApp.ai_status === 'layak' ? 'bg-emerald-50/70 dark:bg-emerald-950/40 border-emerald-200 dark:border-emerald-800/60' : (activeApp.ai_status === 'pertimbangan' ? 'bg-amber-50/70 dark:bg-amber-950/40 border-amber-200 dark:border-amber-800/60' : 'bg-rose-50/70 dark:bg-rose-950/40 border-rose-200 dark:border-rose-800/60')">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded-2xl flex items-center justify-center font-black text-lg border"
                                :class="activeApp.ai_status === 'layak' ? 'bg-emerald-500 text-white border-emerald-400' : (activeApp.ai_status === 'pertimbangan' ? 'bg-amber-500 text-white border-amber-400' : 'bg-rose-500 text-white border-rose-400')">
                                <span x-text="(activeApp.ai_score || 0) + '%'"></span>
                            </div>
                            <div>
                                <span class="text-xs font-bold uppercase tracking-wider block"
                                    :class="activeApp.ai_status === 'layak' ? 'text-emerald-700 dark:text-emerald-300' : (activeApp.ai_status === 'pertimbangan' ? 'text-amber-700 dark:text-amber-300' : 'text-rose-700 dark:text-rose-300')"
                                    x-text="'Status Penilaian: ' + (activeApp.ai_status_label || 'Dinilai')"></span>
                                <span class="text-xs text-slate-600 dark:text-slate-300 font-medium" x-text="activeApp.ai_analysis?.recommendation || 'Sedia untuk tindakan HR.'"></span>
                            </div>
                        </div>

                        <template x-if="activeApp.cv_file_url && activeApp.cv_file_url !== '#'">
                            <a :href="activeApp.cv_file_url" target="_blank" download class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 text-xs font-semibold text-slate-700 dark:text-slate-200 shrink-0 transition">
                                <span>📄</span>
                                <span>Buka Fail CV</span>
                            </a>
                        </template>
                    </div>

                    <!-- Candidate Contact & Background -->
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs bg-slate-50 dark:bg-slate-800/50 p-3.5 rounded-2xl border border-slate-100 dark:border-slate-800">
                        <div>
                            <span class="text-[10px] text-slate-400 block">Umur:</span>
                            <span class="font-bold text-slate-700 dark:text-slate-200" x-text="(activeApp.age ? activeApp.age + ' tahun' : 'Tidak dinyatakan')"></span>
                        </div>
                        <div>
                            <span class="text-[10px] text-slate-400 block">Tarikh Lahir:</span>
                            <span class="font-bold text-slate-700 dark:text-slate-200" x-text="activeApp.birth_date || 'Tiada rekod'"></span>
                        </div>
                        <div>
                            <span class="text-[10px] text-slate-400 block">Emel:</span>
                            <span class="font-bold text-slate-700 dark:text-slate-200 truncate block" x-text="activeApp.email || '-'"></span>
                        </div>
                        <div>
                            <span class="text-[10px] text-slate-400 block">Telefon / WhatsApp:</span>
                            <span class="font-bold text-slate-700 dark:text-slate-200" x-text="activeApp.phone || '-'"></span>
                        </div>
                    </div>

                    <template x-if="activeApp.address">
                        <div class="text-xs bg-slate-50/50 dark:bg-slate-800/30 p-2.5 rounded-xl border border-slate-100 dark:border-slate-800 flex items-start gap-1.5 text-slate-600 dark:text-slate-400">
                            <span>📍</span>
                            <span x-text="'Alamat: ' + activeApp.address"></span>
                        </div>
                    </template>

                    <!-- Summary -->
                    <div>
                        <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-1.5 flex items-center gap-1.5">
                            <span>📝</span>
                            <span>Ringkasan Eksekutif AI</span>
                        </h4>
                        <div class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-slate-800 text-xs text-slate-700 dark:text-slate-200 leading-relaxed"
                            x-text="activeApp.ai_analysis?.summary || 'Tiada ringkasan dijana.'"></div>
                    </div>

                    <!-- Strengths & Gaps -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <!-- Strengths -->
                        <div class="p-3.5 rounded-2xl bg-emerald-50/40 dark:bg-emerald-950/20 border border-emerald-100 dark:border-emerald-900/40 space-y-2">
                            <span class="text-xs font-bold text-emerald-700 dark:text-emerald-300 flex items-center gap-1.5">
                                <span>✅</span>
                                <span>Kekuatan &amp; Padanan Utama</span>
                            </span>
                            <ul class="text-xs space-y-1 text-slate-700 dark:text-slate-300">
                                <template x-for="(st, idx) in (activeApp.ai_analysis?.strengths || [])" :key="idx">
                                    <li class="flex items-start gap-1.5">
                                        <span class="text-emerald-500 shrink-0">•</span>
                                        <span x-text="st"></span>
                                    </li>
                                </template>
                            </ul>
                        </div>

                        <!-- Gaps -->
                        <div class="p-3.5 rounded-2xl bg-amber-50/40 dark:bg-amber-950/20 border border-amber-100 dark:border-amber-900/40 space-y-2">
                            <span class="text-xs font-bold text-amber-700 dark:text-amber-300 flex items-center gap-1.5">
                                <span>⚠️</span>
                                <span>Jurang &amp; Aspek Pengesahan</span>
                            </span>
                            <ul class="text-xs space-y-1 text-slate-700 dark:text-slate-300">
                                <template x-for="(gp, idx) in (activeApp.ai_analysis?.gaps || [])" :key="idx">
                                    <li class="flex items-start gap-1.5">
                                        <span class="text-amber-500 shrink-0">•</span>
                                        <span x-text="gp"></span>
                                    </li>
                                </template>
                            </ul>
                        </div>
                    </div>

                    <!-- Tailored Interview Questions -->
                    <template x-if="activeApp.ai_analysis?.interview_questions && activeApp.ai_analysis.interview_questions.length > 0">
                        <div class="p-3.5 rounded-2xl bg-indigo-50/50 dark:bg-indigo-950/30 border border-indigo-100 dark:border-indigo-900/50 space-y-2">
                            <span class="text-xs font-bold text-indigo-700 dark:text-indigo-300 flex items-center gap-1.5">
                                <span>💡</span>
                                <span>Cadangan Soalan Temuduga Khusus untuk Calon Ini</span>
                            </span>
                            <ol class="text-xs space-y-1.5 text-slate-700 dark:text-slate-300 pl-4 list-decimal">
                                <template x-for="(q, qidx) in activeApp.ai_analysis.interview_questions" :key="qidx">
                                    <li x-text="q"></li>
                                </template>
                            </ol>
                        </div>
                    </template>
                </div>
            </template>

            <!-- Modal Footer -->
            <div class="pt-3 border-t border-slate-100 dark:border-slate-800 flex justify-end gap-2">
                <button type="button" @click="closeAiModal()" class="px-4 py-2 rounded-xl border border-slate-300 dark:border-slate-700 text-xs font-semibold text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800">
                    Tutup
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function applicationsManager() {
    return {
        aiModal: false,
        activeApp: null,
        openAiModal(appData) {
            this.activeApp = appData;
            this.aiModal = true;
        },
        closeAiModal() {
            this.aiModal = false;
            this.activeApp = null;
        }
    };
}
</script>

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
