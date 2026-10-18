@extends('layouts.app', ['title' => 'Invois Jualan — Lekir'])

@section('content')
<div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('finance.index') }}" class="text-xs text-indigo-600 dark:text-indigo-400 font-semibold hover:underline">&larr; Lekir</a>
                <span class="text-slate-400 text-xs">/</span>
                <span class="text-xs text-slate-500 font-medium">Invois</span>
            </div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white mt-1">Invois Jualan & Hasil</h1>
            <p class="text-xs text-slate-500 mt-1">Urus aliran kitaran penuh invois: Draf &rarr; Pos &rarr; Bayar &rarr; Batal (Void).</p>
        </div>
        <div>
            <a href="{{ route('finance.invoices.create') }}" class="px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold shadow-sm transition inline-flex items-center gap-2">
                <span>+ Cipta Invois Baru</span>
            </a>
        </div>
    </div>

    <!-- Search & Filters -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 shadow-sm">
        <form method="GET" action="{{ route('finance.invoices') }}" class="flex flex-col sm:flex-row items-center gap-3">
            <div class="flex-1 w-full">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nombor invois atau nama pelanggan..."
                    class="w-full px-3.5 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            <div class="w-full sm:w-48">
                <select name="status" class="w-full px-3.5 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="">Semua Status</option>
                    <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draf</option>
                    <option value="posted" {{ request('status') === 'posted' ? 'selected' : '' }}>Posted</option>
                    <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>Paid (Lunas)</option>
                    <option value="void" {{ request('status') === 'void' ? 'selected' : '' }}>Void (Batal)</option>
                </select>
            </div>
            <button type="submit" class="w-full sm:w-auto px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-900 text-white text-xs font-semibold transition">
                Tapis
            </button>
            @if(request('search') || request('status'))
            <a href="{{ route('finance.invoices') }}" class="text-xs text-slate-500 hover:underline">Reset</a>
            @endif
        </form>
    </div>

    <!-- Invoices Table -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-800 text-slate-500">
                    <tr>
                        <th class="p-4 font-semibold">No. Invois</th>
                        <th class="p-4 font-semibold">Pelanggan</th>
                        <th class="p-4 font-semibold">Tarikh Keluar</th>
                        <th class="p-4 font-semibold">Tarikh Akhir</th>
                        <th class="p-4 font-semibold">Subtotal</th>
                        <th class="p-4 font-semibold">SST (Cukai)</th>
                        <th class="p-4 font-semibold">Jumlah Bersih</th>
                        <th class="p-4 font-semibold">Status e-Invois</th>
                        <th class="p-4 font-semibold">Status</th>
                        <th class="p-4 font-semibold text-right">Tindakan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($invoices as $inv)
                    <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/40 transition">
                        <td class="p-4 font-bold text-slate-800 dark:text-slate-200">
                            <a href="{{ route('finance.invoices.show', $inv->id) }}" class="hover:text-indigo-600">
                                {{ $inv->invoice_number ?? 'DRAF' }}
                            </a>
                        </td>
                        <td class="p-4">
                            <span class="block font-medium text-slate-800 dark:text-slate-200">{{ $inv->customer_name }}</span>
                            <span class="block text-[11px] text-slate-400">{{ $inv->customer_email ?? '' }}</span>
                        </td>
                        <td class="p-4 text-slate-600 dark:text-slate-400">{{ $inv->issue_date }}</td>
                        <td class="p-4 text-slate-600 dark:text-slate-400">{{ $inv->due_date }}</td>
                        <td class="p-4 text-slate-600 dark:text-slate-400">RM {{ number_format($inv->subtotal, 2) }}</td>
                        <td class="p-4 text-slate-600 dark:text-slate-400">
                            RM {{ number_format($inv->sst_amount, 2) }}
                            <span class="text-[10px] text-slate-400">({{ $inv->sst_rate }}%)</span>
                        </td>
                        <td class="p-4 font-bold text-slate-900 dark:text-white">RM {{ number_format($inv->total_amount, 2) }}</td>
                        <td class="p-4">
                            <span class="text-[10px] px-2 py-0.5 rounded font-mono uppercase bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400">
                                {{ $inv->e_invoice_status }}
                            </span>
                        </td>
                        <td class="p-4">
                            <span class="px-2.5 py-1 rounded-full font-semibold text-[10px] uppercase
                                {{ $inv->status === 'paid' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300' : '' }}
                                {{ $inv->status === 'posted' ? 'bg-blue-100 text-blue-800 dark:bg-blue-950 dark:text-blue-300' : '' }}
                                {{ $inv->status === 'draft' ? 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300' : '' }}
                                {{ $inv->status === 'void' ? 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400' : '' }}">
                                {{ $inv->status }}
                            </span>
                        </td>
                        <td class="p-4 text-right">
                            <a href="{{ route('finance.invoices.show', $inv->id) }}" class="font-semibold text-indigo-600 hover:text-indigo-500 dark:text-indigo-400">
                                Lihat Butiran &rarr;
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="p-8 text-center text-slate-400 text-xs">
                            Tiada rekod invois dijumpai sepadan dengan carian.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($invoices->hasPages())
        <div class="p-4 border-t border-slate-100 dark:border-slate-800">
            {{ $invoices->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
