@extends('layouts.app', ['title' => 'Pengurusan Organisasi — Pentadbiran Utama'])

@section('content')
<div class="space-y-6">
    <!-- Header with Breadcrumbs & Title -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-400">
                <a href="{{ route('superadmin.dashboard') }}" class="hover:text-slate-600 dark:hover:text-slate-200 transition">Pentadbiran Utama</a>
                <span>/</span>
                <span class="text-slate-700 dark:text-slate-200 font-medium">Organisasi</span>
            </div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white mt-1">
                Direktori Organisasi & Penyewa (Tenants)
            </h1>
            <p class="text-xs text-slate-500 mt-0.5">
                Senarai seluruh entiti syarikat berdaftar, metrik staf dan invois, serta sokongan impersonasi tenant.
            </p>
        </div>
        <div>
            <span class="inline-flex items-center px-3 py-1.5 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800 text-xs font-semibold">
                Jumlah Syarikat: {{ $totalOrganizations }} Entiti
            </span>
        </div>
    </div>

    <!-- Search Bar -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 shadow-sm">
        <form method="GET" action="{{ route('superadmin.organizations.index') }}" class="flex flex-col sm:flex-row gap-3">
            <div class="flex-1 relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
                <input type="text" name="search" value="{{ $search }}" placeholder="Cari mengikut nama syarikat atau slug..." class="w-full pl-9 pr-4 py-2 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition">
            </div>
            <div class="flex items-center gap-2">
                <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-semibold shadow-sm transition">
                    Cari Organisasi
                </button>
                @if($search)
                <a href="{{ route('superadmin.organizations.index') }}" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 rounded-xl text-xs font-medium transition">
                    Set Semula
                </a>
                @endif
            </div>
        </form>
    </div>

    <!-- 4.5: Jadual Organisasi Berdaftar -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-50/75 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-800 text-slate-500 dark:text-slate-400 uppercase font-semibold text-[10px] tracking-wider">
                        <th class="py-3.5 pl-6">Nama Syarikat & Slug</th>
                        <th class="py-3.5 px-4">Pemilik Utama (Owner)</th>
                        <th class="py-3.5 px-4 text-center">Bil. Ahli</th>
                        <th class="py-3.5 px-4">Metrik Pantas (Invois / Staf)</th>
                        <th class="py-3.5 px-4">Tarikh Dicipta</th>
                        <th class="py-3.5 pr-6 text-right">Tindakan Sokongan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-slate-600 dark:text-slate-300">
                    @forelse($organizations as $org)
                    @php
                        $ownerMember = $org->members->firstWhere('role', 'owner') ?? $org->members->first();
                        $ownerUser = $ownerMember?->user;
                    @endphp
                    <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition">
                        <!-- Nama Syarikat & Slug -->
                        <td class="py-4 pl-6">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-indigo-500/10 to-amber-500/10 border border-indigo-200/50 dark:border-indigo-800/50 flex items-center justify-center font-bold text-sm text-indigo-700 dark:text-indigo-300 flex-shrink-0">
                                    {{ strtoupper(substr($org->name, 0, 1)) }}
                                </div>
                                <div class="truncate max-w-[220px]">
                                    <span class="block font-semibold text-slate-900 dark:text-white truncate text-xs">{{ $org->name }}</span>
                                    <span class="block font-mono text-[10px] text-slate-400 truncate">slug: {{ $org->slug }}</span>
                                </div>
                            </div>
                        </td>

                        <!-- Pemilik Utama -->
                        <td class="py-4 px-4">
                            @if($ownerUser)
                                <div>
                                    <span class="block font-medium text-slate-800 dark:text-slate-200">{{ $ownerUser->name }}</span>
                                    <span class="block font-mono text-[11px] text-slate-400 truncate">{{ $ownerUser->email }}</span>
                                </div>
                            @else
                                <span class="text-slate-400 italic text-[11px]">Tiada pemilik direkodkan</span>
                            @endif
                        </td>

                        <!-- Bilangan Ahli -->
                        <td class="py-4 px-4 text-center font-semibold text-slate-800 dark:text-slate-200">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-slate-100 dark:bg-slate-800 text-[11px]">
                                {{ $org->members_count }} ahli
                            </span>
                        </td>

                        <!-- Metrik Pantas: Jumlah Invois & Staf -->
                        <td class="py-4 px-4">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-emerald-50 dark:bg-emerald-950/50 text-emerald-700 dark:text-emerald-300 text-[11px] font-medium border border-emerald-200/60 dark:border-emerald-800/60" title="Jumlah Invois Lekir">
                                    <span>🧾</span>
                                    <span><strong>{{ $org->invoices_count }}</strong> Invois</span>
                                </span>
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-blue-50 dark:bg-blue-950/50 text-blue-700 dark:text-blue-300 text-[11px] font-medium border border-blue-200/60 dark:border-blue-800/60" title="Jumlah Kakitangan HR Lekiu">
                                    <span>👥</span>
                                    <span><strong>{{ $org->employees_count }}</strong> Staf</span>
                                </span>
                            </div>
                        </td>

                        <!-- Tarikh Dicipta -->
                        <td class="py-4 px-4 text-slate-500 text-[11px]">
                            <span>{{ $org->created_at->format('d M Y') }}</span>
                            <span class="block text-[10px] text-slate-400">{{ $org->created_at->diffForHumans() }}</span>
                        </td>

                        <!-- Butang Tindakan Impersonate Support -->
                        <td class="py-4 pr-6 text-right">
                            <form method="POST" action="{{ route('superadmin.organizations.impersonate', $org->id) }}" class="inline">
                                @csrf
                                <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-amber-500/10 hover:bg-amber-500/20 text-amber-800 dark:text-amber-300 border border-amber-300 dark:border-amber-700 font-semibold text-xs shadow-xs transition hover:scale-105 active:scale-95 cursor-pointer" title="Log masuk sebagai organisasi ini untuk bantuan sokongan teknikal">
                                    <svg class="w-3.5 h-3.5 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    <span>Buka Paparan Organisasi (Impersonate Support)</span>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-12 text-center text-slate-400">
                            Tiada organisasi ditemui yang sepadan dengan carian anda.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($organizations->hasPages())
        <div class="p-4 border-t border-slate-100 dark:border-slate-800">
            {{ $organizations->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
