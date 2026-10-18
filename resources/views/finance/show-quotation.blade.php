@extends('layouts.app', ['title' => 'Sebutharga #' . $quotation->quotation_number])

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Action Bar (Print-hidden) -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 print:hidden">
        <div class="flex items-center gap-2 text-xs text-slate-400">
            <a href="{{ route('finance.index') }}" class="hover:underline">Lekir</a>
            <span>&rsaquo;</span>
            <a href="{{ route('finance.quotations') }}" class="hover:underline">Sebutharga</a>
            <span>&rsaquo;</span>
            <span class="text-slate-600 dark:text-slate-300">{{ $quotation->quotation_number }}</span>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('finance.quotations') }}" class="px-3 py-1.5 rounded-xl border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 text-xs font-semibold transition">
                &larr; Senarai
            </a>

            <!-- Cetak PDF -->
            <button onclick="window.print()" class="px-3.5 py-1.5 rounded-xl bg-slate-900 dark:bg-slate-700 hover:bg-slate-800 text-white text-xs font-semibold transition flex items-center gap-1.5 shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <span>Cetak / PDF</span>
            </button>

            <!-- Hantar WhatsApp -->
            <a href="{{ $whatsappUrl }}" target="_blank" rel="noopener noreferrer" class="px-3.5 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold transition flex items-center gap-1.5 shadow-sm">
                <span>💬</span>
                <span>Hantar Wasap</span>
            </a>

            <!-- Hantar Email -->
            <a href="{{ $mailtoUrl }}" class="px-3.5 py-1.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold transition flex items-center gap-1.5 shadow-sm">
                <span>✉️</span>
                <span>Hantar Email</span>
            </a>

            <!-- Tukar ke Invois -->
            @if($quotation->status !== 'converted')
            <form method="POST" action="{{ route('finance.quotations.convert', $quotation->id) }}" onsubmit="return confirm('Tukar sebutharga ini kepada Invois rasmi sekarang?')">
                @csrf
                <button type="submit" class="px-4 py-1.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold transition flex items-center gap-1.5 shadow-sm">
                    <span>⚡ Tukar Jadi Invois</span>
                </button>
            </form>
            @else
            <a href="{{ route('finance.invoices.show', $quotation->converted_invoice_id) }}" class="px-3.5 py-1.5 rounded-xl bg-purple-100 text-purple-800 dark:bg-purple-950 dark:text-purple-300 text-xs font-bold transition">
                Invois Dikeluarkan &rarr;
            </a>
            @endif

            <!-- Quick Status Switcher Dropdown -->
            <div x-data="{ open: false }" class="relative">
                <button @click="open = !open" class="px-2.5 py-1.5 rounded-xl border border-slate-200 dark:border-slate-700 text-xs font-medium text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800">
                    Status: <strong class="uppercase text-[11px]">{{ $quotation->status_label }}</strong> ▾
                </button>
                <div x-show="open" @click.away="open = false" class="absolute right-0 mt-1 w-36 bg-white dark:bg-slate-800 rounded-xl shadow-lg border border-slate-200 dark:border-slate-700 py-1 z-50 text-xs">
                    @foreach(['draft' => 'Draf', 'sent' => 'Dihantar', 'accepted' => 'Diterima', 'rejected' => 'Ditolak'] as $stKey => $stVal)
                    <form method="POST" action="{{ route('finance.quotations.status', $quotation->id) }}">
                        @csrf
                        <input type="hidden" name="status" value="{{ $stKey }}">
                        <button type="submit" class="w-full text-left px-3 py-1.5 hover:bg-slate-50 dark:hover:bg-slate-700 {{ $quotation->status === $stKey ? 'font-bold text-indigo-600' : '' }}">
                            {{ $stVal }}
                        </button>
                    </form>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    @if($quotation->status === 'converted' && $quotation->converted_invoice_id)
    <div class="p-4 rounded-2xl bg-purple-50 dark:bg-purple-950/40 border border-purple-200 dark:border-purple-900 text-purple-900 dark:text-purple-200 text-xs flex items-center justify-between print:hidden">
        <div class="flex items-center gap-2">
            <span class="text-base">✓</span>
            <span>Sebutharga ini telah ditukar kepada Invois rasmi. Invois sedia untuk dipos atau menerima bayaran.</span>
        </div>
        <a href="{{ route('finance.invoices.show', $quotation->converted_invoice_id) }}" class="font-bold underline">
            Lihat Invois Berkaitan &rarr;
        </a>
    </div>
    @endif

    <!-- Official Printable Quotation Document -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-8 sm:p-12 shadow-sm space-y-8 print:border-none print:shadow-none print:p-0">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row justify-between items-start gap-4 border-b border-slate-200 dark:border-slate-800 pb-6">
            <div class="space-y-3">
                @if(!empty($currentOrg->logo_url))
                <div class="h-14 max-w-[220px] flex items-center">
                    <img src="{{ $currentOrg->logo_url }}" alt="{{ $currentOrg->name ?? 'Logo Syarikat' }}" class="max-h-14 max-w-full object-contain">
                </div>
                @endif
                <div>
                    <div class="flex items-center gap-2 text-indigo-600 dark:text-indigo-400 font-black text-xl tracking-tight uppercase">
                        @if(empty($currentOrg->logo_url))
                        <span>⚡</span>
                        @endif
                        <span>{{ $currentOrg->name ?? 'KerjaAI Enterprise' }}</span>
                    </div>
                    <div class="text-xs text-slate-500 mt-1">Platform Perniagaan & Pengurusan Operasi Bersepadu Malaysia</div>
                </div>
                <h1 class="text-2xl font-black tracking-tight text-slate-900 dark:text-white mt-2 uppercase">
                    Sebutharga / Quotation
                </h1>
            </div>

            <div class="text-left sm:text-right space-y-1">
                <div class="text-lg font-black text-indigo-600 dark:text-indigo-400">
                    {{ $quotation->quotation_number }}
                </div>
                <div class="text-xs text-slate-500">
                    Tarikh: <strong class="text-slate-800 dark:text-slate-200">{{ $quotation->issue_date->format('d/m/Y') }}</strong>
                </div>
                <div class="text-xs text-slate-500">
                    Sah Sehingga: <strong class="text-rose-600 dark:text-rose-400">{{ $quotation->valid_until->format('d/m/Y') }}</strong>
                </div>
                <div class="mt-2">
                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase
                        {{ $quotation->status === 'converted' ? 'bg-purple-100 text-purple-800 dark:bg-purple-950 dark:text-purple-300' : '' }}
                        {{ $quotation->status === 'accepted' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300' : '' }}
                        {{ $quotation->status === 'sent' ? 'bg-blue-100 text-blue-800 dark:bg-blue-950 dark:text-blue-300' : '' }}
                        {{ $quotation->status === 'draft' ? 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300' : '' }}
                        {{ $quotation->status === 'rejected' ? 'bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-300' : '' }}">
                        {{ $quotation->status_label }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Customer Info -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 bg-slate-50/60 dark:bg-slate-800/40 rounded-2xl p-6 border border-slate-100 dark:border-slate-800">
            <div>
                <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Disediakan Untuk (Pelanggan):</div>
                <div class="text-base font-bold text-slate-900 dark:text-white mt-1">{{ $quotation->customer_name }}</div>
                @if($quotation->customer_phone)
                <div class="text-xs text-slate-600 dark:text-slate-300 mt-1">
                    Telefon / WhatsApp: <strong>{{ $quotation->customer_phone }}</strong>
                </div>
                @endif
                @if($quotation->customer_email)
                <div class="text-xs text-slate-600 dark:text-slate-300">
                    Emel: <strong>{{ $quotation->customer_email }}</strong>
                </div>
                @endif
            </div>

            <div class="space-y-1 sm:text-right">
                <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Syarat Tawaran:</div>
                <div class="text-xs text-slate-600 dark:text-slate-300">Harga adalah tertakluk kepada syarat perkhidmatan syarikat.</div>
                <div class="text-xs text-slate-500">Invois rasmi dan e-Invois LHDN akan dikeluarkan selepas pengesahan sebutharga.</div>
            </div>
        </div>

        <!-- Line Items Table -->
        <div>
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b-2 border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-400 font-bold">
                        <th class="py-3">No.</th>
                        <th class="py-3">Keterangan Item / Perkhidmatan</th>
                        <th class="py-3 text-center">Kuantiti</th>
                        <th class="py-3 text-right">Harga Unit (RM)</th>
                        <th class="py-3 text-right">Jumlah (RM)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($quotation->lines as $idx => $line)
                    <tr>
                        <td class="py-3.5 text-slate-400">{{ $idx + 1 }}</td>
                        <td class="py-3.5 font-medium text-slate-800 dark:text-slate-200">
                            {{ $line->description }}
                        </td>
                        <td class="py-3.5 text-center">{{ number_format($line->quantity, 2) }}</td>
                        <td class="py-3.5 text-right font-mono">{{ number_format($line->unit_price, 2) }}</td>
                        <td class="py-3.5 text-right font-mono font-bold text-slate-900 dark:text-white">
                            {{ number_format($line->total, 2) }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="py-6 text-center text-slate-400">Tiada baris item.</td>
                    </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr class="border-t-2 border-slate-200 dark:border-slate-700">
                        <td colspan="4" class="py-3 text-right text-slate-500 font-semibold">Subtotal Asas:</td>
                        <td class="py-3 text-right font-mono font-bold text-slate-900 dark:text-white">
                            RM {{ number_format($quotation->subtotal, 2) }}
                        </td>
                    </tr>
                    <tr>
                        <td colspan="4" class="py-2 text-right text-slate-500 font-semibold">
                            Cukai SST ({{ $quotation->sst_rate }}%):
                        </td>
                        <td class="py-2 text-right font-mono text-slate-700 dark:text-slate-300">
                            RM {{ number_format($quotation->sst_amount, 2) }}
                        </td>
                    </tr>
                    <tr class="border-t border-slate-200 dark:border-slate-700 font-bold text-base">
                        <td colspan="4" class="py-3 text-right text-indigo-600 dark:text-indigo-400">
                            Jumlah Keseluruhan Tawaran:
                        </td>
                        <td class="py-3 text-right font-mono text-indigo-600 dark:text-indigo-400 text-lg">
                            RM {{ number_format($quotation->total_amount, 2) }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Notes & Terms -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 border-t border-slate-100 dark:border-slate-800 pt-6">
            @if($quotation->notes)
            <div>
                <h3 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Catatan Khas</h3>
                <div class="text-xs text-slate-600 dark:text-slate-400 whitespace-pre-line leading-relaxed">
                    {{ $quotation->notes }}
                </div>
            </div>
            @endif

            @if($quotation->terms)
            <div>
                <h3 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Syarat & Terma Pembayaran</h3>
                <div class="text-xs text-slate-600 dark:text-slate-400 whitespace-pre-line leading-relaxed">
                    {{ $quotation->terms }}
                </div>
            </div>
            @endif
        </div>

        <!-- Signature Blocks -->
        <div class="border-t border-slate-200 dark:border-slate-800 pt-10 grid grid-cols-2 gap-8 text-center text-xs">
            <div class="space-y-12">
                <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Disediakan Oleh</div>
                <div class="border-b border-slate-300 dark:border-slate-700 w-3/4 mx-auto"></div>
                <div class="text-slate-600 dark:text-slate-300 font-semibold">{{ $currentOrg->name ?? 'KerjaAI Enterprise' }}</div>
            </div>

            <div class="space-y-12">
                <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Pengesahan Penerimaan (Pelanggan)</div>
                <div class="border-b border-slate-300 dark:border-slate-700 w-3/4 mx-auto"></div>
                <div class="text-slate-500 text-[10px]">Tandatangan / Cop Rasmi & Tarikh</div>
            </div>
        </div>
    </div>
</div>
@endsection
