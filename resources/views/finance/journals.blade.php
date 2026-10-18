@extends('layouts.app', ['title' => 'Lejar Am & Jurnal — Lekir'])

@section('content')
<div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('finance.index') }}" class="text-xs text-indigo-600 dark:text-indigo-400 font-semibold hover:underline">&larr; Lekir</a>
                <span class="text-slate-400 text-xs">/</span>
                <span class="text-xs text-slate-500 font-medium">Lejar Am</span>
            </div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white mt-1">Audit Jurnal & Lejar Am (Double-Entry)</h1>
            <p class="text-xs text-slate-500 mt-1">Rekod audit perakaunan berasaskan prinsip Debit = Kredit bagi setiap urus niaga perniagaan.</p>
        </div>
    </div>

    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden">
        <div class="p-4 border-b border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/40">
            <h2 class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">Entri Jurnal Berkanun</h2>
        </div>

        <div class="divide-y divide-slate-100 dark:divide-slate-800">
            @forelse($journals as $j)
            <div class="p-5 space-y-3">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <span class="font-mono text-xs font-bold text-indigo-600 dark:text-indigo-400">{{ $j->entry_number }}</span>
                        <span class="text-xs font-semibold text-slate-800 dark:text-slate-200">{{ $j->description }}</span>
                    </div>
                    <span class="text-xs text-slate-400">{{ $j->date }}</span>
                </div>

                <!-- Lines Table -->
                <div class="overflow-x-auto rounded-xl border border-slate-100 dark:border-slate-800 bg-slate-50/30 dark:bg-slate-800/20">
                    <table class="w-full text-left text-xs">
                        <thead class="text-slate-500 border-b border-slate-100 dark:border-slate-800">
                            <tr>
                                <th class="p-2.5 font-medium">Akaun Lejar</th>
                                <th class="p-2.5 font-medium text-right">Debit (RM)</th>
                                <th class="p-2.5 font-medium text-right">Kredit (RM)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            @foreach($j->lines as $line)
                            <tr>
                                <td class="p-2.5 text-slate-700 dark:text-slate-300">{{ $line->ledgerAccount->name ?? 'Akaun Am' }}</td>
                                <td class="p-2.5 text-right font-mono {{ $line->debit > 0 ? 'text-slate-900 dark:text-white font-semibold' : 'text-slate-400' }}">
                                    {{ number_format($line->debit, 2) }}
                                </td>
                                <td class="p-2.5 text-right font-mono {{ $line->credit > 0 ? 'text-slate-900 dark:text-white font-semibold' : 'text-slate-400' }}">
                                    {{ number_format($line->credit, 2) }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @empty
            <div class="p-8 text-center text-xs text-slate-400">
                Tiada rekod jurnal lejar am dijumpai.
            </div>
            @endforelse
        </div>

        @if($journals->hasPages())
        <div class="p-4 border-t border-slate-100 dark:border-slate-800">
            {{ $journals->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
