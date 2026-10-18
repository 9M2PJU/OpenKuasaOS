@extends('layouts.app', ['title' => 'Katalog Produk & Servis — Kasturi'])

@section('content')
<div class="space-y-6" x-data="{ addModal: false, editModal: false, activeProduct: {} }">
    <!-- Header & Action Bar -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('crm.index') }}" class="text-xs text-indigo-600 dark:text-indigo-400 font-semibold hover:underline">&larr; Kasturi Jualan & CRM</a>
                <span class="text-slate-400 text-xs">/</span>
                <span class="text-xs text-slate-500 font-medium">Katalog Produk & Servis</span>
            </div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white mt-1">Katalog Produk & Penawaran Servis</h1>
            <p class="text-xs text-slate-500 mt-1">Pangkalan data produk fizikal, servis, dan langganan digital untuk jualan Kasturi dan penjanaan iklan Jebat.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('reach.studio') }}" class="px-3.5 py-2.5 rounded-xl border border-pink-500/30 bg-pink-500/10 hover:bg-pink-500/20 text-pink-600 dark:text-pink-400 text-xs font-semibold shadow-xs transition flex items-center gap-1.5">
                <span>⚡</span>
                <span>Buka Jebat Ad Studio</span>
            </a>
            <button @click="addModal = true" class="px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold shadow-xs transition cursor-pointer">
                + Tambah Produk Baru
            </button>
        </div>
    </div>

    <!-- Quick Metrics -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-2xs">
            <span class="text-xs text-slate-500 font-medium">Jumlah Item Katalog</span>
            <div class="text-2xl font-black text-slate-900 dark:text-white mt-1">{{ number_format($totalProducts) }}</div>
            <span class="text-[11px] text-slate-400 mt-0.5 block">Produk aktif &amp; servis berdaftar</span>
        </div>
        <div class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-2xs">
            <span class="text-xs text-slate-500 font-medium">Jumlah Baki Stok Fizikal</span>
            <div class="text-2xl font-black text-emerald-600 dark:text-emerald-400 mt-1">{{ number_format($totalStock) }} unit</div>
            <span class="text-[11px] text-slate-400 mt-0.5 block">Inventori tersedia untuk dihantar</span>
        </div>
        <div class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-2xs">
            <span class="text-xs text-slate-500 font-medium">Nilai Kasar Katalog</span>
            <div class="text-2xl font-black text-indigo-600 dark:text-indigo-400 mt-1">RM {{ number_format($catalogValue, 2) }}</div>
            <span class="text-[11px] text-slate-400 mt-0.5 block">Berdasarkan harga jualan semasa</span>
        </div>
    </div>

    <!-- Search & Filter Bar -->
    <div class="flex flex-col sm:flex-row items-center justify-between gap-3 bg-white dark:bg-slate-900 p-3 rounded-2xl border border-slate-200 dark:border-slate-800">
        <form method="GET" action="{{ route('crm.products') }}" class="flex-1 flex items-center gap-2 w-full">
            <div class="relative flex-1">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama produk, SKU atau kategori..."
                    class="w-full pl-9 pr-4 py-2 rounded-xl text-xs bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </div>
            <select name="category" onchange="this.form.submit()" class="px-3 py-2 rounded-xl text-xs bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200">
                <option value="all">Semua Kategori</option>
                <option value="Produk Fizikal" {{ request('category') === 'Produk Fizikal' ? 'selected' : '' }}>Produk Fizikal</option>
                <option value="Perkhidmatan & SaaS" {{ request('category') === 'Perkhidmatan & SaaS' ? 'selected' : '' }}>Perkhidmatan & SaaS</option>
                <option value="Digital / Kursus" {{ request('category') === 'Digital / Kursus' ? 'selected' : '' }}>Digital / Kursus</option>
            </select>
            <button type="submit" class="px-4 py-2 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200 text-xs font-semibold hover:bg-slate-200 transition">
                Tapis
            </button>
        </form>
    </div>

    <!-- Products Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse($products as $p)
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-2xs hover:border-indigo-300 dark:hover:border-indigo-700 transition flex flex-col justify-between space-y-4">
            <div>
                <div class="flex items-start justify-between gap-2">
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400">
                        {{ $p->category }}
                    </span>
                    <span class="text-[10px] font-mono text-slate-400">{{ $p->sku ?: 'TIADA SKU' }}</span>
                </div>
                <h3 class="text-sm font-bold text-slate-900 dark:text-white mt-2 leading-snug">{{ $p->name }}</h3>
                <p class="text-xs text-slate-500 mt-1 line-clamp-2">{{ $p->description ?: 'Tiada deskripsi.' }}</p>

                <!-- USP Highlight (Punca Data Iklan Jebat) -->
                @if($p->usp)
                <div class="mt-3 p-2.5 rounded-xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200/60 dark:border-amber-800/60 text-[11px] text-amber-900 dark:text-amber-200">
                    <span class="font-bold flex items-center gap-1 text-[10px] uppercase text-amber-700 dark:text-amber-400 mb-0.5">
                        <span>💡</span> Kelebihan Unik (USP Iklan):
                    </span>
                    <span class="line-clamp-2 leading-relaxed">{{ $p->usp }}</span>
                </div>
                @endif
            </div>

            <div class="pt-3 border-t border-slate-100 dark:border-slate-800 space-y-3">
                <div class="flex items-center justify-between">
                    <div>
                        <span class="text-[10px] text-slate-400 block">Harga Jualan</span>
                        <span class="text-base font-black text-slate-900 dark:text-white">RM {{ number_format($p->price, 2) }}</span>
                    </div>
                    <div class="text-right">
                        <span class="text-[10px] text-slate-400 block">Baki Stok</span>
                        <span class="text-xs font-bold {{ $p->stock > 10 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                            {{ number_format($p->stock) }} unit
                        </span>
                    </div>
                </div>

                <!-- Action Toolbar: Link to Jebat Ad Studio -->
                <div class="flex items-center justify-between gap-2 pt-1">
                    <a href="{{ route('reach.studio', ['product_id' => $p->id]) }}" class="flex-1 py-2 px-3 rounded-xl bg-gradient-to-r from-pink-500 to-rose-600 hover:from-pink-600 hover:to-rose-700 text-white text-xs font-bold shadow-xs transition flex items-center justify-center gap-1.5" title="Buka Jebat Ad Studio dan jana iklan pintar untuk produk ini">
                        <span>⚡</span>
                        <span>Jana Iklan Jebat</span>
                    </a>

                    <form method="POST" action="{{ route('crm.products.delete', $p->id) }}" onsubmit="return confirm('Padam produk ini daripada katalog?');" class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="p-2 rounded-xl text-slate-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/50 transition cursor-pointer" title="Padam Produk">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    </form>
                </div>
            </div>
        </div>
        @empty
        <div class="col-span-full py-16 text-center bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-8">
            <span class="text-4xl">📦</span>
            <h3 class="text-base font-bold text-slate-800 dark:text-slate-200 mt-2">Tiada Produk Dijumpai</h3>
            <p class="text-xs text-slate-500 mt-1 max-w-sm mx-auto">Daftarkan produk atau servis pertama syarikat anda untuk mula mengurus stok dan menjana iklan digital bersama Jebat.</p>
            <button @click="addModal = true" class="mt-4 px-4 py-2 rounded-xl bg-indigo-600 text-white text-xs font-bold hover:bg-indigo-700 transition">
                + Tambah Produk Sekarang
            </button>
        </div>
        @endforelse
    </div>

    @if($products->hasPages())
    <div class="p-4 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl">
        {{ $products->links() }}
    </div>
    @endif

    <!-- Add Product Modal -->
    <div x-show="addModal" x-cloak class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div @click.away="addModal = false" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl max-w-lg w-full p-6 shadow-xl max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                <h3 class="text-base font-bold text-slate-900 dark:text-white">Daftar Produk / Servis Baru</h3>
                <button type="button" @click="addModal = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 font-bold text-lg">&times;</button>
            </div>

            <form method="POST" action="{{ route('crm.products.store') }}" class="mt-4 space-y-4 text-xs">
                @csrf
                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Nama Produk / Servis *</label>
                    <input type="text" name="name" required placeholder="cth: Minyak Wangi Kasturi Asli 10ml"
                        class="w-full px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Kategori *</label>
                        <select name="category" required class="w-full px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs">
                            <option value="Produk Fizikal">Produk Fizikal</option>
                            <option value="Perkhidmatan & SaaS">Perkhidmatan &amp; SaaS</option>
                            <option value="Digital / Kursus">Digital / Kursus</option>
                            <option value="Pakej B2B">Pakej B2B</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Kod SKU</label>
                        <input type="text" name="sku" placeholder="cth: SKU-PRD-001"
                            class="w-full px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs font-mono">
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Harga Jual (RM) *</label>
                        <input type="number" step="0.01" min="0" name="price" required placeholder="89.00"
                            class="w-full px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs font-bold text-slate-900 dark:text-white">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Harga Kos (RM)</label>
                        <input type="number" step="0.01" min="0" name="cost_price" placeholder="30.00"
                            class="w-full px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Baki Stok</label>
                        <input type="number" min="0" name="stock" placeholder="100"
                            class="w-full px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs">
                    </div>
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Kelebihan Unik / USP Iklan (Digunakan oleh Jebat AI)</label>
                    <textarea name="usp" rows="2" placeholder="cth: Wangian tahan lebih 24 jam tanpa alkohol, formula herba warisan Melayu..."
                        class="w-full px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs"></textarea>
                    <span class="text-[10px] text-slate-400">Jebat AI akan membaca teks ini untuk merangka ayat iklan dan skrip video.</span>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Sasaran Pelanggan (Target Demographic)</label>
                        <input type="text" name="target_audience" placeholder="cth: Lelaki eksekutif 25 - 45 tahun"
                            class="w-full px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Status</label>
                        <select name="status" class="w-full px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs">
                            <option value="active">Aktif (Dijual &amp; Diiklan)</option>
                            <option value="draft">Draf Sahaja</option>
                            <option value="archived">Arkib</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Deskripsi Penuh</label>
                    <textarea name="description" rows="2" placeholder="Penerangan spesifikasi lanjut produk..."
                        class="w-full px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs"></textarea>
                </div>

                <div class="pt-3 flex items-center justify-end gap-2 border-t border-slate-100 dark:border-slate-800">
                    <button type="button" @click="addModal = false" class="px-4 py-2 rounded-xl text-slate-600 dark:text-slate-400 text-xs font-semibold hover:bg-slate-100 dark:hover:bg-slate-800">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold transition">
                        Simpan Produk
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
