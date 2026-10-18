@extends('layouts.app', ['title' => 'Kesihatan Pelayan & Tetapan — Pentadbiran Utama'])

@section('content')
<div class="space-y-8" x-data="{
    registrationEnabled: {{ $registrationEnabled ? 'true' : 'false' }},
    isToggling: false,
    isClearingCache: false,
    toast: { show: false, message: '', type: 'success' },
    showToast(message, type = 'success') {
        this.toast = { show: true, message, type };
        setTimeout(() => { this.toast.show = false; }, 4500);
    },
    async toggleRegistration() {
        if (this.isToggling) return;
        this.isToggling = true;
        const targetState = !this.registrationEnabled;

        try {
            const res = await fetch('{{ route('superadmin.system.settings.update') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ registration_enabled: targetState })
            });

            const data = await res.json();
            if (res.ok && data.success) {
                this.registrationEnabled = targetState;
                this.showToast(targetState ? 'Pendaftaran akaun baharu kini DIBUKA secara awam.' : 'Pendaftaran akaun baharu kini DITUTUP oleh pentadbir.');
            } else {
                this.showToast(data.message || 'Gagal mengubah tetapan pendaftaran.', 'error');
            }
        } catch (err) {
            this.showToast('Ralat rangkaian semasa menukar tetapan.', 'error');
        } finally {
            this.isToggling = false;
        }
    },
    async runCacheClear() {
        if (this.isClearingCache) return;
        this.isClearingCache = true;

        try {
            const res = await fetch('{{ route('superadmin.system.cache.clear') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                    'Accept': 'application/json'
                }
            });

            const data = await res.json();
            if (res.ok && data.success) {
                this.showToast(data.message || 'Cache sistem dan pandangan berjaya dikosongkan!');
            } else {
                this.showToast(data.message || 'Gagal mengosongkan cache.', 'error');
            }
        } catch (err) {
            this.showToast('Ralat semasa menjalankan operasi optimize:clear.', 'error');
        } finally {
            this.isClearingCache = false;
        }
    }
}">
    <!-- Floating Toast Notification -->
    <div x-show="toast.show" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2 scale-95" x-transition:enter-end="opacity-100 translate-y-0 scale-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0 scale-100" x-transition:leave-end="opacity-0 translate-y-2 scale-95" class="fixed bottom-6 right-6 z-50 max-w-sm rounded-xl shadow-xl p-4 text-xs font-semibold flex items-center gap-3 border" :class="toast.type === 'success' ? 'bg-emerald-900/90 text-white border-emerald-700 backdrop-blur-md' : 'bg-rose-900/90 text-white border-rose-700 backdrop-blur-md'" style="display: none;">
        <span class="text-base" x-text="toast.type === 'success' ? '✅' : '⚠️'"></span>
        <div class="flex-1" x-text="toast.message"></div>
        <button @click="toast.show = false" class="text-white/60 hover:text-white">&times;</button>
    </div>

    <!-- Header with Breadcrumbs & Title -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-400">
                <a href="{{ route('superadmin.dashboard') }}" class="hover:text-slate-600 dark:hover:text-slate-200 transition">Pentadbiran Utama</a>
                <span>/</span>
                <span class="text-slate-700 dark:text-slate-200 font-medium">Kesihatan Pelayan & Tetapan</span>
            </div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white mt-1">
                Kesihatan Pelayan &amp; Konfigurasi Self-Hosted
            </h1>
            <p class="text-xs text-slate-500 mt-0.5">
                Pemantauan komponen persekitaran operasi, storan, dan kawalan parameter teras sistem.
            </p>
        </div>
        <div class="flex items-center gap-2">
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 text-xs font-semibold">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                Pelayan Beroperasi Stabil
            </span>
        </div>
    </div>

    <!-- 4.6: Suis Toggle Alpine.js: Buka/Tutup Pendaftaran Baharu (registration_enabled) -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-sm">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div class="space-y-1">
                <div class="flex items-center gap-2">
                    <h2 class="text-base font-bold text-slate-900 dark:text-white">Pendaftaran Pengguna Baharu (registration_enabled)</h2>
                    <span class="text-[10px] px-2 py-0.5 rounded font-extrabold uppercase tracking-wider" :class="registrationEnabled ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300' : 'bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-300'" x-text="registrationEnabled ? 'DIBUKA' : 'DITUTUP'">
                        {{ $registrationEnabled ? 'DIBUKA' : 'DITUTUP' }}
                    </span>
                </div>
                <p class="text-xs text-slate-500 max-w-2xl">
                    Apabila suis ini ditutup, pelawat awam tidak lagi boleh mendaftar akaun baru melalui halaman <code class="px-1.5 py-0.5 rounded bg-slate-100 dark:bg-slate-800 font-mono text-[11px]">/register</code>. Hanya pentadbir yang boleh mencipta tenant atau staf.
                </p>
            </div>

            <div class="flex items-center gap-4 flex-shrink-0">
                <!-- Alpine.js Interactive Toggle Switch -->
                <button type="button" @click="toggleRegistration()" :disabled="isToggling" :class="registrationEnabled ? 'bg-emerald-600' : 'bg-slate-300 dark:bg-slate-700'" class="relative inline-flex h-7 w-14 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-50" role="switch" :aria-checked="registrationEnabled">
                    <span class="sr-only">Toggle Pendaftaran</span>
                    <span aria-hidden="true" :class="registrationEnabled ? 'translate-x-7' : 'translate-x-0'" class="pointer-events-none inline-block h-6 w-6 transform rounded-full bg-white shadow-md ring-0 transition duration-200 ease-in-out flex items-center justify-center text-[10px]">
                        <span x-show="isToggling" class="animate-spin text-slate-400">⏳</span>
                    </span>
                </button>
            </div>
        </div>
    </div>

    <!-- 4.6: Kad Maklumat Pelayan & Infrastruktur -->
    <div>
        <h2 class="text-sm font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-4">
            Maklumat Infrastruktur & Komponen Pelayan
        </h2>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- 1. Versi PHP -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-sm">
                <div class="flex items-center justify-between text-xs text-slate-500">
                    <span>Enjin Pengaturcaraan</span>
                    <span class="p-1 rounded bg-indigo-50 text-indigo-600 dark:bg-indigo-950/60 dark:text-indigo-400 font-bold text-[10px]">PHP</span>
                </div>
                <div class="mt-2.5">
                    <span class="text-xl font-bold text-slate-900 dark:text-white">PHP {{ $health['php_version'] }}</span>
                </div>
                <div class="mt-1 text-[11px] text-emerald-600 dark:text-emerald-400 flex items-center gap-1">
                    <span>✓ Memenuhi spesifikasi PHP 8.3+</span>
                </div>
            </div>

            <!-- 2. Rangka Kerja Laravel -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-sm">
                <div class="flex items-center justify-between text-xs text-slate-500">
                    <span>Rangka Kerja Sistem</span>
                    <span class="p-1 rounded bg-rose-50 text-rose-600 dark:bg-rose-950/60 dark:text-rose-400 font-bold text-[10px]">LARAVEL</span>
                </div>
                <div class="mt-2.5">
                    <span class="text-xl font-bold text-slate-900 dark:text-white">Laravel {{ $health['laravel_version'] }}</span>
                </div>
                <div class="mt-1 text-[11px] text-slate-500 dark:text-slate-400">
                    Versi terkini (Laravel 13 Architecture)
                </div>
            </div>

            <!-- 3. Pangkalan Data PostgreSQL -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-sm">
                <div class="flex items-center justify-between text-xs text-slate-500">
                    <span>Pangkalan Data</span>
                    <span class="p-1 rounded bg-blue-50 text-blue-600 dark:bg-blue-950/60 dark:text-blue-400 font-bold text-[10px]">PGSQL</span>
                </div>
                <div class="mt-2.5">
                    <span class="text-xl font-bold text-slate-900 dark:text-white truncate block">{{ $health['database']['version'] }}</span>
                </div>
                <div class="mt-1 text-[11px] text-emerald-600 dark:text-emerald-400 flex items-center gap-1 font-medium">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                    <span>Sambungan Aktif (Default: {{ $health['database']['driver'] }})</span>
                </div>
            </div>

            <!-- 4. IP Pelayan & Masa Aktif -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-sm">
                <div class="flex items-center justify-between text-xs text-slate-500">
                    <span>IP Pelayan & Uptime</span>
                    <span class="p-1 rounded bg-amber-50 text-amber-600 dark:bg-amber-950/60 dark:text-amber-400 font-bold text-[10px]">HOST</span>
                </div>
                <div class="mt-2.5">
                    <span class="text-base font-bold font-mono text-slate-900 dark:text-white">{{ $health['server_ip'] }}</span>
                </div>
                <div class="mt-1 text-[11px] text-slate-500 dark:text-slate-400 truncate">
                    Masa Aktif: <strong class="text-slate-700 dark:text-slate-300">{{ $health['uptime'] }}</strong>
                </div>
            </div>
        </div>
    </div>

    <!-- AI Engine & Storage Indicators -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <!-- Tuah AI Engine Health -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-sm">
            <div class="flex items-center justify-between mb-3">
                <div class="flex items-center gap-2">
                    <span class="text-lg">🤖</span>
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white">Enjin Pembantu Tuah AI</h3>
                </div>
                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold {{ $health['ai']['ready'] ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300' : 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300' }}">
                    {{ $health['ai']['ready'] ? 'SEDIA & AKTIF' : 'STANDBY' }}
                </span>
            </div>
            <p class="text-xs text-slate-500 mb-3">
                Model Bahasa Tempatan/Awan yang dipautkan untuk pertanyaan Bendahara, Lekiu, Kasturi, Lekir, dan Jebat.
            </p>
            <div class="bg-slate-50 dark:bg-slate-800/60 rounded-xl p-3 text-xs space-y-1 font-mono text-slate-700 dark:text-slate-300">
                <div class="flex justify-between">
                    <span class="text-slate-400">Model Aktif:</span>
                    <span class="font-bold text-indigo-600 dark:text-indigo-400">{{ $health['ai']['model'] }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-400">Status Gateway:</span>
                    <span class="text-emerald-600 dark:text-emerald-400">Online & Ready</span>
                </div>
            </div>
        </div>

        <!-- Disk & Storage Health -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-sm">
            <div class="flex items-center justify-between mb-3">
                <div class="flex items-center gap-2">
                    <span class="text-lg">💾</span>
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white">Kapasiti & Storan Pelayan</h3>
                </div>
                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold {{ $health['storage']['ok'] ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300' : 'bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-300' }}">
                    {{ $health['storage']['ok'] ? 'STORAGE OK' : 'STORAGE LOW' }}
                </span>
            </div>
            <p class="text-xs text-slate-500 mb-3">
                Ruang cakera storan direktori sistem dan lampiran fail invois, resit, dan dokumen perniagaan.
            </p>
            <div class="bg-slate-50 dark:bg-slate-800/60 rounded-xl p-3 text-xs space-y-1 font-mono text-slate-700 dark:text-slate-300">
                <div class="flex justify-between">
                    <span class="text-slate-400">Ruang Bebas:</span>
                    <span class="font-bold text-emerald-600 dark:text-emerald-400">{{ $health['storage']['free_formatted'] }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-400">Kadar Penggunaan:</span>
                    <span>{{ $health['storage']['used_percentage'] }}% Terisi</span>
                </div>
            </div>
        </div>
    </div>

    <!-- 4.6: Butang Penyelenggaraan Pantas (Quick Maintenance) -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-sm">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h3 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                    <span>🧹 Penyelenggaraan & Pembersihan Cache Sistem</span>
                </h3>
                <p class="text-xs text-slate-500 mt-1 max-w-2xl">
                    Melaksanakan arahan <code class="px-1.5 py-0.5 rounded bg-slate-100 dark:bg-slate-800 font-mono text-[11px]">php artisan optimize:clear</code> bagi mengosongkan cache konfigurasi, laluan, dan paparan kompilasi Blade.
                </p>
            </div>
            <div class="flex items-center gap-3">
                <button type="button" @click="runCacheClear()" :disabled="isClearingCache" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 dark:bg-white dark:hover:bg-slate-100 text-white dark:text-slate-900 font-bold text-xs shadow-sm transition hover:scale-105 active:scale-95 disabled:opacity-50 cursor-pointer">
                    <span x-show="!isClearingCache">⚡</span>
                    <span x-show="isClearingCache" class="animate-spin">⏳</span>
                    <span x-text="isClearingCache ? 'Sedang Mengosongkan...' : 'Kosongkan Cache Sistem (optimize:clear)'">
                        Kosongkan Cache Sistem (artisan optimize:clear)
                    </span>
                </button>
            </div>
        </div>
    </div>
</div>
@endsection
