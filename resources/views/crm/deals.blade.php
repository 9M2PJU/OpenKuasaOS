@extends('layouts.app', ['title' => 'Papan Saluran Urus Niaga (Deals) — Kasturi'])

@section('content')
<div class="space-y-6" x-data="{ addModal: false }">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('crm.index') }}" class="text-xs text-indigo-600 dark:text-indigo-400 font-semibold hover:underline">&larr; Kasturi CRM</a>
                <span class="text-slate-400 text-xs">/</span>
                <span class="text-xs text-slate-500 font-medium">Deals Pipeline</span>
            </div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white mt-1">Papan Saluran Urus Niaga (Deals)</h1>
            <p class="text-xs text-slate-500 mt-1">Visualisasi Kanban kitaran jualan dari prospek awal hingga penutupan transaksi.</p>
        </div>
        <div>
            <button @click="addModal = true" class="px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold shadow-sm transition">
                + Tambah Urus Niaga (Deal)
            </button>
        </div>
    </div>

    <!-- Kanban Columns Board -->
    <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-4 items-start overflow-x-auto pb-4">
        @foreach($stages as $stageKey => $stageInfo)
        @php
            $stageDeals = $deals->where('stage_id', $stageKey);
            $stageTotal = $stageDeals->sum('amount');
        @endphp
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-3 shadow-sm flex flex-col min-h-[450px]">
            <!-- Column Header -->
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                <div class="truncate">
                    <span class="font-bold text-xs text-slate-800 dark:text-slate-200 truncate block">{{ $stageInfo['name'] }}</span>
                    <span class="text-[10px] text-slate-400 font-medium">RM {{ number_format($stageTotal, 0) }}</span>
                </div>
                <span class="text-[11px] font-bold px-2 py-0.5 rounded-full {{ $stageInfo['color'] }}">
                    {{ $stageDeals->count() }}
                </span>
            </div>

            <!-- Cards -->
            <div class="space-y-2.5 mt-3 flex-1">
                @forelse($stageDeals as $deal)
                <div class="p-3 rounded-xl border border-slate-100 dark:border-slate-800 bg-slate-50/70 dark:bg-slate-800/40 shadow-xs hover:shadow-sm transition">
                    <span class="block font-semibold text-xs text-slate-900 dark:text-white">{{ $deal->title }}</span>
                    @if($deal->contact)
                    <span class="block text-[11px] text-slate-500 mt-0.5">👤 {{ $deal->contact->name }}</span>
                    @endif
                    <div class="mt-2.5 pt-2 border-t border-slate-200/50 dark:border-slate-700/50 flex items-center justify-between">
                        <span class="font-bold text-xs text-slate-900 dark:text-white">RM {{ number_format($deal->amount, 2) }}</span>

                        <!-- Move stage selector -->
                        <form method="POST" action="{{ route('crm.deals.update-stage', $deal->id) }}">
                            @csrf
                            <select name="stage_id" onchange="this.form.submit()" class="text-[10px] py-0.5 px-1 rounded border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300">
                                @foreach($stages as $k => $s)
                                <option value="{{ $k }}" {{ $deal->stage_id === $k ? 'selected' : '' }}>&rarr; {{ $s['name'] }}</option>
                                @endforeach
                            </select>
                        </form>
                    </div>
                </div>
                @empty
                <div class="py-8 text-center text-slate-400 text-[11px]">Tiada urus niaga</div>
                @endforelse
            </div>
        </div>
        @endforeach
    </div>

    <!-- Add Deal Modal -->
    <div x-show="addModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4" style="display: none;">
        <div @click.away="addModal = false" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl max-w-md w-full p-6 shadow-xl">
            <h3 class="text-base font-bold text-slate-900 dark:text-white">Cipta Urus Niaga (Deal) Baru</h3>
            <p class="text-xs text-slate-500 mt-1">Masukkan butiran urus niaga jualan untuk dijejaki dalam pipeline.</p>

            <form method="POST" action="{{ route('crm.deals.store') }}" class="mt-4 space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Tajuk Urus Niaga</label>
                    <input type="text" name="title" required placeholder="cth: Pembekalan Perisian Enterprise"
                        class="w-full px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Kontak Pelanggan (Pilihan)</label>
                    <select name="contact_id" class="w-full px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs">
                        <option value="">-- Tiada Kontak Dipilih --</option>
                        @foreach($contacts as $c)
                        <option value="{{ $c->id }}">{{ $c->name }} ({{ $c->company ?? 'Individu' }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Nilai Urus Niaga (RM)</label>
                    <input type="number" step="0.01" min="0" name="amount" required placeholder="25000.00"
                        class="w-full px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs font-bold">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Peringkat Permulaan</label>
                    <select name="stage_id" required class="w-full px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs">
                        @foreach($stages as $key => $st)
                        <option value="{{ $key }}">{{ $st['name'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Tarikh Jangkaan Tutup</label>
                    <input type="date" name="expected_close_date" value="{{ date('Y-m-d', strtotime('+30 days')) }}"
                        class="w-full px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs">
                </div>

                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="addModal = false" class="px-4 py-2 rounded-xl border border-slate-300 dark:border-slate-700 text-xs font-semibold">Batal</button>
                    <button type="submit" class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold">Tambah Deal</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
