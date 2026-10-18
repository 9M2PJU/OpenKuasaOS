@extends('layouts.app', ['title' => 'Dashboard Utama'])

@section('content')
<div class="space-y-8">
    <!-- Header with Welcome & Quick CTA -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">Pusat Kawalan Perniagaan</h1>
            <p class="text-xs text-slate-500 mt-1">Gambaran keseluruhan metrik operasi {{ $currentOrg->name ?? 'syarikat anda' }} hari ini.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('command.index') }}" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-amber-500/10 text-amber-700 dark:text-amber-300 border border-amber-300 dark:border-amber-800 text-xs font-semibold hover:bg-amber-500/20 transition">
                <span>⚡ Tanya Tuah AI</span>
            </a>
            <a href="{{ route('finance.invoices.create') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold shadow-sm transition">
                <span>+ Cipta Invois</span>
            </a>
        </div>
    </div>

    <!-- KPI Metric Cards Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Hasil Terkumpul -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-sm">
            <div class="flex items-center justify-between text-xs font-medium text-slate-500">
                <span>Kutipan Terkumpul (Paid)</span>
                <span class="p-1.5 rounded-lg bg-emerald-50 text-emerald-600 dark:bg-emerald-950/60 dark:text-emerald-400 font-bold">RM</span>
            </div>
            <div class="mt-3">
                <span class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">
                    RM {{ number_format($stats['total_revenue'], 2) }}
                </span>
            </div>
            <div class="mt-2 text-[11px] text-slate-400">
                Invois belum jelas: <strong class="text-rose-500">RM {{ number_format($stats['unpaid_invoices'], 2) }}</strong>
            </div>
        </div>

        <!-- Pipeline Deals (Kasturi) -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-sm">
            <div class="flex items-center justify-between text-xs font-medium text-slate-500">
                <span>Pipeline Jualan Aktif</span>
                <span class="p-1.5 rounded-lg bg-blue-50 text-blue-600 dark:bg-blue-950/60 dark:text-blue-400">📊</span>
            </div>
            <div class="mt-3">
                <span class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">
                    RM {{ number_format($stats['active_deals_sum'], 2) }}
                </span>
            </div>
            <div class="mt-2 text-[11px] text-slate-400">
                Jumlah transaksi: <strong class="text-slate-700 dark:text-slate-300">{{ $stats['active_deals_count'] }} urus niaga</strong>
            </div>
        </div>

        <!-- Tenaga Kerja & Kehadiran (Lekiu) -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-sm">
            <div class="flex items-center justify-between text-xs font-medium text-slate-500">
                <span>Staf & Kehadiran Hari Ini</span>
                <span class="p-1.5 rounded-lg bg-purple-50 text-purple-600 dark:bg-purple-950/60 dark:text-purple-400">👥</span>
            </div>
            <div class="mt-3">
                <span class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">
                    {{ $stats['attendance_today'] }} / {{ $stats['total_employees'] }}
                </span>
            </div>
            <div class="mt-2 text-[11px] text-slate-400">
                Punch card kehadiran direkodkan hari ini
            </div>
        </div>

        <!-- Pemasaran & Leads (Jebat) -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-sm">
            <div class="flex items-center justify-between text-xs font-medium text-slate-500">
                <span>Kempen Iklan & Leads</span>
                <span class="p-1.5 rounded-lg bg-amber-50 text-amber-600 dark:bg-amber-950/60 dark:text-amber-400">🎯</span>
            </div>
            <div class="mt-3">
                <span class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">
                    {{ $stats['total_leads'] }} Prospek
                </span>
            </div>
            <div class="mt-2 text-[11px] text-slate-400">
                {{ $stats['active_campaigns'] }} kempen aktif berjalan
            </div>
        </div>
    </div>

    <!-- Grid: Invoices & Deals -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Invois Terkini -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-base font-bold text-slate-900 dark:text-white">Invois Terkini (Lekir)</h2>
                    <p class="text-xs text-slate-500">Status invois dan audit lejar jualan</p>
                </div>
                <a href="{{ route('finance.invoices') }}" class="text-xs font-semibold text-indigo-600 hover:text-indigo-500 dark:text-indigo-400">Lihat Semua &rarr;</a>
            </div>

            @if($recentInvoices->isEmpty())
            <div class="py-8 text-center text-xs text-slate-400">
                Tiada invois direkodkan lagi.
            </div>
            @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="border-b border-slate-100 dark:border-slate-800 text-slate-500">
                            <th class="pb-2.5 font-medium">No. Invois</th>
                            <th class="pb-2.5 font-medium">Pelanggan</th>
                            <th class="pb-2.5 font-medium">Jumlah</th>
                            <th class="pb-2.5 font-medium">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach($recentInvoices as $inv)
                        <tr>
                            <td class="py-3 font-semibold text-slate-800 dark:text-slate-200">
                                <a href="{{ route('finance.invoices.show', $inv->id) }}" class="hover:text-indigo-600">
                                    {{ $inv->invoice_number ?? 'DRAF' }}
                                </a>
                            </td>
                            <td class="py-3 text-slate-600 dark:text-slate-400">{{ $inv->customer_name }}</td>
                            <td class="py-3 font-semibold text-slate-900 dark:text-white">RM {{ number_format($inv->total_amount, 2) }}</td>
                            <td class="py-3">
                                @if($inv->status === 'paid')
                                <span class="px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300 font-semibold text-[10px]">Paid</span>
                                @elseif($inv->status === 'posted')
                                <span class="px-2 py-0.5 rounded-full bg-blue-100 text-blue-800 dark:bg-blue-950 dark:text-blue-300 font-semibold text-[10px]">Posted</span>
                                @elseif($inv->status === 'void')
                                <span class="px-2 py-0.5 rounded-full bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400 font-semibold text-[10px]">Void</span>
                                @else
                                <span class="px-2 py-0.5 rounded-full bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300 font-semibold text-[10px]">Draft</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>

        <!-- Deals Pipeline Terkini -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-base font-bold text-slate-900 dark:text-white">Urus Niaga Pipeline (Kasturi)</h2>
                    <p class="text-xs text-slate-500">Perkembangan rundingan jualan terkini</p>
                </div>
                <a href="{{ route('crm.deals') }}" class="text-xs font-semibold text-indigo-600 hover:text-indigo-500 dark:text-indigo-400">Papan Kanban &rarr;</a>
            </div>

            @if($recentDeals->isEmpty())
            <div class="py-8 text-center text-xs text-slate-400">
                Tiada transaksi ditambah lagi.
            </div>
            @else
            <div class="space-y-3">
                @foreach($recentDeals as $deal)
                <div class="p-3 rounded-xl border border-slate-100 dark:border-slate-800/80 bg-slate-50/50 dark:bg-slate-800/30 flex items-center justify-between">
                    <div>
                        <span class="block font-semibold text-xs text-slate-800 dark:text-slate-200">{{ $deal->title }}</span>
                        <span class="block text-[11px] text-slate-400">Peringkat: <span class="uppercase font-semibold text-indigo-600 dark:text-indigo-400">{{ $deal->stage_id }}</span></span>
                    </div>
                    <div class="text-right">
                        <span class="block font-bold text-xs text-slate-900 dark:text-white">RM {{ number_format($deal->amount, 2) }}</span>
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
