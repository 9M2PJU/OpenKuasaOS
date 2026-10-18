@extends('layouts.app', ['title' => 'Iklan Jawatan — Lekir'])

@section('content')
<div class="space-y-6" x-data="{ addModal: false }">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('hire.index') }}" class="text-xs text-indigo-600 dark:text-indigo-400 font-semibold hover:underline">&larr; Lekiu Pengambilan Staf</a>
                <span class="text-slate-400 text-xs">/</span>
                <span class="text-xs text-slate-500 font-medium">Jawatan Kosong</span>
            </div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white mt-1">Iklan Jawatan Kosong Syarikat</h1>
            <p class="text-xs text-slate-500 mt-1">Urus siaran peluang pekerjaan dan jana salinan kempen rekrutmen Jebat AI.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('reach.studio', ['type' => 'job']) }}" class="px-3.5 py-2.5 rounded-xl border border-pink-500/30 bg-pink-500/10 hover:bg-pink-500/20 text-pink-600 dark:text-pink-400 text-xs font-semibold shadow-xs transition flex items-center gap-1.5">
                <span>⚡</span>
                <span>Jebat Ad Studio</span>
            </a>
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
                        <th class="p-4 font-semibold text-right">Tindakan</th>
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
                        <td class="p-4 text-right">
                            <div class="flex items-center justify-end gap-1.5" x-data="{ linkCopied: false }">
                                <a href="{{ route('careers.show', $job->slug ?: $job->id) }}" target="_blank" title="Buka Portal Calon" class="p-1.5 rounded-xl border border-slate-200 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-600 dark:text-slate-300 transition">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                </a>
                                <button type="button" @click="navigator.clipboard.writeText('{{ route('careers.show', $job->slug ?: $job->id) }}'); linkCopied = true; setTimeout(() => linkCopied = false, 2000)" title="Salin Link Permohonan Calon" class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-xl border border-emerald-300/60 dark:border-emerald-800/60 bg-emerald-50/60 dark:bg-emerald-950/40 hover:bg-emerald-100 dark:hover:bg-emerald-900/60 text-emerald-700 dark:text-emerald-300 text-[11px] font-bold transition">
                                    <span x-show="!linkCopied">🔗 Link Calon</span>
                                    <span x-show="linkCopied" class="text-emerald-600 font-bold">✓ Disalin!</span>
                                </button>
                                <a href="{{ route('reach.studio', ['job_id' => $job->id]) }}" class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-xl bg-pink-50 dark:bg-pink-950/50 hover:bg-pink-100 dark:hover:bg-pink-900/60 text-pink-600 dark:text-pink-300 text-[11px] font-bold border border-pink-200/60 dark:border-pink-800/60 transition shadow-2xs">
                                    <span>⚡</span>
                                    <span>Jana Iklan</span>
                                </a>
                            </div>
                        </td>
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
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Deskripsi Tugas</label>
                    <textarea name="description" required rows="3" placeholder="Bertanggungjawab mengurus operasi dan membangunkan sistem web..."
                        class="w-full px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs"></textarea>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Syarat Kelayakan &amp; Kemahiran (Penting untuk Saringan AI)</label>
                    <textarea name="requirements" rows="3" placeholder="• Mahir dalam PHP, Laravel & PostgreSQL&#10;• Minimum 2 tahun pengalaman industri&#10;• Diploma / Ijazah dalam Sains Komputer atau setaraf"
                        class="w-full px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs"></textarea>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Gaji Min (RM)</label>
                        <input type="number" step="0.01" name="salary_min" placeholder="cth: 3000"
                            class="w-full px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Gaji Max (RM)</label>
                        <input type="number" step="0.01" name="salary_max" placeholder="cth: 5500"
                            class="w-full px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs">
                    </div>
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
