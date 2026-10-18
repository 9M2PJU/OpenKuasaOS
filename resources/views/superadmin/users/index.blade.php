@extends('layouts.app', ['title' => 'Pengurusan Pengguna — Pentadbiran Utama'])

@section('content')
<div class="space-y-6">
    <!-- Header with Breadcrumbs & Title -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-400">
                <a href="{{ route('superadmin.dashboard') }}" class="hover:text-slate-600 dark:hover:text-slate-200 transition">Pentadbiran Utama</a>
                <span>/</span>
                <span class="text-slate-700 dark:text-slate-200 font-medium">Pengguna</span>
            </div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white mt-1">
                Direktori & Pengurusan Pengguna
            </h1>
            <p class="text-xs text-slate-500 mt-0.5">
                Kawal selia senarai akaun, lantikan hak Superadmin, dan status penggantungan akaun pengguna sistem.
            </p>
        </div>
        <div>
            <span class="inline-flex items-center px-3 py-1.5 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800 text-xs font-semibold">
                Jumlah Rekod: {{ $users->total() }} Pengguna
            </span>
        </div>
    </div>

    <!-- 4.4: Bar Carian & Penapis (Search & Filter Bar) -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 shadow-sm space-y-4">
        <form method="GET" action="{{ route('superadmin.users.index') }}" class="flex flex-col md:flex-row gap-3">
            <!-- Search Field -->
            <div class="flex-1 relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
                <input type="text" name="search" value="{{ $search }}" placeholder="Cari mengikut nama pengguna atau emel..." class="w-full pl-9 pr-4 py-2 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition">
            </div>

            <!-- Keep Status Parameter in Form -->
            <input type="hidden" name="status" value="{{ $status }}">

            <!-- Actions -->
            <div class="flex items-center gap-2">
                <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-semibold shadow-sm transition">
                    Cari
                </button>
                @if($search || $status !== 'all')
                <a href="{{ route('superadmin.users.index') }}" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 rounded-xl text-xs font-medium transition">
                    Set Semula
                </a>
                @endif
            </div>
        </form>

        <!-- Status Filter Tabs -->
        <div class="flex items-center gap-2 overflow-x-auto pb-1 text-xs border-t border-slate-100 dark:border-slate-800 pt-3">
            <a href="{{ route('superadmin.users.index', array_merge(request()->query(), ['status' => 'all'])) }}" class="px-3 py-1.5 rounded-lg transition font-medium whitespace-nowrap {{ $status === 'all' ? 'bg-indigo-600 text-white font-semibold' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                Semua ({{ $counts['all'] ?? 0 }})
            </a>
            <a href="{{ route('superadmin.users.index', array_merge(request()->query(), ['status' => 'active'])) }}" class="px-3 py-1.5 rounded-lg transition font-medium whitespace-nowrap {{ $status === 'active' ? 'bg-emerald-600 text-white font-semibold' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                Aktif ({{ $counts['active'] ?? 0 }})
            </a>
            <a href="{{ route('superadmin.users.index', array_merge(request()->query(), ['status' => 'suspended'])) }}" class="px-3 py-1.5 rounded-lg transition font-medium whitespace-nowrap {{ $status === 'suspended' ? 'bg-rose-600 text-white font-semibold' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                Digantung ({{ $counts['suspended'] ?? 0 }})
            </a>
            <a href="{{ route('superadmin.users.index', array_merge(request()->query(), ['status' => 'demo'])) }}" class="px-3 py-1.5 rounded-lg transition font-medium whitespace-nowrap {{ $status === 'demo' ? 'bg-amber-600 text-white font-semibold' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                Akaun Demo ({{ $counts['demo'] ?? 0 }})
            </a>
            <a href="{{ route('superadmin.users.index', array_merge(request()->query(), ['status' => 'superadmin'])) }}" class="px-3 py-1.5 rounded-lg transition font-medium whitespace-nowrap {{ $status === 'superadmin' ? 'bg-amber-500 text-white font-semibold' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                Superadmin ({{ $counts['superadmin'] ?? 0 }})
            </a>
        </div>
    </div>

    <!-- 4.4: Jadual Pengguna Penuh (Full User Table) -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-50/75 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-800 text-slate-500 dark:text-slate-400 uppercase font-semibold text-[10px] tracking-wider">
                        <th class="py-3.5 pl-6">Pengguna & Avatar</th>
                        <th class="py-3.5 px-4">Emel</th>
                        <th class="py-3.5 px-4">Organisasi Dipautkan</th>
                        <th class="py-3.5 px-4">Hak Superadmin</th>
                        <th class="py-3.5 px-4">Status Akaun</th>
                        <th class="py-3.5 px-4">Tarikh Dicipta</th>
                        <th class="py-3.5 pr-6 text-right">Tindakan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-slate-600 dark:text-slate-300">
                    @forelse($users as $user)
                    <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition">
                        <!-- Avatar & Nama -->
                        <td class="py-4 pl-6">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-full {{ $user->isSuperadmin() ? 'bg-amber-100 dark:bg-amber-950 text-amber-700 dark:text-amber-300 ring-2 ring-amber-500/40' : ($user->isDemo() ? 'bg-yellow-100 dark:bg-yellow-950 text-yellow-700 dark:text-yellow-300 ring-2 ring-yellow-500/40' : 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300') }} flex items-center justify-center font-bold text-xs flex-shrink-0">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </div>
                                <div class="truncate max-w-[180px]">
                                    <span class="block font-semibold text-slate-900 dark:text-white truncate">{{ $user->name }}</span>
                                    @if($user->id === Auth::id())
                                        <span class="text-[10px] text-indigo-600 dark:text-indigo-400 font-semibold">(Akaun Anda)</span>
                                    @endif
                                </div>
                            </div>
                        </td>

                        <!-- Emel -->
                        <td class="py-4 px-4 font-mono text-[11px] text-slate-600 dark:text-slate-400">
                            {{ $user->email }}
                        </td>

                        <!-- Organisasi Dipautkan -->
                        <td class="py-4 px-4">
                            @if($user->orgs->isNotEmpty())
                                <div class="flex flex-wrap gap-1 max-w-xs">
                                    @foreach($user->orgs as $org)
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-[11px] border border-slate-200/60 dark:border-slate-700/60" title="{{ $org->name }} ({{ $org->pivot->role ?? 'member' }})">
                                            <span>🏢</span>
                                            <span class="font-medium truncate max-w-[130px]">{{ $org->name }}</span>
                                            <span class="text-[9px] text-slate-400">({{ $org->pivot->role ?? 'member' }})</span>
                                        </span>
                                    @endforeach
                                </div>
                            @else
                                <span class="text-slate-400 italic text-[11px]">Tiada pautan</span>
                            @endif
                        </td>

                        <!-- Status Superadmin (Badge Hijau/Emas vs Kelabu) -->
                        <td class="py-4 px-4">
                            @if($user->isSuperadmin())
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-[10px] font-bold bg-amber-100 text-amber-900 dark:bg-amber-950/80 dark:text-amber-300 border border-amber-300 dark:border-amber-700/60">
                                    <span>👑</span>
                                    <span>SUPERADMIN</span>
                                </span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-medium bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400 border border-slate-200/50 dark:border-slate-700/50">
                                    Pengguna Standard
                                </span>
                            @endif
                        </td>

                        <!-- Status Akaun (Aktif / Digantung / Demo) -->
                        <td class="py-4 px-4">
                            <div class="flex items-center gap-1.5 flex-wrap">
                                @if($user->status === 'active')
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        Aktif
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-rose-50 text-rose-700 dark:bg-rose-950/60 dark:text-rose-300 border border-rose-200 dark:border-rose-800">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                        Digantung
                                    </span>
                                @endif

                                @if($user->isDemo())
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold bg-yellow-100 text-yellow-800 dark:bg-yellow-950 dark:text-yellow-300 border border-yellow-300 dark:border-yellow-700/60" title="Akaun Demo Rasmi — Dilindungi">
                                        <span>🧪</span>
                                        <span>DEMO</span>
                                    </span>
                                @endif
                            </div>
                        </td>

                        <!-- Tarikh Dicipta -->
                        <td class="py-4 px-4 text-slate-500 text-[11px]">
                            <span>{{ $user->created_at->format('d M Y') }}</span>
                            <span class="block text-[10px] text-slate-400">{{ $user->created_at->diffForHumans() }}</span>
                        </td>

                        <!-- Tindakan Interaktif (Alpine.js Action Dropdown) -->
                        <td class="py-4 pr-6 text-right">
                            <div class="relative inline-block text-left" x-data="{ open: false }">
                                <button @click="open = !open" type="button" class="p-1.5 rounded-lg text-slate-500 hover:text-slate-800 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"/></svg>
                                </button>

                                <div x-show="open" @click.away="open = false" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="transform opacity-0 scale-95" x-transition:enter-end="transform opacity-100 scale-100" class="origin-top-right absolute right-0 mt-2 w-56 rounded-xl shadow-lg bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 py-1.5 z-50 text-xs">
                                    <!-- Header Info -->
                                    <div class="px-3 py-1.5 border-b border-slate-100 dark:border-slate-700/60">
                                        <span class="block text-[10px] uppercase font-bold text-slate-400">Tindakan Pengguna</span>
                                        <span class="block font-semibold text-slate-800 dark:text-slate-200 truncate">{{ $user->name }}</span>
                                    </div>

                                    <!-- 1. Lantik / Lucutkan Superadmin -->
                                    <form method="POST" action="{{ route('superadmin.users.toggle-superadmin', $user->id) }}">
                                        @csrf
                                        <button type="submit" class="w-full text-left px-3 py-2 hover:bg-slate-50 dark:hover:bg-slate-700/60 flex items-center gap-2 text-slate-700 dark:text-slate-200 transition">
                                            @if($user->isSuperadmin())
                                                <span class="text-rose-500">🔻</span>
                                                <span>Lucutkan Superadmin</span>
                                            @else
                                                <span class="text-amber-500">👑</span>
                                                <span class="font-medium text-amber-700 dark:text-amber-300">Jadikan Superadmin</span>
                                            @endif
                                        </button>
                                    </form>

                                    <!-- 2. Gantung / Aktifkan Akaun -->
                                    <form method="POST" action="{{ route('superadmin.users.status', $user->id) }}">
                                        @csrf
                                        @if($user->status === 'active')
                                            <input type="hidden" name="status" value="suspended">
                                            <button type="submit" class="w-full text-left px-3 py-2 hover:bg-rose-50 dark:hover:bg-rose-950/40 text-rose-600 dark:text-rose-400 flex items-center gap-2 transition" onclick="return confirm('Adakah anda pasti mahu menggantung akaun pengguna ini?')">
                                                <span>⛔</span>
                                                <span>Gantung Akaun (Suspend)</span>
                                            </button>
                                        @else
                                            <input type="hidden" name="status" value="active">
                                            <button type="submit" class="w-full text-left px-3 py-2 hover:bg-emerald-50 dark:hover:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 flex items-center gap-2 transition">
                                                <span>✅</span>
                                                <span>Aktifkan Semula Akaun</span>
                                            </button>
                                        @endif
                                    </form>

                                    @if($user->isDemo())
                                    <div class="px-3 py-1.5 mt-1 border-t border-slate-100 dark:border-slate-700/60 bg-amber-50/50 dark:bg-amber-950/30 text-[10px] text-amber-800 dark:text-amber-300">
                                        🛡️ <strong>Akaun Demo Simulasi:</strong> Dilindungi daripada penghapusan tidak sengaja.
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-12 text-center text-slate-400">
                            Tiada rekod pengguna sepadan dengan kriteria carian.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
        <div class="p-4 border-t border-slate-100 dark:border-slate-800">
            {{ $users->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
