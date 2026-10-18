@extends('layouts.app', ['title' => 'Baucar Bayaran #' . $expense->voucher_number])

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Action Bar -->
    <div class="flex items-center justify-between print:hidden">
        <div class="flex items-center gap-2 text-xs text-slate-400">
            <a href="{{ route('finance.index') }}" class="hover:underline">Lekir</a>
            <span>&rsaquo;</span>
            <a href="{{ route('finance.expenses') }}" class="hover:underline">Perbelanjaan</a>
            <span>&rsaquo;</span>
            <span class="text-slate-600 dark:text-slate-300">{{ $expense->voucher_number }}</span>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('finance.expenses') }}" class="px-3 py-1.5 rounded-xl border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 text-xs font-semibold transition">
                &larr; Kembali
            </a>
            <button onclick="window.print()" class="px-3.5 py-1.5 rounded-xl bg-slate-900 dark:bg-slate-700 hover:bg-slate-800 text-white text-xs font-semibold transition flex items-center gap-1.5 shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <span>Cetak Baucar</span>
            </button>
            @if($expense->status === 'posted')
            <div x-data="{ open: false }" class="relative">
                <button @click="open = true" class="px-3 py-1.5 rounded-xl border border-rose-200 text-rose-600 dark:border-rose-900 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/40 text-xs font-semibold transition">
                    Batal Baucar (Void)
                </button>
                <div x-show="open" @click.away="open = false" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
                    <div class="bg-white dark:bg-slate-900 rounded-2xl max-w-md w-full p-6 shadow-xl space-y-4 border border-slate-200 dark:border-slate-800">
                        <h3 class="text-base font-bold text-slate-900 dark:text-white">Batal Baucar Pembayaran (Void)</h3>
                        <p class="text-xs text-slate-500">Tindakan ini akan memulihkan baki akaun bank dan mencipta jurnal pembalikan automatik dalam lejar am.</p>
                        <form method="POST" action="{{ route('finance.expenses.void', $expense->id) }}">
                            @csrf
                            <div class="space-y-3">
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Sebab Pembatalan <span class="text-rose-500">*</span></label>
                                    <input type="text" name="reason" required placeholder="Cth: Salah amaun / transaksi dibatalkan pembekal" class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs">
                                </div>
                                <div class="flex items-center justify-end gap-2 pt-2">
                                    <button type="button" @click="open = false" class="px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-700 text-xs">Batal</button>
                                    <button type="submit" class="px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold">Sahkan Batal (Void)</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>

    <!-- Official Printable Payment Voucher (LHDN Standard) -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-8 sm:p-10 shadow-sm space-y-8 print:border-none print:shadow-none print:p-0">
        <!-- Voucher Header -->
        <div class="flex flex-col sm:flex-row justify-between items-start gap-4 border-b border-slate-200 dark:border-slate-800 pb-6">
            <div>
                <span class="text-[11px] font-bold tracking-wider uppercase px-2.5 py-1 rounded bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300">
                    Dokumen Pematuhan Kewangan & Percukaian LHDN
                </span>
                <h1 class="text-2xl font-black tracking-tight text-slate-900 dark:text-white mt-3 uppercase">
                    Baucar Pembayaran
                </h1>
                <div class="text-xs text-slate-500 mt-1">Payment Voucher / Bukti Wang Keluar Perniagaan</div>
            </div>
            <div class="text-left sm:text-right space-y-1">
                <div class="text-lg font-black text-indigo-600 dark:text-indigo-400">
                    {{ $expense->voucher_number }}
                </div>
                <div class="text-xs text-slate-500">
                    Tarikh Bayaran: <strong class="text-slate-800 dark:text-slate-200">{{ $expense->payment_date->format('d/m/Y') }}</strong>
                </div>
                <div class="text-xs text-slate-500">
                    Status: <span class="uppercase font-bold {{ $expense->status === 'posted' ? 'text-emerald-600' : 'text-rose-600' }}">{{ $expense->status }}</span>
                </div>
            </div>
        </div>

        <!-- Payee & Tax Information Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 bg-slate-50/60 dark:bg-slate-800/40 rounded-2xl p-6 border border-slate-100 dark:border-slate-800">
            <div class="space-y-2">
                <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Dibayar Kepada / Pembekal:</div>
                <div class="text-base font-bold text-slate-900 dark:text-white">{{ $expense->vendor_name }}</div>
                @if($expense->vendor_tin)
                <div class="text-xs text-slate-600 dark:text-slate-300">
                    No. Cukai Pendapatan LHDN (TIN): <strong>{{ $expense->vendor_tin }}</strong>
                </div>
                @endif
                @if($expense->vendor_brn)
                <div class="text-xs text-slate-600 dark:text-slate-300">
                    No. Pendaftaran SSM / MyKad: <strong>{{ $expense->vendor_brn }}</strong>
                </div>
                @endif
                @if($expense->reference_number)
                <div class="text-xs text-slate-500">
                    No. Invois Pembekal / Resit: <strong>{{ $expense->reference_number }}</strong>
                </div>
                @endif
            </div>

            <div class="space-y-2">
                <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Klasifikasi Percukaian LHDN:</div>
                <div>
                    <span class="inline-block px-2.5 py-1 rounded text-xs font-bold
                        {{ $expense->expense_category === 'cogs' ? 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300' : '' }}
                        {{ $expense->expense_category === 'opex' ? 'bg-blue-100 text-blue-800 dark:bg-blue-950 dark:text-blue-300' : '' }}
                        {{ $expense->expense_category === 'capex' ? 'bg-purple-100 text-purple-800 dark:bg-purple-950 dark:text-purple-300' : '' }}
                        {{ $expense->expense_category === 'equity_injection' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300' : '' }}
                        {{ $expense->expense_category === 'non_deductible' ? 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300' : '' }}">
                        {{ $expense->category_label }}
                    </span>
                </div>
                <div class="text-xs text-slate-700 dark:text-slate-300 font-medium">
                    Status Cukai: <strong>{{ $expense->tax_classification_label }}</strong>
                </div>
                <div class="text-xs text-slate-500">
                    Akaun Sumber: <strong>{{ $expense->account->name ?? 'N/A' }}</strong> ({{ strtoupper($expense->payment_method) }})
                </div>
                @if($expense->e_invoice_uuid)
                <div class="text-xs text-emerald-600 font-mono break-all">
                    e-Invois UUID: {{ $expense->e_invoice_uuid }}
                </div>
                @endif
            </div>
        </div>

        <!-- Breakdown Table -->
        <div>
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b-2 border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-400 font-bold">
                        <th class="py-3">Keterangan / Butiran Transaksi</th>
                        <th class="py-3 text-center">Akaun Lejar LHDN</th>
                        <th class="py-3 text-right">Amaun Asas (RM)</th>
                        <th class="py-3 text-right">Cukai SST (RM)</th>
                        <th class="py-3 text-right">Jumlah Bersih (RM)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    <tr>
                        <td class="py-4">
                            <div class="font-bold text-slate-800 dark:text-slate-200">
                                {{ $expense->description ?? 'Bayaran kepada ' . $expense->vendor_name }}
                            </div>
                            <div class="text-[11px] text-slate-400 mt-0.5">
                                Kategori: {{ $expense->category_label }}
                            </div>
                        </td>
                        <td class="py-4 text-center font-mono text-slate-600 dark:text-slate-300">
                            [{{ $expense->ledgerAccount->code ?? 'N/A' }}] {{ $expense->ledgerAccount->name ?? '' }}
                        </td>
                        <td class="py-4 text-right font-medium">
                            RM {{ number_format($expense->subtotal, 2) }}
                        </td>
                        <td class="py-4 text-right text-slate-500">
                            @if($expense->tax_amount > 0)
                                RM {{ number_format($expense->tax_amount, 2) }} ({{ $expense->tax_rate }}%)
                            @else
                                RM 0.00 (0%)
                            @endif
                        </td>
                        <td class="py-4 text-right font-bold text-slate-900 dark:text-white">
                            RM {{ number_format($expense->total_amount, 2) }}
                        </td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr class="border-t-2 border-slate-200 dark:border-slate-700 font-bold text-sm">
                        <td colspan="4" class="py-3 text-right text-slate-700 dark:text-slate-300">
                            Jumlah Pembayaran Selesai:
                        </td>
                        <td class="py-3 text-right text-indigo-600 dark:text-indigo-400 text-base">
                            RM {{ number_format($expense->total_amount, 2) }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Double-entry Ledger Journal Trail -->
        @if($expense->journals->isNotEmpty())
        <div class="border-t border-slate-100 dark:border-slate-800 pt-6 space-y-3">
            <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Catatan Lejar Am Dwientri (Audit Trail)</h3>
            @foreach($expense->journals as $journal)
            <div class="rounded-xl border border-slate-100 dark:border-slate-800 overflow-hidden">
                <table class="w-full text-left text-[11px]">
                    <thead class="bg-slate-50 dark:bg-slate-800/50 text-slate-500">
                        <tr>
                            <th class="p-2.5">Akaun Carta Lejar</th>
                            <th class="p-2.5">Keterangan</th>
                            <th class="p-2.5 text-right">Debit (RM)</th>
                            <th class="p-2.5 text-right">Kredit (RM)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach($journal->lines as $line)
                        <tr>
                            <td class="p-2.5 font-medium text-slate-800 dark:text-slate-200">
                                [{{ $line->ledgerAccount->code ?? 'N/A' }}] {{ $line->ledgerAccount->name ?? 'Akaun' }}
                            </td>
                            <td class="p-2.5 text-slate-500">{{ $line->description }}</td>
                            <td class="p-2.5 text-right font-mono font-medium {{ $line->debit > 0 ? 'text-slate-900 dark:text-white' : 'text-slate-300' }}">
                                {{ $line->debit > 0 ? number_format($line->debit, 2) : '—' }}
                            </td>
                            <td class="p-2.5 text-right font-mono font-medium {{ $line->credit > 0 ? 'text-slate-900 dark:text-white' : 'text-slate-300' }}">
                                {{ $line->credit > 0 ? number_format($line->credit, 2) : '—' }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endforeach
        </div>
        @endif

        <!-- Standard Authorization & Audit Signatures -->
        <div class="border-t border-slate-200 dark:border-slate-800 pt-8 grid grid-cols-3 gap-6 text-center text-xs">
            <div class="space-y-12">
                <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Disediakan Oleh</div>
                <div class="border-b border-slate-300 dark:border-slate-700 w-3/4 mx-auto"></div>
                <div class="text-slate-500 text-[10px]">Pegawai Akaun / Kerani</div>
            </div>
            <div class="space-y-12">
                <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Disahkan Oleh</div>
                <div class="border-b border-slate-300 dark:border-slate-700 w-3/4 mx-auto"></div>
                <div class="text-slate-500 text-[10px]">Bendahara / Pengurus Kewangan</div>
            </div>
            <div class="space-y-12">
                <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Diterima / Disemak</div>
                <div class="border-b border-slate-300 dark:border-slate-700 w-3/4 mx-auto"></div>
                <div class="text-slate-500 text-[10px]">Penerima / Audit Dalaman</div>
            </div>
        </div>
    </div>
</div>
@endsection
