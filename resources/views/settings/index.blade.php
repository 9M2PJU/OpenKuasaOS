@php
    $isMalay = ($currentLocale ?? app()->getLocale()) === 'ms';
@endphp

@extends('layouts.app', ['title' => $isMalay ? 'Tetapan Sistem' : 'System Settings'])

@section('content')
<div class="max-w-4xl mx-auto space-y-6 pb-12">
    <!-- Header -->
    <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-800 pb-5">
        <div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 font-bold text-sm">
                    ⚙️
                </span>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">
                    {{ $isMalay ? 'Tetapan Sistem' : 'System Settings' }}
                </h1>
            </div>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                {{ $isMalay ? 'Urus pilihan bahasa lalai, maklumat profil akaun, dan organisasi anda.' : 'Manage default language, account profile, and organization settings.' }}
            </p>
        </div>

        <div class="flex items-center gap-2">
            <span class="px-3 py-1 rounded-full text-xs font-semibold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700">
                {{ $isMalay ? 'Bahasa Semasa: 🇲🇾 Bahasa Melayu' : 'Current Language: 🇬🇧 English' }}
            </span>
        </div>
    </div>

    @if(session('success'))
    <div class="p-4 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300 text-xs flex items-center justify-between">
        <div class="flex items-center gap-2">
            <span class="text-base">✓</span>
            <span>{{ session('success') }}</span>
        </div>
    </div>
    @endif

    @if($errors->any())
    <div class="p-4 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 text-rose-800 dark:text-rose-300 text-xs">
        <ul class="list-disc list-inside space-y-1">
            @foreach($errors->all() as $err)
            <li>{{ $err }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <!-- 1. Pilihan Bahasa Utama (Language Selection Card) -->
    <div class="bg-white dark:bg-[#101726] border border-slate-200 dark:border-[#1F293D] rounded-2xl p-6 shadow-sm space-y-5">
        <div class="border-b border-slate-100 dark:border-slate-800/80 pb-4">
            <h2 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                <span>🌐</span>
                <span>{{ $isMalay ? 'Pilihan Bahasa Utama (Default Language)' : 'Default System Language' }}</span>
            </h2>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                {{ $isMalay ? 'Tetapkan bahasa standard bagi antaramuka KerjaAI. Bahasa Melayu adalah bahasa lalai sistem.' : 'Select the default language for the KerjaAI interface. Bahasa Melayu is set as default.' }}
            </p>
        </div>

        <form method="POST" action="{{ route('settings.language') }}" class="space-y-5">
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <!-- Option 1: Bahasa Melayu -->
                <label class="relative flex flex-col p-4 rounded-xl border-2 cursor-pointer transition {{ ($currentLocale ?? 'ms') === 'ms' ? 'border-indigo-600 bg-indigo-50/40 dark:bg-indigo-950/20' : 'border-slate-200 dark:border-slate-800 hover:border-slate-300 dark:hover:border-slate-700' }}">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <span class="text-2xl">🇲🇾</span>
                            <div>
                                <span class="block font-bold text-sm text-slate-900 dark:text-white">Bahasa Melayu</span>
                                <span class="block text-[11px] text-slate-500 dark:text-slate-400">Bahasa Rasmi & Lalai Sistem</span>
                            </div>
                        </div>
                        <input type="radio" name="locale" value="ms" {{ ($currentLocale ?? 'ms') === 'ms' ? 'checked' : '' }} class="w-4 h-4 text-indigo-600 focus:ring-indigo-500">
                    </div>
                    <div class="mt-3 text-[11px] text-slate-600 dark:text-slate-300">
                        {{ $isMalay ? 'Semua modul, dashboard, lejar am, baucar dan invois dipaparkan dalam Bahasa Melayu standard.' : 'Modules, dashboards, ledger accounts, vouchers and invoices displayed in standard Bahasa Melayu.' }}
                    </div>
                    <div class="mt-2">
                        <span class="inline-block px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800 dark:bg-amber-950/80 dark:text-amber-300 border border-amber-300/40">
                            {{ $isMalay ? 'Lalai (Default)' : 'Default' }}
                        </span>
                    </div>
                </label>

                <!-- Option 2: English -->
                <label class="relative flex flex-col p-4 rounded-xl border-2 cursor-pointer transition {{ ($currentLocale ?? 'ms') === 'en' ? 'border-indigo-600 bg-indigo-50/40 dark:bg-indigo-950/20' : 'border-slate-200 dark:border-slate-800 hover:border-slate-300 dark:hover:border-slate-700' }}">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <span class="text-2xl">🇬🇧</span>
                            <div>
                                <span class="block font-bold text-sm text-slate-900 dark:text-white">English</span>
                                <span class="block text-[11px] text-slate-500 dark:text-slate-400">International English</span>
                            </div>
                        </div>
                        <input type="radio" name="locale" value="en" {{ ($currentLocale ?? 'ms') === 'en' ? 'checked' : '' }} class="w-4 h-4 text-indigo-600 focus:ring-indigo-500">
                    </div>
                    <div class="mt-3 text-[11px] text-slate-600 dark:text-slate-300">
                        {{ $isMalay ? 'Paparan antaramuka, carta dashboard dan dokumen ditukar ke Bahasa Inggeris antarabangsa.' : 'System user interface, analytics charts, and documents presented in international English.' }}
                    </div>
                    <div class="mt-2">
                        <span class="inline-block px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400">
                            Alternative
                        </span>
                    </div>
                </label>
            </div>

            <div class="flex items-center justify-end pt-2">
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow-sm transition flex items-center gap-2">
                    <span>💾</span>
                    <span>{{ $isMalay ? 'Simpan Pilihan Bahasa' : 'Save Language Preference' }}</span>
                </button>
            </div>
        </form>
    </div>

    <!-- 2. Pilihan Tema Paparan (Display Theme) -->
    <div class="bg-white dark:bg-[#101726] border border-slate-200 dark:border-[#1F293D] rounded-2xl p-6 shadow-sm space-y-5">
        <div class="border-b border-slate-100 dark:border-slate-800/80 pb-4 flex items-center justify-between flex-wrap gap-2">
            <div>
                <h2 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                    <span>🎨</span>
                    <span>{{ $isMalay ? 'Tema Paparan' : 'Display Theme' }}</span>
                </h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                    {{ $isMalay ? 'Pilih mod paparan antaramuka KerjaAI mengikut keselesaan anda. Mod Cerah adalah tema lalai sistem.' : 'Select your preferred KerjaAI interface mode. Light Mode is set as the system default.' }}
                </p>
            </div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700">
                    <span data-theme-icon class="flex items-center"></span>
                    <span data-theme-text>{{ $isMalay ? 'Mod Cerah' : 'Light Mode' }}</span>
                </span>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <!-- Pilihan 1: Mod Cerah (Default / Lalai) -->
            <div data-theme-select="light" data-theme-card="light" class="relative flex flex-col p-4 rounded-xl border-2 cursor-pointer transition border-slate-200 dark:border-slate-800 hover:border-slate-300 dark:hover:border-slate-700">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 dark:bg-amber-950/60 dark:text-amber-400 flex items-center justify-center font-bold text-lg shadow-2xs border border-amber-200/50 dark:border-amber-800/50">
                            ☀️
                        </div>
                        <div>
                            <span class="block font-bold text-sm text-slate-900 dark:text-white">{{ $isMalay ? 'Mod Cerah (Light Mode)' : 'Light Mode' }}</span>
                            <span class="block text-[11px] text-slate-500 dark:text-slate-400">{{ $isMalay ? 'Lalai Sistem' : 'System Default' }}</span>
                        </div>
                    </div>
                    <input type="radio" name="theme_preference" value="light" class="w-4 h-4 text-indigo-600 focus:ring-indigo-500 pointer-events-none">
                </div>
                <div class="mt-3 text-[11px] text-slate-600 dark:text-slate-300 leading-relaxed">
                    {{ $isMalay ? 'Latar belakang cerah dengan kontras teks optimum. Sesuai untuk kegunaan harian dan persekitaran pejabat.' : 'Crisp light background with optimal text contrast for day-to-day productivity.' }}
                </div>
                <div class="mt-3 flex items-center gap-2">
                    <span class="inline-block px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800 dark:bg-amber-950/80 dark:text-amber-300 border border-amber-300/40">
                        {{ $isMalay ? 'Lalai (Default)' : 'Default' }}
                    </span>
                </div>
            </div>

            <!-- Pilihan 2: Mod Gelap (Dark Mode) -->
            <div data-theme-select="dark" data-theme-card="dark" class="relative flex flex-col p-4 rounded-xl border-2 cursor-pointer transition border-slate-200 dark:border-slate-800 hover:border-slate-300 dark:hover:border-slate-700">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-slate-900 text-indigo-400 border border-slate-700 flex items-center justify-center font-bold text-lg shadow-2xs">
                            🌙
                        </div>
                        <div>
                            <span class="block font-bold text-sm text-slate-900 dark:text-white">{{ $isMalay ? 'Mod Gelap (Dark Mode)' : 'Dark Mode' }}</span>
                            <span class="block text-[11px] text-slate-500 dark:text-slate-400">{{ $isMalay ? 'Pilihan Alternatif' : 'Alternative' }}</span>
                        </div>
                    </div>
                    <input type="radio" name="theme_preference" value="dark" class="w-4 h-4 text-indigo-600 focus:ring-indigo-500 pointer-events-none">
                </div>
                <div class="mt-3 text-[11px] text-slate-600 dark:text-slate-300 leading-relaxed">
                    {{ $isMalay ? 'Latar belakang gelap mewah slate-950 bagi mengurangkan keletihan mata dalam suasana pencahayaan malap.' : 'Sleek slate-950 dark background engineered to reduce eye strain in darker environments.' }}
                </div>
                <div class="mt-3 flex items-center gap-2">
                    <span class="inline-block px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400">
                        {{ $isMalay ? 'Alternatif' : 'Alternative' }}
                    </span>
                </div>
            </div>
        </div>

        <div class="pt-3 border-t border-slate-100 dark:border-slate-800/80 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div class="text-[11px] text-slate-500 dark:text-slate-400">
                <span>{{ $isMalay ? 'Pilihan tema disimpan secara setempat pada pelayar anda.' : 'Theme preference is stored locally in your browser.' }}</span>
            </div>

            <button data-theme-toggle type="button" class="px-4 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 dark:bg-indigo-600 dark:hover:bg-indigo-700 text-white text-xs font-semibold shadow-xs transition flex items-center justify-center gap-2 cursor-pointer active:scale-95 flex-shrink-0">
                <span data-theme-icon class="flex items-center"></span>
                <span>{{ $isMalay ? 'Tukar Mod' : 'Toggle Mode' }}</span>
            </button>
        </div>
    </div>

    <!-- 3. Maklumat Pengguna & Organisasi (Profile Info) -->
    <div class="bg-white dark:bg-[#101726] border border-slate-200 dark:border-[#1F293D] rounded-2xl p-6 shadow-sm space-y-5">
        <div class="border-b border-slate-100 dark:border-slate-800/80 pb-4 flex items-center justify-between">
            <div>
                <h2 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                    <span>👤</span>
                    <span>{{ $isMalay ? 'Profil Pengguna & Syarikat' : 'User Profile & Organization' }}</span>
                </h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                    {{ $isMalay ? 'Maklumat pengenalan akaun anda dalam organisasi semasa.' : 'Account identification within the active tenant organization.' }}
                </p>
            </div>
            @if($org)
            <span class="px-3 py-1 rounded-lg text-xs font-bold bg-indigo-50 text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800">
                🏢 {{ $org->name }}
            </span>
            @endif
        </div>

        <form method="POST" action="{{ route('settings.profile') }}" class="space-y-4">
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                        {{ $isMalay ? 'Nama Penuh' : 'Full Name' }}
                    </label>
                    <input type="text" name="name" value="{{ old('name', $user->name ?? '') }}" required class="w-full px-3.5 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                        {{ $isMalay ? 'Alamat Emel' : 'Email Address' }}
                    </label>
                    <input type="email" name="email" value="{{ old('email', $user->email ?? '') }}" required class="w-full px-3.5 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
                </div>

                @if($org)
                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                        {{ $isMalay ? 'Nama Syarikat / Organisasi' : 'Company / Organization Name' }}
                    </label>
                    <input type="text" name="org_name" value="{{ old('org_name', $org->name ?? '') }}" class="w-full px-3.5 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500" placeholder="{{ $isMalay ? 'Masukkan nama syarikat anda' : 'Enter company name' }}">
                    <p class="text-[11px] text-slate-400 mt-1">
                        {{ $isMalay ? 'Nama syarikat yang tertera pada invois, sebutharga, dan penyata kewangan.' : 'Company name displayed on invoices, quotations, and financial statements.' }}
                    </p>
                </div>

                <!-- Logo Syarikat (Auto-Upload & Instant Save) -->
                <div class="sm:col-span-2 pt-4 border-t border-slate-100 dark:border-slate-800/80" x-data="companyLogoUploader()">
                    <div class="flex items-center justify-between pb-2">
                        <div>
                            <label class="block text-xs font-bold text-slate-800 dark:text-slate-200">
                                {{ $isMalay ? 'Logo Rasmi Syarikat (Invois, Sebutharga & Dokumen)' : 'Company Official Logo (Invoices, Quotations & Documents)' }}
                            </label>
                            <p class="text-[11px] text-slate-500 dark:text-slate-400">
                                {{ $isMalay ? 'Pilih fail gambar logo anda. Logo akan dimuat naik dan disimpan terus secara automatik.' : 'Select your company logo. The file will be auto-uploaded and saved immediately.' }}
                            </p>
                        </div>
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800">
                            ⚡ {{ $isMalay ? 'Auto-Upload & Save' : 'Instant Save' }}
                        </span>
                    </div>

                    <!-- Logo Uploader Box -->
                    <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/40 border border-slate-200 dark:border-slate-700/80 space-y-4">
                        <div class="flex flex-col sm:flex-row items-center gap-5">
                            <!-- Preview Box -->
                            <div class="relative w-44 h-28 rounded-xl bg-white dark:bg-slate-900 border-2 border-dashed border-slate-200 dark:border-slate-700 flex items-center justify-center p-3 overflow-hidden shadow-2xs flex-shrink-0 group">
                                <template x-if="logoUrl">
                                    <img :src="logoUrl" alt="Logo Syarikat" class="max-h-full max-w-full object-contain">
                                </template>
                                <template x-if="!logoUrl">
                                    <div class="text-center text-slate-400 dark:text-slate-500">
                                        <div class="text-2xl mb-1">🏢</div>
                                        <span class="text-[10px] font-medium block leading-tight">{{ $isMalay ? 'Tiada Logo' : 'No Logo' }}</span>
                                        <span class="text-[9px] text-slate-400 block">{{ $isMalay ? 'Pilih fail di sebelah' : 'Select file' }}</span>
                                    </div>
                                </template>

                                <!-- Loading Spinner Overlay -->
                                <div x-show="isUploading" x-transition class="absolute inset-0 bg-white/90 dark:bg-slate-900/90 backdrop-blur-xs flex flex-col items-center justify-center gap-1 text-indigo-600 dark:text-indigo-400">
                                    <svg class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    <span class="text-[10px] font-bold">{{ $isMalay ? 'Menyimpan...' : 'Saving...' }}</span>
                                </div>
                            </div>

                            <!-- Controls & Status -->
                            <div class="flex-1 space-y-3 w-full">
                                <input type="file" x-ref="logoFileInput" @change="uploadLogo($event)" accept="image/png,image/jpeg,image/jpg,image/webp,image/svg+xml" class="hidden">

                                <div class="flex flex-wrap items-center gap-2.5">
                                    <button type="button" @click="$refs.logoFileInput.click()" :disabled="isUploading" class="px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow-xs transition flex items-center gap-2 cursor-pointer disabled:opacity-50">
                                        <span>📁</span>
                                        <span x-text="logoUrl ? '{{ $isMalay ? 'Tukar Logo Baharu' : 'Change Logo' }}' : '{{ $isMalay ? 'Pilih & Muat Naik Logo' : 'Select & Upload Logo' }}'">{{ !empty($org->logo_url) ? ($isMalay ? 'Tukar Logo Baharu' : 'Change Logo') : ($isMalay ? 'Pilih & Muat Naik Logo' : 'Select & Upload Logo') }}</span>
                                    </button>

                                    <button type="button" @click="removeLogo()" x-show="logoUrl" :disabled="isUploading" class="px-3.5 py-2.5 rounded-xl border border-rose-200 dark:border-rose-800/80 text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/40 text-xs font-semibold transition flex items-center gap-1.5 cursor-pointer disabled:opacity-50">
                                        <span>🗑️</span>
                                        <span>{{ $isMalay ? 'Padam Logo' : 'Remove' }}</span>
                                    </button>
                                </div>

                                <!-- Feedback Message -->
                                <div x-show="uploadMessage" x-transition class="text-xs font-medium flex items-center gap-1.5" :class="isError ? 'text-rose-600 dark:text-rose-400' : 'text-emerald-600 dark:text-emerald-400'">
                                    <span x-text="uploadMessage"></span>
                                </div>

                                <div class="text-[11px] text-slate-500 dark:text-slate-400 flex items-center gap-1.5">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                    <span>{{ $isMalay ? 'Tindakan automatik: Logo disimpan serta-merta tanpa perlu menekan butang simpan profil di bawah.' : 'Automatic action: The logo is saved immediately without needing to click the profile save button below.' }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Recommendations Card (Size & Format) -->
                        <div class="pt-3 border-t border-slate-200/80 dark:border-slate-700/80 grid grid-cols-1 md:grid-cols-3 gap-3 text-xs">
                            <div class="p-3 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800">
                                <div class="font-bold text-slate-800 dark:text-slate-200 flex items-center gap-1.5">
                                    <span>🎨</span>
                                    <span>{{ $isMalay ? 'Format Disyorkan' : 'Recommended Format' }}</span>
                                </div>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">
                                    <strong class="text-indigo-600 dark:text-indigo-400">PNG (Latar Lutsinar)</strong>, JPG, WebP, atau SVG. Latar belakang lutsinar menjamin kekemasan pada invois.
                                </p>
                            </div>

                            <div class="p-3 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800">
                                <div class="font-bold text-slate-800 dark:text-slate-200 flex items-center gap-1.5">
                                    <span>📐</span>
                                    <span>{{ $isMalay ? 'Dimensi / Nisbah Ideal' : 'Ideal Dimensions' }}</span>
                                </div>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">
                                    <strong>400 × 120 px</strong> (nisbah mendatar 3:1 atau 4:1) untuk kepala invois, atau <strong>400 × 400 px</strong> (segi empat sama) untuk ikon/avatar.
                                </p>
                            </div>

                            <div class="p-3 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800">
                                <div class="font-bold text-slate-800 dark:text-slate-200 flex items-center gap-1.5">
                                    <span>⚖️</span>
                                    <span>{{ $isMalay ? 'Had Saiz Fail' : 'File Size Limit' }}</span>
                                </div>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">
                                    Maksimum <strong>2MB (2,048 KB)</strong>. Saiz optimum memastikan fail PDF invois pantas dimuat turun dan dihantar kepada pelanggan.
                                </p>
                            </div>
                        </div>

                        <!-- Mini Invoice Header Preview -->
                        <div class="pt-3 border-t border-slate-200/80 dark:border-slate-700/80">
                            <span class="text-[10px] uppercase font-bold tracking-wider text-slate-400 block mb-2">
                                {{ $isMalay ? 'Pratonton Paparan Pada Kepala Invois & Sebutharga:' : 'Live Preview on Invoice & Quotation Header:' }}
                            </span>
                            <div class="p-4 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                <div class="flex items-center gap-3">
                                    <div class="h-10 w-28 rounded-lg bg-slate-50 dark:bg-slate-800 flex items-center justify-center p-1 border border-slate-100 dark:border-slate-700 overflow-hidden">
                                        <template x-if="logoUrl">
                                            <img :src="logoUrl" alt="Logo Invois" class="max-h-full max-w-full object-contain">
                                        </template>
                                        <template x-if="!logoUrl">
                                            <span class="text-[10px] text-slate-400 font-semibold">{{ $isMalay ? 'Logo Anda' : 'Your Logo' }}</span>
                                        </template>
                                    </div>
                                    <div>
                                        <span class="block font-bold text-xs text-slate-900 dark:text-white">{{ $org->name ?? 'Syarikat Anda' }}</span>
                                        <span class="block text-[10px] text-slate-500 dark:text-slate-400">Platform KerjaAI &bull; PKS Berdaftar</span>
                                    </div>
                                </div>
                                <div class="sm:text-right">
                                    <span class="px-2 py-0.5 rounded text-[9px] font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300">
                                        INVOIS RASMI #INV-2026-081
                                    </span>
                                    <span class="block text-[10px] text-slate-400 mt-0.5">MyInvois LHDN Ready</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
            </div>

            <div class="flex items-center justify-end pt-2">
                <button type="submit" class="px-5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 text-xs font-semibold text-slate-700 dark:text-slate-200 transition">
                    {{ $isMalay ? 'Kemaskini Profil & Syarikat' : 'Update Profile & Organization' }}
                </button>
            </div>
        </form>
    </div>

    <!-- 4. Konfigurasi Emel SMTP Syarikat (Company SMTP Email Gateway Card) -->
    @if($org)
    <div class="bg-white dark:bg-[#101726] border border-slate-200 dark:border-[#1F293D] rounded-2xl p-6 shadow-sm space-y-5" x-data="companySmtpComponent()">
        <div class="border-b border-slate-100 dark:border-slate-800/80 pb-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h2 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                    <span>📧</span>
                    <span>{{ $isMalay ? 'Konfigurasi Emel SMTP Syarikat (Dedicated SMTP Gateway)' : 'Company SMTP Email Gateway' }}</span>
                </h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                    {{ $isMalay ? 'Setiap syarikat mempunyai pelayan SMTP tersendiri untuk penghantaran invois, sebutharga & notifikasi rasmi. Kata laluan disulitkan penuh (AES-256) dalam pangkalan data demi keselamatan mutlak.' : 'Each company configures its own SMTP server for sending invoices, quotations & notifications. Credentials are fully encrypted (AES-256) in the database.' }}
                </p>
            </div>
            <div class="flex items-center gap-2 flex-shrink-0">
                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                    🛡️ {{ $isMalay ? 'Disulitkan Selamat (AES-256)' : 'AES-256 Encrypted' }}
                </span>
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-bold border transition" :class="isActive ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-300 border-indigo-200 dark:border-indigo-800' : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400 border-slate-200 dark:border-slate-700'">
                    <span class="w-2 h-2 rounded-full" :class="isActive ? 'bg-indigo-600 animate-pulse' : 'bg-slate-400'"></span>
                    <span x-text="isActive ? '{{ $isMalay ? 'SMTP Aktif' : 'SMTP Active' }}' : '{{ $isMalay ? 'SMTP Nyahaktif' : 'SMTP Inactive' }}'">
                        {{ ($smtp && $smtp->is_active) ? ($isMalay ? 'SMTP Aktif' : 'SMTP Active') : ($isMalay ? 'SMTP Nyahaktif' : 'SMTP Inactive') }}
                    </span>
                </span>
            </div>
        </div>

        <!-- Security Notice Alert -->
        <div class="p-3.5 rounded-xl bg-amber-50/70 dark:bg-amber-950/30 border border-amber-200/80 dark:border-amber-900/40 text-xs text-amber-900 dark:text-amber-300 flex items-start gap-2.5">
            <span class="text-base flex-shrink-0">🔐</span>
            <div>
                <strong class="font-bold">{{ $isMalay ? 'Jaminan Keselamatan Pangkalan Data:' : 'Database Security Guarantee:' }}</strong>
                <span>{{ $isMalay ? 'Kata laluan SMTP disimpan dalam format ciphertext disulitkan (AES-256 dengan kunci rahsia unik aplikasi). Sekiranya pangkalan data dicuri atau digodam secara terus, kata laluan asal tidak boleh dibaca atau disalahgunakan sama sekali.' : 'SMTP passwords are stored as encrypted ciphertext (AES-256 with HMAC). Even in the event of an unauthorized database dump, credentials cannot be decrypted or stolen.' }}</span>
            </div>
        </div>

        <form method="POST" action="{{ route('settings.smtp') }}" class="space-y-4">
            @csrf

            <!-- Toggle Aktifkan SMTP -->
            <div class="flex items-center justify-between p-3.5 rounded-xl bg-slate-50 dark:bg-slate-800/40 border border-slate-200 dark:border-slate-700/80">
                <div>
                    <label for="is_active_smtp" class="text-xs font-bold text-slate-800 dark:text-slate-200 cursor-pointer">
                        {{ $isMalay ? 'Gunakan Pelayan SMTP Khas Syarikat Ini' : 'Enable Dedicated SMTP Gateway for this Organization' }}
                    </label>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400">
                        {{ $isMalay ? 'Apabila diaktifkan, semua invois dan sebutharga pelanggan dihantar terus melalui pelayan emel syarikat anda.' : 'When enabled, all customer invoices and quotations will be sent directly through your company email server.' }}
                    </p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer flex-shrink-0">
                    <input type="checkbox" id="is_active_smtp" name="is_active" value="1" x-model="isActive" {{ old('is_active', $smtp->is_active ?? false) ? 'checked' : '' }} class="sr-only peer">
                    <div class="w-11 h-6 bg-slate-200 peer-focus:outline-hidden rounded-full peer dark:bg-slate-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-slate-600 peer-checked:bg-indigo-600"></div>
                </label>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <!-- Mail Host -->
                <div class="sm:col-span-2">
                    <label for="mail_host" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                        {{ $isMalay ? 'Hos Pelayan SMTP (Mail Host)' : 'SMTP Mail Host' }} <span class="text-slate-400 font-normal">(cth: smtp.gmail.com, mail.syarikat.com)</span>
                    </label>
                    <input type="text" id="mail_host" name="mail_host" x-model="form.mail_host" value="{{ old('mail_host', $smtp->mail_host ?? '') }}" placeholder="smtp.domainanda.com" class="w-full px-3.5 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
                </div>

                <!-- Mail Port -->
                <div>
                    <label for="mail_port" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                        {{ $isMalay ? 'Port SMTP' : 'SMTP Port' }}
                    </label>
                    <input type="number" id="mail_port" name="mail_port" x-model="form.mail_port" value="{{ old('mail_port', $smtp->mail_port ?? 587) }}" placeholder="587" class="w-full px-3.5 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
                    <span class="text-[10px] text-slate-400 mt-0.5 block">Standard: 587 (TLS), 465 (SSL)</span>
                </div>

                <!-- Mail Encryption -->
                <div>
                    <label for="mail_encryption" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                        {{ $isMalay ? 'Penyulitan Sambungan' : 'Encryption' }}
                    </label>
                    <select id="mail_encryption" name="mail_encryption" x-model="form.mail_encryption" class="w-full px-3.5 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
                        <option value="tls" {{ old('mail_encryption', $smtp->mail_encryption ?? 'tls') === 'tls' ? 'selected' : '' }}>TLS (Disyorkan / Port 587)</option>
                        <option value="ssl" {{ old('mail_encryption', $smtp->mail_encryption ?? '') === 'ssl' ? 'selected' : '' }}>SSL (Port 465)</option>
                        <option value="none" {{ old('mail_encryption', $smtp->mail_encryption ?? '') === 'none' ? 'selected' : '' }}>None (Tiada Penyulitan)</option>
                    </select>
                </div>

                <!-- Mail Username -->
                <div class="sm:col-span-2">
                    <label for="mail_username" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                        {{ $isMalay ? 'Nama Pengguna SMTP / Emel Log Masuk' : 'SMTP Username / Login Email' }}
                    </label>
                    <input type="text" id="mail_username" name="mail_username" x-model="form.mail_username" value="{{ old('mail_username', $smtp->mail_username ?? '') }}" placeholder="billing@syarikatanda.com" class="w-full px-3.5 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500" autocomplete="off">
                </div>

                <!-- Mail Password (Encrypted at rest) -->
                <div class="sm:col-span-3">
                    <label for="mail_password" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                        {{ $isMalay ? 'Kata Laluan SMTP / App Password' : 'SMTP Password / App Password' }}
                        @if($smtp && $smtp->hasPassword())
                        <span class="ml-2 font-normal text-emerald-600 dark:text-emerald-400 text-[11px]">
                            ✓ {{ $isMalay ? 'Kata laluan semasa disimpan disulitkan (Tinggalkan kosong jika tidak ingin menukar)' : 'Current password stored encrypted (Leave empty to keep current)' }}
                        </span>
                        @endif
                    </label>
                    <div class="relative">
                        <input :type="showPassword ? 'text' : 'password'" id="mail_password" name="mail_password" x-model="form.mail_password" placeholder="{{ ($smtp && $smtp->hasPassword()) ? '••••••••••••••••' : ($isMalay ? 'Masukkan kata laluan pelayan SMTP' : 'Enter SMTP password') }}" class="w-full px-3.5 py-2 pr-10 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500" autocomplete="new-password">
                        <button type="button" @click="showPassword = !showPassword" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition" tabindex="-1">
                            <span x-text="showPassword ? '🙈' : '👁️'" class="text-xs"></span>
                        </button>
                    </div>
                </div>

                <!-- Mail From Address -->
                <div class="sm:col-span-2">
                    <label for="mail_from_address" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                        {{ $isMalay ? 'Alamat Emel Penghantar (From Address)' : 'Sender Email Address (From Address)' }}
                    </label>
                    <input type="email" id="mail_from_address" name="mail_from_address" x-model="form.mail_from_address" value="{{ old('mail_from_address', $smtp->mail_from_address ?? '') }}" placeholder="invois@syarikatanda.com" class="w-full px-3.5 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
                    <span class="text-[10px] text-slate-400 mt-0.5 block">{{ $isMalay ? 'Alamat yang akan dilihat oleh pelanggan dalam inbox mereka.' : 'Address seen by customers in their email inbox.' }}</span>
                </div>

                <!-- Mail From Name -->
                <div>
                    <label for="mail_from_name" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                        {{ $isMalay ? 'Nama Penghantar (From Name)' : 'Sender Name (From Name)' }}
                    </label>
                    <input type="text" id="mail_from_name" name="mail_from_name" x-model="form.mail_from_name" value="{{ old('mail_from_name', $smtp->mail_from_name ?? $org->name ?? '') }}" placeholder="{{ $org->name ?? 'Nama Syarikat' }}" class="w-full px-3.5 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>

            <!-- Action Buttons & Test Connection -->
            <div class="pt-3 border-t border-slate-100 dark:border-slate-800/80 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <!-- Test Email Section -->
                <div class="flex items-center gap-2">
                    <input type="email" x-model="testEmail" placeholder="{{ $user->email ?? 'emel@anda.com' }}" class="px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500 w-48 sm:w-56" :disabled="isTesting">
                    <button type="button" @click="testSmtpConnection()" :disabled="isTesting" class="px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-bold transition flex items-center gap-1.5 cursor-pointer disabled:opacity-50">
                        <span x-show="!isTesting">🧪</span>
                        <svg x-show="isTesting" class="animate-spin w-3.5 h-3.5 text-indigo-600" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span x-text="isTesting ? '{{ $isMalay ? 'Sedang Menguji...' : 'Testing...' }}' : '{{ $isMalay ? 'Uji Sambungan Emel' : 'Test Connection' }}'">
                            {{ $isMalay ? 'Uji Sambungan Emel' : 'Test Connection' }}
                        </span>
                    </button>
                </div>

                <button type="submit" class="px-5 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 dark:bg-indigo-600 dark:hover:bg-indigo-700 text-white text-xs font-bold shadow-sm transition flex items-center justify-center gap-2 cursor-pointer">
                    <span>💾</span>
                    <span>{{ $isMalay ? 'Simpan Konfigurasi SMTP' : 'Save SMTP Settings' }}</span>
                </button>
            </div>

            <!-- Test Feedback Alert -->
            <div x-show="testResult.message" x-transition class="p-3 rounded-xl text-xs font-medium flex items-center gap-2" :class="testResult.success ? 'bg-emerald-50 text-emerald-800 dark:bg-emerald-950/40 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800' : 'bg-rose-50 text-rose-800 dark:bg-rose-950/40 dark:text-rose-300 border border-rose-200 dark:border-rose-800'">
                <span x-text="testResult.success ? '✓' : '⚠️'"></span>
                <span x-text="testResult.message"></span>
            </div>
        </form>
    </div>
    @endif

    <!-- 5. Keselamatan & Penukaran Kata Laluan (Security & Password Change Card) -->
    <div class="bg-white dark:bg-[#101726] border border-slate-200 dark:border-[#1F293D] rounded-2xl p-6 shadow-sm space-y-5" x-data="{ showCurrent: false, showNew: false, showConfirm: false }">
        <div class="border-b border-slate-100 dark:border-slate-800/80 pb-4 flex items-center justify-between">
            <div>
                <h2 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                    <span>🔒</span>
                    <span>{{ $isMalay ? 'Keselamatan & Tukar Kata Laluan' : 'Security & Change Password' }}</span>
                </h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                    {{ $isMalay ? 'Demi keselamatan akaun, anda diwajibkan memasukkan kata laluan asal untuk menetapkan kata laluan baharu.' : 'For account security, your current password is required before setting a new password.' }}
                </p>
            </div>
            <span class="px-2.5 py-1 rounded-full text-[11px] font-semibold bg-rose-50 text-rose-700 dark:bg-rose-950/60 dark:text-rose-300 border border-rose-200/60 dark:border-rose-800/60 flex items-center gap-1.5">
                <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                <span>{{ $isMalay ? 'Perlu Kata Laluan Asal' : 'Current Password Required' }}</span>
            </span>
        </div>

        <form method="POST" action="{{ route('settings.password') }}" class="space-y-4">
            @csrf

            <!-- Kata Laluan Asal -->
            <div class="max-w-md">
                <label for="current_password" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                    {{ $isMalay ? 'Kata Laluan Asal (Semasa)' : 'Current Password' }} <span class="text-rose-500">*</span>
                </label>
                <div class="relative">
                    <input :type="showCurrent ? 'text' : 'password'" id="current_password" name="current_password" required autocomplete="current-password" placeholder="{{ $isMalay ? 'Masukkan kata laluan semasa anda' : 'Enter current password' }}" class="w-full px-3.5 py-2 pr-10 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500 @error('current_password') border-rose-500 ring-rose-500 @enderror">
                    <button type="button" @click="showCurrent = !showCurrent" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition" tabindex="-1">
                        <span x-text="showCurrent ? '🙈' : '👁️'" class="text-xs"></span>
                    </button>
                </div>
                @error('current_password')
                    <p class="text-rose-600 dark:text-rose-400 text-[11px] font-medium mt-1">{{ $message }}</p>
                @enderror
                <p class="text-[11px] text-slate-400 mt-1">
                    {{ $isMalay ? 'Sahkan identiti anda menggunakan kata laluan sedia ada.' : 'Verify your identity using your existing password.' }}
                </p>
            </div>

            <!-- Kata Laluan Baharu & Pengesahan -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 max-w-2xl pt-2 border-t border-slate-100 dark:border-slate-800/60">
                <div>
                    <label for="password" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                        {{ $isMalay ? 'Kata Laluan Baharu' : 'New Password' }} <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <input :type="showNew ? 'text' : 'password'" id="password" name="password" required autocomplete="new-password" placeholder="{{ $isMalay ? 'Sekurang-kurangnya 8 aksara' : 'Minimum 8 characters' }}" class="w-full px-3.5 py-2 pr-10 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500 @error('password') border-rose-500 ring-rose-500 @enderror">
                        <button type="button" @click="showNew = !showNew" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition" tabindex="-1">
                            <span x-text="showNew ? '🙈' : '👁️'" class="text-xs"></span>
                        </button>
                    </div>
                    @error('password')
                        <p class="text-rose-600 dark:text-rose-400 text-[11px] font-medium mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password_confirmation" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                        {{ $isMalay ? 'Sahkan Kata Laluan Baharu' : 'Confirm New Password' }} <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <input :type="showConfirm ? 'text' : 'password'" id="password_confirmation" name="password_confirmation" required autocomplete="new-password" placeholder="{{ $isMalay ? 'Ulang kata laluan baharu' : 'Repeat new password' }}" class="w-full px-3.5 py-2 pr-10 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
                        <button type="button" @click="showConfirm = !showConfirm" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition" tabindex="-1">
                            <span x-text="showConfirm ? '🙈' : '👁️'" class="text-xs"></span>
                        </button>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-between pt-3">
                <div class="text-[11px] text-slate-400 flex items-center gap-1.5">
                    <span>🛡️</span>
                    <span>{{ $isMalay ? 'Disulitkan & disimpan menggunakan standard hash bcrypt selamat.' : 'Encrypted & stored securely using bcrypt hashing standard.' }}</span>
                </div>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 dark:bg-indigo-600 dark:hover:bg-indigo-700 text-white text-xs font-bold shadow-sm transition flex items-center gap-2">
                    <span>🔑</span>
                    <span>{{ $isMalay ? 'Tukar Kata Laluan' : 'Update Password' }}</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function companyLogoUploader() {
    return {
        isUploading: false,
        logoUrl: @json($org->logo_url ?? ''),
        uploadMessage: '',
        isError: false,
        async uploadLogo(e) {
            const input = e.target;
            const file = input.files && input.files[0];
            if (!file) return;

            // 1. Semak had saiz maksimum 2MB
            if (file.size > 2 * 1024 * 1024) {
                this.isError = true;
                this.uploadMessage = '{{ $isMalay ? 'Ralat: Saiz fail logo melebihi had maksimum 2MB. Sila pilih fail yang lebih kecil.' : 'Error: Logo file exceeds 2MB limit. Please choose a smaller file.' }}';
                input.value = '';
                return;
            }

            // 2. Semak format dibenarkan (MIME & extension)
            const allowedTypes = ['image/png', 'image/jpeg', 'image/jpg', 'image/webp', 'image/svg+xml', 'image/svg'];
            const ext = file.name ? file.name.split('.').pop().toLowerCase() : '';
            const allowedExts = ['png', 'jpg', 'jpeg', 'webp', 'svg'];
            if (!allowedTypes.includes(file.type) && !allowedExts.includes(ext)) {
                this.isError = true;
                this.uploadMessage = '{{ $isMalay ? 'Ralat: Format gambar tidak disokong. Sila gunakan PNG, JPG, WebP atau SVG.' : 'Error: Unsupported format. Please use PNG, JPG, WebP, or SVG.' }}';
                input.value = '';
                return;
            }

            this.isUploading = true;
            this.uploadMessage = '{{ $isMalay ? 'Sedang memuat naik & menyimpan terus...' : 'Uploading and saving...' }}';
            this.isError = false;

            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
            const formData = new FormData();
            formData.append('logo', file);
            formData.append('_token', csrfToken);

            try {
                const res = await fetch('{{ route('settings.logo', [], false) }}', {
                    method: 'POST',
                    body: formData,
                    credentials: 'same-origin',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrfToken
                    }
                });

                let data = null;
                const contentType = res.headers.get('content-type') || '';
                if (contentType.includes('application/json')) {
                    try {
                        data = await res.json();
                    } catch (_) {}
                }

                if (res.ok && data && data.success) {
                    this.logoUrl = data.logo_url;
                    this.isError = false;
                    this.uploadMessage = '{{ $isMalay ? '✓ Logo berjaya dimuat naik & disimpan terus!' : '✓ Logo successfully uploaded and saved!' }}';
                    input.value = '';
                } else {
                    this.isError = true;
                    let errMsg = '';
                    if (data) {
                        if (data.message) {
                            errMsg = data.message;
                        } else if (data.error && data.error.details && data.error.details.logo) {
                            errMsg = Array.isArray(data.error.details.logo) ? data.error.details.logo[0] : data.error.details.logo;
                        } else if (data.error && data.error.message) {
                            errMsg = data.error.message;
                        } else if (data.errors && data.errors.logo) {
                            errMsg = Array.isArray(data.errors.logo) ? data.errors.logo[0] : data.errors.logo;
                        }
                    }

                    if (!errMsg) {
                        if (res.status === 419) {
                            errMsg = '{{ $isMalay ? 'Sesi anda telah tamat tempoh. Sila muat semula (refresh) halaman dan cuba lagi.' : 'Session expired. Please refresh the page and try again.' }}';
                        } else if (res.status === 413) {
                            errMsg = '{{ $isMalay ? 'Fail terlalu besar untuk diproses oleh pelayan (Had PHP: 2MB).' : 'File too large for the server.' }}';
                        } else if (res.status === 403) {
                            errMsg = '{{ $isMalay ? 'Anda tiada akses kepada organisasi yang dipilih.' : 'You do not have access to this organization.' }}';
                        } else {
                            errMsg = '{{ $isMalay ? 'Ralat pelayan semasa memuat naik logo (Status ' : 'Server error while uploading logo (Status ' }}' + res.status + ')';
                        }
                    }

                    this.uploadMessage = errMsg;
                }
            } catch (err) {
                console.error('Logo upload error:', err);
                this.isError = true;
                this.uploadMessage = '{{ $isMalay ? 'Ralat sambungan: ' : 'Connection error: ' }}' + (err.message || 'Sila periksa sambungan rangkaian anda.');
            } finally {
                this.isUploading = false;
            }
        },
        async removeLogo() {
            if (!confirm('{{ $isMalay ? 'Adakah anda pasti mahu memadam logo syarikat?' : 'Are you sure you want to remove the company logo?' }}')) return;

            this.isUploading = true;
            this.uploadMessage = '{{ $isMalay ? 'Memadam logo...' : 'Removing logo...' }}';
            this.isError = false;

            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
            const formData = new FormData();
            formData.append('_token', csrfToken);

            try {
                const res = await fetch('{{ route('settings.logo.remove', [], false) }}', {
                    method: 'POST',
                    body: formData,
                    credentials: 'same-origin',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrfToken
                    }
                });

                let data = null;
                const contentType = res.headers.get('content-type') || '';
                if (contentType.includes('application/json')) {
                    try {
                        data = await res.json();
                    } catch (_) {}
                }

                if (res.ok && data && data.success) {
                    this.logoUrl = '';
                    this.isError = false;
                    this.uploadMessage = '{{ $isMalay ? '✓ Logo syarikat telah berjaya dipadam.' : '✓ Company logo removed.' }}';
                } else {
                    this.isError = true;
                    this.uploadMessage = (data && data.message) ? data.message : '{{ $isMalay ? 'Gagal memadam logo (Status ' : 'Failed to remove logo (Status ' }}' + res.status + ')';
                }
            } catch (err) {
                console.error('Logo remove error:', err);
                this.isError = true;
                this.uploadMessage = '{{ $isMalay ? 'Ralat sambungan semasa memadam logo: ' : 'Connection error while removing logo: ' }}' + (err.message || 'Sila periksa sambungan.');
            } finally {
                this.isUploading = false;
            }
        }
    };
}

function companySmtpComponent() {
    return {
        isActive: {{ ($smtp && $smtp->is_active) ? 'true' : 'false' }},
        showPassword: false,
        isTesting: false,
        testEmail: @json($user->email ?? ''),
        testResult: {
            success: false,
            message: ''
        },
        form: {
            mail_host: @json($smtp->mail_host ?? ''),
            mail_port: @json($smtp->mail_port ?? 587),
            mail_encryption: @json($smtp->mail_encryption ?? 'tls'),
            mail_username: @json($smtp->mail_username ?? ''),
            mail_password: '',
            mail_from_address: @json($smtp->mail_from_address ?? ''),
            mail_from_name: @json($smtp->mail_from_name ?? ($org->name ?? '')),
        },
        async testSmtpConnection() {
            if (!this.testEmail) {
                this.testResult = {
                    success: false,
                    message: '{{ $isMalay ? 'Sila masukkan alamat emel penerima untuk ujian.' : 'Please enter a test recipient email address.' }}'
                };
                return;
            }

            this.isTesting = true;
            this.testResult = { success: false, message: '' };

            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';

            try {
                const res = await fetch('{{ route('settings.smtp.test', [], false) }}', {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({
                        test_email: this.testEmail,
                        mail_host: this.form.mail_host,
                        mail_port: this.form.mail_port,
                        mail_encryption: this.form.mail_encryption,
                        mail_username: this.form.mail_username,
                        mail_password: this.form.mail_password,
                        mail_from_address: this.form.mail_from_address,
                        mail_from_name: this.form.mail_from_name,
                    })
                });

                const data = await res.json().catch(() => ({}));

                if (res.ok && data.success) {
                    this.testResult = {
                        success: true,
                        message: data.message || '{{ $isMalay ? '✓ Sambungan SMTP sah & emel ujian berjaya dihantar!' : '✓ SMTP connection verified & test email sent!' }}'
                    };
                } else {
                    let errMsg = data.message || (data.error && data.error.message);
                    if (!errMsg && data.errors) {
                        errMsg = Object.values(data.errors).flat()[0];
                    }
                    this.testResult = {
                        success: false,
                        message: errMsg || '{{ $isMalay ? 'Gagal menyambung ke pelayan SMTP.' : 'Failed to connect to SMTP server.' }}'
                    };
                }
            } catch (err) {
                this.testResult = {
                    success: false,
                    message: '{{ $isMalay ? 'Ralat rangkaian semasa menguji SMTP: ' : 'Network error testing SMTP: ' }}' + (err.message || 'Sila semak semula sambungan.')
                };
            } finally {
                this.isTesting = false;
            }
        }
    };
}
</script>
@endsection
