@extends('layouts.app', ['title' => 'Direktori Pekerja — Lekiu'])

@section('content')
<div class="space-y-6" x-data="{ addModal: false }">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('people.index') }}" class="text-xs text-indigo-600 dark:text-indigo-400 font-semibold hover:underline">&larr; Lekiu HR</a>
                <span class="text-slate-400 text-xs">/</span>
                <span class="text-xs text-slate-500 font-medium">Pekerja</span>
            </div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white mt-1">Direktori Tenaga Kerja</h1>
            <p class="text-xs text-slate-500 mt-1">Pengurusan maklumat peribadi, gaji asas dan status staf syarikat.</p>
        </div>
        <div>
            <button @click="addModal = true" class="px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold shadow-sm transition">
                + Tambah Pekerja Baru
            </button>
        </div>
    </div>

    <!-- Table -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-800 text-slate-500">
                    <tr>
                        <th class="p-4 font-semibold">No. Staf</th>
                        <th class="p-4 font-semibold">Nama Penuh</th>
                        <th class="p-4 font-semibold">No. K/P</th>
                        <th class="p-4 font-semibold">Jawatan</th>
                        <th class="p-4 font-semibold">Gaji Asas (RM)</th>
                        <th class="p-4 font-semibold">Tarikh Mula</th>
                        <th class="p-4 font-semibold">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($employees as $emp)
                    <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/40 transition">
                        <td class="p-4 font-mono font-bold text-indigo-600 dark:text-indigo-400">{{ $emp->employee_number }}</td>
                        <td class="p-4 font-semibold text-slate-800 dark:text-slate-200">{{ $emp->full_name }}</td>
                        <td class="p-4 text-slate-500 font-mono">{{ $emp->ic_passport_number }}</td>
                        <td class="p-4 text-slate-700 dark:text-slate-300">{{ $emp->position }}</td>
                        <td class="p-4 font-bold text-slate-900 dark:text-white">RM {{ number_format($emp->basic_salary, 2) }}</td>
                        <td class="p-4 text-slate-500">{{ $emp->join_date }}</td>
                        <td class="p-4">
                            <span class="px-2 py-0.5 rounded-full font-bold uppercase text-[10px] {{ $emp->status === 'active' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300' : 'bg-slate-100 text-slate-600' }}">
                                {{ $emp->status }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="p-8 text-center text-slate-400 text-xs">Tiada rekod pekerja didaftarkan.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($employees->hasPages())
        <div class="p-4 border-t border-slate-100 dark:border-slate-800">
            {{ $employees->links() }}
        </div>
        @endif
    </div>

    <!-- Add Employee Modal -->
    <div x-show="addModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4" style="display: none;">
        <div @click.away="addModal = false" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl max-w-md w-full p-6 shadow-xl">
            <h3 class="text-base font-bold text-slate-900 dark:text-white">Daftar Pekerja Baru</h3>
            <p class="text-xs text-slate-500 mt-1">Masukkan butiran pekerja untuk pengurusan payroll dan kehadiran.</p>

            <form method="POST" action="{{ route('people.employees.store') }}" class="mt-4 space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">No. Pekerja (Staf ID)</label>
                    <input type="text" name="employee_number" required placeholder="cth: EMP-001"
                        class="w-full px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Nama Penuh</label>
                    <input type="text" name="full_name" required placeholder="cth: Siti Nurhaliza"
                        class="w-full px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">No. Kad Pengenalan / Pasport</label>
                    <input type="text" name="ic_passport_number" required placeholder="cth: 920514-10-5544"
                        class="w-full px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Jawatan / Posisi</label>
                    <input type="text" name="position" required placeholder="cth: Eksekutif Pemasaran Digital"
                        class="w-full px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Gaji Asas Bulanan (RM)</label>
                    <input type="number" step="0.01" min="0" name="basic_salary" required placeholder="3500.00"
                        class="w-full px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs font-bold">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Tarikh Mula Bertugas</label>
                    <input type="date" name="join_date" required value="{{ date('Y-m-d') }}"
                        class="w-full px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs">
                </div>

                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="addModal = false" class="px-4 py-2 rounded-xl border border-slate-300 dark:border-slate-700 text-xs font-semibold">Batal</button>
                    <button type="submit" class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold">Simpan Staf</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
