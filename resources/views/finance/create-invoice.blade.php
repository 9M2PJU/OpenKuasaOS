@extends('layouts.app', ['title' => 'Cipta Invois Baru — Lekir'])

@section('content')
<div class="max-w-4xl mx-auto space-y-6" x-data="invoiceForm()">
    <div>
        <div class="flex items-center gap-2">
            <a href="{{ route('finance.invoices') }}" class="text-xs text-indigo-600 dark:text-indigo-400 font-semibold hover:underline">&larr; Kembali ke Senarai Invois</a>
        </div>
        <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white mt-1">Cipta Draf Invois Baru</h1>
        <p class="text-xs text-slate-500 mt-1">Isi maklumat pelanggan, baris item dan kadar SST untuk menjana invois jualan.</p>
    </div>

    @if ($errors->any())
    <div class="bg-rose-50 dark:bg-rose-950/60 border border-rose-200 dark:border-rose-800 rounded-2xl p-4 text-xs text-rose-800 dark:text-rose-200">
        <strong class="font-semibold block mb-1">Terdapat ralat pada borang:</strong>
        <ul class="list-disc list-inside space-y-1">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form method="POST" action="{{ route('finance.invoices.store') }}" class="space-y-6">
        @csrf

        <!-- Customer & Date Info -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-sm space-y-4">
            <h2 class="text-sm font-bold text-slate-900 dark:text-white border-b border-slate-100 dark:border-slate-800 pb-3">1. Maklumat Pelanggan & Tarikh</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Nama Pelanggan / Syarikat <span class="text-rose-500">*</span></label>
                    <input type="text" name="customer_name" required value="{{ old('customer_name') }}" placeholder="cth: Megah Holdings Sdn Bhd"
                        class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs focus:ring-2 focus:ring-indigo-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Alamat Emel Pelanggan</label>
                    <input type="email" name="customer_email" value="{{ old('customer_email') }}" placeholder="akaun@megah.my"
                        class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs focus:ring-2 focus:ring-indigo-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">No. Telefon / WhatsApp (Untuk hantar terus)</label>
                    <input type="text" name="customer_phone" value="{{ old('customer_phone') }}" placeholder="0123456789 atau 6012..."
                        class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs focus:ring-2 focus:ring-indigo-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Tarikh Invois Dikeluarkan <span class="text-rose-500">*</span></label>
                    <input type="date" name="issue_date" required value="{{ old('issue_date', date('Y-m-d')) }}"
                        class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs focus:ring-2 focus:ring-indigo-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Tarikh Akhir Bayaran (Due Date) <span class="text-rose-500">*</span></label>
                    <input type="date" name="due_date" required value="{{ old('due_date', date('Y-m-d', strtotime('+30 days'))) }}"
                        class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>
        </div>

        <!-- Line Items -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-sm space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                <h2 class="text-sm font-bold text-slate-900 dark:text-white">2. Butiran Item & Servis</h2>
                <button type="button" @click="addLine()" class="px-3 py-1.5 rounded-lg bg-indigo-50 hover:bg-indigo-100 dark:bg-indigo-950/60 dark:hover:bg-indigo-900/60 text-indigo-700 dark:text-indigo-400 text-xs font-semibold transition">
                    + Tambah Baris Item
                </button>
            </div>

            <div class="space-y-3">
                <template x-for="(line, index) in lines" :key="index">
                    <div class="grid grid-cols-12 gap-3 items-center p-3 rounded-xl bg-slate-50/70 dark:bg-slate-800/40 border border-slate-100 dark:border-slate-800">
                        <div class="col-span-6">
                            <label class="block text-[10px] font-semibold text-slate-500 uppercase mb-1">Deskripsi Item</label>
                            <input type="text" :name="`lines[${index}][description]`" x-model="line.description" required placeholder="Pakej Langganan Perisian"
                                class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs">
                        </div>
                        <div class="col-span-2">
                            <label class="block text-[10px] font-semibold text-slate-500 uppercase mb-1">Kuantiti</label>
                            <input type="number" step="1" min="1" :name="`lines[${index}][quantity]`" x-model.number="line.quantity" required
                                class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs">
                        </div>
                        <div class="col-span-3">
                            <label class="block text-[10px] font-semibold text-slate-500 uppercase mb-1">Harga Seunit (RM)</label>
                            <input type="number" step="0.01" min="0" :name="`lines[${index}][unit_price]`" x-model.number="line.unit_price" required
                                class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs">
                        </div>
                        <div class="col-span-1 text-right pt-4">
                            <button type="button" @click="removeLine(index)" x-show="lines.length > 1" class="text-rose-500 hover:text-rose-700 p-1 text-sm font-bold">
                                &times;
                            </button>
                        </div>
                    </div>
                </template>
            </div>

            <!-- Tax Rate & Totals Summary -->
            <div class="pt-4 border-t border-slate-100 dark:border-slate-800 grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Kadar Cukai Jualan & Perkhidmatan (SST)</label>
                    <select name="sst_rate" x-model.number="sstRate" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs">
                        <option value="0">0% (Dikecualikan Cukai / Zero-rated)</option>
                        <option value="6">6% (Kadar Am Standard)</option>
                        <option value="8">8% (Perkhidmatan Digital & Korporat 2024+)</option>
                    </select>
                </div>

                <div class="bg-slate-50 dark:bg-slate-800/60 p-4 rounded-xl space-y-2 text-xs">
                    <div class="flex justify-between text-slate-600 dark:text-slate-400">
                        <span>Subtotal:</span>
                        <span class="font-semibold text-slate-800 dark:text-slate-200">RM <span x-text="subtotal().toFixed(2)"></span></span>
                    </div>
                    <div class="flex justify-between text-slate-600 dark:text-slate-400">
                        <span>SST (<span x-text="sstRate"></span>%):</span>
                        <span class="font-semibold text-slate-800 dark:text-slate-200">RM <span x-text="taxAmount().toFixed(2)"></span></span>
                    </div>
                    <div class="flex justify-between text-sm font-bold text-slate-900 dark:text-white pt-2 border-t border-slate-200 dark:border-slate-700">
                        <span>Jumlah Keseluruhan:</span>
                        <span class="text-indigo-600 dark:text-indigo-400">RM <span x-text="totalAmount().toFixed(2)"></span></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('finance.invoices') }}" class="px-5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 text-xs font-semibold hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                Batal
            </a>
            <button type="submit" class="px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold shadow-sm transition">
                Simpan Sebagai Draf Invois
            </button>
        </div>
    </form>
</div>

<script>
function invoiceForm() {
    return {
        sstRate: 0,
        lines: [
            { description: '', quantity: 1, unit_price: 100.00 }
        ],
        addLine() {
            this.lines.push({ description: '', quantity: 1, unit_price: 0.00 });
        },
        removeLine(index) {
            if (this.lines.length > 1) {
                this.lines.splice(index, 1);
            }
        },
        subtotal() {
            return this.lines.reduce((sum, line) => sum + ((line.quantity || 0) * (line.unit_price || 0)), 0);
        },
        taxAmount() {
            return this.subtotal() * (this.sstRate / 100);
        },
        totalAmount() {
            return this.subtotal() + this.taxAmount();
        }
    }
}
</script>
@endsection
