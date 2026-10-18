@extends('layouts.app', ['title' => 'Bendahara — Rekod Perbelanjaan & Modal (LHDN)'])

@section('content')
<div class="max-w-4xl mx-auto space-y-6" x-data="{
    category: 'opex',
    taxClassification: 'deductible',
    subtotal: 0.00,
    taxRate: 0,
    get taxAmount() {
        return Math.round((parseFloat(this.subtotal || 0) * (parseFloat(this.taxRate || 0) / 100)) * 100) / 100;
    },
    get totalAmount() {
        return Math.round((parseFloat(this.subtotal || 0) + this.taxAmount) * 100) / 100;
    },
    updateCategory(val) {
        this.category = val;
        if (val === 'cogs') this.taxClassification = 'cogs';
        else if (val === 'opex') this.taxClassification = 'deductible';
        else if (val === 'capex') this.taxClassification = 'capital_allowance';
        else if (val === 'equity_injection') this.taxClassification = 'equity';
        else if (val === 'non_deductible') this.taxClassification = 'non_deductible';
    }
}">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-400 mb-1">
                <a href="{{ route('finance.index') }}" class="hover:underline">Bendahara</a>
                <span>&rsaquo;</span>
                <a href="{{ route('finance.expenses') }}" class="hover:underline">Perbelanjaan & Belian</a>
                <span>&rsaquo;</span>
                <span class="text-slate-600 dark:text-slate-300">Borang Baru</span>
            </div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">Rekod Perbelanjaan & Belian Modal</h1>
            <p class="text-xs text-slate-500 mt-1">Piawaian pematuhan cukai LHDN Malaysia (Akta Cukai Pendapatan 1967 & e-Invois Pembekal).</p>
        </div>
        <a href="{{ route('finance.expenses') }}" class="px-3.5 py-2 rounded-xl border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 text-xs font-semibold transition">
            &larr; Kembali
        </a>
    </div>

    @if($errors->any())
    <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs dark:bg-rose-950/40 dark:border-rose-900 dark:text-rose-300">
        <strong class="font-bold">Sila perbetulkan ralat berikut:</strong>
        <ul class="list-disc list-inside mt-1 space-y-0.5">
            @foreach($errors->all() as $err)
                <li>{{ $err }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form method="POST" action="{{ route('finance.expenses.store') }}" class="space-y-6">
        @csrf

        <!-- 1. Kategori Transaksi & Percukaian LHDN -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-sm space-y-4">
            <div class="border-b border-slate-100 dark:border-slate-800 pb-3">
                <h2 class="text-sm font-bold text-slate-900 dark:text-white">1. Kategori Transaksi & Pematuhan LHDN</h2>
                <p class="text-xs text-slate-400 mt-0.5">Pilih tujuan perbelanjaan atau kemasukan modal bagi pelaporan cukai yang tepat.</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                        Jenis Transaksi / Belanja <span class="text-rose-500">*</span>
                    </label>
                    <select name="expense_category" x-model="category" @change="updateCategory($event.target.value)" class="w-full px-3.5 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs focus:ring-2 focus:ring-indigo-500">
                        <option value="cogs">Belian Stok / Barangan Niaga (COGS — Seksyen 33(1))</option>
                        <option value="opex">Perbelanjaan Operasi & Pejabat (OPEX — Utiliti/Sewa/Gaji)</option>
                        <option value="capex">Pembelian Aset Modal (CAPEX — Komputer/Mesin/Peralatan)</option>
                        <option value="equity_injection">Suntikan Modal Pemilik (Modal Masuk Bank)</option>
                        <option value="non_deductible">Perbelanjaan Tidak Layak Tolak Cukai</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                        Klasifikasi Cukai LHDN <span class="text-rose-500">*</span>
                    </label>
                    <select name="tax_classification" x-model="taxClassification" class="w-full px-3.5 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs focus:ring-2 focus:ring-indigo-500">
                        <option value="deductible">Boleh Tolak Cukai Penuh (Seksyen 33(1) ACP)</option>
                        <option value="cogs">Kos Barangan Dijual (COGS)</option>
                        <option value="capital_allowance">Tuntutan Elaun Modal (Jadual 3 ACP 1967)</option>
                        <option value="non_deductible">Tidak Layak Tolak Cukai</option>
                        <option value="equity">Bukan Hasil / Modal Dikecualikan Cukai</option>
                    </select>
                </div>
            </div>

            <!-- Guidance notice based on selection -->
            <div class="p-3.5 rounded-xl text-xs bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700">
                <template x-if="category === 'cogs'">
                    <div class="text-amber-800 dark:text-amber-300">
                        <strong>Belian Stok & Kos Jualan (COGS):</strong> Ditolak secara langsung daripada Pendapatan Kasar mengikut peruntukan Seksyen 33(1) Akta Cukai Pendapatan 1967.
                    </div>
                </template>
                <template x-if="category === 'opex'">
                    <div class="text-blue-800 dark:text-blue-300">
                        <strong>Belanja Operasi (OPEX):</strong> Meliputi sewa premis, bil utiliti, pemasaran, dan keperluan pejabat yang dibelanjakan sepenuhnya untuk operasi perniagaan.
                    </div>
                </template>
                <template x-if="category === 'capex'">
                    <div class="text-purple-800 dark:text-purple-300">
                        <strong>Aset Modal (CAPEX):</strong> Tidak ditolak sebagai belanja tahun semasa, sebaliknya layak menuntut <em>Elaun Modal (Capital Allowance)</em> di bawah Jadual 3 ACP 1967 (Initial & Annual Allowance).
                    </div>
                </template>
                <template x-if="category === 'equity_injection'">
                    <div class="text-emerald-800 dark:text-emerald-300">
                        <strong>Suntikan Modal Pemilik:</strong> Wang modal masuk akan menambahkan baki akaun bank perniagaan tanpa dikenakan cukai pendapatan syarikat.
                    </div>
                </template>
                <template x-if="category === 'non_deductible'">
                    <div class="text-slate-700 dark:text-slate-300">
                        <strong>Tidak Layak Tolak Cukai:</strong> Perbelanjaan peribadi atau denda kompaun yang direkodkan dalam buku tetapi ditambah balik (add-back) dalam pengiraan cukai LHDN.
                    </div>
                </template>
            </div>
        </div>

        <!-- 2. Maklumat Pembekal / Pihak Dibayar (Standard e-Invois LHDN) -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-sm space-y-4">
            <div class="border-b border-slate-100 dark:border-slate-800 pb-3">
                <h2 class="text-sm font-bold text-slate-900 dark:text-white">2. Maklumat Pembekal / Pihak Dibayar</h2>
                <p class="text-xs text-slate-400 mt-0.5">Maklumat pengenalan perniagaan pembekal untuk tujuan semakan audit LHDN.</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                        Nama Pembekal / Vendor <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="vendor_name" required value="{{ old('vendor_name') }}" placeholder="Cth: Tenaga Nasional / Pembekal Barangan" class="w-full px-3.5 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs focus:ring-2 focus:ring-indigo-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                        No. Cukai LHDN Pembekal (TIN)
                    </label>
                    <input type="text" name="vendor_tin" value="{{ old('vendor_tin') }}" placeholder="Cth: C25819001200 / IG..." class="w-full px-3.5 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs focus:ring-2 focus:ring-indigo-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                        No. Pendaftaran SSM / MyKad
                    </label>
                    <input type="text" name="vendor_brn" value="{{ old('vendor_brn') }}" placeholder="Cth: 202401009876 / 8801..." class="w-full px-3.5 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>
        </div>

        <!-- 3. Akaun Lejar & Pembayaran -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-sm space-y-4">
            <div class="border-b border-slate-100 dark:border-slate-800 pb-3">
                <h2 class="text-sm font-bold text-slate-900 dark:text-white">3. Akaun Lejar & Transaksi Bank</h2>
                <p class="text-xs text-slate-400 mt-0.5">Penetapan akaun bank pembayar dan carta akaun lejar am dwientri.</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                        Akaun Carta Lejar Am LHDN <span class="text-rose-500">*</span>
                    </label>
                    <select name="ledger_account_id" required class="w-full px-3.5 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs focus:ring-2 focus:ring-indigo-500">
                        @foreach($ledgerAccounts as $lac)
                            <option value="{{ $lac->id }}" {{ $lac->code === '5000' || $lac->code === '6100' ? 'selected' : '' }}>
                                [{{ $lac->code }}] {{ $lac->name }} ({{ strtoupper($lac->type) }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                        Akaun Bank / Tunai Sumber <span class="text-rose-500">*</span>
                    </label>
                    <select name="account_id" required class="w-full px-3.5 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs focus:ring-2 focus:ring-indigo-500">
                        @foreach($accounts as $acc)
                            <option value="{{ $acc->id }}">
                                {{ $acc->name }} (Baki: RM {{ number_format($acc->balance, 2) }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                        Tarikh Transaksi / Bayaran <span class="text-rose-500">*</span>
                    </label>
                    <input type="date" name="payment_date" required value="{{ old('payment_date', date('Y-m-d')) }}" class="w-full px-3.5 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs focus:ring-2 focus:ring-indigo-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                        Kaedah Bayaran <span class="text-rose-500">*</span>
                    </label>
                    <select name="payment_method" class="w-full px-3.5 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs focus:ring-2 focus:ring-indigo-500">
                        <option value="bank_transfer">Pindahan Bank Dalam Talian (FPX/DuitNow)</option>
                        <option value="cash">Tunai Runcit (Petty Cash)</option>
                        <option value="credit_card">Kad Kredit / Debit Perniagaan</option>
                        <option value="cheque">Cek Bank</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- 4. Amaun & Cukai SST Pembekal -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-sm space-y-4">
            <div class="border-b border-slate-100 dark:border-slate-800 pb-3">
                <h2 class="text-sm font-bold text-slate-900 dark:text-white">4. Jumlah Bayaran & Cukai</h2>
                <p class="text-xs text-slate-400 mt-0.5">Kiraan amaun asas dan cukai SST (0%, 6%, 8%) jika dikenakan oleh pembekal.</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 items-center">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                        Amaun Asas (RM) <span class="text-rose-500">*</span>
                    </label>
                    <input type="number" step="0.01" min="0.01" name="subtotal" x-model="subtotal" required placeholder="0.00" class="w-full px-3.5 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs font-bold text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                        Kadar Cukai SST Pembekal
                    </label>
                    <select name="tax_rate" x-model="taxRate" class="w-full px-3.5 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs focus:ring-2 focus:ring-indigo-500">
                        <option value="0">0% (Tiada Cukai / Dikecualikan)</option>
                        <option value="6">6% (SST Perkhidmatan Am)</option>
                        <option value="8">8% (SST Standard Malaysia)</option>
                    </select>
                </div>

                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700">
                    <div class="text-[11px] text-slate-400">Amaun Cukai SST:</div>
                    <div class="font-bold text-slate-700 dark:text-slate-300" x-text="'RM ' + taxAmount.toFixed(2)">RM 0.00</div>
                    <div class="text-[11px] text-slate-400 mt-1">Jumlah Keseluruhan (Tolak Bank):</div>
                    <div class="text-base font-extrabold text-indigo-600 dark:text-indigo-400" x-text="'RM ' + totalAmount.toFixed(2)">RM 0.00</div>
                </div>
            </div>
        </div>

        <!-- 5. Bukti Transaksi & e-Invois LHDN -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-sm space-y-4">
            <div class="border-b border-slate-100 dark:border-slate-800 pb-3">
                <h2 class="text-sm font-bold text-slate-900 dark:text-white">5. Pematuhan e-Invois LHDN & Dokumen Sokongan</h2>
                <p class="text-xs text-slate-400 mt-0.5">Nombor rujukan resit pembekal atau nombor unik e-Invois LHDN untuk jejak audit.</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                        No. Invois Pembekal / Resit
                    </label>
                    <input type="text" name="reference_number" value="{{ old('reference_number') }}" placeholder="Cth: INV-SUP-8910 / RCT-0192" class="w-full px-3.5 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs focus:ring-2 focus:ring-indigo-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                        Status e-Invois LHDN
                    </label>
                    <select name="e_invoice_status" class="w-full px-3.5 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs focus:ring-2 focus:ring-indigo-500">
                        <option value="none">Tiada (Resit Manual / Luar Talian)</option>
                        <option value="supplier_validated">Invois Pembekal Disahkan LHDN</option>
                        <option value="self_billed">Self-Billed e-Invois (Dikeluarkan Sendiri)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                        UUID e-Invois LHDN (Pilihan)
                    </label>
                    <input type="text" name="e_invoice_uuid" value="{{ old('e_invoice_uuid') }}" placeholder="Cth: MY-INV-2026-..." class="w-full px-3.5 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                    Keterangan & Catatan Transaksi
                </label>
                <textarea name="description" rows="2" placeholder="Catatan ringkas mengenai tujuan belian barang atau perbelanjaan ini..." class="w-full px-3.5 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs focus:ring-2 focus:ring-indigo-500">{{ old('description') }}</textarea>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="{{ route('finance.expenses') }}" class="px-5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 text-xs font-semibold transition">
                Batal
            </a>
            <button type="submit" class="px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow transition flex items-center gap-2">
                <span>Pos & Sahkan ke Lejar Am LHDN &rarr;</span>
            </button>
        </div>
    </form>
</div>
@endsection
