@extends('layouts.app', ['title' => 'Iklan Jawatan — Lekir'])

@section('content')
<div class="space-y-6" x-data="{ addModal: false }">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('hire.index') }}" class="text-xs text-indigo-600 dark:text-indigo-400 font-semibold hover:underline">&larr; Lekir Hire</a>
                <span class="text-slate-400 text-xs">/</span>
                <span class="text-xs text-slate-500 font-medium">Jawatan Kosong</span>
            </div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white mt-1">Iklan Jawatan Kosong Syarikat</h1>
            <p class="text-xs text-slate-500 mt-1">Urus siaran peluang pekerjaan dan penyerapan bakat baru.</p>
        </div>
        <div>
            <button @click="addModal = true" class="px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold shadow-sm transition">
                + Buka Iklan Jawatan Baru
            </button>
        </div>
    </div>

    <!-- Jobs Table -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-800 text-slate-500">
                    <tr>
                        <th class="p-4 font-semibold">Jawatan</th>
                        <th class="p-4 font-semibold">Jabatan</th>
                        <th class="p-4 font-semibold">Jenis Pekerjaan</th>
                        <th class="p-4 font-semibold">Pemohon</th>
                        <th class="p-4 font-semibold">Status</th>
                        <th class="p-4 font-semibold">Tarikh Cipta</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($jobs as $job)
                    <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/40 transition">
                        <td class="p-4">
                            <span class="font-bold text-slate-900 dark:text-white block">{{ $job->title }}</span>
                            <span class="text-[11px] text-slate-400 line-clamp-1 mt-0.5">{{ $job->description }}</span>
                        </td>
                        <td class="p-4 text-slate-700 dark:text-slate-300">{{ $job->department }}</td>
                        <td class="p-4 text-slate-500">{{ str_replace('_', ' ', ucfirst($job->employment_type)) }}</td>
                        <td class="p-4 font-bold text-indigo-600 dark:text-indigo-400">{{ $job->applications_count }} calon</td>
                        <td class="p-4">
                            <span class="px-2.5 py-1 rounded-full font-bold uppercase text-[10px]
                                {{ $job->status === 'published' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300' : '' }}
                                {{ $job->status === 'draft' ? 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300' : '' }}
                                {{ $job->status === 'closed' ? 'bg-slate-100 text-slate-600 dark:bg-slate-800' : '' }}">
                                {{ $job->status }}
                            </span>
                        </td>
                        <td class="p-4 text-slate-400">{{ $job->created_at ? $job->created_at->format('d M Y') : '-' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="p-8 text-center text-slate-400 text-xs">Tiada rekod jawatan dibuka.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($jobs->hasPages())
        <div class="p-4 border-t border-slate-100 dark:border-slate-800">
            {{ $jobs->links() }}
        </div>
        @endif
    </div>

    <!-- Add Job Modal -->
    <div x-show="addModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4" style="display: none;">
        <div @click.away="addModal = false" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl max-w-lg w-full p-6 shadow-xl">
            <h3 class="text-base font-bold text-slate-900 dark:text-white">Buka Iklan Jawatan Kosong</h3>
            <p class="text-xs text-slate-500 mt-1">Masukkan spesifikasi dan peranan tugas jawatan.</p>

            <form method="POST" action="{{ route('hire.jobs.store') }}" class="mt-4 space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Tajuk Jawatan</label>
                    <input type="text" name="title" required placeholder="cth: Pengaturcara Perisian Kanan (PHP/Laravel)"
                        class="w-full px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Jabatan</label>
                        <input type="text" name="department" required placeholder="Teknologi Maklumat"
                            class="w-full px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Jenis Pekerjaan</label>
                        <select name="employment_type" required class="w-full px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs">
                            <option value="full_time">Sepenuh Masa (Full Time)</option>
                            <option value="part_time">Sambilan (Part Time)</option>
                            <option value="contract">Kontrak</option>
                            <option value="internship">Latihan Industri (Internship)</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Deskripsi Tugas & Syarat Kelayakan</label>
                    <textarea name="description" required rows="4" placeholder="Pengalaman dalam membina sistem berasaskan Laravel 13 dan pangkalan data PostgreSQL..."
                        class="w-full px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs"></textarea>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Status Iklan</label>
                    <select name="status" required class="w-full px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs">
                        <option value="published">Diterbitkan (Published)</option>
                        <option value="draft">Draf (Draft)</option>
                    </select>
                </div>

                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="addModal = false" class="px-4 py-2 rounded-xl border border-slate-300 dark:border-slate-700 text-xs font-semibold">Batal</button>
                    <button type="submit" class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold">Terbitkan Jawatan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
