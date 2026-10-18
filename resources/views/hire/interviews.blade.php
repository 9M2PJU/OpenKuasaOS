@extends('layouts.app', ['title' => 'Sesi Temu Duga — Lekir'])

@section('content')
<div class="space-y-6" x-data="{
    addModal: false,
    editModal: false,
    deleteModal: false,
    isSaving: false,
    isUpdating: false,
    isDeleting: false,
    editItem: {
        id: '',
        candidate_name: '',
        job_title: '',
        scheduled_at: '',
        interviewer_name: '',
        location_or_link: '',
        status: 'scheduled',
        notes: ''
    },
    deleteItem: {
        id: '',
        candidate_name: '',
        scheduled_at: ''
    },
    openEdit(item) {
        this.editItem = { ...item };
        this.isUpdating = false;
        this.editModal = true;
    },
    openDelete(item) {
        this.deleteItem = { ...item };
        this.isDeleting = false;
        this.deleteModal = true;
    }
}">
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
            <button @click="addModal = true; isSaving = false" class="px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 active:scale-95 text-white text-xs font-semibold shadow-sm transition cursor-pointer">
                + Tetapkan Temu Duga Baru
            </button>
        </div>
    </div>

    <!-- Alert Banners -->
    @if(session('success'))
    <div class="p-4 rounded-2xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-xs text-emerald-800 dark:text-emerald-200 flex items-center gap-2">
        <span>✅</span>
        <span class="font-medium">{{ session('success') }}</span>
    </div>
    @endif

    @if(session('info'))
    <div class="p-4 rounded-2xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800 text-xs text-amber-800 dark:text-amber-200 flex items-center gap-2">
        <span>ℹ️</span>
        <span class="font-medium">{{ session('info') }}</span>
    </div>
    @endif

    @if($errors->any())
    <div class="p-4 rounded-2xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-900/60 text-xs text-rose-700 dark:text-rose-300 space-y-1">
        <div class="font-bold flex items-center gap-1.5">
            <span>⚠️</span>
            <span>Sila semak maklumat yang dimasukkan:</span>
        </div>
        <ul class="list-disc list-inside space-y-0.5 pl-1">
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <!-- Interviews List -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-800 text-slate-500">
                    <tr>
                        <th class="p-4 font-semibold">Calon &amp; Jawatan</th>
                        <th class="p-4 font-semibold">Tarikh &amp; Masa</th>
                        <th class="p-4 font-semibold">Penemuduga</th>
                        <th class="p-4 font-semibold">Lokasi / Pautan Google Meet</th>
                        <th class="p-4 font-semibold">Status</th>
                        <th class="p-4 font-semibold text-right">Tindakan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($interviews as $int)
                    @php
                        $formattedIso = $int->scheduled_at ? $int->scheduled_at->format('Y-m-d\TH:i') : '';
                        $formattedDisplay = $int->scheduled_at ? $int->scheduled_at->format('d/m/Y, h:i A') : '-';
                        $interviewPayload = [
                            'id' => $int->id,
                            'candidate_name' => $int->application->candidate_name ?? 'Calon',
                            'job_title' => $int->application->job->title ?? 'Jawatan',
                            'scheduled_at' => $formattedIso,
                            'interviewer_name' => $int->interviewer_name ?? ($int->interviewer->name ?? ''),
                            'location_or_link' => $int->location_or_link ?? ($int->meeting_link ?? ''),
                            'status' => $int->status ?? 'scheduled',
                            'notes' => $int->notes ?? ($int->feedback ?? ''),
                        ];
                    @endphp
                    <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/40 transition">
                        <td class="p-4">
                            <span class="font-bold text-slate-800 dark:text-slate-200 block">{{ $int->application->candidate_name ?? 'Calon' }}</span>
                            <span class="text-[11px] text-slate-400">{{ $int->application->job->title ?? 'Jawatan' }}</span>
                        </td>
                        <td class="p-4 font-semibold text-slate-900 dark:text-white font-mono">
                            {{ $formattedDisplay }}
                        </td>
                        <td class="p-4 text-slate-700 dark:text-slate-300 font-medium">
                            {{ $int->interviewer_name ?: ($int->interviewer->name ?? 'Panel Penemuduga') }}
                        </td>
                        <td class="p-4 text-slate-500">
                            @php
                                $loc = $int->location_or_link ?: ($int->meeting_link ?: 'Pejabat Utama');
                                $isLink = str_starts_with($loc, 'http://') || str_starts_with($loc, 'https://');
                            @endphp
                            @if($isLink)
                                <a href="{{ $loc }}" target="_blank" rel="noopener noreferrer" class="text-indigo-600 dark:text-indigo-400 hover:underline inline-flex items-center gap-1 font-medium">
                                    <span>📹 Pautan Sesi</span>
                                    <span class="text-[10px]">&nearr;</span>
                                </a>
                            @else
                                <span>{{ $loc }}</span>
                            @endif
                        </td>
                        <td class="p-4">
                            @if($int->status === 'completed')
                                <span class="px-2.5 py-1 rounded-full font-bold uppercase text-[10px] bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                                    Selesai
                                </span>
                            @elseif($int->status === 'cancelled')
                                <span class="px-2.5 py-1 rounded-full font-bold uppercase text-[10px] bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400 border border-slate-200 dark:border-slate-700">
                                    Dibatalkan
                                </span>
                            @else
                                <span class="px-2.5 py-1 rounded-full font-bold uppercase text-[10px] bg-indigo-100 text-indigo-800 dark:bg-indigo-950 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800">
                                    Dijadualkan
                                </span>
                            @endif
                        </td>
                        <td class="p-4 text-right">
                            <div class="flex items-center justify-end gap-1.5">
                                <button type="button"
                                        @click="openEdit({{ json_encode($interviewPayload) }})"
                                        class="px-2.5 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 hover:bg-indigo-50 dark:hover:bg-slate-800 hover:border-indigo-300 text-slate-700 dark:text-slate-300 hover:text-indigo-600 dark:hover:text-indigo-400 text-xs font-semibold transition cursor-pointer flex items-center gap-1"
                                        title="Sunting Sesi Temu Duga">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                    <span>Sunting</span>
                                </button>
                                <button type="button"
                                        @click="openDelete({ id: '{{ $int->id }}', candidate_name: '{{ addslashes($int->application->candidate_name ?? 'Calon') }}', scheduled_at: '{{ $formattedDisplay }}' })"
                                        class="px-2.5 py-1.5 rounded-lg border border-rose-200 dark:border-rose-900/60 hover:bg-rose-50 dark:hover:bg-rose-950/50 text-rose-600 dark:text-rose-400 text-xs font-semibold transition cursor-pointer flex items-center gap-1"
                                        title="Padam Rekod Temu Duga">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    <span>Padam</span>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="p-8 text-center text-slate-400 text-xs">Tiada jadual sesi temu duga berdaftar.</td>
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

    <!-- SCHEDULE NEW INTERVIEW MODAL -->
    <div x-show="addModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4" style="display: none;">
        <div @click.away="addModal = false" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl max-w-md w-full p-6 shadow-xl">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                <div>
                    <h3 class="text-base font-bold text-slate-900 dark:text-white">Tetapkan Jadual Temu Duga</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Pilih calon pemohon dan masukkan maklumat masa sesi.</p>
                </div>
                <button type="button" @click="addModal = false" class="text-slate-400 hover:text-slate-600 text-lg">&times;</button>
            </div>

            <form method="POST" action="{{ route('hire.interviews.store') }}" @submit="if(isSaving) { $event.preventDefault(); return false; } isSaving = true;" class="mt-4 space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Pilih Calon Pemohon <span class="text-rose-500">*</span></label>
                    <select name="application_id" required class="w-full px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
                        @foreach($applications as $app)
                        <option value="{{ $app->id }}">{{ $app->candidate_name }} ({{ $app->job->title ?? 'Jawatan' }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Tarikh &amp; Waktu Temu Duga <span class="text-rose-500">*</span></label>
                    <input type="datetime-local" name="scheduled_at" required
                        class="w-full px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Nama Panel Penemuduga <span class="text-rose-500">*</span></label>
                    <input type="text" name="interviewer_name" required placeholder="cth: Dr. Kamarulzaman &amp; Pn. Aisyah"
                        class="w-full px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Lokasi Bilik / Pautan Video (Google Meet/Zoom)</label>
                    <input type="text" name="location_or_link" placeholder="cth: https://meet.google.com/abc-defg-hij"
                        class="w-full px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Catatan / Nota Sesi (Pilihan)</label>
                    <textarea name="notes" rows="2" placeholder="cth: Calon diminta menyediakan slaid portfolio teknikal..."
                        class="w-full px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500"></textarea>
                </div>

                <div class="flex justify-end gap-2 pt-3 border-t border-slate-100 dark:border-slate-800">
                    <button type="button" @click="addModal = false" class="px-4 py-2 rounded-xl border border-slate-300 dark:border-slate-700 text-xs font-semibold hover:bg-slate-50 dark:hover:bg-slate-800 cursor-pointer">
                        Batal
                    </button>
                    <!-- Anti-Double-Click Submit Button -->
                    <button type="submit"
                            :disabled="isSaving"
                            class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 active:scale-95 text-white text-xs font-semibold shadow-sm transition flex items-center justify-center gap-1.5 cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed">
                        <span x-show="!isSaving">Jadualkan Temu Duga</span>
                        <span x-show="isSaving" class="flex items-center gap-1.5" style="display: none;">
                            <svg class="animate-spin w-3.5 h-3.5 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                            <span>Sedang Dijadualkan...</span>
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- EDIT INTERVIEW MODAL -->
    <div x-show="editModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4" style="display: none;">
        <div @click.away="editModal = false" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl max-w-md w-full p-6 shadow-xl">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                <div>
                    <h3 class="text-base font-bold text-slate-900 dark:text-white">Kemaskini Jadual Temu Duga</h3>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Calon: <strong class="text-slate-800 dark:text-slate-200" x-text="editItem.candidate_name"></strong>
                        <span class="text-slate-400" x-text="'(' + editItem.job_title + ')'"></span>
                    </p>
                </div>
                <button type="button" @click="editModal = false" class="text-slate-400 hover:text-slate-600 text-lg">&times;</button>
            </div>

            <form method="POST" :action="'/hire/interviews/' + editItem.id" @submit="if(isUpdating) { $event.preventDefault(); return false; } isUpdating = true;" class="mt-4 space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Tarikh &amp; Waktu Temu Duga <span class="text-rose-500">*</span></label>
                    <input type="datetime-local" name="scheduled_at" x-model="editItem.scheduled_at" required
                        class="w-full px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Nama Panel Penemuduga <span class="text-rose-500">*</span></label>
                    <input type="text" name="interviewer_name" x-model="editItem.interviewer_name" required placeholder="cth: Dr. Kamarulzaman &amp; Pn. Aisyah"
                        class="w-full px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Lokasi Bilik / Pautan Video (Google Meet/Zoom)</label>
                    <input type="text" name="location_or_link" x-model="editItem.location_or_link" placeholder="cth: https://meet.google.com/abc-defg-hij"
                        class="w-full px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Status Sesi Temu Duga <span class="text-rose-500">*</span></label>
                    <select name="status" x-model="editItem.status" required
                        class="w-full px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
                        <option value="scheduled">Dijadualkan (Scheduled)</option>
                        <option value="completed">Selesai (Completed)</option>
                        <option value="cancelled">Dibatalkan (Cancelled)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Catatan / Nota Sesi</label>
                    <textarea name="notes" x-model="editItem.notes" rows="2" placeholder="Catatan tambahan panel penilai..."
                        class="w-full px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500"></textarea>
                </div>

                <div class="flex justify-end gap-2 pt-3 border-t border-slate-100 dark:border-slate-800">
                    <button type="button" @click="editModal = false" class="px-4 py-2 rounded-xl border border-slate-300 dark:border-slate-700 text-xs font-semibold hover:bg-slate-50 dark:hover:bg-slate-800 cursor-pointer">
                        Batal
                    </button>
                    <!-- Anti-Double-Click Update Button -->
                    <button type="submit"
                            :disabled="isUpdating"
                            class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 active:scale-95 text-white text-xs font-semibold shadow-sm transition flex items-center justify-center gap-1.5 cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed">
                        <span x-show="!isUpdating">Simpan Perubahan</span>
                        <span x-show="isUpdating" class="flex items-center gap-1.5" style="display: none;">
                            <svg class="animate-spin w-3.5 h-3.5 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                            <span>Menyimpan...</span>
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- DELETE CONFIRMATION MODAL -->
    <div x-show="deleteModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4" style="display: none;">
        <div @click.away="deleteModal = false" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl max-w-sm w-full p-6 shadow-xl">
            <div class="w-10 h-10 rounded-full bg-rose-100 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400 flex items-center justify-center text-lg mb-3">
                🗑️
            </div>
            <h3 class="text-base font-bold text-slate-900 dark:text-white">Padam Sesi Temu Duga?</h3>
            <p class="text-xs text-slate-500 mt-2 leading-relaxed">
                Adakah anda pasti mahu memadamkan jadual temu duga untuk calon <strong class="text-slate-800 dark:text-slate-200" x-text="deleteItem.candidate_name"></strong> pada waktu <span class="font-mono text-slate-700 dark:text-slate-300 font-semibold" x-text="deleteItem.scheduled_at"></span>?
            </p>
            <p class="text-[11px] text-rose-500 mt-1">Tindakan ini akan membuang rekod jadual temu duga ini.</p>

            <form method="POST" :action="'/hire/interviews/' + deleteItem.id" @submit="if(isDeleting) { $event.preventDefault(); return false; } isDeleting = true;" class="mt-5 flex justify-end gap-2">
                @csrf
                @method('DELETE')
                <button type="button" @click="deleteModal = false" class="px-3.5 py-2 rounded-xl border border-slate-300 dark:border-slate-700 text-xs font-semibold text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 cursor-pointer">
                    Batal
                </button>
                <button type="submit"
                        :disabled="isDeleting"
                        class="px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 active:scale-95 text-white text-xs font-semibold transition flex items-center gap-1.5 cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed">
                    <span x-show="!isDeleting">Ya, Padamkan</span>
                    <span x-show="isDeleting" class="flex items-center gap-1.5" style="display: none;">
                        <svg class="animate-spin w-3.5 h-3.5 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                        <span>Memadam...</span>
                    </span>
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
