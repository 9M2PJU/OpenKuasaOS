@extends('layouts.app', ['title' => 'Lekir — Cipta Sebutharga Baru'])

@section('content')
<div class="max-w-4xl mx-auto space-y-6" x-data="{
    sstRate: 0,
    lines: [
        { description: '', quantity: 1, unit_price: 0 }
    ],
    addLine() {
        this.lines.push({ description: '', quantity: 1, unit_price: 0 });
    },
    removeLine(index) {
        if (this.lines.length > 1) {
            this.lines.splice(index, 1);
        }
    },
    get subtotal() {
        return this.lines.reduce((sum, line) => {
            return sum + (parseFloat(line.quantity || 0) * parseFloat(line.unit_price || 0));
        }, 0);
    },
    get sstAmount() {
        return Math.round((this.subtotal * (parseFloat(this.sstRate || 0) / 100)) * 100) / 100;
    },
    get totalAmount() {
        return Math.round((this.subtotal + this.sstAmount) * 100) / 100;
    }
}">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-400 mb-1">
                <a href="{{ route('finance.index') }}" class="hover:underline">Lekir</a>
                <span>&rsaquo;</span>
                <a href="{{ route('finance.quotations') }}" class="hover:underline">Sebutharga</a>
                <span>&rsaquo;</span>
                <span class="text-slate-600 dark:text-slate-300">Cipta Baru</span>
            </div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">Cipta Sebutharga Baru (Quotation)</h1>
            <p class="text-xs text-slate-500 mt-1">Sediakan tawaran harga rasmi kepada pelanggan yang boleh ditukar terus kepada invois kemudian.</p>
        </div>
        <a href="{{ route('finance.quotations') }}" class="px-3.5 py-2 rounded-xl border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 text-xs font-semibold transition">
            &larr; Kembali
        </a>
    </div>

    @if($errors->any())
    <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs dark:bg-rose-950/40 dark:border-rose-900 dark:text-rose-300">
        <strong class="font-bold">Sila semak ralat berikut:</strong>
        <ul class="list-disc list-inside mt-1 space-y-0.5">
            @foreach($errors->all() as $err)
                <li>{{ $err }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form method="POST" action="{{ route('finance.quotations.store') }}" class="space-y-6">
        @csrf

        <!-- 1. Maklumat Pelanggan & Tarikh -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-sm space-y-4">
            <h2 class="text-sm font-bold text-slate-900 dark:text-white border-b border-slate-100 dark:border-slate-800 pb-3">
                1. Maklumat Pelanggan & Tempoh Sah
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                        Nama Pelanggan / Syarikat <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="customer_name" required value="{{ old('customer_name') }}" placeholder="Cth: Megah Holdings Berhad" class="w-full px-3.5 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs focus:ring-2 focus:ring-indigo-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                        No. Telefon / WhatsApp (Untuk hantar terus)
                    </label>
                    <input type="text" name="customer_phone" value="{{ old('customer_phone') }}" placeholder="Cth: 0123456789 atau 6012..." class="w-full px-3.5 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs focus:ring-2 focus:ring-indigo-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                        Alamat Emel Pelanggan
                    </label>
                    <input type="email" name="customer_email" value="{{ old('customer_email') }}" placeholder="Cth: akaun@megah.my" class="w-full px-3.5 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs focus:ring-2 focus:ring-indigo-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                        Tarikh Sebutharga <span class="text-rose-500">*</span>
                    </label>
                    <input type="date" name="issue_date" required value="{{ old('issue_date', date('Y-m-d')) }}" class="w-full px-3.5 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs focus:ring-2 focus:ring-indigo-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                        Sah Sehingga (Valid Until) <span class="text-rose-500">*</span>
                    </label>
                    <input type="date" name="valid_until" required value="{{ old('valid_until', date('Y-m-d', strtotime('+30 days'))) }}" class="w-full px-3.5 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs focus:ring-2 focus:ring-indigo-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                        Kadar Cukai SST
                    </label>
                    <select name="sst_rate" x-model="sstRate" class="w-full px-3.5 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs focus:ring-2 focus:ring-indigo-500">
                        <option value="0">0% (Tanpa Cukai / Dikecualikan)</option>
                        <option value="6">6% (Cukai Perkhidmatan Am)</option>
                        <option value="8">8% (Cukai SST Standard Malaysia)</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- 2. Baris Item Sebutharga -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-sm space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                <h2 class="text-sm font-bold text-slate-900 dark:text-white">
                    2. Senarai Item & Perkhidmatan
                </h2>
                <button type="button" @click="addLine()" class="px-3 py-1.5 rounded-lg bg-indigo-50 text-indigo-600 hover:bg-indigo-100 dark:bg-indigo-950 dark:text-indigo-300 text-xs font-semibold transition">
                    + Tambah Baris
                </button>
            </div>

            <div class="space-y-3">
                <template x-for="(line, index) in lines" :key="index">
                    <div class="grid grid-cols-12 gap-3 items-center bg-slate-50/60 dark:bg-slate-800/40 p-3 rounded-xl border border-slate-100 dark:border-slate-800">
                        <div class="col-span-12 sm:col-span-6">
                            <label class="block text-[11px] font-semibold text-slate-500 mb-1">Keterangan Item / Servis</label>
                            <input type="text" :name="`lines[${index}][description]`" x-model="line.description" required placeholder="Cth: Pakej Langganan Sistem OpenKuasa" class="w-full px-3 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs">
                        </div>

                        <div class="col-span-4 sm:col-span-2">
                            <label class="block text-[11px] font-semibold text-slate-500 mb-1">Kuantiti</label>
                            <input type="number" step="0.01" min="0.01" :name="`lines[${index}][quantity]`" x-model="line.quantity" required class="w-full px-3 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs">
                        </div>

                        <div class="col-span-4 sm:col-span-2">
                            <label class="block text-[11px] font-semibold text-slate-500 mb-1">Harga Unit (RM)</label>
                            <input type="number" step="0.01" min="0" :name="`lines[${index}][unit_price]`" x-model="line.unit_price" required class="w-full px-3 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs">
                        </div>

                        <div class="col-span-3 sm:col-span-1 text-right">
                            <label class="block text-[11px] font-semibold text-slate-500 mb-1">Jumlah</label>
                            <span class="text-xs font-bold text-slate-800 dark:text-slate-200" x-text="'RM ' + (line.quantity * line.unit_price).toFixed(2)">RM 0.00</span>
                        </div>

                        <div class="col-span-1 text-right">
                            <label class="block text-[11px] text-transparent mb-1">A</label>
                            <button type="button" @click="removeLine(index)" class="p-1 rounded text-slate-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950 transition" title="Padam baris">
                                ✕
                            </button>
                        </div>
                    </div>
                </template>
            </div>

            <!-- Total Calculation Summary -->
            <div class="border-t border-slate-100 dark:border-slate-800 pt-4 flex justify-end">
                <div class="w-72 space-y-2 text-xs">
                    <div class="flex justify-between text-slate-500">
                        <span>Subtotal Asas:</span>
                        <span class="font-bold text-slate-800 dark:text-slate-200" x-text="'RM ' + subtotal.toFixed(2)">RM 0.00</span>
                    </div>
                    <div class="flex justify-between text-slate-500">
                        <span x-text="`Cukai SST (${sstRate}%):`">Cukai SST:</span>
                        <span class="font-bold text-slate-800 dark:text-slate-200" x-text="'RM ' + sstAmount.toFixed(2)">RM 0.00</span>
                    </div>
                    <div class="flex justify-between text-base font-extrabold text-indigo-600 dark:text-indigo-400 border-t border-slate-200 dark:border-slate-700 pt-2">
                        <span>Jumlah Keseluruhan:</span>
                        <span x-text="'RM ' + totalAmount.toFixed(2)">RM 0.00</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- 3. Catatan & Syarat Tawaran -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-sm space-y-4">
            <h2 class="text-sm font-bold text-slate-900 dark:text-white border-b border-slate-100 dark:border-slate-800 pb-3">
                3. Terma & Catatan Sebutharga
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                        Catatan Khas / Skop Tugasan
                    </label>
                    <textarea name="notes" rows="3" placeholder="Sebarang catatan tambahan mengenai skop projek atau servis..." class="w-full px-3.5 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs focus:ring-2 focus:ring-indigo-500">{{ old('notes') }}</textarea>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                        Syarat Pembayaran & Terma Tawaran
                    </label>
                    <textarea name="terms" rows="3" placeholder="Cth: Bayaran 50% deposit diperlukan sebelum projek bermula..." class="w-full px-3.5 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs focus:ring-2 focus:ring-indigo-500">{{ old('terms', "1. Sebutharga ini sah dalam tempoh 30 hari dari tarikh dikeluarkan.\n2. Invois rasmi akan dikeluarkan setelah sebutharga disahkan.") }}</textarea>
                </div>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="{{ route('finance.quotations') }}" class="px-5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 text-xs font-semibold transition">
                Batal
            </a>
            <button type="submit" class="px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow transition flex items-center gap-2">
                <span>Cipta Sebutharga &rarr;</span>
            </button>
        </div>
    </form>
</div>
@endsection
