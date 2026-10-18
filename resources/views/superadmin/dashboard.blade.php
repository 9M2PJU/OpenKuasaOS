@extends('layouts.app', ['title' => 'Pusat Kawalan Superadmin'])

@section('content')
<div class="space-y-8">
    <!-- Header with Master Badge & Quick Navigation -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-extrabold bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300 border border-amber-300 dark:border-amber-700/60 uppercase tracking-widest">
                    Self-Hosted Master Console
                </span>
                <span class="text-xs text-slate-400">KerjaAI Core</span>
            </div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white mt-1">
                Pusat Kawalan Superadmin
            </h1>
            <p class="text-xs text-slate-500 mt-0.5">
                Pemantauan telemetri instalasi sistem, pendaftaran tenant perniagaan, dan kesihatan infrastruktur pelayan.
            </p>
        </div>
        <div class="flex items-center gap-2.5 flex-wrap">
            <a href="{{ route('superadmin.users.index') }}" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-xs font-semibold text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800 transition shadow-sm">
                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                <span>Pengguna</span>
            </a>
            <a href="{{ route('superadmin.organizations.index') }}" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-xs font-semibold text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800 transition shadow-sm">
                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                <span>Organisasi</span>
            </a>
            <a href="{{ route('superadmin.system.index') }}" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-amber-500/10 text-amber-700 dark:text-amber-300 border border-amber-300 dark:border-amber-800/80 text-xs font-semibold hover:bg-amber-500/20 transition shadow-sm">
                <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                <span>Kesihatan Pelayan</span>
            </a>
        </div>
    </div>

    <!-- 4.3: 4 Kad KPI Utama Sistem -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- 1. Jumlah Pendaftaran Pengguna -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-sm relative overflow-hidden">
            <div class="flex items-center justify-between text-xs font-medium text-slate-500">
                <span>Jumlah Pendaftaran Pengguna</span>
                <span class="p-1.5 rounded-lg bg-indigo-50 text-indigo-600 dark:bg-indigo-950/60 dark:text-indigo-400 font-bold">
                    👥
                </span>
            </div>
            <div class="mt-3 flex items-baseline gap-2">
                <span class="text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">
                    {{ $breakdown['total'] }}
                </span>
                <span class="text-xs text-slate-500 font-medium">pengguna</span>
            </div>
            <div class="mt-2.5 pt-2.5 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-[11px]">
                <span class="text-slate-600 dark:text-slate-400">
                    <strong class="text-slate-900 dark:text-white font-semibold">{{ $breakdown['real'] }}</strong> Pemilik Sebenar
                </span>
                <span class="text-amber-600 dark:text-amber-400 font-medium">
                    <strong class="font-semibold">{{ $breakdown['demo'] }}</strong> Demo
                </span>
            </div>
        </div>

        <!-- 2. Jumlah Organisasi / Syarikat -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-sm relative overflow-hidden">
            <div class="flex items-center justify-between text-xs font-medium text-slate-500">
                <span>Jumlah Organisasi / Syarikat</span>
                <span class="p-1.5 rounded-lg bg-emerald-50 text-emerald-600 dark:bg-emerald-950/60 dark:text-emerald-400">
                    🏢
                </span>
            </div>
            <div class="mt-3 flex items-baseline gap-2">
                <span class="text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">
                    {{ $totalOrgs }}
                </span>
                <span class="text-xs text-slate-500 font-medium">syarikat berdaftar</span>
            </div>
            <div class="mt-2.5 pt-2.5 border-t border-slate-100 dark:border-slate-800 text-[11px] text-slate-500 dark:text-slate-400 truncate">
                Contoh: Sabily Enterprise, Rimba Ventures
            </div>
        </div>

        <!-- 3. Pengguna Aktif (7 Hari Terakhir) -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-sm relative overflow-hidden">
            <div class="flex items-center justify-between text-xs font-medium text-slate-500">
                <span>Pengguna Aktif (7 Hari)</span>
                <span class="p-1.5 rounded-lg bg-blue-50 text-blue-600 dark:bg-blue-950/60 dark:text-blue-400">
                    ⚡
                </span>
            </div>
            <div class="mt-3 flex items-baseline gap-2">
                <span class="text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">
                    {{ $activeUsers }}
                </span>
                <span class="text-xs text-slate-500 font-medium">log masuk aktif</span>
            </div>
            <div class="mt-2.5 pt-2.5 border-t border-slate-100 dark:border-slate-800 text-[11px] text-emerald-600 dark:text-emerald-400 flex items-center gap-1 font-medium">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 inline-block animate-pulse"></span>
                <span>Interaksi sistem direkodkan dalam 7 hari</span>
            </div>
        </div>

        <!-- 4. Status Kesihatan Pelayan -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-sm relative overflow-hidden">
            <div class="flex items-center justify-between text-xs font-medium text-slate-500">
                <span>Kesihatan Pelayan</span>
                <span class="p-1.5 rounded-lg bg-emerald-50 text-emerald-600 dark:bg-emerald-950/60 dark:text-emerald-400">
                    🛡️
                </span>
            </div>
            <div class="mt-3 space-y-1.5">
                <!-- PostgreSQL Status -->
                <div class="flex items-center justify-between text-[11px]">
                    <span class="text-slate-600 dark:text-slate-400 flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full {{ $health['database']['connected'] ? 'bg-emerald-500' : 'bg-rose-500' }}"></span>
                        PostgreSQL
                    </span>
                    <span class="font-semibold {{ $health['database']['connected'] ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600' }}">
                        {{ $health['database']['connected'] ? 'Connected' : 'Disconnected' }}
                    </span>
                </div>
                <!-- Tuah AI Status -->
                <div class="flex items-center justify-between text-[11px]">
                    <span class="text-slate-600 dark:text-slate-400 flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full {{ $health['ai']['ready'] ? 'bg-emerald-500' : 'bg-amber-500' }}"></span>
                        Tuah AI Engine
                    </span>
                    <span class="font-semibold text-emerald-600 dark:text-emerald-400">
                        {{ $health['ai']['ready'] ? 'Ready' : 'Standby' }}
                    </span>
                </div>
                <!-- Storage Status -->
                <div class="flex items-center justify-between text-[11px]">
                    <span class="text-slate-600 dark:text-slate-400 flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full {{ $health['storage']['ok'] ? 'bg-emerald-500' : 'bg-rose-500' }}"></span>
                        Storan Pelayan
                    </span>
                    <span class="font-semibold text-emerald-600 dark:text-emerald-400">
                        Storage OK ({{ $health['storage']['free_formatted'] }})
                    </span>
                </div>
            </div>
            <div class="mt-2 pt-2 border-t border-slate-100 dark:border-slate-800 text-[10px] text-slate-400 flex items-center justify-between">
                <span>PHP {{ $health['php_version'] }}</span>
                <span>Laravel {{ $health['laravel_version'] }}</span>
            </div>
        </div>
    </div>

    <!-- 4.3: Carta Tren Pendaftaran (Registration Trends) -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-sm">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-6">
            <div>
                <h2 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                    <span>📈 Tren Pendaftaran Pengguna Baharu</span>
                </h2>
                <p class="text-xs text-slate-500">Statistik pendaftaran akaun harian sepanjang 14 hari terakhir.</p>
            </div>
            <div class="flex items-center gap-2 text-xs text-slate-500 font-medium">
                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300 font-semibold border border-indigo-200 dark:border-indigo-800">
                    Jumlah Tempoh: {{ $trends['total_in_period'] }} pendaftaran
                </span>
            </div>
        </div>

        <!-- Interactive Chart.js Canvas Container -->
        <div class="h-64 relative w-full" x-data="{
            initChart() {
                if (typeof Chart !== 'undefined' && this.$refs.canvas) {
                    const ctx = this.$refs.canvas.getContext('2d');
                    new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: {{ json_encode($trends['labels']) }},
                            datasets: [{
                                label: 'Pendaftaran Baharu',
                                data: {{ json_encode($trends['data']) }},
                                borderColor: '#6366f1',
                                backgroundColor: 'rgba(99, 102, 241, 0.1)',
                                tension: 0.35,
                                fill: true,
                                borderWidth: 2.5,
                                pointBackgroundColor: '#4f46e5',
                                pointBorderColor: '#ffffff',
                                pointBorderWidth: 2,
                                pointRadius: 4,
                                pointHoverRadius: 6,
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: { display: false },
                                tooltip: {
                                    backgroundColor: '#1e293b',
                                    titleColor: '#ffffff',
                                    bodyColor: '#cbd5e1',
                                    padding: 10,
                                    borderRadius: 8,
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        precision: 0,
                                        color: '#94a3b8',
                                        font: { size: 10 }
                                    },
                                    grid: { color: 'rgba(148, 163, 184, 0.1)' }
                                },
                                x: {
                                    ticks: {
                                        color: '#94a3b8',
                                        font: { size: 10 }
                                    },
                                    grid: { display: false }
                                }
                            }
                        }
                    });
                }
            }
        }" x-init="initChart()">
            <canvas x-ref="canvas"></canvas>
        </div>

        <!-- Visual Tailwind Fallback Bars for Quick Inspection -->
        <div class="mt-6 pt-4 border-t border-slate-100 dark:border-slate-800">
            <span class="text-[11px] font-semibold text-slate-400 block mb-3 uppercase tracking-wider">Perincian Harian</span>
            <div class="grid grid-cols-7 sm:grid-cols-14 gap-1.5 text-center">
                @php
                    $maxCount = max(max($trends['data']), 1);
                @endphp
                @foreach($trends['trends'] as $item)
                <div class="flex flex-col items-center gap-1 group">
                    <span class="text-[10px] font-bold text-slate-700 dark:text-slate-300">{{ $item['count'] }}</span>
                    <div class="w-full bg-slate-100 dark:bg-slate-800 rounded h-16 flex items-end justify-center p-0.5">
                        <div class="w-full bg-gradient-to-t from-indigo-600 to-indigo-400 rounded-sm transition-all duration-300 group-hover:from-indigo-500 group-hover:to-indigo-300" style="height: {{ max(10, ($item['count'] / $maxCount) * 100) }}%"></div>
                    </div>
                    <span class="text-[9px] text-slate-400 truncate w-full">{{ $item['label'] }}</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- 4.3: Jadual Aktiviti Terkini (Recent Signups) -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-sm">
        <div class="flex items-center justify-between mb-5">
            <div>
                <h2 class="text-base font-bold text-slate-900 dark:text-white">Pendaftaran Pengguna Terbaharu</h2>
                <p class="text-xs text-slate-500">5 akaun pengguna dan penyewa terbaharu yang menyertai instalasi ini.</p>
            </div>
            <a href="{{ route('superadmin.users.index') }}" class="text-xs font-semibold text-indigo-600 hover:text-indigo-500 dark:text-indigo-400 flex items-center gap-1">
                <span>Lihat Semua Pengguna</span>
                <span>&rarr;</span>
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-slate-200 dark:border-slate-800 text-slate-400 uppercase font-semibold text-[10px] tracking-wider">
                        <th class="pb-3 pl-2">Nama Pengguna</th>
                        <th class="pb-3">Emel</th>
                        <th class="pb-3">Syarikat / Organisasi</th>
                        <th class="pb-3">Tarikh Daftar</th>
                        <th class="pb-3">Peranan Global</th>
                        <th class="pb-3 pr-2 text-right">Status Akaun</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-slate-600 dark:text-slate-300 font-medium">
                    @forelse($recentSignups as $signup)
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition">
                        <td class="py-3 pl-2 flex items-center gap-3 font-semibold text-slate-900 dark:text-white">
                            <div class="w-7 h-7 rounded-full bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 flex items-center justify-center font-bold text-[11px] text-slate-700 dark:text-slate-300 flex-shrink-0">
                                {{ strtoupper(substr($signup->name, 0, 1)) }}
                            </div>
                            <span class="truncate">{{ $signup->name }}</span>
                        </td>
                        <td class="py-3 text-slate-500">
                            {{ $signup->email }}
                        </td>
                        <td class="py-3 text-slate-700 dark:text-slate-300 font-medium">
                            @if($signup->orgs->isNotEmpty())
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded bg-slate-100 dark:bg-slate-800 text-slate-800 dark:text-slate-200 text-[11px]">
                                    🏢 {{ $signup->orgs->first()->name }}
                                </span>
                            @else
                                <span class="text-slate-400 italic">Tiada Organisasi</span>
                            @endif
                        </td>
                        <td class="py-3 text-slate-500 text-[11px]">
                            <span>{{ $signup->created_at->format('d M Y, h:i A') }}</span>
                            <span class="block text-[10px] text-slate-400">{{ $signup->created_at->diffForHumans() }}</span>
                        </td>
                        <td class="py-3">
                            @if($signup->isSuperadmin())
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300 border border-amber-300 dark:border-amber-700/60">
                                    👑 SUPERADMIN
                                </span>
                            @elseif($signup->isDemo())
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-yellow-100 text-yellow-800 dark:bg-yellow-950 dark:text-yellow-300 border border-yellow-300 dark:border-yellow-700/60">
                                    🧪 DEMO
                                </span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300">
                                    Pengguna Biasa
                                </span>
                            @endif
                        </td>
                        <td class="py-3 pr-2 text-right">
                            @if($signup->status === 'active')
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                    Aktif
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-rose-50 text-rose-700 dark:bg-rose-950/60 dark:text-rose-300 border border-rose-200 dark:border-rose-800">
                                    <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                    Digantung
                                </span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-8 text-center text-slate-400 italic">
                            Tiada rekod pendaftaran ditemui.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
