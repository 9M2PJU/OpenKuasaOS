@extends('layouts.app', ['title' => 'Bendahara — Rekod Perbelanjaan & Belian LHDN'])

@section('content')
<div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-400 mb-1">
                <a href="{{ route('finance.index') }}" class="hover:underline">Bendahara</a>
                <span>&rsaquo;</span>
                <span class="text-slate-600 dark:text-slate-300">Perbelanjaan & Belian Modal</span>
            </div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">Perbelanjaan & Belian Modal (Piawaian LHDN)</h1>
            <p class="text-xs text-slate-500 mt-1">Pengauditan baucar bayaran, belian stok niaga (COGS), operasi (OPEX) dan tuntutan elaun modal mengikut Akta Cukai Pendapatan 1967.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('finance.journals') }}" class="px-3.5 py-2 rounded-xl border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 text-xs font-semibold transition">
                Lejar Am
            </a>
            <a href="{{ route('finance.expenses.create') }}" class="px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold shadow-sm transition">
                + Rekod Belanja / Modal
            </a>
        </div>
    </div>

    <!-- Summary Metrics Compatible with LHDN Tax Audit -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-sm">
            <span class="block text-xs font-medium text-slate-500">Jumlah Perbelanjaan Bersih</span>
            <span class="block text-2xl font-bold text-slate-900 dark:text-white tracking-tight mt-2">
                RM {{ number_format($totalExpenses, 2) }}
            </span>
            <span class="block text-[11px] text-slate-400 mt-1">Kos belian, operasi & aset syarikat</span>
        </div>

        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-sm">
            <span class="block text-xs font-medium text-slate-500">Pelepasan Cukai Penuh (Seksyen 33(1))</span>
            <span class="block text-2xl font-bold text-emerald-600 tracking-tight mt-2">
                RM {{ number_format($totalDeductible, 2) }}
            </span>
            <span class="block text-[11px] text-slate-400 mt-1">Kos jualan & belanja layak tolak cukai</span>
        </div>

        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-sm">
            <span class="block text-xs font-medium text-slate-500">Tuntutan Elaun Modal (Jadual 3 ACP)</span>
            <span class="block text-2xl font-bold text-purple-600 tracking-tight mt-2">
                RM {{ number_format($totalCapital, 2) }}
            </span>
            <span class="block text-[11px] text-slate-400 mt-1">Pembelian aset modal (CAPEX)</span>
        </div>

        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-sm">
            <span class="block text-xs font-medium text-slate-500">Suntikan Modal Pemilik (Ekuiti)</span>
            <span class="block text-2xl font-bold text-blue-600 tracking-tight mt-2">
                RM {{ number_format($totalCapitalInjected, 2) }}
            </span>
            <span class="block text-[11px] text-slate-400 mt-1">Dikecualikan cukai pendapatan</span>
        </div>
    </div>

    <!-- Filter & Search Controls -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 shadow-sm">
        <form method="GET" action="{{ route('finance.expenses') }}" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3">
            <div>
                <label class="block text-[11px] font-semibold text-slate-500 mb-1">Kategori Perbelanjaan</label>
                <select name="category" class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs">
                    <option value="">Semua Kategori</option>
                    <option value="cogs" {{ request('category') === 'cogs' ? 'selected' : '' }}>Belian Stok / Barang Niaga (COGS)</option>
                    <option value="opex" {{ request('category') === 'opex' ? 'selected' : '' }}>Belanja Operasi & Keperluan (OPEX)</option>
                    <option value="capex" {{ request('category') === 'capex' ? 'selected' : '' }}>Aset Modal / Kelengkapan (CAPEX)</option>
                    <option value="equity_injection" {{ request('category') === 'equity_injection' ? 'selected' : '' }}>Suntikan Modal Pemilik</option>
                    <option value="non_deductible" {{ request('category') === 'non_deductible' ? 'selected' : '' }}>Tidak Layak Tolak Cukai</option>
                </select>
            </div>

            <div>
                <label class="block text-[11px] font-semibold text-slate-500 mb-1">Klasifikasi Cukai LHDN</label>
                <select name="tax_classification" class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs">
                    <option value="">Semua Status Percukaian</option>
                    <option value="deductible" {{ request('tax_classification') === 'deductible' ? 'selected' : '' }}>Boleh Tolak Cukai (Seksyen 33(1))</option>
                    <option value="cogs" {{ request('tax_classification') === 'cogs' ? 'selected' : '' }}>Kos Barangan Niaga</option>
                    <option value="capital_allowance" {{ request('tax_classification') === 'capital_allowance' ? 'selected' : '' }}>Elaun Modal (Jadual 3)</option>
                    <option value="non_deductible" {{ request('tax_classification') === 'non_deductible' ? 'selected' : '' }}>Tidak Boleh Tolak Cukai</option>
                    <option value="equity" {{ request('tax_classification') === 'equity' ? 'selected' : '' }}>Suntikan Modal (Bukan Hasil)</option>
                </select>
            </div>

            <div>
                <label class="block text-[11px] font-semibold text-slate-500 mb-1">Carian Kata Kunci</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="No. Baucar, Pembekal, TIN, Resit..." class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs">
            </div>

            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 py-2 px-4 rounded-xl bg-slate-900 dark:bg-slate-700 hover:bg-slate-800 text-white text-xs font-semibold transition">
                    Tapis
                </button>
                @if(request()->hasAny(['category', 'tax_classification', 'search']))
                <a href="{{ route('finance.expenses') }}" class="py-2 px-3 rounded-xl border border-slate-200 dark:border-slate-700 text-slate-500 text-xs hover:bg-slate-50 dark:hover:bg-slate-800">
                    Set Semula
                </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Expenses Table -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50/75 dark:bg-slate-800/50 border-b border-slate-100 dark:border-slate-800 text-slate-500">
                    <tr>
                        <th class="p-3.5 font-semibold">No. Baucar</th>
                        <th class="p-3.5 font-semibold">Tarikh</th>
                        <th class="p-3.5 font-semibold">Pembekal / Penerima & LHDN</th>
                        <th class="p-3.5 font-semibold">Kategori</th>
                        <th class="p-3.5 font-semibold">Klasifikasi Cukai LHDN</th>
                        <th class="p-3.5 font-semibold">Akaun Bank</th>
                        <th class="p-3.5 font-semibold">Cukai SST</th>
                        <th class="p-3.5 font-semibold">Jumlah (RM)</th>
                        <th class="p-3.5 font-semibold">e-Invois LHDN</th>
                        <th class="p-3.5 font-semibold text-right">Tindakan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($expenses as $exp)
                    <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition">
                        <td class="p-3.5 font-bold text-slate-900 dark:text-white">
                            {{ $exp->voucher_number }}
                        </td>
                        <td class="p-3.5 text-slate-500">
                            {{ $exp->payment_date->format('Y-m-d') }}
                        </td>
                        <td class="p-3.5">
                            <div class="font-semibold text-slate-800 dark:text-slate-200">{{ $exp->vendor_name }}</div>
                            <div class="flex items-center gap-2 mt-0.5 text-[10px] text-slate-400">
                                @if($exp->vendor_tin)
                                    <span>TIN: <strong class="text-slate-500 dark:text-slate-300">{{ $exp->vendor_tin }}</strong></span>
                                @endif
                                @if($exp->vendor_brn)
                                    <span>SSM: {{ $exp->vendor_brn }}</span>
                                @endif
                            </div>
                        </td>
                        <td class="p-3.5">
                            <span class="px-2 py-0.5 rounded text-[10px] font-semibold
                                {{ $exp->expense_category === 'cogs' ? 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300' : '' }}
                                {{ $exp->expense_category === 'opex' ? 'bg-blue-100 text-blue-800 dark:bg-blue-950 dark:text-blue-300' : '' }}
                                {{ $exp->expense_category === 'capex' ? 'bg-purple-100 text-purple-800 dark:bg-purple-950 dark:text-purple-300' : '' }}
                                {{ $exp->expense_category === 'equity_injection' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300' : '' }}
                                {{ $exp->expense_category === 'non_deductible' ? 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300' : '' }}">
                                {{ $exp->category_label }}
                            </span>
                        </td>
                        <td class="p-3.5">
                            <span class="inline-flex items-center gap-1.5 text-[11px] font-medium text-slate-700 dark:text-slate-300">
                                @if(in_array($exp->tax_classification, ['deductible', 'cogs']))
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                @elseif($exp->tax_classification === 'capital_allowance')
                                    <span class="w-1.5 h-1.5 rounded-full bg-purple-500"></span>
                                @elseif($exp->tax_classification === 'equity')
                                    <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
                                @else
                                    <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                                @endif
                                {{ $exp->tax_classification_label }}
                            </span>
                        </td>
                        <td class="p-3.5 text-slate-600 dark:text-slate-300">
                            <div>{{ $exp->account->name ?? 'N/A' }}</div>
                            <div class="text-[10px] text-slate-400 uppercase">{{ $exp->payment_method }}</div>
                        </td>
                        <td class="p-3.5 text-slate-500">
                            @if($exp->tax_amount > 0)
                                RM {{ number_format($exp->tax_amount, 2) }} <span class="text-[10px]">({{ $exp->tax_rate }}%)</span>
                            @else
                                <span class="text-slate-300 dark:text-slate-600">—</span>
                            @endif
                        </td>
                        <td class="p-3.5 font-bold text-slate-900 dark:text-white">
                            <span class="{{ $exp->expense_category === 'equity_injection' ? 'text-emerald-600' : 'text-slate-900 dark:text-white' }}">
                                {{ $exp->expense_category === 'equity_injection' ? '+' : '-' }} RM {{ number_format($exp->total_amount, 2) }}
                            </span>
                        </td>
                        <td class="p-3.5">
                            @if($exp->e_invoice_status === 'supplier_validated')
                                <span class="px-2 py-0.5 rounded font-semibold text-[10px] bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300">Disahkan Pembekal</span>
                            @elseif($exp->e_invoice_status === 'self_billed')
                                <span class="px-2 py-0.5 rounded font-semibold text-[10px] bg-blue-100 text-blue-800 dark:bg-blue-950 dark:text-blue-300">Self-Billed</span>
                            @else
                                <span class="text-slate-400 text-[10px]">Resit / Manual</span>
                            @endif
                        </td>
                        <td class="p-3.5 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('finance.expenses.show', $exp->id) }}" class="px-2.5 py-1 rounded-lg border border-slate-200 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-800 text-[11px] font-semibold text-slate-700 dark:text-slate-300">
                                    Baucar &rarr;
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="p-8 text-center text-slate-400">
                            Tiada rekod perbelanjaan atau belian dijumpai mengikut tapisan semasa.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($expenses->hasPages())
        <div class="p-4 border-t border-slate-100 dark:border-slate-800">
            {{ $expenses->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
