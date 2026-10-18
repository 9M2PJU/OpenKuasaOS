<?php

namespace App\Http\Controllers\Api\V1\People;

use App\Http\Controllers\Concerns\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\People\GeneratePayrollRequest;
use App\Http\Resources\PayslipResource;
use App\Models\HrEmployee;
use App\Models\HrPayrollRun;
use App\Models\HrPayslip;
use App\Services\Payroll\MalaysianPayrollEngine;
use App\Services\Tenancy\OrganizationContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PayrollController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected MalaysianPayrollEngine $payrollEngine
    ) {}

    /**
     * Run statutory monthly payroll calculations for active employees.
     */
    public function generate(GeneratePayrollRequest $request): JsonResponse
    {
        $month = (int) $request->input('month');
        $year = (int) $request->input('year');
        $orgId = OrganizationContext::getId();

        $activeEmployees = HrEmployee::where('status', 'active')->get();

        if ($activeEmployees->isEmpty()) {
            return $this->error('Tiada pekerja aktif dijumpai untuk pemprosesan gaji.', 'NO_ACTIVE_EMPLOYEES', null, 422);
        }

        $payrollRun = DB::transaction(function () use ($month, $year, $orgId, $activeEmployees) {
            $run = HrPayrollRun::firstOrCreate(
                [
                    'org_id' => $orgId,
                    'period_month' => $month,
                    'period_year' => $year,
                ],
                [
                    'status' => 'draft',
                    'total_gross' => 0.00,
                    'total_statutory' => 0.00,
                    'total_net' => 0.00,
                ]
            );

            $totalGross = 0.00;
            $totalStatutory = 0.00;
            $totalNet = 0.00;

            foreach ($activeEmployees as $emp) {
                $calc = $this->payrollEngine->calculatePayslip((float) $emp->basic_salary, [
                    'epf_rate' => (float) $emp->epf_rate,
                ]);

                $totalGross += $calc['gross_pay'];
                $statutory = $calc['epf_employee'] + $calc['epf_employer']
                    + $calc['socso_employee'] + $calc['socso_employer']
                    + $calc['eis_employee'] + $calc['eis_employer']
                    + $calc['pcb_amount'];
                $totalStatutory += $statutory;
                $totalNet += $calc['net_pay'];

                HrPayslip::updateOrCreate(
                    [
                        'payroll_run_id' => $run->id,
                        'employee_id' => $emp->id,
                    ],
                    [
                        'org_id' => $orgId,
                        'gross_pay' => $calc['gross_pay'],
                        'epf_employee' => $calc['epf_employee'],
                        'epf_employer' => $calc['epf_employer'],
                        'socso_employee' => $calc['socso_employee'],
                        'socso_employer' => $calc['socso_employer'],
                        'eis_employee' => $calc['eis_employee'],
                        'eis_employer' => $calc['eis_employer'],
                        'pcb_amount' => $calc['pcb_amount'],
                        'net_pay' => $calc['net_pay'],
                        'status' => 'draft',
                    ]
                );
            }

            $run->total_gross = round($totalGross, 2);
            $run->total_statutory = round($totalStatutory, 2);
            $run->total_net = round($totalNet, 2);
            $run->save();

            return $run->load('payslips.employee');
        });

        return $this->success(
            [
                'payroll_run' => $payrollRun,
                'payslips' => PayslipResource::collection($payrollRun->payslips),
            ],
            'Larian gaji bulanan berjaya dijana dengan caruman statutori KWSP, SOCSO, EIS & PCB.'
        );
    }

    public function runs(): JsonResponse
    {
        $runs = HrPayrollRun::withCount('payslips')
            ->orderByDesc('period_year')
            ->orderByDesc('period_month')
            ->paginate(12);

        return $this->success(
            $runs->items(),
            'Senarai rekod larian gaji berjaya dimuat turun.',
            [
                'page' => $runs->currentPage(),
                'per_page' => $runs->perPage(),
                'total' => $runs->total(),
            ]
        );
    }

    public function payslips(Request $request): JsonResponse
    {
        $query = HrPayslip::with('employee')
            ->orderByDesc('created_at');

        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->query('employee_id'));
        }

        if ($request->filled('payroll_run_id')) {
            $query->where('payroll_run_id', $request->query('payroll_run_id'));
        }

        $payslips = $query->paginate(20);

        return $this->success(
            PayslipResource::collection($payslips->items()),
            'Senarai slip gaji berjaya dimuat turun.',
            [
                'page' => $payslips->currentPage(),
                'per_page' => $payslips->perPage(),
                'total' => $payslips->total(),
            ]
        );
    }

    public function showPayslip(string $id): JsonResponse
    {
        $payslip = HrPayslip::with('employee')->findOrFail($id);

        return $this->success(
            new PayslipResource($payslip),
            'Maklumat slip gaji berjaya diperolehi.'
        );
    }
}
