@extends('layouts.app', ['title' => 'Sesi Temu Duga — Lekir'])

@section('content')
<div class="space-y-6" x-data="{ addModal: false }">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('hire.index') }}" class="text-xs text-indigo-600 dark:text-indigo-400 font-semibold hover:underline">&larr; Lekir Hire</a>
                <span class="text-slate-400 text-xs">/</span>
                <span class="text-xs text-slate-500 font-medium">Temu Duga</span>
            </div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white mt-1">Jadual Sesi Temu Duga</h1>
            <p class="text-xs text-slate-500 mt-1">Pengaturan masa temu duga calon bersama panel penilai dan pautan mesyuarat dalam talian.</p>
        </div>
        <div>
            <button @click="addModal = true" class="px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold shadow-sm transition">
                + Tetapkan Temu Duga Baru
            </button>
        </div>
    </div>

    <!-- Interviews List -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-800 text-slate-500">
                    <tr>
                        <th class="p-4 font-semibold">Calon & Jawatan</th>
                        <th class="p-4 font-semibold">Tarikh & Masa</th>
                        <th class="p-4 font-semibold">Penemuduga</th>
                        <th class="p-4 font-semibold">Lokasi / Pautan Google Meet</th>
                        <th class="p-4 font-semibold">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($interviews as $int)
                    <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/40 transition">
                        <td class="p-4">
                            <span class="font-bold text-slate-800 dark:text-slate-200 block">{{ $int->application->candidate_name ?? 'Calon' }}</span>
                            <span class="text-[11px] text-slate-400">{{ $int->application->job->title ?? 'Jawatan' }}</span>
                        </td>
                        <td class="p-4 font-semibold text-slate-900 dark:text-white font-mono">{{ $int->scheduled_at }}</td>
                        <td class="p-4 text-slate-700 dark:text-slate-300 font-medium">{{ $int->interviewer_name }}</td>
                        <td class="p-4 text-slate-500">{{ $int->location_or_link ?? 'Pejabat Utama' }}</td>
                        <td class="p-4">
                            <span class="px-2.5 py-1 rounded-full font-bold uppercase text-[10px] bg-purple-100 text-purple-800 dark:bg-purple-950 dark:text-purple-300">
                                {{ $int->status }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="p-8 text-center text-slate-400 text-xs">Tiada jadual sesi temu duga berdaftar.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($interviews->hasPages())
        <div class="p-4 border-t border-slate-100 dark:border-slate-800">
            {{ $interviews->links() }}
        </div>
        @endif
    </div>

    <!-- Schedule Interview Modal -->
    <div x-show="addModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4" style="display: none;">
        <div @click.away="addModal = false" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl max-w-md w-full p-6 shadow-xl">
            <h3 class="text-base font-bold text-slate-900 dark:text-white">Tetapkan Jadual Temu Duga</h3>
            <p class="text-xs text-slate-500 mt-1">Pilih calon pemohon dan masukkan maklumat masa sesi.</p>

            <form method="POST" action="{{ route('hire.interviews.store') }}" class="mt-4 space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Pilih Calon Pemohon</label>
                    <select name="application_id" required class="w-full px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs">
                        @foreach($applications as $app)
                        <option value="{{ $app->id }}">{{ $app->candidate_name }} ({{ $app->job->title ?? 'Jawatan' }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Tarikh & Waktu Temu Duga</label>
                    <input type="datetime-local" name="scheduled_at" required
                        class="w-full px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Nama Panel Penemuduga</label>
                    <input type="text" name="interviewer_name" required placeholder="cth: Dr. Kamarulzaman & Pn. Aisyah"
                        class="w-full px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Lokasi Bilik / Pautan Video (Google Meet/Zoom)</label>
                    <input type="text" name="location_or_link" placeholder="cth: https://meet.google.com/abc-defg-hij"
                        class="w-full px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs">
                </div>

                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="addModal = false" class="px-4 py-2 rounded-xl border border-slate-300 dark:border-slate-700 text-xs font-semibold">Batal</button>
                    <button type="submit" class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold">Jadualkan Temu Duga</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
