@extends('layouts.app', ['title' => 'Butiran Invois — Lekir'])

@section('content')
<div class="max-w-4xl mx-auto space-y-6" x-data="{ payModal: false, voidModal: false }">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('finance.invoices') }}" class="text-xs text-indigo-600 dark:text-indigo-400 font-semibold hover:underline">&larr; Senarai Invois</a>
            </div>
            <div class="flex items-center gap-3 mt-1">
                <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">
                    {{ $invoice->invoice_number ?? 'Draf Invois' }}
                </h1>
                <span class="px-2.5 py-0.5 rounded-full font-semibold text-[11px] uppercase
                    {{ $invoice->status === 'paid' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300' : '' }}
                    {{ $invoice->status === 'posted' ? 'bg-blue-100 text-blue-800 dark:bg-blue-950 dark:text-blue-300' : '' }}
                    {{ $invoice->status === 'draft' ? 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300' : '' }}
                    {{ $invoice->status === 'void' ? 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400' : '' }}">
                    {{ $invoice->status }}
                </span>
            </div>
        </div>

        <!-- Action Buttons depending on status -->
        <div class="flex flex-wrap items-center gap-2 print:hidden">
            <!-- Cetak PDF -->
            <button onclick="window.print()" class="px-3.5 py-2 rounded-xl bg-slate-900 dark:bg-slate-700 hover:bg-slate-800 text-white text-xs font-semibold shadow-sm transition flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <span>Cetak / PDF</span>
            </button>

            <!-- Hantar WhatsApp -->
            <a href="{{ $whatsappUrl }}" target="_blank" rel="noopener noreferrer" class="px-3.5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold shadow-sm transition flex items-center gap-1.5">
                <span>💬</span>
                <span>Hantar Wasap</span>
            </a>

            <!-- Hantar Email -->
            <a href="{{ $mailtoUrl }}" class="px-3.5 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold shadow-sm transition flex items-center gap-1.5">
                <span>✉️</span>
                <span>Hantar Email</span>
            </a>

            @if($invoice->status === 'draft')
            <form method="POST" action="{{ route('finance.invoices.post', $invoice->id) }}">
                @csrf
                <button type="submit" onclick="return confirm('Adakah anda pasti mahu memposkan invois ini? Tindakan ini akan mengunci draf dan menjana entri lejar am.')"
                    class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold shadow-sm transition">
                    Pos Invois (Post & Lock)
                </button>
            </form>
            @endif

            @if(in_array($invoice->status, ['posted', 'overdue']))
            <button @click="payModal = true" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold shadow-sm transition">
                Rekod Bayaran Masuk
            </button>
            <button @click="voidModal = true" class="px-4 py-2 rounded-xl border border-rose-300 dark:border-rose-800 text-rose-600 dark:text-rose-400 hover:bg-rose-50 text-xs font-semibold transition">
                Batal (Void)
            </button>
            @endif
        </div>
    </div>

    <!-- Official Invoice Document Container -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-8 shadow-sm space-y-8 print:border-none print:shadow-none print:p-0">
        <!-- Header: Org Info & Invoice Meta -->
        <div class="flex flex-col sm:flex-row justify-between gap-6 pb-6 border-b border-slate-100 dark:border-slate-800">
            <div>
                <h2 class="text-xl font-bold text-slate-900 dark:text-white">{{ $currentOrg->name ?? 'Syarikat' }}</h2>
                <p class="text-xs text-slate-500 mt-1">Platform OpenKuasa &bull; PKS Berdaftar</p>
                @if($invoice->e_invoice_uuid)
                <div class="mt-3 inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-indigo-50 dark:bg-indigo-950/60 border border-indigo-200 dark:border-indigo-800 text-[11px] font-mono text-indigo-700 dark:text-indigo-300">
                    <span>LHDN MyInvois UUID:</span>
                    <strong>{{ $invoice->e_invoice_uuid }}</strong>
                </div>
                @endif
            </div>

            <div class="sm:text-right space-y-1 text-xs">
                <div><span class="text-slate-400">Tarikh Invois:</span> <strong class="text-slate-700 dark:text-slate-300">{{ $invoice->issue_date }}</strong></div>
                <div><span class="text-slate-400">Tarikh Akhir:</span> <strong class="text-slate-700 dark:text-slate-300">{{ $invoice->due_date }}</strong></div>
                <div><span class="text-slate-400">Kadar SST:</span> <strong class="text-slate-700 dark:text-slate-300">{{ $invoice->sst_rate }}%</strong></div>
            </div>
        </div>

        <!-- Bill To -->
        <div class="flex flex-col sm:flex-row justify-between items-start gap-4">
            <div>
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Ditujukan Kepada:</span>
                <h3 class="text-base font-bold text-slate-800 dark:text-slate-200 mt-1">{{ $invoice->customer_name }}</h3>
                <div class="text-xs text-slate-500 space-y-0.5 mt-0.5">
                    @if($invoice->customer_phone)
                    <div>Telefon / WhatsApp: <strong>{{ $invoice->customer_phone }}</strong></div>
                    @endif
                    @if($invoice->customer_email)
                    <div>Emel: <strong>{{ $invoice->customer_email }}</strong></div>
                    @endif
                </div>
            </div>
            @if($invoice->quotation)
            <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-slate-800 text-xs">
                <span class="text-slate-400 text-[11px] block">Rujukan Sebutharga Asal:</span>
                <a href="{{ route('finance.quotations.show', $invoice->quotation_id) }}" class="font-bold text-indigo-600 dark:text-indigo-400 hover:underline">
                    #{{ $invoice->quotation->quotation_number }} &rarr;
                </a>
            </div>
            @endif
        </div>

        <!-- Line items table -->
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-slate-200 dark:border-slate-800 text-slate-500">
                        <th class="py-3 font-semibold">Deskripsi Item</th>
                        <th class="py-3 font-semibold text-center">Kuantiti</th>
                        <th class="py-3 font-semibold text-right">Harga Seunit (RM)</th>
                        <th class="py-3 font-semibold text-right">Jumlah (RM)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @foreach($invoice->lines as $line)
                    <tr>
                        <td class="py-4 font-medium text-slate-800 dark:text-slate-200">{{ $line->description }}</td>
                        <td class="py-4 text-center text-slate-600 dark:text-slate-400">{{ $line->quantity }}</td>
                        <td class="py-4 text-right text-slate-600 dark:text-slate-400">{{ number_format($line->unit_price, 2) }}</td>
                        <td class="py-4 text-right font-semibold text-slate-900 dark:text-white">{{ number_format($line->total ?? ($line->quantity * $line->unit_price), 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Totals summary block -->
        <div class="flex justify-end pt-4 border-t border-slate-100 dark:border-slate-800">
            <div class="w-full sm:w-64 space-y-2 text-xs">
                <div class="flex justify-between text-slate-500">
                    <span>Subtotal:</span>
                    <span class="font-semibold text-slate-800 dark:text-slate-200">RM {{ number_format($invoice->subtotal, 2) }}</span>
                </div>
                <div class="flex justify-between text-slate-500">
                    <span>SST ({{ $invoice->sst_rate }}%):</span>
                    <span class="font-semibold text-slate-800 dark:text-slate-200">RM {{ number_format($invoice->sst_amount, 2) }}</span>
                </div>
                <div class="flex justify-between text-sm font-bold text-slate-900 dark:text-white pt-2 border-t border-slate-200 dark:border-slate-700">
                    <span>Jumlah Perlu Dibayar:</span>
                    <span class="text-indigo-600 dark:text-indigo-400">RM {{ number_format($invoice->total_amount, 2) }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Pay Invoice Modal -->
    <div x-show="payModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4" style="display: none;">
        <div @click.away="payModal = false" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl max-w-md w-full p-6 shadow-xl">
            <h3 class="text-base font-bold text-slate-900 dark:text-white">Rekod Penerimaan Bayaran</h3>
            <p class="text-xs text-slate-500 mt-1">Sahkan kemasukan wang ke akaun perbankan syarikat.</p>

            <form method="POST" action="{{ route('finance.invoices.pay', $invoice->id) }}" class="mt-4 space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Pilih Akaun Bank/Tunai</label>
                    <select name="account_id" required class="w-full px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs">
                        @foreach($accounts as $acc)
                        <option value="{{ $acc->id }}">{{ $acc->name }} (Baki semasa: RM {{ number_format($acc->balance, 2) }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Jumlah Bayaran (RM)</label>
                    <input type="number" step="0.01" name="amount" required value="{{ $invoice->total_amount }}"
                        class="w-full px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Tarikh Bayaran</label>
                    <input type="date" name="payment_date" required value="{{ date('Y-m-d') }}"
                        class="w-full px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">No. Rujukan Bank / Resit</label>
                    <input type="text" name="reference" placeholder="cth: MBB-REF-99238"
                        class="w-full px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs">
                </div>

                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="payModal = false" class="px-4 py-2 rounded-xl border border-slate-300 dark:border-slate-700 text-xs font-semibold">Batal</button>
                    <button type="submit" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold">Sahkan Bayaran</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Void Invoice Modal -->
    <div x-show="voidModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4" style="display: none;">
        <div @click.away="voidModal = false" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl max-w-md w-full p-6 shadow-xl">
            <h3 class="text-base font-bold text-rose-600">Batal (Void) Invois</h3>
            <p class="text-xs text-slate-500 mt-1">Invois yang dibatalkan tidak boleh dibuka semula dan entri pembalikan akan dipos.</p>

            <form method="POST" action="{{ route('finance.invoices.void', $invoice->id) }}" class="mt-4 space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Sebab Pembatalan</label>
                    <textarea name="reason" required rows="3" placeholder="cth: Pelanggan membatalkan pesanan projek..."
                        class="w-full px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs"></textarea>
                </div>

                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="voidModal = false" class="px-4 py-2 rounded-xl border border-slate-300 dark:border-slate-700 text-xs font-semibold">Tutup</button>
                    <button type="submit" class="px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold">Batal Invois</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
