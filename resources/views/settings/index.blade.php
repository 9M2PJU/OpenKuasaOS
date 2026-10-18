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
                {{ $isMalay ? 'Tetapkan bahasa standard bagi antaramuka OpenKuasa. Bahasa Melayu adalah bahasa lalai sistem.' : 'Select the default language for the OpenKuasa interface. Bahasa Melayu is set as default.' }}
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
                    {{ $isMalay ? 'Pilih mod paparan antaramuka OpenKuasa mengikut keselesaan anda. Mod Cerah adalah tema lalai sistem.' : 'Select your preferred OpenKuasa interface mode. Light Mode is set as the system default.' }}
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
                @endif
            </div>

            <div class="flex items-center justify-end pt-2">
                <button type="submit" class="px-5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 text-xs font-semibold text-slate-700 dark:text-slate-200 transition">
                    {{ $isMalay ? 'Kemaskini Profil & Syarikat' : 'Update Profile & Organization' }}
                </button>
            </div>
        </form>
    </div>

    <!-- 3. Keselamatan & Penukaran Kata Laluan (Security & Password Change Card) -->
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
@endsection
