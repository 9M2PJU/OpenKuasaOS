@extends('layouts.app', ['title' => 'Kempen Iklan — Jebat'])

@section('content')
<div class="space-y-6" x-data="{ addModal: false }">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('reach.index') }}" class="text-xs text-indigo-600 dark:text-indigo-400 font-semibold hover:underline">&larr; Jebat Pemasaran</a>
                <span class="text-slate-400 text-xs">/</span>
                <span class="text-xs text-slate-500 font-medium">Kempen Iklan</span>
            </div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white mt-1">Kempen Pengiklanan Digital</h1>
            <p class="text-xs text-slate-500 mt-1">Urus peruntukan bajet iklan berbayar merentas platform Meta, Google & TikTok.</p>
        </div>
        <div>
            <button @click="addModal = true" class="px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold shadow-sm transition">
                + Lancar Kempen Baru
            </button>
        </div>
    </div>

    <!-- Campaigns Table -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-800 text-slate-500">
                    <tr>
                        <th class="p-4 font-semibold">Nama Kempen</th>
                        <th class="p-4 font-semibold">Platform</th>
                        <th class="p-4 font-semibold">Peruntukan Bajet</th>
                        <th class="p-4 font-semibold">Perbelanjaan (Spend)</th>
                        <th class="p-4 font-semibold">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($campaigns as $camp)
                    <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/40 transition">
                        <td class="p-4 font-semibold text-slate-800 dark:text-slate-200">{{ $camp->name }}</td>
                        <td class="p-4">
                            <span class="px-2 py-0.5 rounded font-bold uppercase text-[10px] bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300">
                                {{ $camp->platform }}
                            </span>
                        </td>
                        <td class="p-4 font-bold text-slate-900 dark:text-white">RM {{ number_format($camp->budget, 2) }}</td>
                        <td class="p-4 text-slate-500">RM {{ number_format($camp->spend, 2) }}</td>
                        <td class="p-4">
                            <span class="px-2.5 py-1 rounded-full font-bold uppercase text-[10px]
                                {{ $camp->status === 'active' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300' : '' }}
                                {{ $camp->status === 'draft' ? 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300' : '' }}
                                {{ $camp->status === 'paused' ? 'bg-slate-100 text-slate-600 dark:bg-slate-800' : '' }}
                                {{ $camp->status === 'completed' ? 'bg-blue-100 text-blue-800 dark:bg-blue-950 dark:text-blue-300' : '' }}">
                                {{ $camp->status }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="p-8 text-center text-slate-400 text-xs">Tiada kempen iklan didaftarkan.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($campaigns->hasPages())
        <div class="p-4 border-t border-slate-100 dark:border-slate-800">
            {{ $campaigns->links() }}
        </div>
        @endif
    </div>

    <!-- Add Campaign Modal -->
    <div x-show="addModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4" style="display: none;">
        <div @click.away="addModal = false" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl max-w-md w-full p-6 shadow-xl">
            <h3 class="text-base font-bold text-slate-900 dark:text-white">Daftar Kempen Iklan Baru</h3>
            <p class="text-xs text-slate-500 mt-1">Masukkan parameter bajet dan platform sasaran iklan.</p>

            <form method="POST" action="{{ route('reach.campaigns.store') }}" class="mt-4 space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Nama Kempen</label>
                    <input type="text" name="name" required placeholder="cth: Pelancaran Produk Q4 2026"
                        class="w-full px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Platform Iklan</label>
                    <select name="platform" required class="w-full px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs">
                        <option value="facebook">Meta (Facebook Ads)</option>
                        <option value="instagram">Instagram Ads</option>
                        <option value="whatsapp">WhatsApp Business Broadcast</option>
                        <option value="tiktok">TikTok Ads</option>
                        <option value="google">Google Search & YouTube Ads</option>
                        <option value="linkedin">LinkedIn B2B</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Peruntukan Bajet (RM)</label>
                    <input type="number" step="0.01" min="0" name="budget" required placeholder="5000.00"
                        class="w-full px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs font-bold">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Status Kempen</label>
                    <select name="status" required class="w-full px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs">
                        <option value="active">Aktif (Active)</option>
                        <option value="draft">Draf (Draft)</option>
                        <option value="paused">Dijeda (Paused)</option>
                    </select>
                </div>

                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="addModal = false" class="px-4 py-2 rounded-xl border border-slate-300 dark:border-slate-700 text-xs font-semibold">Batal</button>
                    <button type="submit" class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold">Simpan Kempen</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
