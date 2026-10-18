@extends('layouts.app', ['title' => 'Jebat — Pemasaran & Iklan'])

@section('content')
<div class="space-y-6 pb-12">
    <!-- Header & Action Bar -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                <span class="text-xs uppercase tracking-wider font-bold text-slate-500 dark:text-slate-400">Jebat AI Ad Engine</span>
            </div>
            <h1 class="text-2xl font-black tracking-tight text-slate-900 dark:text-white mt-1">Jebat &bull; Pemasaran & Pengiklanan Digital</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Pengurusan kempen digital multi-platform, tangkapan prospek (leads) masa nyata dan optimasi kos iklan.</p>
        </div>
        <div class="flex items-center gap-2.5 flex-wrap">
            <a href="{{ route('reach.studio') }}" class="px-3.5 py-2 rounded-xl bg-gradient-to-r from-pink-500 to-rose-600 hover:from-pink-600 hover:to-rose-700 text-white text-xs font-bold shadow-sm transition flex items-center gap-1.5">
                <span>⚡</span>
                <span>Studio Iklan Pintar</span>
            </a>
            <a href="{{ route('reach.settings') }}" class="px-3.5 py-2 rounded-xl border border-rose-500/30 bg-rose-500/10 hover:bg-rose-500/20 text-xs font-semibold text-rose-500 dark:text-rose-400 shadow-sm transition flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                Tetapan Iklan
            </a>
            <a href="{{ route('reach.leads') }}" class="px-3.5 py-2 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 hover:bg-slate-50 dark:hover:bg-slate-800/80 text-xs font-semibold text-slate-700 dark:text-slate-300 shadow-sm transition">
                Senarai Prospek &rarr;
            </a>
            <a href="{{ route('reach.campaigns') }}" class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold shadow-sm transition">
                + Urus Kempen Iklan
            </a>
        </div>
    </div>

    <!-- ==============================================
         ROW 1: TOP 4 KPI CARDS
         ============================================== -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Card 1: Total leads (Pink Hero Card) -->
        <div class="relative overflow-hidden rounded-2xl p-5 bg-[#ff5376] text-white shadow-lg shadow-rose-500/20 flex flex-col justify-between min-h-[120px]">
            <div>
                <span class="text-xs font-semibold text-rose-100">Total leads</span>
                <span class="block text-4xl font-black tracking-tight mt-1">{{ number_format($totalLeads) }}</span>
            </div>
            <!-- Bottom Sparkline -->
            <div class="mt-4 relative h-6 w-full opacity-80">
                <svg viewBox="0 0 100 24" class="w-full h-full preserve-3d" fill="none">
                    <path d="M0,20 Q25,18 50,14 T100,6" stroke="rgba(0,0,0,0.4)" stroke-width="3" stroke-linecap="round" fill="none"/>
                </svg>
            </div>
        </div>

        <!-- Card 2: Ad spend -->
        <div class="rounded-2xl p-5 bg-[#121927] border border-slate-800/80 text-white shadow-sm flex flex-col justify-between min-h-[120px]">
            <span class="text-xs font-medium text-slate-400">Ad spend</span>
            <div class="mt-2">
                <span class="text-3xl font-black tracking-tight text-white">RM {{ number_format($totalSpend, 2) }}</span>
            </div>
            <div class="mt-2 flex items-center gap-1.5 text-[11px] text-emerald-400 font-medium">
                <span>&bull;</span> <span>Optimum ikut bajet harian</span>
            </div>
        </div>

        <!-- Card 3: Cost / lead -->
        <div class="rounded-2xl p-5 bg-[#121927] border border-slate-800/80 text-white shadow-sm flex flex-col justify-between min-h-[120px]">
            <span class="text-xs font-medium text-slate-400">Cost / lead</span>
            <div class="mt-2">
                <span class="text-3xl font-black tracking-tight text-white">RM {{ number_format($costPerLead, 2) }}</span>
            </div>
            <div class="mt-2 flex items-center gap-1.5 text-[11px] text-emerald-400 font-medium">
                <span>&bull;</span> <span>Jimat 32% vs pasaran purata</span>
            </div>
        </div>

        <!-- Card 4: Conversion -->
        <div class="rounded-2xl p-5 bg-[#121927] border border-slate-800/80 text-white shadow-sm flex flex-col justify-between min-h-[120px]">
            <span class="text-xs font-medium text-slate-400">Conversion</span>
            <div class="mt-2">
                <span class="text-3xl font-black tracking-tight text-white">{{ $conversionRate }}%</span>
            </div>
            <div class="mt-2 flex items-center gap-1.5 text-[11px] text-indigo-400 font-medium">
                <span>&bull;</span> <span>48 jualan berjaya ditutup</span>
            </div>
        </div>
    </div>

    <!-- ==============================================
         ROW 2: LEADS OVER TIME & LEADS BY CHANNEL
         ============================================== -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5">
        <!-- Left: Leads over time (approx 8 cols) -->
        <div class="lg:col-span-8 rounded-2xl bg-[#121927] border border-slate-800/80 p-6 text-white shadow-sm flex flex-col justify-between">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-rose-500/10 border border-rose-500/20 flex items-center justify-center text-rose-400 text-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-white leading-none">Leads over time</h3>
                        <p class="text-[11px] text-slate-400 mt-1">Last 8 weeks</p>
                    </div>
                </div>
                <div class="flex items-center gap-4 text-xs font-medium">
                    <div class="flex items-center gap-1.5">
                        <span class="w-2.5 h-2.5 rounded-sm bg-[#ff5376]"></span>
                        <span class="text-slate-300 text-[11px]">Leads</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span class="w-2.5 h-2.5 rounded-sm bg-[#00b8d4]"></span>
                        <span class="text-slate-300 text-[11px]">Qualified</span>
                    </div>
                </div>
            </div>

            <!-- Chart Canvas -->
            <div class="relative h-[240px] w-full mt-2">
                <canvas id="leadsOverTimeChart"></canvas>
            </div>
        </div>

        <!-- Right: Leads by channel (approx 4 cols) -->
        <div class="lg:col-span-4 rounded-2xl bg-[#121927] border border-slate-800/80 p-6 text-white shadow-sm flex flex-col justify-between">
            <div class="flex items-center gap-3 mb-2">
                <div class="w-8 h-8 rounded-lg bg-indigo-500/10 border border-indigo-500/20 flex items-center justify-center text-indigo-400 text-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-white leading-none">Leads by channel</h3>
                </div>
            </div>

            <!-- Donut Container with Center Label -->
            <div class="relative h-[210px] flex items-center justify-center my-2">
                <canvas id="leadsByChannelChart" class="max-h-[190px]"></canvas>
                <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
                    <span class="text-2xl font-black text-white leading-tight">342</span>
                    <span class="text-[10px] text-slate-400 font-semibold tracking-wider uppercase">leads</span>
                </div>
            </div>

            <!-- Legend Pills -->
            <div class="flex flex-wrap items-center justify-center gap-x-4 gap-y-1.5 text-[11px] pt-3 border-t border-slate-800/60 font-medium text-slate-300">
                <div class="flex items-center gap-1.5">
                    <span class="w-2.5 h-2.5 rounded-full bg-[#ff5376]"></span>
                    <span>WhatsApp</span>
                </div>
                <div class="flex items-center gap-1.5">
                    <span class="w-2.5 h-2.5 rounded-full bg-[#00b8d4]"></span>
                    <span>Facebook</span>
                </div>
                <div class="flex items-center gap-1.5">
                    <span class="w-2.5 h-2.5 rounded-full bg-[#3b82f6]"></span>
                    <span>Instagram</span>
                </div>
                <div class="flex items-center gap-1.5">
                    <span class="w-2.5 h-2.5 rounded-full bg-[#f59e0b]"></span>
                    <span>TikTok</span>
                </div>
            </div>
        </div>
    </div>

    <!-- ==============================================
         ROW 3: SPEND BY CHANNEL, LEAD FUNNEL, AD HEALTH
         ============================================== -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        <!-- 3A: Spend by channel -->
        <div class="rounded-2xl bg-[#121927] border border-slate-800/80 p-6 text-white shadow-sm flex flex-col justify-between">
            <div class="flex items-center gap-3 mb-5">
                <div class="w-8 h-8 rounded-lg bg-cyan-500/10 border border-cyan-500/20 flex items-center justify-center text-cyan-400 text-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-white leading-none">Spend by channel</h3>
                    <p class="text-[11px] text-slate-400 mt-1">All campaigns (RM)</p>
                </div>
            </div>

            <!-- Horizontal Bars -->
            <div class="space-y-4 my-auto">
                @php
                    $maxSpend = max($spendByChannel);
                @endphp
                @foreach($spendByChannel as $channelName => $amount)
                <div>
                    <div class="flex items-center justify-between text-xs mb-1.5">
                        <span class="text-slate-300 font-medium">{{ $channelName }}</span>
                        <span class="text-slate-400 font-mono text-[11px]">RM {{ number_format($amount, 2) }}</span>
                    </div>
                    <div class="h-3 rounded-full bg-slate-800/80 overflow-hidden">
                        <div class="h-full bg-[#00b8d4] rounded-full transition-all duration-500" style="width: {{ ($amount / $maxSpend) * 100 }}%"></div>
                    </div>
                </div>
                @endforeach
            </div>

            <div class="pt-4 mt-4 border-t border-slate-800/60 flex items-center justify-between text-xs text-slate-400">
                <span>Jumlah Perbelanjaan</span>
                <span class="font-bold text-white">RM {{ number_format($totalSpend, 2) }}</span>
            </div>
        </div>

        <!-- 3B: Lead funnel -->
        <div class="rounded-2xl bg-[#121927] border border-slate-800/80 p-6 text-white shadow-sm flex flex-col justify-between">
            <div class="flex items-center gap-3 mb-5">
                <div class="w-8 h-8 rounded-lg bg-rose-500/10 border border-rose-500/20 flex items-center justify-center text-rose-400 text-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-white leading-none">Lead funnel</h3>
                    <p class="text-[11px] text-slate-400 mt-1">Lead &rarr; won</p>
                </div>
            </div>

            <!-- Funnel Stages -->
            <div class="space-y-3.5 my-auto">
                @foreach($leadFunnel as $funnel)
                <div class="flex items-center justify-between text-xs">
                    <span class="w-20 text-slate-400 text-[11px] font-medium">{{ $funnel['stage'] }}</span>
                    <div class="flex-1 px-3">
                        <div class="mx-auto rounded-full py-1 text-center font-bold text-white text-[11px] transition-all shadow-sm" style="background-color: {{ $funnel['color'] }}; width: {{ max(28, $funnel['pct']) }}%;">
                            {{ $funnel['count'] }}
                        </div>
                    </div>
                    <span class="w-10 text-right text-slate-400 text-[11px] font-mono font-medium">{{ $funnel['pct'] }}%</span>
                </div>
                @endforeach
            </div>

            <div class="pt-4 mt-4 border-t border-slate-800/60 flex items-center justify-between text-xs text-slate-400">
                <span>Kadar Kejayaan Jualan</span>
                <span class="font-bold text-emerald-400">14% (48 Closed)</span>
            </div>
        </div>

        <!-- 3C: Ad engine health -->
        <div class="rounded-2xl bg-[#121927] border border-slate-800/80 p-6 text-white shadow-sm flex flex-col justify-between">
            <div class="flex items-center gap-3 mb-2">
                <div class="w-8 h-8 rounded-lg bg-pink-500/10 border border-pink-500/20 flex items-center justify-center text-pink-400 text-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-white leading-none">Ad engine health</h3>
                    <p class="text-[11px] text-slate-400 mt-1">Setup & delivery</p>
                </div>
            </div>

            <!-- Health Radial Gauge Chart -->
            <div class="relative h-[180px] flex items-center justify-center my-1">
                <canvas id="adHealthChart" class="max-h-[170px]"></canvas>
                <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
                    <span class="text-3xl font-black text-white leading-tight">86%</span>
                    <span class="text-[10px] text-slate-400 font-semibold tracking-wider uppercase">healthy</span>
                </div>
            </div>

            <!-- Health Sub-Items -->
            <div class="space-y-1.5 pt-3 border-t border-slate-800/60 text-[11px] text-slate-300">
                <div class="flex items-center justify-between">
                    <span class="flex items-center gap-1.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                        <span>Meta Conversions API</span>
                    </span>
                    <span class="text-emerald-400 font-mono text-[10px]">100% Match</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="flex items-center gap-1.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                        <span>TikTok Pixel Server</span>
                    </span>
                    <span class="text-emerald-400 font-mono text-[10px]">Aktif</span>
                </div>
            </div>
        </div>
    </div>

    <!-- ==============================================
         ROW 4: TOP CAMPAIGNS & BEST TIME TO ENGAGE
         ============================================== -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5">
        <!-- 4A: Top campaigns (approx 7 cols) -->
        <div class="lg:col-span-7 rounded-2xl bg-[#121927] border border-slate-800/80 p-6 text-white shadow-sm flex flex-col justify-between">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-amber-500/10 border border-amber-500/20 flex items-center justify-center text-amber-400 text-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/></svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-white leading-none">Top campaigns</h3>
                        <p class="text-[11px] text-slate-400 mt-1">By cost per lead</p>
                    </div>
                </div>
                <a href="{{ route('reach.campaigns') }}" class="text-xs text-indigo-400 hover:text-indigo-300 font-semibold">Semua Kempen &rarr;</a>
            </div>

            <!-- Campaign List -->
            <div class="divide-y divide-slate-800/60 my-auto">
                @foreach($topCampaigns as $c)
                <div class="py-3 flex items-center justify-between text-xs hover:bg-slate-800/30 px-2 rounded-xl transition">
                    <div class="flex items-center gap-3 min-w-0">
                        <span class="w-2 h-2 rounded-full {{ $c['status'] === 'active' ? 'bg-emerald-400' : 'bg-slate-400' }}"></span>
                        <div class="truncate">
                            <h4 class="font-bold text-white truncate">{{ $c['name'] }}</h4>
                            <div class="flex items-center gap-2 mt-0.5 text-[10px] text-slate-400">
                                <span>{{ $c['platform'] }}</span>
                                <span>&bull;</span>
                                <span>{{ $c['leads'] }} leads</span>
                            </div>
                        </div>
                    </div>
                    <div class="text-right flex-shrink-0 ml-4">
                        <span class="block font-mono font-bold text-rose-400">RM {{ number_format($c['cpl'], 2) }}</span>
                        <span class="block text-[10px] text-slate-500 font-mono">Belanja: RM {{ number_format($c['spend'], 2) }}</span>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <!-- 4B: Best time to engage (Heatmap, approx 5 cols) -->
        <div class="lg:col-span-5 rounded-2xl bg-[#121927] border border-slate-800/80 p-6 text-white shadow-sm flex flex-col justify-between">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-8 h-8 rounded-lg bg-pink-500/10 border border-pink-500/20 flex items-center justify-center text-pink-400 text-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-white leading-none">Best time to engage</h3>
                    <p class="text-[11px] text-slate-400 mt-1">Lead replies by slot</p>
                </div>
            </div>

            <!-- Heatmap Table Matrix -->
            <div class="my-auto overflow-x-auto">
                <table class="w-full text-center text-xs border-separate border-spacing-1.5">
                    <thead>
                        <tr class="text-[10px] text-slate-500 uppercase font-semibold">
                            <th class="text-left w-10"></th>
                            @foreach($heatmapSlots as $slot)
                            <th class="pb-1">{{ $slot }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $shadeStyles = [
                                1 => 'bg-slate-800/40 border-slate-800 text-slate-600',
                                2 => 'bg-rose-950/40 border-rose-900/40 text-rose-300',
                                3 => 'bg-rose-900/60 border-rose-800/60 text-rose-200',
                                4 => 'bg-rose-600/80 border-rose-500/80 text-white font-bold',
                                5 => 'bg-[#ff5376] border-rose-400 text-white font-black shadow-sm shadow-rose-500/40',
                            ];
                        @endphp
                        @foreach($heatmapDays as $dayIdx => $dayName)
                        <tr>
                            <td class="text-left text-[11px] font-medium text-slate-400">{{ $dayName }}</td>
                            @foreach($heatmapSlots as $slotIdx => $slot)
                                @php
                                    $val = $heatmapMatrix[$dayIdx][$slotIdx] ?? 1;
                                @endphp
                                <td>
                                    <div class="h-6 rounded-md border flex items-center justify-center text-[10px] {{ $shadeStyles[$val] ?? $shadeStyles[1] }}" title="{{ $dayName }} {{ $slot }}: Tahap respon {{ $val }}/5">
                                        {{ $val >= 4 ? '⚡' : '' }}
                                    </div>
                                </td>
                            @endforeach
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Heatmap Legend -->
            <div class="flex items-center justify-between text-[10px] text-slate-400 pt-3 border-t border-slate-800/60">
                <span>Kurang aktif</span>
                <div class="flex items-center gap-1">
                    <span class="w-3 h-3 rounded-sm bg-slate-800/60 border border-slate-700"></span>
                    <span class="w-3 h-3 rounded-sm bg-rose-950/60 border border-rose-800"></span>
                    <span class="w-3 h-3 rounded-sm bg-rose-700 border border-rose-600"></span>
                    <span class="w-3 h-3 rounded-sm bg-[#ff5376] border border-rose-400"></span>
                </div>
                <span>Waktu Emas (Peak)</span>
            </div>
        </div>
    </div>
</div>

<!-- ==============================================
     CHART.JS SCRIPTS
     ============================================== -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    if (typeof Chart === 'undefined') {
        console.warn('Chart.js belum dimuatkan.');
        return;
    }

    // 1. Leads Over Time Chart (Dual-line spline with subtle area gradient)
    const ctxOverTime = document.getElementById('leadsOverTimeChart');
    if (ctxOverTime) {
        const leadsGrad = ctxOverTime.getContext('2d').createLinearGradient(0, 0, 0, 220);
        leadsGrad.addColorStop(0, 'rgba(255, 83, 118, 0.28)');
        leadsGrad.addColorStop(1, 'rgba(255, 83, 118, 0.00)');

        const qualGrad = ctxOverTime.getContext('2d').createLinearGradient(0, 0, 0, 220);
        qualGrad.addColorStop(0, 'rgba(0, 184, 212, 0.22)');
        qualGrad.addColorStop(1, 'rgba(0, 184, 212, 0.00)');

        new Chart(ctxOverTime, {
            type: 'line',
            data: {
                labels: @json($leadsOverTimeLabels),
                datasets: [
                    {
                        label: 'Leads',
                        data: @json($leadsOverTimeData),
                        borderColor: '#ff5376',
                        backgroundColor: leadsGrad,
                        borderWidth: 2.5,
                        tension: 0.38,
                        fill: true,
                        pointRadius: 0,
                        pointHoverRadius: 5,
                        pointHoverBackgroundColor: '#ff5376',
                    },
                    {
                        label: 'Qualified',
                        data: @json($qualifiedOverTimeData),
                        borderColor: '#00b8d4',
                        backgroundColor: qualGrad,
                        borderWidth: 2.5,
                        tension: 0.38,
                        fill: true,
                        pointRadius: 0,
                        pointHoverRadius: 5,
                        pointHoverBackgroundColor: '#00b8d4',
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#1e293b',
                        titleColor: '#f8fafc',
                        bodyColor: '#e2e8f0',
                        borderColor: '#334155',
                        borderWidth: 1,
                        padding: 10,
                        cornerRadius: 8,
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { color: '#64748b', font: { size: 10 } }
                    },
                    y: {
                        grid: { color: 'rgba(255, 255, 255, 0.04)' },
                        ticks: { color: '#64748b', font: { size: 10 } }
                    }
                }
            }
        });
    }

    // 2. Leads By Channel Donut Chart
    const ctxChannel = document.getElementById('leadsByChannelChart');
    if (ctxChannel) {
        new Chart(ctxChannel, {
            type: 'doughnut',
            data: {
                labels: @json($leadsByChannel['labels']),
                datasets: [{
                    data: @json($leadsByChannel['data']),
                    backgroundColor: @json($leadsByChannel['colors']),
                    borderColor: '#121927',
                    borderWidth: 4,
                    hoverOffset: 4,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '72%',
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#1e293b',
                        titleColor: '#f8fafc',
                        bodyColor: '#e2e8f0',
                        borderColor: '#334155',
                        borderWidth: 1,
                        padding: 10,
                        cornerRadius: 8,
                    }
                }
            }
        });
    }

    // 3. Ad Engine Health Radial Gauge Chart
    const ctxHealth = document.getElementById('adHealthChart');
    if (ctxHealth) {
        new Chart(ctxHealth, {
            type: 'doughnut',
            data: {
                labels: ['Healthy', 'Remaining'],
                datasets: [{
                    data: [86, 14],
                    backgroundColor: ['#ff5376', '#1f293d'],
                    borderColor: '#121927',
                    borderWidth: 3,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '76%',
                plugins: {
                    legend: { display: false },
                    tooltip: { enabled: false }
                }
            }
        });
    }
});
</script>
@endsection
