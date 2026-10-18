@extends('layouts.app', ['title' => 'Bendahara — Sebutharga (Quotations)'])

@section('content')
<div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-400 mb-1">
                <a href="{{ route('finance.index') }}" class="hover:underline">Bendahara</a>
                <span>&rsaquo;</span>
                <span class="text-slate-600 dark:text-slate-300">Sebutharga</span>
            </div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">Sebutharga & Tawaran Harga (Quotations)</h1>
            <p class="text-xs text-slate-500 mt-1">Cipta sebutharga profesional, hantar melalui WhatsApp/Email, cetak PDF dan tukar terus kepada Invois apabila dipersetujui.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('finance.invoices') }}" class="px-3.5 py-2 rounded-xl border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 text-xs font-semibold transition">
                Invois Jualan
            </a>
            <a href="{{ route('finance.quotations.create') }}" class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold shadow-sm transition">
                + Sebutharga Baru
            </a>
        </div>
    </div>

    <!-- Summary Metrics -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-sm">
            <span class="block text-xs font-medium text-slate-500">Jumlah Sebutharga Dikeluarkan</span>
            <span class="block text-2xl font-bold text-slate-900 dark:text-white tracking-tight mt-2">
                {{ $totalQuotationsCount }} Dokumen
            </span>
            <span class="block text-[11px] text-slate-400 mt-1">Rekod tawaran harga pelanggan</span>
        </div>

        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-sm">
            <span class="block text-xs font-medium text-slate-500">Nilai Keseluruhan Tawaran</span>
            <span class="block text-2xl font-bold text-indigo-600 dark:text-indigo-400 tracking-tight mt-2">
                RM {{ number_format($totalQuotationsValue, 2) }}
            </span>
            <span class="block text-[11px] text-slate-400 mt-1">Jumlah potensi pendapatan</span>
        </div>

        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-sm">
            <span class="block text-xs font-medium text-slate-500">Berjaya Ditukar ke Invois</span>
            <span class="block text-2xl font-bold text-emerald-600 tracking-tight mt-2">
                {{ $convertedCount }} Sebutharga
            </span>
            <span class="block text-[11px] text-slate-400 mt-1">Tawaran diterima & jadi jualan rasmi</span>
        </div>
    </div>

    <!-- Filter & Search Controls -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 shadow-sm">
        <form method="GET" action="{{ route('finance.quotations') }}" class="flex flex-col sm:flex-row items-center gap-3">
            <div class="w-full sm:w-48">
                <select name="status" class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs">
                    <option value="">Semua Status</option>
                    <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draf</option>
                    <option value="sent" {{ request('status') === 'sent' ? 'selected' : '' }}>Dihantar</option>
                    <option value="accepted" {{ request('status') === 'accepted' ? 'selected' : '' }}>Diterima</option>
                    <option value="converted" {{ request('status') === 'converted' ? 'selected' : '' }}>Ditukar ke Invois</option>
                    <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Ditolak</option>
                </select>
            </div>

            <div class="flex-1 w-full">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nombor sebutharga, nama pelanggan..." class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs">
            </div>

            <div class="flex items-center gap-2 w-full sm:w-auto">
                <button type="submit" class="py-2 px-4 rounded-xl bg-slate-900 dark:bg-slate-700 hover:bg-slate-800 text-white text-xs font-semibold transition w-full sm:w-auto">
                    Tapis
                </button>
                @if(request()->hasAny(['status', 'search']))
                <a href="{{ route('finance.quotations') }}" class="py-2 px-3 rounded-xl border border-slate-200 dark:border-slate-700 text-slate-500 text-xs hover:bg-slate-50 dark:hover:bg-slate-800">
                    Reset
                </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Quotations Table -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50/75 dark:bg-slate-800/50 border-b border-slate-100 dark:border-slate-800 text-slate-500">
                    <tr>
                        <th class="p-3.5 font-semibold">No. Sebutharga</th>
                        <th class="p-3.5 font-semibold">Pelanggan & Maklumat</th>
                        <th class="p-3.5 font-semibold">Tarikh Dikeluarkan</th>
                        <th class="p-3.5 font-semibold">Sah Sehingga</th>
                        <th class="p-3.5 font-semibold">Cukai SST</th>
                        <th class="p-3.5 font-semibold">Jumlah (RM)</th>
                        <th class="p-3.5 font-semibold">Status</th>
                        <th class="p-3.5 font-semibold text-right">Tindakan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($quotations as $quo)
                    <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition">
                        <td class="p-3.5 font-bold text-slate-900 dark:text-white">
                            {{ $quo->quotation_number }}
                        </td>
                        <td class="p-3.5">
                            <div class="font-semibold text-slate-800 dark:text-slate-200">{{ $quo->customer_name }}</div>
                            <div class="text-[10px] text-slate-400 mt-0.5">
                                @if($quo->customer_phone) <span>📱 {{ $quo->customer_phone }}</span> @endif
                                @if($quo->customer_email) <span class="ml-1">✉️ {{ $quo->customer_email }}</span> @endif
                            </div>
                        </td>
                        <td class="p-3.5 text-slate-500">{{ $quo->issue_date->format('Y-m-d') }}</td>
                        <td class="p-3.5 text-slate-500">{{ $quo->valid_until->format('Y-m-d') }}</td>
                        <td class="p-3.5 text-slate-500">
                            @if($quo->sst_amount > 0)
                                RM {{ number_format($quo->sst_amount, 2) }} <span class="text-[10px]">({{ $quo->sst_rate }}%)</span>
                            @else
                                <span class="text-slate-300 dark:text-slate-600">—</span>
                            @endif
                        </td>
                        <td class="p-3.5 font-bold text-slate-900 dark:text-white">
                            RM {{ number_format($quo->total_amount, 2) }}
                        </td>
                        <td class="p-3.5">
                            <span class="px-2 py-0.5 rounded-full font-semibold text-[10px] uppercase
                                {{ $quo->status === 'converted' ? 'bg-purple-100 text-purple-800 dark:bg-purple-950 dark:text-purple-300' : '' }}
                                {{ $quo->status === 'accepted' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300' : '' }}
                                {{ $quo->status === 'sent' ? 'bg-blue-100 text-blue-800 dark:bg-blue-950 dark:text-blue-300' : '' }}
                                {{ $quo->status === 'draft' ? 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300' : '' }}
                                {{ $quo->status === 'rejected' ? 'bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-300' : '' }}">
                                {{ $quo->status_label }}
                            </span>
                        </td>
                        <td class="p-3.5 text-right">
                            <div class="flex items-center justify-end gap-1.5">
                                <a href="{{ route('finance.quotations.show', $quo->id) }}" class="px-2.5 py-1 rounded-lg border border-slate-200 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-800 text-[11px] font-semibold text-slate-700 dark:text-slate-300">
                                    Lihat & Hantar &rarr;
                                </a>
                                @if($quo->status !== 'converted')
                                <form method="POST" action="{{ route('finance.quotations.convert', $quo->id) }}" onsubmit="return confirm('Tukar sebutharga ini kepada Invois sekarang?')">
                                    @csrf
                                    <button type="submit" class="px-2.5 py-1 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-[11px] font-semibold transition" title="Tukar terus ke Invois">
                                        + Jadi Invois
                                    </button>
                                </form>
                                @else
                                <a href="{{ route('finance.invoices.show', $quo->converted_invoice_id) }}" class="px-2 py-1 rounded-lg bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 text-[10px] font-bold">
                                    Lihat Invois
                                </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="p-8 text-center text-slate-400">
                            Tiada sebutharga dijumpai. <a href="{{ route('finance.quotations.create') }}" class="text-indigo-600 font-medium underline">Cipta sebutharga baru</a>.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($quotations->hasPages())
        <div class="p-4 border-t border-slate-100 dark:border-slate-800">
            {{ $quotations->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
