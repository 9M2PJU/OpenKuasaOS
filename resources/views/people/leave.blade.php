@extends('layouts.app', ['title' => 'Pengurusan Cuti — Lekiu'])

@section('content')
<div class="space-y-6" x-data="{ applyModal: false }">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('people.index') }}" class="text-xs text-indigo-600 dark:text-indigo-400 font-semibold hover:underline">&larr; Lekiu HR</a>
                <span class="text-slate-400 text-xs">/</span>
                <span class="text-xs text-slate-500 font-medium">Cuti</span>
            </div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white mt-1">Pengurusan Cuti & Kelulusan</h1>
            <p class="text-xs text-slate-500 mt-1">Semakan baki cuti tahunan, cuti sakit dan kelulusan cuti pekerja tanpa pertindihan tarikh.</p>
        </div>
        <div>
            <button @click="applyModal = true" class="px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold shadow-sm transition">
                + Mohon Cuti Baru
            </button>
        </div>
    </div>

    <!-- Leave Requests Table -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-800 text-slate-500">
                    <tr>
                        <th class="p-4 font-semibold">Nama Pekerja</th>
                        <th class="p-4 font-semibold">Jenis Cuti</th>
                        <th class="p-4 font-semibold">Tarikh Mula</th>
                        <th class="p-4 font-semibold">Tarikh Tamat</th>
                        <th class="p-4 font-semibold">Hari</th>
                        <th class="p-4 font-semibold">Sebab / Catatan</th>
                        <th class="p-4 font-semibold">Status</th>
                        <th class="p-4 font-semibold text-right">Tindakan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($leaveRequests as $lr)
                    <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/40 transition">
                        <td class="p-4 font-semibold text-slate-800 dark:text-slate-200">
                            {{ $lr->employee->full_name ?? 'Pekerja' }}
                        </td>
                        <td class="p-4 font-medium text-slate-700 dark:text-slate-300">
                            {{ ucfirst($lr->leave_type) }}
                        </td>
                        <td class="p-4 text-slate-500">{{ $lr->start_date }}</td>
                        <td class="p-4 text-slate-500">{{ $lr->end_date }}</td>
                        <td class="p-4 font-bold">{{ $lr->days_count }}</td>
                        <td class="p-4 text-slate-400">{{ $lr->reason ?? '-' }}</td>
                        <td class="p-4">
                            <span class="px-2.5 py-1 rounded-full font-bold uppercase text-[10px]
                                {{ $lr->status === 'approved' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300' : '' }}
                                {{ $lr->status === 'pending' ? 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300' : '' }}
                                {{ $lr->status === 'rejected' ? 'bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-300' : '' }}">
                                {{ $lr->status }}
                            </span>
                        </td>
                        <td class="p-4 text-right">
                            @if($lr->status === 'pending')
                            <div class="flex items-center justify-end gap-2">
                                <form method="POST" action="{{ route('people.leave.approve', $lr->id) }}">
                                    @csrf
                                    <button type="submit" class="px-2.5 py-1 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-[11px] shadow-sm">
                                        Lulus
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('people.leave.reject', $lr->id) }}">
                                    @csrf
                                    <button type="submit" class="px-2.5 py-1 rounded-lg border border-rose-300 text-rose-600 hover:bg-rose-50 font-semibold text-[11px]">
                                        Tolak
                                    </button>
                                </form>
                            </div>
                            @else
                            <span class="text-slate-400 text-[11px]">Selesai</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="p-8 text-center text-slate-400 text-xs">Tiada permohonan cuti didaftarkan.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($leaveRequests->hasPages())
        <div class="p-4 border-t border-slate-100 dark:border-slate-800">
            {{ $leaveRequests->links() }}
        </div>
        @endif
    </div>

    <!-- Apply Leave Modal -->
    <div x-show="applyModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4" style="display: none;">
        <div @click.away="applyModal = false" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl max-w-md w-full p-6 shadow-xl">
            <h3 class="text-base font-bold text-slate-900 dark:text-white">Borang Permohonan Cuti</h3>
            <p class="text-xs text-slate-500 mt-1">Hantar permohonan untuk semakan dan kelulusan majikan.</p>

            <form method="POST" action="{{ route('people.leave.store') }}" class="mt-4 space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Pilih Pekerja</label>
                    <select name="employee_id" required class="w-full px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs">
                        @foreach($employees as $emp)
                        <option value="{{ $emp->id }}">{{ $emp->full_name }} ({{ $emp->position }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Jenis Cuti</label>
                    <select name="leave_type" required class="w-full px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs">
                        <option value="annual">Cuti Tahunan (Annual Leave)</option>
                        <option value="medical">Cuti Sakit (MC / Medical)</option>
                        <option value="unpaid">Cuti Tanpa Gaji (Unpaid Leave)</option>
                        <option value="maternity">Cuti Bersalin (Maternity)</option>
                        <option value="paternity">Cuti Isteri Bersalin (Paternity)</option>
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Tarikh Mula</label>
                        <input type="date" name="start_date" required value="{{ date('Y-m-d') }}"
                            class="w-full px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Tarikh Tamat</label>
                        <input type="date" name="end_date" required value="{{ date('Y-m-d') }}"
                            class="w-full px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Bilangan Hari</label>
                    <input type="number" step="0.5" min="0.5" name="days_count" required value="1"
                        class="w-full px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Sebab Permohonan</label>
                    <textarea name="reason" rows="2" placeholder="Urusan peribadi / kecemasan..."
                        class="w-full px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs"></textarea>
                </div>

                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="applyModal = false" class="px-4 py-2 rounded-xl border border-slate-300 dark:border-slate-700 text-xs font-semibold">Batal</button>
                    <button type="submit" class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold">Hantar Permohonan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
