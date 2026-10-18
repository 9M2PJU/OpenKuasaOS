@extends('layouts.app', ['title' => 'Lekir — Modul Kewangan & Percukaian'])

@section('content')
<div class="space-y-6 pb-12">
    <!-- Top Action & Navigation Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-amber-500/10 text-amber-500 border border-amber-500/20 font-bold text-sm">
                    ⚖️
                </span>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">Lekir (Kewangan, Perakaunan & Percukaian)</h1>
            </div>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Papan Pemantauan Prestasi Kewangan, Aliran Tunai, e-Invois LHDN & Pematuhan Lejar Dwientri.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('finance.quotations') }}" class="px-3.5 py-2 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-[#101726] hover:bg-slate-50 dark:hover:bg-slate-800/80 text-xs font-semibold text-slate-700 dark:text-slate-200 transition">
                Sebutharga ({{ $quotationsCount }})
            </a>
            <a href="{{ route('finance.invoices') }}" class="px-3.5 py-2 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-[#101726] hover:bg-slate-50 dark:hover:bg-slate-800/80 text-xs font-semibold text-slate-700 dark:text-slate-200 transition">
                Invois ({{ $invoicesCount }})
            </a>
            <a href="{{ route('finance.expenses') }}" class="px-3.5 py-2 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-[#101726] hover:bg-slate-50 dark:hover:bg-slate-800/80 text-xs font-semibold text-slate-700 dark:text-slate-200 transition">
                Perbelanjaan & Belian
            </a>
            <a href="{{ route('finance.accounts') }}" class="px-3.5 py-2 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-[#101726] hover:bg-slate-50 dark:hover:bg-slate-800/80 text-xs font-semibold text-slate-700 dark:text-slate-200 transition">
                Akaun & Lejar
            </a>
            <a href="{{ route('finance.quotations.create') }}" class="px-3.5 py-2 rounded-xl bg-amber-600 hover:bg-amber-700 text-white text-xs font-semibold shadow-sm transition">
                + Sebutharga
            </a>
            <a href="{{ route('finance.expenses.create') }}" class="px-3.5 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold shadow-sm transition">
                + Belanja / Modal
            </a>
            <a href="{{ route('finance.invoices.create') }}" class="px-3.5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold shadow-sm transition">
                + Invois Terus
            </a>
        </div>
    </div>

@php
    $isMalay = app()->getLocale() === 'ms';
@endphp

    <!-- 1. Top KPI Summary Cards (Bahasa Melayu Default) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Card 1: Cash balance (Distinctive Warm Amber / Gold Gradient) -->
        <div class="rounded-2xl p-5 shadow-sm relative overflow-hidden bg-gradient-to-b from-[#caa048] via-[#a37c27] to-[#755512] text-slate-950 flex flex-col justify-between border border-amber-300/30">
            <div>
                <span class="block text-xs font-bold text-amber-950/80 tracking-wide">{{ $isMalay ? 'Baki Tunai (Cash balance)' : 'Cash balance' }}</span>
                <div class="flex items-center gap-2 mt-2">
                    <span class="text-2xl lg:text-3xl font-extrabold tracking-tight text-slate-950">
                        RM {{ number_format($displayCash, 0) }}
                    </span>
                    <span class="text-[11px] font-bold px-2 py-0.5 rounded bg-black/20 text-slate-950">
                        +6%
                    </span>
                </div>
            </div>
            <!-- Organic Sparkline Wave -->
            <div class="pt-3">
                <svg class="w-full h-7 overflow-visible" viewBox="0 0 200 30" fill="none" preserveAspectRatio="none">
                    <path d="M 0 25 Q 50 20, 100 24 T 200 22" stroke="#1c1605" stroke-width="2.5" stroke-linecap="round"/>
                </svg>
            </div>
        </div>

        <!-- Card 2: Revenue · MTD -->
        <div class="rounded-2xl p-5 shadow-sm bg-[#101726] border border-[#1F293D] text-white flex flex-col justify-between">
            <div>
                <span class="block text-xs font-medium text-slate-400">{{ $isMalay ? 'Hasil Semasa (Revenue · MTD)' : 'Revenue · MTD' }}</span>
                <div class="flex items-center gap-2 mt-2">
                    <span class="text-2xl lg:text-3xl font-bold tracking-tight text-white">
                        RM {{ number_format($revenueMtd, 0) }}
                    </span>
                    <span class="text-[11px] font-bold px-2 py-0.5 rounded bg-emerald-950/80 text-emerald-400 border border-emerald-800/40">
                        +14%
                    </span>
                </div>
            </div>
            <div class="pt-3">
                <svg class="w-full h-7 overflow-visible" viewBox="0 0 200 30" fill="none" preserveAspectRatio="none">
                    <path d="M 0 26 Q 50 25, 100 23 T 200 20" stroke="#ca8a04" stroke-width="2" stroke-linecap="round"/>
                </svg>
            </div>
        </div>

        <!-- Card 3: Outstanding · AR -->
        <div class="rounded-2xl p-5 shadow-sm bg-[#101726] border border-[#1F293D] text-white flex flex-col justify-between">
            <div>
                <span class="block text-xs font-medium text-slate-400">{{ $isMalay ? 'Belum Jelas (Outstanding · AR)' : 'Outstanding · AR' }}</span>
                <div class="flex items-center gap-2 mt-2">
                    <span class="text-2xl lg:text-3xl font-bold tracking-tight text-white">
                        RM {{ number_format($displayReceivables, 0) }}
                    </span>
                    <span class="text-[11px] font-bold px-2 py-0.5 rounded bg-cyan-950/80 text-cyan-400 border border-cyan-800/40">
                        -5%
                    </span>
                </div>
            </div>
            <div class="pt-3">
                <svg class="w-full h-7 overflow-visible" viewBox="0 0 200 30" fill="none" preserveAspectRatio="none">
                    <path d="M 0 22 Q 50 20, 100 22 T 200 23" stroke="#06b6d4" stroke-width="2" stroke-linecap="round"/>
                </svg>
            </div>
        </div>

        <!-- Card 4: Payable · AP -->
        <div class="rounded-2xl p-5 shadow-sm bg-[#101726] border border-[#1F293D] text-white flex flex-col justify-between">
            <div>
                <span class="block text-xs font-medium text-slate-400">{{ $isMalay ? 'Pemiutang / Kos (Payable · AP)' : 'Payable · AP' }}</span>
                <div class="flex items-center gap-2 mt-2">
                    <span class="text-2xl lg:text-3xl font-bold tracking-tight text-white">
                        RM {{ number_format($totalPayables, 0) }}
                    </span>
                    <span class="text-[11px] font-bold px-2 py-0.5 rounded bg-rose-950/80 text-rose-400 border border-rose-800/40">
                        +RM 1.2k
                    </span>
                </div>
            </div>
            <div class="pt-3">
                <svg class="w-full h-7 overflow-visible" viewBox="0 0 200 30" fill="none" preserveAspectRatio="none">
                    <path d="M 0 24 Q 50 23, 100 22 T 200 20" stroke="#10b981" stroke-width="2" stroke-linecap="round"/>
                </svg>
            </div>
        </div>
    </div>

    <!-- 2. Middle Row: Cash In vs Out Chart & Revenue by Category Donut Chart -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5">
        <!-- Main Area Spline Chart: Cash in vs out -->
        <div class="lg:col-span-8 rounded-2xl p-6 bg-[#101726] border border-[#1F293D] flex flex-col justify-between shadow-sm">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-8 h-8 rounded-lg bg-slate-800/80 flex items-center justify-center text-amber-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                    </svg>
                </div>
                <div>
                    <h2 class="text-sm font-bold text-white">{{ $isMalay ? 'Aliran Tunai Masuk vs Keluar (Cash in vs out)' : 'Cash in vs out' }}</h2>
                    <p class="text-[11px] text-slate-400">{{ $isMalay ? 'Mingguan · 8 minggu terakhir' : 'Weekly · last 8 weeks' }}</p>
                </div>
            </div>

            <!-- Canvas Container -->
            <div class="w-full h-64 relative">
                <canvas id="cashInOutChart"></canvas>
            </div>

            <!-- Legend Indicator Matching Screenshot -->
            <div class="flex items-center justify-center gap-6 pt-4 text-xs">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-sm bg-[#06b6d4]"></span>
                    <span class="text-slate-300 font-medium">{{ $isMalay ? 'Duit Masuk (Cash in - RM k)' : 'Cash in (RM k)' }}</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-sm bg-[#10b981]"></span>
                    <span class="text-slate-300 font-medium">{{ $isMalay ? 'Duit Keluar (Cash out - RM k)' : 'Cash out (RM k)' }}</span>
                </div>
            </div>
        </div>

        <!-- Donut Chart: Revenue by Category -->
        <div class="lg:col-span-4 rounded-2xl p-6 bg-[#101726] border border-[#1F293D] flex flex-col justify-between shadow-sm">
            <div class="flex items-center gap-3 mb-2">
                <div class="w-8 h-8 rounded-lg bg-slate-800/80 flex items-center justify-center text-amber-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"/>
                    </svg>
                </div>
                <div>
                    <h2 class="text-sm font-bold text-white">{{ $isMalay ? 'Pecahan Hasil (Revenue by category)' : 'Revenue by category' }}</h2>
                    <p class="text-[11px] text-slate-400">{{ $isMalay ? 'Bulan ini' : 'This month' }}</p>
                </div>
            </div>

            <!-- Donut Canvas with Centered Amount -->
            <div class="relative flex items-center justify-center my-4">
                <div class="w-48 h-48">
                    <canvas id="revenueCategoryChart"></canvas>
                </div>
                <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
                    <span class="text-lg font-black tracking-tight text-white">
                        RM {{ number_format($revenueMtd / 1000, 1) }}k
                    </span>
                    <span class="text-[11px] text-slate-400 uppercase tracking-wider font-medium">
                        {{ $isMalay ? 'hasil' : 'revenue' }}
                    </span>
                </div>
            </div>

            <!-- Legend Items Matching Screenshot -->
            <div class="grid grid-cols-2 gap-2 pt-2 text-[11px] text-slate-300">
                <div class="flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-sm bg-[#caa048]"></span>
                    <span>{{ $isMalay ? 'Servis' : 'Services' }}</span>
                </div>
                <div class="flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-sm bg-[#0284c7]"></span>
                    <span>{{ $isMalay ? 'Projek' : 'Projects' }}</span>
                </div>
                <div class="flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-sm bg-[#38bdf8]"></span>
                    <span>{{ $isMalay ? 'Bekalan' : 'Supplies' }}</span>
                </div>
                <div class="flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-sm bg-[#0d9488]"></span>
                    <span>{{ $isMalay ? 'Penyelenggaraan' : 'Maintenance' }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- 3. Third Row: Collections Funnel, Outstanding by Customer, & Collection Rate -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        <!-- Widget 1: Collections Funnel (Issued -> paid) -->
        <div class="rounded-2xl p-5 bg-[#101726] border border-[#1F293D] flex flex-col justify-between shadow-sm">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-8 h-8 rounded-lg bg-slate-800/80 flex items-center justify-center text-amber-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                    </svg>
                </div>
                <div>
                    <h2 class="text-sm font-bold text-white">{{ $isMalay ? 'Corong Kutipan (Collections)' : 'Collections' }}</h2>
                    <p class="text-[11px] text-slate-400">{{ $isMalay ? 'Dikeluarkan → Dibayar (Issued → paid)' : 'Issued → paid' }}</p>
                </div>
            </div>

            <!-- Stylized Horizontal Funnel Bars -->
            <div class="space-y-3 py-2">
                <div class="flex items-center gap-3 text-xs">
                    <span class="w-16 text-slate-400 font-medium text-[11px] truncate">{{ $isMalay ? 'Dikeluarkan' : 'Issued' }}</span>
                    <div class="flex-1 bg-slate-800/50 rounded-full h-6 p-0.5 relative flex items-center">
                        <div class="h-full rounded-full bg-[#caa048] flex items-center justify-center font-bold text-slate-950 text-[11px] shadow-sm transition-all" style="width: 100%;">
                            {{ $funnelIssued }}
                        </div>
                    </div>
                    <span class="w-10 text-right text-slate-400 text-[11px]">100%</span>
                </div>

                <div class="flex items-center gap-3 text-xs">
                    <span class="w-16 text-slate-400 font-medium text-[11px] truncate">{{ $isMalay ? 'Dihantar' : 'Sent' }}</span>
                    <div class="flex-1 bg-slate-800/50 rounded-full h-6 p-0.5 relative flex items-center">
                        <div class="h-full rounded-full bg-[#0d9488] flex items-center justify-center font-bold text-white text-[11px] shadow-sm transition-all" style="width: 91%;">
                            {{ $funnelSent }}
                        </div>
                    </div>
                    <span class="w-10 text-right text-slate-400 text-[11px]">91%</span>
                </div>

                <div class="flex items-center gap-3 text-xs">
                    <span class="w-16 text-slate-400 font-medium text-[11px] truncate">{{ $isMalay ? 'Dilihat' : 'Viewed' }}</span>
                    <div class="flex-1 bg-slate-800/50 rounded-full h-6 p-0.5 relative flex items-center">
                        <div class="h-full rounded-full bg-[#38bdf8] flex items-center justify-center font-bold text-slate-950 text-[11px] shadow-sm transition-all" style="width: 78%;">
                            {{ $funnelViewed }}
                        </div>
                    </div>
                    <span class="w-10 text-right text-slate-400 text-[11px]">78%</span>
                </div>

                <div class="flex items-center gap-3 text-xs">
                    <span class="w-16 text-slate-400 font-medium text-[11px] truncate">{{ $isMalay ? 'Dibayar' : 'Paid' }}</span>
                    <div class="flex-1 bg-slate-800/50 rounded-full h-6 p-0.5 relative flex items-center">
                        <div class="h-full rounded-full bg-[#f43f5e] flex items-center justify-center font-bold text-white text-[11px] shadow-sm transition-all" style="width: 65%;">
                            {{ $funnelPaid }}
                        </div>
                    </div>
                    <span class="w-10 text-right text-slate-400 text-[11px]">65%</span>
                </div>
            </div>
            <div></div>
        </div>

        <!-- Widget 2: Outstanding by customer (Horizontal Bar Chart) -->
        <div class="rounded-2xl p-5 bg-[#101726] border border-[#1F293D] flex flex-col justify-between shadow-sm">
            <div class="flex items-center gap-3 mb-2">
                <div class="w-8 h-8 rounded-lg bg-slate-800/80 flex items-center justify-center text-amber-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
                <div>
                    <h2 class="text-sm font-bold text-white">{{ $isMalay ? 'Penghutang Belum Jelas (Outstanding by customer)' : 'Outstanding by customer' }}</h2>
                    <p class="text-[11px] text-slate-400">{{ $isMalay ? 'Akaun Belum Terima (Receivables - RM k)' : 'Receivables (RM k)' }}</p>
                </div>
            </div>

            <!-- Horizontal Bars Canvas -->
            <div class="w-full h-44 relative">
                <canvas id="outstandingCustomerChart"></canvas>
            </div>
            <div></div>
        </div>

        <!-- Widget 3: Collection rate (Radial Gauge Progress) -->
        <div class="rounded-2xl p-5 bg-[#101726] border border-[#1F293D] flex flex-col justify-between shadow-sm">
            <div class="flex items-center gap-3 mb-2">
                <div class="w-8 h-8 rounded-lg bg-slate-800/80 flex items-center justify-center text-amber-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                </div>
                <div>
                    <h2 class="text-sm font-bold text-white">{{ $isMalay ? 'Kadar Kutipan (Collection rate)' : 'Collection rate' }}</h2>
                    <p class="text-[11px] text-slate-400">{{ $isMalay ? 'Dikutip daripada jumlah bil' : 'Collected of billed' }}</p>
                </div>
            </div>

            <!-- Circular Gauge -->
            <div class="relative flex items-center justify-center my-3">
                <div class="w-36 h-36">
                    <canvas id="collectionGaugeChart"></canvas>
                </div>
                <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
                    <span class="text-3xl font-black tracking-tight text-white">
                        {{ $collectionRate }}%
                    </span>
                    <span class="text-[11px] text-slate-400 font-medium">
                        {{ $isMalay ? 'berjaya dikutip' : 'collected' }}
                    </span>
                </div>
            </div>
            <div></div>
        </div>
    </div>

    <!-- 4. Fourth Row: Overdue Invoices & e-Invois LHDN MyInvois Tracker -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
        <!-- Card 1: Overdue Invoices (Receivables to chase) -->
        <div class="rounded-2xl p-5 bg-[#101726] border border-[#1F293D] flex flex-col justify-between shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-slate-800/80 flex items-center justify-center text-amber-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-sm font-bold text-white">{{ $isMalay ? 'Invois Tertunggak (Overdue invoices)' : 'Overdue invoices' }}</h2>
                        <p class="text-[11px] text-slate-400">{{ $isMalay ? 'Belum terima perlu tindakan' : 'Receivables to chase' }}</p>
                    </div>
                </div>
                <a href="{{ route('finance.invoices', ['status' => 'posted']) }}" class="text-xs font-semibold text-cyan-400 hover:underline">
                    {{ $isMalay ? 'Semak Semua' : 'View All' }} &rarr;
                </a>
            </div>

            <div class="divide-y divide-slate-800/60 text-xs">
                @forelse($overdueInvoices as $inv)
                <div class="py-3 flex items-center justify-between">
                    <div>
                        <div class="font-bold text-slate-100 flex items-center gap-2">
                            <span>{{ $inv->customer_name }}</span>
                            @php
                                $daysOverdue = max(5, (int) now()->diffInDays($inv->due_date));
                            @endphp
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-rose-950/80 text-rose-400 border border-rose-800/40">
                                {{ $daysOverdue }} {{ $isMalay ? 'hari lewat' : 'days overdue' }}
                            </span>
                        </div>
                        <div class="text-[11px] text-slate-400 mt-0.5 font-mono">
                            {{ $inv->invoice_number ?? 'DRAF' }} · {{ $isMalay ? 'Sah sehingga' : 'Due' }}: {{ $inv->due_date->format('d M Y') }}
                        </div>
                    </div>
                    <div class="text-right flex items-center gap-3">
                        <span class="font-bold text-sm text-white">
                            RM {{ number_format($inv->total_amount, 2) }}
                        </span>
                        @if($inv->customer_phone)
                        @php
                            $cleanPhone = preg_replace('/[^0-9]/', '', $inv->customer_phone);
                            if (str_starts_with($cleanPhone, '0')) {
                                $cleanPhone = '60' . substr($cleanPhone, 1);
                            }
                            $reminderMsg = urlencode("Salam sejahtera {$inv->customer_name}, peringatan mesra bagi invois {$inv->invoice_number} berjumlah RM " . number_format($inv->total_amount, 2) . ". Mohon maklumbalas pembayaran. Terima kasih.");
                        @endphp
                        <a href="https://wa.me/{{ $cleanPhone }}?text={{ $reminderMsg }}" target="_blank" class="px-2.5 py-1 rounded-lg bg-emerald-600/20 hover:bg-emerald-600/30 text-emerald-400 border border-emerald-500/30 text-[10px] font-bold transition flex items-center gap-1" title="Tuntut bayaran via WhatsApp">
                            <span>💬 Wasap</span>
                        </a>
                        @endif
                    </div>
                </div>
                @empty
                <div class="py-6 text-center text-slate-400 text-xs">
                    {{ $isMalay ? 'Tiada invois tertunggak pada masa ini.' : 'No overdue invoices at this time.' }}
                </div>
                @endforelse
            </div>
        </div>

        <!-- Card 2: e-Invois · LHDN MyInvois (Submission status) -->
        <div class="rounded-2xl p-5 bg-[#101726] border border-[#1F293D] flex flex-col justify-between shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-slate-800/80 flex items-center justify-center text-amber-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-sm font-bold text-white">e-Invois · LHDN MyInvois</h2>
                        <p class="text-[11px] text-slate-400">{{ $isMalay ? 'Status Penghantaran (Submission status)' : 'Submission status' }}</p>
                    </div>
                </div>
                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-emerald-950/80 text-emerald-400 border border-emerald-800/40">
                    Live Portal
                </span>
            </div>

            <div class="divide-y divide-slate-800/60 text-xs">
                @forelse($eInvoiceSubmissions as $sub)
                <div class="py-3 flex items-center justify-between">
                    <div>
                        <div class="font-bold text-slate-100 flex items-center gap-2">
                            <span>{{ $sub->customer_name }}</span>
                            <span class="font-mono text-[10px] text-slate-400">({{ $sub->invoice_number ?? 'DRAF' }})</span>
                        </div>
                        <div class="text-[11px] text-slate-400 mt-0.5">
                            Tarikh: {{ $sub->issue_date->format('d M Y') }} · RM {{ number_format($sub->total_amount, 2) }}
                        </div>
                    </div>
                    <div>
                        @if($sub->e_invoice_status === 'submitted')
                            <span class="px-2.5 py-1 rounded-full font-bold text-[10px] bg-cyan-950/80 text-cyan-400 border border-cyan-800/50">
                                Submitted
                            </span>
                        @elseif($sub->status === 'paid' || $sub->status === 'posted')
                            <span class="px-2.5 py-1 rounded-full font-bold text-[10px] bg-emerald-950/80 text-emerald-400 border border-emerald-800/50">
                                Validated
                            </span>
                        @else
                            <span class="px-2.5 py-1 rounded-full font-bold text-[10px] bg-amber-950/80 text-amber-400 border border-amber-800/50">
                                Pending
                            </span>
                        @endif
                    </div>
                </div>
                @empty
                <div class="py-6 text-center text-slate-400 text-xs">
                    Tiada rekod e-Invois dihantar lagi.
                </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- 5. Tabular Registers Section: Quotations, Invoices, Expenses & Bank Accounts -->
    <div class="space-y-6 pt-4">
        <!-- Quotations Table -->
        <div class="bg-white dark:bg-[#101726] border border-slate-200 dark:border-[#1F293D] rounded-2xl p-6 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-base font-bold text-slate-900 dark:text-white">Sebutharga Terkini (Quotations)</h2>
                    <p class="text-xs text-slate-400 mt-0.5">Sebutharga boleh terus ditukar menjadi invois rasmi dengan 1 klik atau jana invois terus.</p>
                </div>
                <div class="flex items-center gap-3">
                    <a href="{{ route('finance.quotations.create') }}" class="text-xs font-semibold text-amber-600 hover:text-amber-500 dark:text-amber-400">+ Sebutharga Baru</a>
                    <span class="text-slate-300 dark:text-slate-700">|</span>
                    <a href="{{ route('finance.quotations') }}" class="text-xs font-semibold text-indigo-600 hover:text-indigo-400">Semua Sebutharga &rarr;</a>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="border-b border-slate-100 dark:border-slate-800 text-slate-500">
                            <th class="pb-3 font-medium">No. Sebutharga</th>
                            <th class="pb-3 font-medium">Pelanggan</th>
                            <th class="pb-3 font-medium">Tarikh Sebutharga</th>
                            <th class="pb-3 font-medium">Sah Sehingga</th>
                            <th class="pb-3 font-medium">SST</th>
                            <th class="pb-3 font-medium">Jumlah (RM)</th>
                            <th class="pb-3 font-medium">Status</th>
                            <th class="pb-3 font-medium text-right">Tindakan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse($recentQuotations as $quotation)
                        <tr>
                            <td class="py-3 font-bold text-slate-800 dark:text-slate-200 font-mono">
                                {{ $quotation->quotation_number }}
                            </td>
                            <td class="py-3">
                                <div class="font-medium text-slate-800 dark:text-slate-200">{{ $quotation->customer_name }}</div>
                                @if($quotation->customer_phone)
                                <div class="text-[10px] text-slate-400">{{ $quotation->customer_phone }}</div>
                                @endif
                            </td>
                            <td class="py-3 text-slate-500">{{ $quotation->issue_date->format('Y-m-d') }}</td>
                            <td class="py-3 text-slate-500">{{ $quotation->valid_until ? $quotation->valid_until->format('Y-m-d') : '-' }}</td>
                            <td class="py-3 text-slate-500">RM {{ number_format($quotation->sst_amount, 2) }} ({{ $quotation->sst_rate }}%)</td>
                            <td class="py-3 font-bold text-slate-900 dark:text-white">RM {{ number_format($quotation->total_amount, 2) }}</td>
                            <td class="py-3">
                                <span class="px-2 py-0.5 rounded-full font-semibold text-[10px]
                                    {{ $quotation->status === 'converted' ? 'bg-indigo-950 text-indigo-300 border border-indigo-800' : '' }}
                                    {{ $quotation->status === 'accepted' ? 'bg-emerald-950 text-emerald-300 border border-emerald-800' : '' }}
                                    {{ $quotation->status === 'sent' ? 'bg-blue-950 text-blue-300 border border-blue-800' : '' }}
                                    {{ $quotation->status === 'draft' ? 'bg-amber-950 text-amber-300 border border-amber-800' : '' }}
                                    {{ $quotation->status === 'rejected' ? 'bg-rose-950 text-rose-300 border border-rose-800' : '' }}">
                                    {{ $quotation->status_label }}
                                </span>
                            </td>
                            <td class="py-3 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('finance.quotations.show', $quotation->id) }}" class="font-semibold text-indigo-600 hover:text-indigo-400">
                                        Butiran &rarr;
                                    </a>
                                    @if($quotation->status !== 'converted' && $quotation->status !== 'rejected')
                                    <form method="POST" action="{{ route('finance.quotations.convert', $quotation->id) }}" class="inline" onsubmit="return confirm('Tukar sebutharga ini kepada invois rasmi sekarang?')">
                                        @csrf
                                        <button type="submit" class="text-[10px] px-2 py-0.5 font-bold rounded bg-indigo-950 text-indigo-300 hover:bg-indigo-900 border border-indigo-800 transition">
                                            Tukar ke Invois
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="py-6 text-center text-slate-400">
                                Tiada sebutharga direkodkan. <a href="{{ route('finance.quotations.create') }}" class="text-indigo-400 underline font-medium">Bina sebutharga pertama</a>.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Invoices Table -->
        <div class="bg-white dark:bg-[#101726] border border-slate-200 dark:border-[#1F293D] rounded-2xl p-6 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-base font-bold text-slate-900 dark:text-white">Invois Terkini (Jualan)</h2>
                <a href="{{ route('finance.invoices') }}" class="text-xs font-semibold text-indigo-600 hover:text-indigo-400">Semua Invois &rarr;</a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="border-b border-slate-100 dark:border-slate-800 text-slate-500">
                            <th class="pb-3 font-medium">No. Invois</th>
                            <th class="pb-3 font-medium">Pelanggan</th>
                            <th class="pb-3 font-medium">Tarikh Invois</th>
                            <th class="pb-3 font-medium">Tarikh Akhir</th>
                            <th class="pb-3 font-medium">SST</th>
                            <th class="pb-3 font-medium">Jumlah (RM)</th>
                            <th class="pb-3 font-medium">Status</th>
                            <th class="pb-3 font-medium text-right">Tindakan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse($recentInvoices as $inv)
                        <tr>
                            <td class="py-3 font-bold text-slate-800 dark:text-slate-200 font-mono">
                                {{ $inv->invoice_number ?? 'DRAF' }}
                            </td>
                            <td class="py-3">{{ $inv->customer_name }}</td>
                            <td class="py-3 text-slate-500">{{ $inv->issue_date->format('Y-m-d') }}</td>
                            <td class="py-3 text-slate-500">{{ $inv->due_date->format('Y-m-d') }}</td>
                            <td class="py-3 text-slate-500">RM {{ number_format($inv->sst_amount, 2) }} ({{ $inv->sst_rate }}%)</td>
                            <td class="py-3 font-bold text-slate-900 dark:text-white">RM {{ number_format($inv->total_amount, 2) }}</td>
                            <td class="py-3">
                                <span class="px-2 py-0.5 rounded-full font-semibold text-[10px] uppercase
                                    {{ $inv->status === 'paid' ? 'bg-emerald-950 text-emerald-300 border border-emerald-800' : '' }}
                                    {{ $inv->status === 'posted' ? 'bg-blue-950 text-blue-300 border border-blue-800' : '' }}
                                    {{ $inv->status === 'draft' ? 'bg-amber-950 text-amber-300 border border-amber-800' : '' }}
                                    {{ $inv->status === 'void' ? 'bg-slate-800 text-slate-400 border border-slate-700' : '' }}">
                                    {{ $inv->status }}
                                </span>
                            </td>
                            <td class="py-3 text-right">
                                <a href="{{ route('finance.invoices.show', $inv->id) }}" class="font-semibold text-indigo-600 hover:text-indigo-400">
                                    Butiran &rarr;
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="py-6 text-center text-slate-400">Tiada invois dijumpai.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Expenses & Capital Table (LHDN) -->
        <div class="bg-white dark:bg-[#101726] border border-slate-200 dark:border-[#1F293D] rounded-2xl p-6 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-base font-bold text-slate-900 dark:text-white">Perbelanjaan & Belian Modal Terkini (LHDN)</h2>
                    <p class="text-xs text-slate-400 mt-0.5">Rekod baucar perbelanjaan, kos jualan stok, modal dan pematuhan cukai LHDN.</p>
                </div>
                <a href="{{ route('finance.expenses') }}" class="text-xs font-semibold text-indigo-600 hover:text-indigo-400">Semua Perbelanjaan &rarr;</a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="border-b border-slate-100 dark:border-slate-800 text-slate-500">
                            <th class="pb-3 font-medium">No. Baucar</th>
                            <th class="pb-3 font-medium">Tarikh</th>
                            <th class="pb-3 font-medium">Pembekal / Vendor</th>
                            <th class="pb-3 font-medium">Kategori</th>
                            <th class="pb-3 font-medium">Klasifikasi Cukai LHDN</th>
                            <th class="pb-3 font-medium">Akaun Bank</th>
                            <th class="pb-3 font-medium">Jumlah (RM)</th>
                            <th class="pb-3 font-medium text-right">Tindakan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse($recentExpenses as $exp)
                        <tr>
                            <td class="py-3 font-bold text-slate-800 dark:text-slate-200 font-mono">
                                {{ $exp->voucher_number }}
                            </td>
                            <td class="py-3 text-slate-500">{{ $exp->payment_date->format('Y-m-d') }}</td>
                            <td class="py-3">
                                <div class="font-medium text-slate-800 dark:text-slate-200">{{ $exp->vendor_name }}</div>
                                @if($exp->vendor_tin)
                                <div class="text-[10px] text-slate-400">TIN: {{ $exp->vendor_tin }}</div>
                                @endif
                            </td>
                            <td class="py-3">
                                <span class="px-2 py-0.5 rounded text-[10px] font-semibold
                                    {{ $exp->expense_category === 'cogs' ? 'bg-amber-950 text-amber-300 border border-amber-800' : '' }}
                                    {{ $exp->expense_category === 'opex' ? 'bg-blue-950 text-blue-300 border border-blue-800' : '' }}
                                    {{ $exp->expense_category === 'capex' ? 'bg-purple-950 text-purple-300 border border-purple-800' : '' }}
                                    {{ $exp->expense_category === 'equity_injection' ? 'bg-emerald-950 text-emerald-300 border border-emerald-800' : '' }}
                                    {{ $exp->expense_category === 'non_deductible' ? 'bg-slate-800 text-slate-300 border border-slate-700' : '' }}">
                                    {{ $exp->category_label }}
                                </span>
                            </td>
                            <td class="py-3 text-slate-400">
                                {{ $exp->tax_classification_label }}
                            </td>
                            <td class="py-3 text-slate-500">{{ $exp->account->name ?? 'N/A' }}</td>
                            <td class="py-3 font-bold text-slate-900 dark:text-white">
                                {{ $exp->expense_category === 'equity_injection' ? '+' : '-' }} RM {{ number_format($exp->total_amount, 2) }}
                            </td>
                            <td class="py-3 text-right">
                                <a href="{{ route('finance.expenses.show', $exp->id) }}" class="font-semibold text-indigo-600 hover:text-indigo-400">
                                    Baucar &rarr;
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="py-6 text-center text-slate-400">
                                Tiada rekod perbelanjaan atau belian modal lagi. <a href="{{ route('finance.expenses.create') }}" class="text-indigo-400 underline font-medium">Rekod sekarang</a>.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Bank Accounts Preview -->
        <div class="bg-white dark:bg-[#101726] border border-slate-200 dark:border-[#1F293D] rounded-2xl p-6 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-base font-bold text-slate-900 dark:text-white">Akaun Bank Perniagaan & Tunai Runcit</h2>
                <a href="{{ route('finance.accounts') }}" class="text-xs font-semibold text-indigo-600 hover:text-indigo-400">Urus Akaun &rarr;</a>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                @forelse($accounts as $acc)
                <div class="p-4 rounded-xl border border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/60">
                    <div class="flex items-center justify-between">
                        <span class="font-semibold text-xs text-slate-800 dark:text-slate-200">{{ $acc->name }}</span>
                        <span class="uppercase text-[10px] px-2 py-0.5 rounded font-bold bg-slate-200 dark:bg-slate-800 text-slate-700 dark:text-slate-300">{{ $acc->type }}</span>
                    </div>
                    <div class="text-[11px] text-slate-400 mt-1">No: {{ $acc->account_number ?? 'N/A' }}</div>
                    <div class="text-base font-bold text-slate-900 dark:text-white mt-3">
                        RM {{ number_format($acc->balance, 2) }}
                    </div>
                </div>
                @empty
                <div class="col-span-3 text-center py-6 text-xs text-slate-400">
                    Tiada akaun bank didaftarkan.
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<!-- Chart.js Scripts Initialization -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const chartJs = window.Chart;
    if (!chartJs) return;

    // Global Defaults for Dark Dashboard
    chartJs.defaults.color = '#94a3b8';
    chartJs.defaults.font.family = "Instrument Sans, system-ui, -apple-system, sans-serif";

    // 1. Cash In vs Out Spline Area Chart
    const cashCtx = document.getElementById('cashInOutChart');
    if (cashCtx) {
        const ctx2d = cashCtx.getContext('2d');
        const gradCyan = ctx2d.createLinearGradient(0, 0, 0, 240);
        gradCyan.addColorStop(0, 'rgba(6, 182, 212, 0.35)');
        gradCyan.addColorStop(1, 'rgba(6, 182, 212, 0.00)');

        const gradMint = ctx2d.createLinearGradient(0, 0, 0, 240);
        gradMint.addColorStop(0, 'rgba(16, 185, 129, 0.25)');
        gradMint.addColorStop(1, 'rgba(16, 185, 129, 0.00)');

        new chartJs(cashCtx, {
            type: 'line',
            data: {
                labels: @json($weeklyLabels),
                datasets: [
                    {
                        label: @json($isMalay ? 'Duit Masuk (RM k)' : 'Cash in (RM k)'),
                        data: @json($cashInData),
                        borderColor: '#06b6d4',
                        backgroundColor: gradCyan,
                        fill: true,
                        tension: 0.45,
                        borderWidth: 2.5,
                        pointRadius: 0,
                        pointHoverRadius: 5,
                        pointHoverBackgroundColor: '#06b6d4',
                    },
                    {
                        label: @json($isMalay ? 'Duit Keluar (RM k)' : 'Cash out (RM k)'),
                        data: @json($cashOutData),
                        borderColor: '#10b981',
                        backgroundColor: gradMint,
                        fill: true,
                        tension: 0.45,
                        borderWidth: 2,
                        pointRadius: 0,
                        pointHoverRadius: 5,
                        pointHoverBackgroundColor: '#10b981',
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#0b1120',
                        titleColor: '#f8fafc',
                        bodyColor: '#cbd5e1',
                        borderColor: '#1e293b',
                        borderWidth: 1,
                        padding: 10,
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': RM ' + (context.raw * 1000).toLocaleString('en-US');
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        border: { display: false },
                        ticks: { color: '#64748b', font: { size: 11 } }
                    },
                    y: {
                        display: false,
                        grid: { display: false }
                    }
                }
            }
        });
    }

    // 2. Revenue by Category Donut Chart
    const categoryCtx = document.getElementById('revenueCategoryChart');
    if (categoryCtx) {
        new chartJs(categoryCtx, {
            type: 'doughnut',
            data: {
                labels: {!! json_encode($isMalay ? ['Servis', 'Projek', 'Bekalan', 'Penyelenggaraan'] : ['Services', 'Projects', 'Supplies', 'Maintenance']) !!},
                datasets: [{
                    data: [
                        {{ $categoryBreakdown['services'] }},
                        {{ $categoryBreakdown['projects'] }},
                        {{ $categoryBreakdown['supplies'] }},
                        {{ $categoryBreakdown['maintenance'] }}
                    ],
                    backgroundColor: [
                        '#caa048', // Services - Warm Gold
                        '#0284c7', // Projects - Sky Blue
                        '#38bdf8', // Supplies - Light Blue
                        '#0d9488'  // Maintenance - Teal
                    ],
                    borderWidth: 0,
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '72%',
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#0b1120',
                        titleColor: '#f8fafc',
                        bodyColor: '#cbd5e1',
                        borderColor: '#1e293b',
                        borderWidth: 1,
                        padding: 8,
                        callbacks: {
                            label: function(context) {
                                return ' ' + context.label + ': RM ' + context.raw.toLocaleString('en-US');
                            }
                        }
                    }
                }
            }
        });
    }

    // 3. Outstanding by Customer Horizontal Bar Chart
    const outstandingCtx = document.getElementById('outstandingCustomerChart');
    if (outstandingCtx) {
        new chartJs(outstandingCtx, {
            type: 'bar',
            data: {
                labels: @json($customerLabels),
                datasets: [{
                    label: @json($isMalay ? 'Belum Jelas (RM k)' : 'Outstanding (RM k)'),
                    data: @json($customerValues),
                    backgroundColor: '#06b6d4',
                    borderRadius: 4,
                    barPercentage: 0.65,
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#0b1120',
                        titleColor: '#f8fafc',
                        bodyColor: '#cbd5e1',
                        borderColor: '#1e293b',
                        borderWidth: 1,
                        padding: 8,
                        callbacks: {
                            label: function(context) {
                                return ' Outstanding (RM k) ' + context.raw;
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        display: false,
                        grid: { display: false }
                    },
                    y: {
                        grid: { display: false },
                        border: { display: false },
                        ticks: { color: '#94a3b8', font: { size: 10 } }
                    }
                }
            }
        });
    }

    // 4. Collection Rate Gauge Chart
    const gaugeCtx = document.getElementById('collectionGaugeChart');
    if (gaugeCtx) {
        const rate = {{ $collectionRate }};
        new chartJs(gaugeCtx, {
            type: 'doughnut',
            data: {
                datasets: [{
                    data: [rate, Math.max(0, 100 - rate)],
                    backgroundColor: [
                        '#06b6d4', // Vibrant Cyan Arc
                        '#1e293b'  // Dark Track
                    ],
                    borderWidth: 0,
                    circumference: 300,
                    rotation: 210
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '78%',
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
