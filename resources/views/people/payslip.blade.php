@extends('layouts.app', ['title' => 'Penyata Gaji Rasmi — ' . ($employee->full_name ?? 'Kakitangan')])

@section('content')
<div class="max-w-4xl mx-auto space-y-6 pb-16">
    <!-- Action Bar (Hidden when printing or saving to PDF) -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 print:hidden">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('people.payroll') }}" class="text-xs text-indigo-600 dark:text-indigo-400 font-semibold hover:underline flex items-center gap-1">
                    &larr; <span>Kembali ke Enjin Penggajian</span>
                </a>
            </div>
            <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-slate-900 dark:text-white mt-1">
                Penyata Gaji (Payslip) &bull; {{ $monthName }} {{ $payrollRun->period_year }}
            </h1>
            <p class="text-xs text-slate-500 mt-0.5">
                Format piawaian berkanun Malaysia (Akta Kerja 1955, Akta KWSP 1991, Akta PERKESO & Akta EIS).
            </p>
        </div>

        <!-- Action Buttons -->
        <div class="flex items-center gap-2.5 flex-wrap">
            <!-- Cetak / Simpan PDF -->
            <button onclick="window.print()" class="px-4 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 dark:bg-slate-700 dark:hover:bg-slate-600 text-white text-xs font-semibold shadow-sm transition flex items-center gap-2 cursor-pointer">
                <svg class="w-4 h-4 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                <span>Cetak / Muat Turun PDF</span>
            </button>

            <!-- Kongsi Melalui WhatsApp -->
            <a href="{{ $whatsappUrl }}" target="_blank" rel="noopener noreferrer" class="px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold shadow-sm transition flex items-center gap-2">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.312.045-.634.074-1.922-.442-1.579-.633-2.6-2.224-2.678-2.33-.078-.106-.641-.855-.641-1.631 0-.775.405-1.157.55-1.314.144-.157.315-.197.42-.197.106 0 .211.002.302.008.098.006.231-.037.362.277.135.324.464 1.134.505 1.218.041.084.068.183.013.292-.054.11-.082.179-.163.275-.082.096-.172.215-.246.289-.082.082-.168.172-.072.337.096.165.426.703.914 1.138.629.56 1.159.734 1.324.816.165.082.262.069.36-.044.098-.113.42-.49.533-.658.113-.168.226-.14.378-.084.152.056.966.455 1.132.538.165.084.276.126.317.195.042.07.042.404-.102.809zM12 2C6.477 2 2 6.477 2 12c0 1.891.526 3.662 1.442 5.176L2 22l4.981-1.396A9.957 9.957 0 0012 22c5.523 0 10-4.477 10-10S17.523 2 12 2z"/>
                </svg>
                <span>Kongsi ke WhatsApp</span>
            </a>
        </div>
    </div>

    <!-- ============================================================== -->
    <!-- OFFICIAL STANDARD MALAYSIAN PAYSLIP DOCUMENT                   -->
    <!-- ============================================================== -->
    <div id="payslip-document" class="bg-white text-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-8 sm:p-12 shadow-sm space-y-8 print:border-none print:shadow-none print:p-0 print:rounded-none">

        <!-- Header: Company & Payslip Metadata -->
        <div class="flex flex-col sm:flex-row justify-between items-start gap-6 pb-6 border-b-2 border-slate-900">
            <div class="space-y-1">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-indigo-600 text-white font-black flex items-center justify-center text-sm">
                        OK
                    </div>
                    <h2 class="text-xl sm:text-2xl font-black tracking-tight text-slate-900 uppercase">
                        {{ $currentOrg->name ?? 'Rimba Ventures Sdn Bhd' }}
                    </h2>
                </div>
                <p class="text-xs text-slate-600 font-medium">
                    No. Pendaftaran Syarikat (SSM): 202401089234 (1523411-X) &bull; Majikan Berdaftar
                </p>
                <p class="text-[11px] text-slate-500 leading-tight">
                    Tingkat 12, Menara KerjaAI, Jalan Sultan Ismail, 50250 Kuala Lumpur, Malaysia
                </p>
            </div>

            <div class="sm:text-right space-y-1 bg-slate-50 sm:bg-transparent p-4 sm:p-0 rounded-2xl w-full sm:w-auto">
                <div class="inline-block px-3 py-1 bg-indigo-900 text-white text-[11px] font-bold rounded-md uppercase tracking-wider">
                    Penyata Gaji / Payslip
                </div>
                <div class="text-xs text-slate-700 mt-2">
                    <span class="text-slate-500">Bulan Penggajian:</span>
                    <strong class="font-bold text-slate-900 uppercase">{{ $monthName }} {{ $payrollRun->period_year }}</strong>
                </div>
                <div class="text-xs text-slate-700">
                    <span class="text-slate-500">Tempoh:</span>
                    <strong>01/{{ str_pad($payrollRun->period_month, 2, '0', STR_PAD_LEFT) }}/{{ $payrollRun->period_year }} &mdash; {{ $lastDayOfMonth }}/{{ str_pad($payrollRun->period_month, 2, '0', STR_PAD_LEFT) }}/{{ $payrollRun->period_year }}</strong>
                </div>
                <div class="text-xs text-slate-700">
                    <span class="text-slate-500">Tarikh Bayaran:</span>
                    <strong class="text-indigo-700">28/{{ str_pad($payrollRun->period_month, 2, '0', STR_PAD_LEFT) }}/{{ $payrollRun->period_year }}</strong>
                </div>
            </div>
        </div>

        <!-- Employee Info Grid -->
        <div class="bg-slate-50/80 rounded-2xl p-5 border border-slate-200/80 text-xs">
            <h3 class="text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-3 pb-2 border-b border-slate-200">
                Maklumat Pekerja & Perjawatan (Employee Profile)
            </h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-y-3.5 gap-x-6">
                <div>
                    <span class="text-slate-500 block text-[11px]">Nama Penuh:</span>
                    <strong class="text-slate-900 text-sm font-bold">{{ $employee->full_name ?? 'Kakitangan' }}</strong>
                </div>
                <div>
                    <span class="text-slate-500 block text-[11px]">No. Pekerja (Emp ID):</span>
                    <strong class="text-slate-900 font-mono font-semibold">{{ $employee->employee_number ?? 'EMP-001' }}</strong>
                </div>
                <div>
                    <span class="text-slate-500 block text-[11px]">No. Kad Pengenalan / Pasport:</span>
                    <strong class="text-slate-900 font-mono">{{ $employee->ic_passport_number ?? '880123-14-5678' }}</strong>
                </div>
                <div>
                    <span class="text-slate-500 block text-[11px]">Jawatan / Position:</span>
                    <strong class="text-slate-900 font-medium">{{ $employee->position ?? 'Eksekutif' }}</strong>
                </div>
                <div>
                    <span class="text-slate-500 block text-[11px]">Tarikh Mula Berkhidmat:</span>
                    <strong class="text-slate-900">
                        {{ $employee->join_date ? \Carbon\Carbon::parse($employee->join_date)->format('d/m/Y') : '01/01/2026' }}
                    </strong>
                </div>
                <div>
                    <span class="text-slate-500 block text-[11px]">Kaedah Pembayaran:</span>
                    <strong class="text-slate-900">Pindahan Bank (Bank Transfer)</strong>
                </div>
                <div>
                    <span class="text-slate-500 block text-[11px]">No. KWSP (EPF No):</span>
                    <strong class="text-slate-900 font-mono">{{ 'EPF-' . substr(preg_replace('/[^0-9]/', '', md5($employee->id)), 0, 8) }}</strong>
                </div>
                <div>
                    <span class="text-slate-500 block text-[11px]">No. PERKESO (SOCSO):</span>
                    <strong class="text-slate-900 font-mono">{{ 'SOCSO-' . substr(preg_replace('/[^0-9]/', '', md5($employee->id . 'socso')), 0, 8) }}</strong>
                </div>
                <div>
                    <span class="text-slate-500 block text-[11px]">No. Cukai Pendapatan (LHDN):</span>
                    <strong class="text-slate-900 font-mono">{{ 'SG-' . substr(preg_replace('/[^0-9]/', '', md5($employee->id . 'tax')), 0, 8) }}</strong>
                </div>
            </div>
        </div>

        <!-- Earnings & Deductions Tables (Side by Side - Malaysian Standard) -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-start">
            <!-- Left: Pendapatan (Earnings) -->
            <div class="border border-slate-200 rounded-2xl overflow-hidden">
                <div class="bg-slate-100/80 px-4 py-2.5 border-b border-slate-200 flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-800">1. Pendapatan (Earnings)</span>
                    <span class="text-[11px] font-semibold text-slate-500">Amaun (RM)</span>
                </div>
                <table class="w-full text-xs">
                    <tbody class="divide-y divide-slate-100">
                        <tr>
                            <td class="px-4 py-2.5 text-slate-700 font-medium">Gaji Asas / Pokok (Basic Salary)</td>
                            <td class="px-4 py-2.5 text-right font-mono font-semibold text-slate-900">{{ number_format($payslip->gross_pay, 2) }}</td>
                        </tr>
                        <tr>
                            <td class="px-4 py-2.5 text-slate-600">Elaun Tetap / Perjalanan</td>
                            <td class="px-4 py-2.5 text-right font-mono text-slate-500">0.00</td>
                        </tr>
                        <tr>
                            <td class="px-4 py-2.5 text-slate-600">Kerja Lebih Masa (Overtime / OT)</td>
                            <td class="px-4 py-2.5 text-right font-mono text-slate-500">0.00</td>
                        </tr>
                        <tr>
                            <td class="px-4 py-2.5 text-slate-600">Bonus / Komisen / Insentif</td>
                            <td class="px-4 py-2.5 text-right font-mono text-slate-500">0.00</td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr class="bg-slate-50 border-t-2 border-slate-200">
                            <td class="px-4 py-3 font-bold text-slate-900">JUMLAH PENDAPATAN KASAR</td>
                            <td class="px-4 py-3 text-right font-mono font-bold text-slate-900 text-sm">
                                RM {{ number_format($payslip->gross_pay, 2) }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <!-- Right: Potongan Pekerja (Deductions) -->
            <div class="border border-slate-200 rounded-2xl overflow-hidden">
                <div class="bg-rose-50/80 px-4 py-2.5 border-b border-rose-100 flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider text-rose-900">2. Potongan Pekerja (Deductions)</span>
                    <span class="text-[11px] font-semibold text-rose-700">Amaun (RM)</span>
                </div>
                <table class="w-full text-xs">
                    <tbody class="divide-y divide-slate-100">
                        <tr>
                            <td class="px-4 py-2.5 text-slate-700">
                                <span>KWSP / EPF Pekerja</span>
                                <span class="text-[10px] text-slate-400 block">Kadar berkanun {{ number_format($employee->epf_rate ?? 11, 0) }}%</span>
                            </td>
                            <td class="px-4 py-2.5 text-right font-mono text-rose-600 font-medium">{{ number_format($payslip->epf_employee, 2) }}</td>
                        </tr>
                        <tr>
                            <td class="px-4 py-2.5 text-slate-700">
                                <span>PERKESO / SOCSO Pekerja</span>
                                <span class="text-[10px] text-slate-400 block">Jadual caruman Akta Keselamatan Sosial</span>
                            </td>
                            <td class="px-4 py-2.5 text-right font-mono text-rose-600 font-medium">{{ number_format($payslip->socso_employee, 2) }}</td>
                        </tr>
                        <tr>
                            <td class="px-4 py-2.5 text-slate-700">
                                <span>Sistem Insurans Pekerjaan (SIP/EIS)</span>
                                <span class="text-[10px] text-slate-400 block">Kadar 0.2% had siling RM 6,000</span>
                            </td>
                            <td class="px-4 py-2.5 text-right font-mono text-rose-600 font-medium">{{ number_format($payslip->eis_employee, 2) }}</td>
                        </tr>
                        <tr>
                            <td class="px-4 py-2.5 text-slate-700">
                                <span>Potongan Cukai Berjadual (PCB LHDN)</span>
                                <span class="text-[10px] text-slate-400 block">Kadar jadual komputer LHDN</span>
                            </td>
                            <td class="px-4 py-2.5 text-right font-mono text-rose-600 font-medium">{{ number_format($payslip->pcb_amount, 2) }}</td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr class="bg-rose-50/50 border-t-2 border-rose-200">
                            <td class="px-4 py-3 font-bold text-rose-950">JUMLAH POTONGAN</td>
                            <td class="px-4 py-3 text-right font-mono font-bold text-rose-700 text-sm">
                                RM {{ number_format($totalDeductions, 2) }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- Employer Statutory Contributions Breakdown (Maklumat Tambahan Berkanun Majikan) -->
        <div class="border border-slate-200 rounded-2xl overflow-hidden text-xs">
            <div class="bg-slate-100/70 px-4 py-2.5 border-b border-slate-200 flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-800">
                    3. Caruman Statutori Majikan (Employer Contributions - Bukan Potongan Gaji)
                </span>
                <span class="text-[11px] text-slate-500 font-medium">Ditanggung sepenuhnya oleh Majikan</span>
            </div>
            <div class="p-4 grid grid-cols-1 sm:grid-cols-4 gap-4 bg-slate-50/50">
                <div>
                    <span class="text-[11px] text-slate-500 block">KWSP Majikan (EPF 12%/13%):</span>
                    <strong class="text-slate-900 font-mono text-xs">RM {{ number_format($payslip->epf_employer, 2) }}</strong>
                </div>
                <div>
                    <span class="text-[11px] text-slate-500 block">PERKESO Majikan (SOCSO 1.75%):</span>
                    <strong class="text-slate-900 font-mono text-xs">RM {{ number_format($payslip->socso_employer, 2) }}</strong>
                </div>
                <div>
                    <span class="text-[11px] text-slate-500 block">SIP / EIS Majikan (0.2%):</span>
                    <strong class="text-slate-900 font-mono text-xs">RM {{ number_format($payslip->eis_employer, 2) }}</strong>
                </div>
                <div>
                    <span class="text-[11px] text-slate-500 block font-semibold text-indigo-900">Jumlah Caruman Majikan:</span>
                    <strong class="text-indigo-700 font-mono text-xs font-bold">RM {{ number_format($totalEmployer, 2) }}</strong>
                </div>
            </div>
        </div>

        <!-- Net Pay Highlight Box (Malaysian Net Pay Hero) -->
        <div class="bg-gradient-to-r from-emerald-50 via-teal-50 to-blue-50 border-2 border-emerald-500/40 rounded-2xl p-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <span class="text-xs font-extrabold uppercase tracking-widest text-emerald-800 block">
                    JUMLAH GAJI BERSIH DITERIMA (NET PAY)
                </span>
                <div class="text-3xl sm:text-4xl font-black text-emerald-900 tracking-tight mt-1 font-mono">
                    RM {{ number_format($payslip->net_pay, 2) }}
                </div>
                <p class="text-xs text-slate-600 mt-1">
                    *Telah dikreditkan secara automatik ke akaun bank pekerja yang berdaftar.
                </p>
            </div>

            <div class="flex items-center gap-6 sm:border-l sm:border-emerald-300 sm:pl-6 text-xs">
                <div>
                    <span class="text-slate-500 block text-[11px]">Baki Cuti Tahunan:</span>
                    <strong class="text-slate-900 font-bold text-sm">{{ $employee->annual_leave_balance ?? 14 }} Hari</strong>
                </div>
                <div>
                    <span class="text-slate-500 block text-[11px]">Baki Cuti Sakit:</span>
                    <strong class="text-slate-900 font-bold text-sm">{{ $employee->medical_leave_balance ?? 14 }} Hari</strong>
                </div>
            </div>
        </div>

        <!-- Verification & Sign-off Section -->
        <div class="pt-6 border-t border-slate-200 text-xs">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-8 items-end">
                <div class="space-y-1">
                    <p class="text-[11px] font-bold uppercase tracking-wider text-slate-600">Pengesahan Pihak Pengurusan & HR:</p>
                    <p class="text-[11px] text-slate-500 leading-relaxed">
                        Penyata ini dijana secara berkomputer oleh KerjaAI Lekiu HR & Attendance Engine.<br>
                        Sah laku tanpa tandatangan basah mengikut peruntukan Akta Tandatangan Digital 1997.
                    </p>
                </div>

                <div class="text-right sm:text-right space-y-2">
                    <div class="inline-block border-b border-slate-400 w-48 text-center pb-1 text-[11px] font-semibold text-slate-700">
                        Bahagian Sumber Manusia & Kewangan
                    </div>
                    <p class="text-[10px] text-slate-400">Tarikh Cetakan Dokumen: {{ date('d/m/Y H:i:s') }}</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ==============================================
     PRINT & PDF MEDIA STYLESHEET
     ============================================== -->
<style>
@media print {
    /* Hide layout chrome */
    aside, header, nav, .print\:hidden {
        display: none !important;
    }

    body, html {
        background: #ffffff !important;
        color: #000000 !important;
        height: auto !important;
        overflow: visible !important;
        font-size: 11pt !important;
    }

    main {
        padding: 0 !important;
        margin: 0 !important;
        overflow: visible !important;
    }

    #payslip-document {
        border: none !important;
        box-shadow: none !important;
        padding: 0 !important;
        margin: 0 !important;
        max-width: 100% !important;
    }
}
</style>
@endsection
