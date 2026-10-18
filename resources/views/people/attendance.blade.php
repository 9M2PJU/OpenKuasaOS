@extends('layouts.app', ['title' => 'Sistem Kehadiran (Punch Card) — Lekiu'])

@section('content')
<div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('people.index') }}" class="text-xs text-indigo-600 dark:text-indigo-400 font-semibold hover:underline">&larr; Lekiu HR</a>
                <span class="text-slate-400 text-xs">/</span>
                <span class="text-xs text-slate-500 font-medium">Kehadiran</span>
            </div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white mt-1">Sistem Punch Card Kehadiran</h1>
            <p class="text-xs text-slate-500 mt-1">Perekodan masa bertugas kakitangan mengikut zon waktu Malaysia (Asia/Kuala_Lumpur).</p>
        </div>
    </div>

    <!-- Live Punch Card Action Banner -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 shadow-sm">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="space-y-1">
                <span class="text-xs font-semibold text-indigo-600 dark:text-indigo-400 uppercase tracking-wider">Status Anda Hari Ini ({{ date('d M Y') }})</span>
                <h2 class="text-xl font-bold text-slate-900 dark:text-white">
                    @if($myTodayAttendance && $myTodayAttendance->clock_in)
                        Sudah Clock In: <span class="text-emerald-600 font-mono">{{ $myTodayAttendance->clock_in }}</span>
                        @if($myTodayAttendance->clock_out)
                            &bull; Clock Out: <span class="text-rose-600 font-mono">{{ $myTodayAttendance->clock_out }}</span>
                        @endif
                    @else
                        Belum Clock In Hari Ini
                    @endif
                </h2>
                <p class="text-xs text-slate-400">Pastikan anda menekan Clock In sebaik sahaja memulakan tugas.</p>
            </div>

            <div class="flex items-center gap-3">
                <form method="POST" action="{{ route('people.attendance.clock-in') }}">
                    @csrf
                    <button type="submit"
                        class="px-5 py-3 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md transition flex items-center gap-2">
                        <span>⏰ Rekod Masuk (Clock In)</span>
                    </button>
                </form>

                <form method="POST" action="{{ route('people.attendance.clock-out') }}">
                    @csrf
                    <button type="submit"
                        class="px-5 py-3 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs shadow-md transition flex items-center gap-2">
                        <span>🚪 Rekod Keluar (Clock Out)</span>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Attendance Logs Table -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden">
        <div class="p-4 border-b border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/40">
            <h3 class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">Log Kehadiran Hari Ini</h3>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 dark:bg-slate-800/30 border-b border-slate-200 dark:border-slate-800 text-slate-500">
                    <tr>
                        <th class="p-4 font-semibold">Nama Pekerja</th>
                        <th class="p-4 font-semibold">Tarikh</th>
                        <th class="p-4 font-semibold">Waktu Masuk (Clock In)</th>
                        <th class="p-4 font-semibold">Waktu Keluar (Clock Out)</th>
                        <th class="p-4 font-semibold">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($todayAttendances as $att)
                    <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/40 transition">
                        <td class="p-4 font-semibold text-slate-800 dark:text-slate-200">
                            {{ $att->employee->full_name ?? 'Pekerja' }}
                        </td>
                        <td class="p-4 text-slate-500">{{ $att->date }}</td>
                        <td class="p-4 font-mono font-bold text-emerald-600">{{ $att->clock_in ?? '-' }}</td>
                        <td class="p-4 font-mono font-bold text-rose-600">{{ $att->clock_out ?? '-' }}</td>
                        <td class="p-4">
                            <span class="px-2 py-0.5 rounded-full font-bold uppercase text-[10px] bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300">
                                {{ $att->status }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="p-8 text-center text-slate-400 text-xs">
                            Tiada rekod kehadiran direkodkan setakat ini untuk hari ini.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($todayAttendances->hasPages())
        <div class="p-4 border-t border-slate-100 dark:border-slate-800">
            {{ $todayAttendances->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
