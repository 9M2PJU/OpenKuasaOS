<!DOCTYPE html>
<html lang="ms" class="h-full bg-slate-50 dark:bg-slate-950 text-slate-900 dark:text-slate-100 antialiased">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'OpenKuasa' }} — Sistem Operasi Perniagaan Bersepadu</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="h-full flex overflow-hidden" x-data="{
    collapsed: localStorage.getItem('openkuasa_sidebar_collapsed') === 'true',
    toggleSidebar() {
        this.collapsed = !this.collapsed;
        localStorage.setItem('openkuasa_sidebar_collapsed', this.collapsed ? 'true' : 'false');
    }
}">
    <!-- Sidebar -->
    <aside :class="collapsed ? 'w-20' : 'w-64'" class="flex-shrink-0 bg-white dark:bg-slate-900 border-r border-slate-200 dark:border-slate-800 flex flex-col justify-between transition-all duration-200 ease-in-out">
        <div>
            <!-- Logo & Brand Header with Toggle Collapse -->
            <div class="h-16 flex items-center border-b border-slate-200 dark:border-slate-800 transition-all duration-200" :class="collapsed ? 'justify-center px-2' : 'justify-between px-4'">
                <div class="flex items-center gap-3 overflow-hidden">
                    <button @click="toggleSidebar()" type="button" class="w-9 h-9 flex-shrink-0 rounded-xl overflow-hidden flex items-center justify-center shadow-sm hover:scale-105 transition cursor-pointer" :title="collapsed ? 'Klik untuk besarkan menu' : 'OpenKuasa — Klik untuk kecilkan menu'">
                        <img src="{{ asset('images/logo.png') }}" alt="Logo" class="w-full h-full object-cover">
                    </button>
                    <div x-show="!collapsed" class="transition-all duration-150 overflow-hidden whitespace-nowrap">
                        <h1 class="font-bold text-base leading-tight tracking-tight">Open<span class="text-indigo-600 dark:text-indigo-400">Kuasa</span></h1>
                        <span class="text-[10px] uppercase tracking-wider text-slate-500 font-semibold">Business Platform</span>
                    </div>
                </div>
                <button x-show="!collapsed" @click="toggleSidebar()" type="button" class="p-1.5 rounded-lg text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition flex-shrink-0" title="Kecilkan Menu (Ikon Sahaja)">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7" />
                    </svg>
                </button>
            </div>

            <!-- Organization Switcher -->
            <div class="border-b border-slate-100 dark:border-slate-800/60 transition-all duration-200" :class="collapsed ? 'p-2' : 'p-3'" x-data="{ open: false }">
                <div class="relative">
                    <button @click="open = !open" type="button" :title="collapsed ? ('Organisasi: ' + '{{ addslashes($currentOrg->name ?? 'Perniagaan') }}') : ''" class="w-full flex items-center rounded-lg border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800/80 transition text-left text-xs font-medium" :class="collapsed ? 'justify-center p-2' : 'justify-between p-2.5'">
                        <div class="truncate flex items-center gap-2" :class="collapsed ? 'justify-center' : ''">
                            <span class="w-6 h-6 rounded-md bg-indigo-50 dark:bg-indigo-950/80 text-indigo-700 dark:text-indigo-300 flex items-center justify-center text-[10px] font-bold flex-shrink-0">
                                {{ strtoupper(substr($currentOrg->name ?? 'P', 0, 1)) }}
                            </span>
                            <div x-show="!collapsed" class="truncate">
                                <span class="block text-[10px] text-slate-600 dark:text-slate-400 uppercase font-semibold">Organisasi Semasa</span>
                                <span class="block font-semibold text-slate-800 dark:text-slate-200 truncate">{{ $currentOrg->name ?? 'Perniagaan Saya' }}</span>
                            </div>
                        </div>
                        <svg x-show="!collapsed" class="w-4 h-4 text-slate-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    @if(isset($userOrgs) && count($userOrgs) > 1)
                    <div x-show="open" @click.away="open = false" class="absolute left-0 mt-1.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg shadow-lg py-1 z-50 text-xs" :class="collapsed ? 'w-48 left-full ml-2 top-0' : 'right-0'">
                        @foreach($userOrgs as $org)
                        <form method="POST" action="{{ route('org.switch', $org->id) }}">
                            @csrf
                            <button type="submit" class="w-full text-left px-3 py-2 hover:bg-slate-100 dark:hover:bg-slate-700/60 flex items-center justify-between {{ ($currentOrg->id ?? '') === $org->id ? 'font-bold text-indigo-600 dark:text-indigo-400' : '' }}">
                                <span class="truncate">{{ $org->name }}</span>
                                @if(($currentOrg->id ?? '') === $org->id)
                                <span class="text-xs">✓</span>
                                @endif
                            </button>
                        </form>
                        @endforeach
                    </div>
                    @endif
                </div>
            </div>

            <!-- Navigation Links -->
            <nav class="space-y-1 text-sm font-medium transition-all duration-200" :class="collapsed ? 'p-2' : 'p-3'">
                <!-- Dashboard Utama -->
                <a href="{{ route('dashboard') }}" title="Dashboard Utama" class="flex items-center rounded-lg transition {{ request()->routeIs('dashboard') ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-400 font-semibold' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/60' }}" :class="collapsed ? 'justify-center p-2.5' : 'gap-3 px-3 py-2'">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                    <span x-show="!collapsed" class="truncate">Dashboard Utama</span>
                </a>

                <!-- Tuah (AI Command) -->
                <a href="{{ route('command.index') }}" title="Tuah (Pusat Kawalan AI)" class="flex items-center rounded-lg transition {{ request()->is('command*') ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-400 font-semibold' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/60' }}" :class="collapsed ? 'justify-center p-2.5' : 'gap-3 px-3 py-2'">
                    <svg class="w-5 h-5 flex-shrink-0 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    <div x-show="!collapsed" class="flex-1 flex items-center justify-between truncate">
                        <span class="truncate">Tuah (AI Command)</span>
                        <span class="text-[10px] px-1.5 py-0.5 rounded bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300 font-bold ml-1">AI</span>
                    </div>
                </a>

                <!-- Section Divider: Modul Perniagaan -->
                <div class="pt-2 pb-1" :class="collapsed ? 'px-1' : 'px-3'">
                    <span x-show="!collapsed" class="text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Modul Perniagaan</span>
                    <div x-show="collapsed" class="border-t border-slate-200 dark:border-slate-800 my-1"></div>
                </div>

                <!-- 1. Jebat (Iklan & Pemasaran / Reach) -->
                <a href="{{ route('reach.index') }}" title="Jebat (Iklan & Pemasaran AI)" class="flex items-center rounded-lg transition {{ request()->is('reach*') ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-400 font-semibold' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/60' }}" :class="collapsed ? 'justify-center p-2.5' : 'gap-3 px-3 py-2'">
                    <svg class="w-5 h-5 flex-shrink-0 text-pink-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/></svg>
                    <span x-show="!collapsed" class="truncate">Jebat (Iklan & Pemasaran)</span>
                </a>

                <!-- 2. Kasturi (CRM & Jualan / Pipeline) -->
                <a href="{{ route('crm.index') }}" title="Kasturi (CRM & Saluran Jualan)" class="flex items-center rounded-lg transition {{ request()->is('crm*') ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-400 font-semibold' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/60' }}" :class="collapsed ? 'justify-center p-2.5' : 'gap-3 px-3 py-2'">
                    <svg class="w-5 h-5 flex-shrink-0 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                    <span x-show="!collapsed" class="truncate">Kasturi (CRM & Jualan)</span>
                </a>

                <!-- 3. Lekiu (HR & Gaji / People) -->
                <a href="{{ route('people.index') }}" title="Lekiu (HR, Kehadiran & Gaji)" class="flex items-center rounded-lg transition {{ request()->is('people*') ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-400 font-semibold' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/60' }}" :class="collapsed ? 'justify-center p-2.5' : 'gap-3 px-3 py-2'">
                    <svg class="w-5 h-5 flex-shrink-0 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    <span x-show="!collapsed" class="truncate">Lekiu (HR & Gaji)</span>
                </a>

                <!-- 4. Lekir (Pengambilan Staf / Hire) -->
                <a href="{{ route('hire.index') }}" title="Lekir (Pengambilan Staf & Temu Duga)" class="flex items-center rounded-lg transition {{ request()->is('hire*') ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-400 font-semibold' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/60' }}" :class="collapsed ? 'justify-center p-2.5' : 'gap-3 px-3 py-2'">
                    <svg class="w-5 h-5 flex-shrink-0 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    <span x-show="!collapsed" class="truncate">Lekir (Pengambilan Staf)</span>
                </a>

                <!-- 5. Bendahara (Kewangan, Perakaunan & Percukaian) -->
                <a href="{{ route('finance.index') }}" title="Bendahara (Kewangan, Perakaunan & Cukai)" class="flex items-center rounded-lg transition {{ request()->is('finance*') ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-400 font-semibold' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/60' }}" :class="collapsed ? 'justify-center p-2.5' : 'gap-3 px-3 py-2'">
                    <svg class="w-5 h-5 flex-shrink-0 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span x-show="!collapsed" class="truncate">{{ app()->getLocale() === 'en' ? 'Bendahara (Finance)' : 'Bendahara (Kewangan)' }}</span>
                </a>

                <!-- 6. Tetapan Sistem & Bahasa -->
                <a href="{{ route('settings.index') }}" title="{{ app()->getLocale() === 'en' ? 'Settings & Language' : 'Tetapan & Bahasa' }}" class="flex items-center rounded-lg transition {{ request()->is('settings*') ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-400 font-semibold' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/60' }}" :class="collapsed ? 'justify-center p-2.5' : 'gap-3 px-3 py-2'">
                    <svg class="w-5 h-5 flex-shrink-0 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    <span x-show="!collapsed" class="truncate">{{ app()->getLocale() === 'en' ? 'Settings' : 'Tetapan' }}</span>
                </a>

                @if(Auth::check() && Auth::user()->isSuperadmin())
                <!-- Section Divider: Pentadbiran Utama (Self-Hosted Master Console) -->
                <div class="pt-3 pb-1" :class="collapsed ? 'px-1' : 'px-3'">
                    <div class="flex items-center gap-1.5" x-show="!collapsed">
                        <svg class="w-3.5 h-3.5 text-amber-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 1.944A11.954 11.954 0 012.166 5C2.056 5.649 2 6.319 2 7c0 5.225 3.34 9.67 8 11.317C14.66 16.67 18 12.225 18 7c0-.682-.057-1.35-.166-2.001A11.954 11.954 0 0110 1.944zM11 14a1 1 0 11-2 0 1 1 0 012 0zm0-7a1 1 0 10-2 0v3a1 1 0 102 0V7z" clip-rule="evenodd" />
                        </svg>
                        <span class="text-[11px] font-bold uppercase tracking-wider text-amber-600 dark:text-amber-400">Pentadbiran Utama</span>
                    </div>
                    <div x-show="collapsed" class="border-t border-amber-300 dark:border-amber-700/60 my-1"></div>
                </div>

                <!-- Pusat Kawalan Superadmin -->
                <a href="{{ route('superadmin.dashboard') }}" title="Pusat Kawalan Superadmin" class="flex items-center rounded-lg transition {{ request()->routeIs('superadmin.dashboard') ? 'bg-amber-50 text-amber-900 dark:bg-amber-950/60 dark:text-amber-300 font-semibold' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/60' }}" :class="collapsed ? 'justify-center p-2.5' : 'gap-3 px-3 py-2'">
                    <svg class="w-5 h-5 flex-shrink-0 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                    <div x-show="!collapsed" class="flex-1 flex items-center justify-between truncate">
                        <span class="truncate">Pusat Kawalan Superadmin</span>
                        <span class="text-[9px] px-1.5 py-0.5 rounded bg-amber-100 text-amber-800 dark:bg-amber-900/60 dark:text-amber-300 font-bold ml-1">MASTER</span>
                    </div>
                </a>

                <!-- Pengurusan Pengguna -->
                <a href="{{ route('superadmin.users.index') }}" title="Pengurusan Pengguna" class="flex items-center rounded-lg transition {{ request()->routeIs('superadmin.users.*') ? 'bg-amber-50 text-amber-900 dark:bg-amber-950/60 dark:text-amber-300 font-semibold' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/60' }}" :class="collapsed ? 'justify-center p-2.5' : 'gap-3 px-3 py-2'">
                    <svg class="w-5 h-5 flex-shrink-0 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    <span x-show="!collapsed" class="truncate">Pengurusan Pengguna</span>
                </a>

                <!-- Pengurusan Organisasi -->
                <a href="{{ route('superadmin.organizations.index') }}" title="Pengurusan Organisasi" class="flex items-center rounded-lg transition {{ request()->routeIs('superadmin.organizations.*') ? 'bg-amber-50 text-amber-900 dark:bg-amber-950/60 dark:text-amber-300 font-semibold' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/60' }}" :class="collapsed ? 'justify-center p-2.5' : 'gap-3 px-3 py-2'">
                    <svg class="w-5 h-5 flex-shrink-0 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                    <span x-show="!collapsed" class="truncate">Pengurusan Organisasi</span>
                </a>

                <!-- Kesihatan Pelayan -->
                <a href="{{ route('superadmin.system.index') }}" title="Kesihatan Pelayan & Tetapan" class="flex items-center rounded-lg transition {{ request()->routeIs('superadmin.system.*') ? 'bg-amber-50 text-amber-900 dark:bg-amber-950/60 dark:text-amber-300 font-semibold' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/60' }}" :class="collapsed ? 'justify-center p-2.5' : 'gap-3 px-3 py-2'">
                    <svg class="w-5 h-5 flex-shrink-0 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01" />
                    </svg>
                    <span x-show="!collapsed" class="truncate">Kesihatan Pelayan</span>
                </a>
                @endif
            </nav>
        </div>

        <!-- User Profile, Expand Helper & Logout -->
        <div>
            <!-- Dedicated expand button when collapsed -->
            <div x-show="collapsed" class="px-2 pb-2">
                <button @click="toggleSidebar()" type="button" class="w-full flex items-center justify-center p-2 rounded-lg text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition" title="Besarkan Menu (Buka Penuh)">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7" />
                    </svg>
                </button>
            </div>

            <div class="border-t border-slate-200 dark:border-slate-800 transition-all duration-200" :class="collapsed ? 'p-2' : 'p-3'">
                <div class="flex items-center" :class="collapsed ? 'flex-col gap-2 justify-center' : 'justify-between'">
                    <div class="flex items-center gap-3 truncate" :class="collapsed ? 'justify-center' : ''">
                        <div class="w-8 h-8 rounded-full {{ Auth::user()->isSuperadmin() ? 'bg-amber-100 dark:bg-amber-950/80 text-amber-700 dark:text-amber-300 ring-2 ring-amber-500/50' : (Auth::user()->isDemo() ? 'bg-yellow-100 dark:bg-yellow-950/80 text-yellow-700 dark:text-yellow-300 ring-2 ring-yellow-500/50' : 'bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-200') }} flex items-center justify-center font-bold text-xs flex-shrink-0" :title="'{{ addslashes(Auth::user()->name ?? 'Pengguna') }}' + '{{ Auth::user()->isSuperadmin() ? ' [SUPERADMIN]' : (Auth::user()->isDemo() ? ' [DEMO MODE]' : '') }}'">
                            {{ strtoupper(substr(Auth::user()->name ?? 'U', 0, 1)) }}
                        </div>
                        <div x-show="!collapsed" class="truncate">
                            <div class="flex items-center gap-1.5 flex-wrap">
                                <span class="block text-xs font-semibold truncate">{{ Auth::user()->name ?? 'Pengguna' }}</span>
                                @if(Auth::user()->isSuperadmin())
                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[9px] font-extrabold bg-amber-100 text-amber-900 dark:bg-amber-950 dark:text-amber-300 border border-amber-300 dark:border-amber-700/60 tracking-wider">
                                    [SUPERADMIN]
                                </span>
                                @elseif(Auth::user()->isDemo())
                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[9px] font-extrabold bg-yellow-100 text-yellow-900 dark:bg-yellow-950 dark:text-yellow-300 border border-yellow-300 dark:border-yellow-700/60 tracking-wider">
                                    [DEMO MODE]
                                </span>
                                @endif
                            </div>
                            <span class="block text-[11px] text-slate-600 dark:text-slate-400 truncate">{{ Auth::user()->email ?? '' }}</span>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" title="Log Keluar" class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/40 transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </aside>

    <!-- Main Content Area -->
    <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
        <!-- 4.2 Banner Notifikasi Demo & Impersonasi -->
        @if(Auth::check() && Auth::user()->isDemo())
        <!-- Banner Kekal Akaun Demo -->
        <div class="bg-gradient-to-r from-amber-600 to-amber-700 text-white px-6 py-2.5 flex items-center justify-between text-xs font-medium shadow-sm z-30 flex-shrink-0">
            <div class="flex items-center gap-2.5">
                <span class="w-5 h-5 rounded-full bg-white/20 flex items-center justify-center text-xs">🧪</span>
                <span>Anda sedang menggunakan Akaun Demo (Ahmad Zulkifli / Rimba Ventures). Data di sini adalah simulasi.</span>
            </div>
            <span class="px-2 py-0.5 rounded bg-black/25 text-white font-bold text-[10px] uppercase tracking-wider hidden sm:inline-block">Simulasi Sahaja</span>
        </div>
        @endif

        @if((session()->has('impersonated_org_id') || !empty($isImpersonating)) && Auth::check() && Auth::user()->isSuperadmin())
        <!-- Banner Mod Bantuan Organisasi (Impersonation) -->
        <div class="bg-gradient-to-r from-orange-600 via-amber-600 to-rose-600 text-white px-6 py-3 flex items-center justify-between text-xs font-medium shadow-md z-30 flex-shrink-0">
            <div class="flex items-center gap-2.5">
                <span class="w-6 h-6 rounded-lg bg-black/20 flex items-center justify-center text-sm animate-pulse">🛠️</span>
                <div>
                    <span class="font-bold uppercase tracking-wider text-[10px] bg-black/30 px-2 py-0.5 rounded mr-2">Mod Bantuan Aktif</span>
                    <span>Anda kini sedang mengakses pandangan organisasi: <strong>{{ $currentOrg->name ?? session('impersonated_org_name', 'Organisasi Pilihan') }}</strong></span>
                </div>
            </div>
            <form method="POST" action="{{ route('superadmin.impersonate.leave') }}" class="inline">
                @csrf
                <button type="submit" class="px-3.5 py-1.5 bg-white text-orange-800 hover:bg-orange-50 font-bold rounded-lg shadow-sm transition flex items-center gap-1.5 cursor-pointer text-xs hover:scale-105 active:scale-95">
                    <svg class="w-4 h-4 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    Keluar Dari Mod Bantuan Organisasi
                </button>
            </form>
        </div>
        @endif

        <!-- Top Banner / Alert Notifications -->
        @if(session('success'))
        <div class="bg-emerald-50 dark:bg-emerald-950/60 border-b border-emerald-200 dark:border-emerald-800 px-6 py-2.5 flex items-center justify-between text-xs text-emerald-800 dark:text-emerald-200 font-medium">
            <div class="flex items-center gap-2">
                <span>✓</span>
                <span>{{ session('success') }}</span>
            </div>
        </div>
        @endif

        @if(session('error'))
        <div class="bg-rose-50 dark:bg-rose-950/60 border-b border-rose-200 dark:border-rose-800 px-6 py-2.5 flex items-center justify-between text-xs text-rose-800 dark:text-rose-200 font-medium">
            <div class="flex items-center gap-2">
                <span>⚠️</span>
                <span>{{ session('error') }}</span>
            </div>
        </div>
        @endif

        @if(session('info'))
        <div class="bg-blue-50 dark:bg-blue-950/60 border-b border-blue-200 dark:border-blue-800 px-6 py-2.5 flex items-center justify-between text-xs text-blue-800 dark:text-blue-200 font-medium">
            <div class="flex items-center gap-2">
                <span>ℹ️</span>
                <span>{{ session('info') }}</span>
            </div>
        </div>
        @endif

        <!-- Main Body -->
        <main class="flex-1 overflow-y-auto p-8">
            @yield('content')
        </main>
    </div>
</body>
</html>
