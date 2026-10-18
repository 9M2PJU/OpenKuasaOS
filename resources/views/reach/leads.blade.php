@extends('layouts.app', ['title' => 'Senarai Prospek (Leads) — Jebat'])

@section('content')
<div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('reach.index') }}" class="text-xs text-indigo-600 dark:text-indigo-400 font-semibold hover:underline">&larr; Jebat Pemasaran</a>
                <span class="text-slate-400 text-xs">/</span>
                <span class="text-xs text-slate-500 font-medium">Prospek</span>
            </div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white mt-1">Pangkalan Data Prospek (Leads)</h1>
            <p class="text-xs text-slate-500 mt-1">Maklumat bakal pelanggan yang ditangkap melalui borang iklan digital dan kempen pemasaran.</p>
        </div>
    </div>

    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-800 text-slate-500">
                    <tr>
                        <th class="p-4 font-semibold">Nama Prospek</th>
                        <th class="p-4 font-semibold">Emel</th>
                        <th class="p-4 font-semibold">Telefon</th>
                        <th class="p-4 font-semibold">Saluran</th>
                        <th class="p-4 font-semibold">Kempen Punca</th>
                        <th class="p-4 font-semibold">Status Corong (Funnel)</th>
                        <th class="p-4 font-semibold">Tarikh Tangkapan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($leads as $lead)
                    <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/40 transition">
                        <td class="p-4 font-semibold text-slate-800 dark:text-slate-200">{{ $lead->name }}</td>
                        <td class="p-4 text-slate-500">{{ $lead->email }}</td>
                        <td class="p-4 text-slate-500 font-mono">{{ $lead->phone ?? '-' }}</td>
                        <td class="p-4">
                            @php
                                $src = strtolower($lead->source ?? 'facebook');
                            @endphp
                            <span class="px-2 py-0.5 rounded font-bold uppercase text-[10px]
                                {{ $src === 'whatsapp' ? 'bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-300' : '' }}
                                {{ $src === 'facebook' ? 'bg-cyan-100 text-cyan-800 dark:bg-cyan-950 dark:text-cyan-300' : '' }}
                                {{ $src === 'instagram' ? 'bg-blue-100 text-blue-800 dark:bg-blue-950 dark:text-blue-300' : '' }}
                                {{ $src === 'tiktok' ? 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300' : '' }}">
                                {{ $lead->source ?? 'Direct' }}
                            </span>
                        </td>
                        <td class="p-4 text-slate-600 dark:text-slate-400 font-medium">{{ $lead->campaign->name ?? 'Tangkapan Terus' }}</td>
                        <td class="p-4">
                            <span class="px-2.5 py-1 rounded-full font-bold uppercase text-[10px]
                                {{ $lead->status === 'won' ? 'bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-300' : '' }}
                                {{ $lead->status === 'booked' ? 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300' : '' }}
                                {{ $lead->status === 'qualified' ? 'bg-blue-100 text-blue-800 dark:bg-blue-950 dark:text-blue-300' : '' }}
                                {{ $lead->status === 'contacted' ? 'bg-cyan-100 text-cyan-800 dark:bg-cyan-950 dark:text-cyan-300' : '' }}
                                {{ $lead->status === 'new' ? 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300' : '' }}">
                                {{ $lead->status ?? 'new' }}
                            </span>
                        </td>
                        <td class="p-4 text-slate-400">{{ $lead->created_at ? $lead->created_at->format('d M Y, h:i A') : '-' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="p-8 text-center text-slate-400 text-xs">Tiada prospek ditangkap setakat ini.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($leads->hasPages())
        <div class="p-4 border-t border-slate-100 dark:border-slate-800">
            {{ $leads->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
