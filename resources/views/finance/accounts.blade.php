@extends('layouts.app', ['title' => 'Akaun Bank & Tunai — Lekir'])

@section('content')
<div class="space-y-6" x-data="{ addModal: false }">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('finance.index') }}" class="text-xs text-indigo-600 dark:text-indigo-400 font-semibold hover:underline">&larr; Lekir</a>
                <span class="text-slate-400 text-xs">/</span>
                <span class="text-xs text-slate-500 font-medium">Akaun Bank</span>
            </div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white mt-1">Akaun Bank & Baki Tunai</h1>
            <p class="text-xs text-slate-500 mt-1">Senarai semua akaun korporat, akaun semasa dan peti tunai runcit syarikat.</p>
        </div>
        <div>
            <button @click="addModal = true" class="px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold shadow-sm transition">
                + Tambah Akaun Bank
            </button>
        </div>
    </div>

    <!-- Accounts Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        @forelse($accounts as $acc)
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-sm flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between">
                    <span class="font-bold text-sm text-slate-900 dark:text-white">{{ $acc->name }}</span>
                    <span class="text-[10px] uppercase font-bold px-2 py-0.5 rounded bg-indigo-50 dark:bg-indigo-950 text-indigo-600 dark:text-indigo-400">
                        {{ $acc->type }}
                    </span>
                </div>
                <div class="text-xs text-slate-400 font-mono mt-2">
                    {{ $acc->account_number ?? 'Tiada nombor akaun' }}
                </div>
            </div>

            <div class="mt-6 pt-4 border-t border-slate-100 dark:border-slate-800 flex items-baseline justify-between">
                <span class="text-xs text-slate-500">Baki Semasa:</span>
                <span class="text-lg font-bold text-slate-900 dark:text-white">
                    {{ $acc->currency }} {{ number_format($acc->balance, 2) }}
                </span>
            </div>
        </div>
        @empty
        <div class="col-span-3 text-center py-12 bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 text-slate-400 text-xs">
            Tiada akaun didaftarkan. Klik "Tambah Akaun Bank" untuk bermula.
        </div>
        @endforelse
    </div>

    <!-- Add Account Modal -->
    <div x-show="addModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4" style="display: none;">
        <div @click.away="addModal = false" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl max-w-md w-full p-6 shadow-xl">
            <h3 class="text-base font-bold text-slate-900 dark:text-white">Daftar Akaun Kewangan Baru</h3>
            <p class="text-xs text-slate-500 mt-1">Masukkan butiran akaun bank atau tunai syarikat.</p>

            <form method="POST" action="{{ route('finance.accounts.store') }}" class="mt-4 space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Nama Akaun / Institusi</label>
                    <input type="text" name="name" required placeholder="cth: Maybank Islamic Berhad"
                        class="w-full px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Jenis Akaun</label>
                    <select name="type" required class="w-full px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs">
                        <option value="bank">Akaun Bank Semasa</option>
                        <option value="cash">Tunai Dalam Tangan (Petty Cash)</option>
                        <option value="credit_card">Kad Kredit Korporat</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Nombor Akaun</label>
                    <input type="text" name="account_number" placeholder="5642 1234 5678"
                        class="w-full px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs font-mono">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Mata Wang</label>
                    <input type="text" name="currency" required value="MYR"
                        class="w-full px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs uppercase font-bold">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Baki Permulaan (RM)</label>
                    <input type="number" step="0.01" name="balance" required value="0.00"
                        class="w-full px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs font-bold">
                </div>

                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="addModal = false" class="px-4 py-2 rounded-xl border border-slate-300 dark:border-slate-700 text-xs font-semibold">Batal</button>
                    <button type="submit" class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold">Simpan Akaun</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
