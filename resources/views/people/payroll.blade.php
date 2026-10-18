@extends('layouts.app', ['title' => 'Enjin Penggajian & Slip Gaji — Lekiu'])

@section('content')
<div class="space-y-6" x-data="{ runModal: false }">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('people.index') }}" class="text-xs text-indigo-600 dark:text-indigo-400 font-semibold hover:underline">&larr; Lekiu HR</a>
                <span class="text-slate-400 text-xs">/</span>
                <span class="text-xs text-slate-500 font-medium">Payroll</span>
            </div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white mt-1">Enjin Penggajian Berkanun Malaysia</h1>
            <p class="text-xs text-slate-500 mt-1">Pengiraan automatik caruman KWSP (Jadual Ketiga), PERKESO, EIS & potongan cukai berjadual (PCB LHDN).</p>
        </div>
        <div>
            <button @click="runModal = true" class="px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold shadow-sm transition">
                ⚡ Jalankan Pengiraan Gaji Bulanan
            </button>
        </div>
    </div>

    <!-- Payroll Runs History & Payslips -->
    <div class="space-y-6">
        @forelse($payrollRuns as $run)
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 shadow-sm space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-100 dark:border-slate-800">
                <div>
                    <span class="text-[10px] font-bold uppercase tracking-wider text-indigo-600 dark:text-indigo-400">Kitaran Gaji Bulanan</span>
                    <h2 class="text-lg font-bold text-slate-900 dark:text-white mt-0.5">
                        Bulan {{ $run->period_month }}/{{ $run->period_year }}
                    </h2>
                </div>
                <div class="flex items-center gap-6 text-xs">
                    <div>
                        <span class="text-slate-400 block">Jumlah Kasar (Gross):</span>
                        <strong class="text-slate-800 dark:text-slate-200">RM {{ number_format($run->total_gross, 2) }}</strong>
                    </div>
                    <div>
                        <span class="text-slate-400 block">Statutori (KWSP/SOCSO/PCB):</span>
                        <strong class="text-rose-500">RM {{ number_format($run->total_statutory, 2) }}</strong>
                    </div>
                    <div>
                        <span class="text-slate-400 block">Jumlah Bersih (Net Pay):</span>
                        <strong class="text-emerald-600 font-bold text-sm">RM {{ number_format($run->total_net, 2) }}</strong>
                    </div>
                </div>
            </div>

            <!-- Payslips breakdown table -->
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="text-slate-500 border-b border-slate-100 dark:border-slate-800">
                            <th class="py-2.5 font-semibold">Nama Pekerja</th>
                            <th class="py-2.5 font-semibold text-right">Gaji Kasar</th>
                            <th class="py-2.5 font-semibold text-right">KWSP Pekerja (11%)</th>
                            <th class="py-2.5 font-semibold text-right">KWSP Majikan</th>
                            <th class="py-2.5 font-semibold text-right">SOCSO Pekerja</th>
                            <th class="py-2.5 font-semibold text-right">EIS</th>
                            <th class="py-2.5 font-semibold text-right">PCB</th>
                            <th class="py-2.5 font-semibold text-right">Gaji Bersih (RM)</th>
                            <th class="py-2.5 font-semibold text-center w-28">Slip Gaji</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach($run->payslips as $ps)
                        <tr>
                            <td class="py-3 font-semibold text-slate-800 dark:text-slate-200">
                                {{ $ps->employee->full_name ?? 'Pekerja' }}
                            </td>
                            <td class="py-3 text-right">{{ number_format($ps->gross_pay, 2) }}</td>
                            <td class="py-3 text-right text-rose-500">-{{ number_format($ps->epf_employee, 2) }}</td>
                            <td class="py-3 text-right text-slate-400">{{ number_format($ps->epf_employer, 2) }}</td>
                            <td class="py-3 text-right text-rose-500">-{{ number_format($ps->socso_employee, 2) }}</td>
                            <td class="py-3 text-right text-rose-500">-{{ number_format($ps->eis_employee, 2) }}</td>
                            <td class="py-3 text-right text-rose-500">-{{ number_format($ps->pcb_amount, 2) }}</td>
                            <td class="py-3 text-right font-bold text-emerald-600 dark:text-emerald-400">
                                RM {{ number_format($ps->net_pay, 2) }}
                            </td>
                            <td class="py-3 text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    <!-- Icon Mata (Buka & Muat Turun PDF Penyata Gaji) -->
                                    <a href="{{ route('people.payroll.payslip', $ps->id) }}" target="_blank"
                                       class="p-1.5 rounded-lg text-indigo-600 hover:text-indigo-800 hover:bg-indigo-50 dark:text-indigo-400 dark:hover:bg-indigo-950/60 transition inline-flex items-center justify-center"
                                       title="Buka Slip Gaji Standard Malaysia (PDF / Cetak)">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                    </a>

                                    @php
                                        $empName = $ps->employee->full_name ?? 'Kakitangan';
                                        $waText = "📄 *PENYATA GAJI (PAYSLIP)*\n"
                                            . "Bulan: *{$run->period_month}/{$run->period_year}*\n"
                                            . "Nama: *{$empName}*\n"
                                            . "Gaji Bersih: *RM " . number_format($ps->net_pay, 2) . "*\n\n"
                                            . "Pautan Slip Gaji Rasmi (PDF):\n"
                                            . route('people.payroll.payslip', $ps->id);
                                        $waUrl = 'https://wa.me/?text=' . rawurlencode($waText);
                                    @endphp
                                    <!-- Icon WhatsApp (Kongsi Terus ke WhatsApp) -->
                                    <a href="{{ $waUrl }}" target="_blank" rel="noopener noreferrer"
                                       class="p-1.5 rounded-lg text-emerald-600 hover:text-emerald-800 hover:bg-emerald-50 dark:text-emerald-400 dark:hover:bg-emerald-950/60 transition inline-flex items-center justify-center"
                                       title="Kongsi Slip Gaji ke WhatsApp">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.312.045-.634.074-1.922-.442-1.579-.633-2.6-2.224-2.678-2.33-.078-.106-.641-.855-.641-1.631 0-.775.405-1.157.55-1.314.144-.157.315-.197.42-.197.106 0 .211.002.302.008.098.006.231-.037.362.277.135.324.464 1.134.505 1.218.041.084.068.183.013.292-.054.11-.082.179-.163.275-.082.096-.172.215-.246.289-.082.082-.168.172-.072.337.096.165.426.703.914 1.138.629.56 1.159.734 1.324.816.165.082.262.069.36-.044.098-.113.42-.49.533-.658.113-.168.226-.14.378-.084.152.056.966.455 1.132.538.165.084.276.126.317.195.042.07.042.404-.102.809zM12 2C6.477 2 2 6.477 2 12c0 1.891.526 3.662 1.442 5.176L2 22l4.981-1.396A9.957 9.957 0 0012 22c5.523 0 10-4.477 10-10S17.523 2 12 2z"/>
                                        </svg>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @empty
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-12 text-center text-slate-400 text-xs">
            Belum ada rekod kitaran gaji dijalankan. Klik "Jalankan Pengiraan Gaji Bulanan" untuk mengira gaji bagi semua pekerja berdaftar.
        </div>
        @endforelse
    </div>

    <!-- Generate Payroll Run Modal -->
    <div x-show="runModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4" style="display: none;">
        <div @click.away="runModal = false" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl max-w-md w-full p-6 shadow-xl">
            <h3 class="text-base font-bold text-slate-900 dark:text-white">Jalankan Pengiraan Gaji Bulanan</h3>
            <p class="text-xs text-slate-500 mt-1">Sistem akan mengira caruman statutori KWSP, SOCSO, EIS dan PCB secara automatik untuk semua kakitangan aktif.</p>

            <form method="POST" action="{{ route('people.payroll.generate') }}" class="mt-4 space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Bulan Gaji</label>
                    <select name="period_month" required class="w-full px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs">
                        @for($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" {{ $m == date('n') ? 'selected' : '' }}>
                            Bulan {{ $m }} ({{ date('F', mktime(0, 0, 0, $m, 10)) }})
                        </option>
                        @endfor
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Tahun Gaji</label>
                    <input type="number" name="period_year" required value="{{ date('Y') }}"
                        class="w-full px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs font-bold">
                </div>

                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="runModal = false" class="px-4 py-2 rounded-xl border border-slate-300 dark:border-slate-700 text-xs font-semibold">Batal</button>
                    <button type="submit" class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold">Jana Gaji Sekarang</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
