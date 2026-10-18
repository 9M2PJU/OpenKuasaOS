<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HrPayslip extends Model
{
    use BelongsToTenant, HasFactory, HasUuids;

    protected $table = 'hr_payslips';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'org_id',
        'payroll_run_id',
        'employee_id',
        'gross_pay',
        'epf_employee',
        'epf_employer',
        'socso_employee',
        'socso_employer',
        'eis_employee',
        'eis_employer',
        'pcb_amount',
        'net_pay',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'gross_pay' => 'decimal:2',
            'epf_employee' => 'decimal:2',
            'epf_employer' => 'decimal:2',
            'socso_employee' => 'decimal:2',
            'socso_employer' => 'decimal:2',
            'eis_employee' => 'decimal:2',
            'eis_employer' => 'decimal:2',
            'pcb_amount' => 'decimal:2',
            'net_pay' => 'decimal:2',
        ];
    }

    public function payrollRun(): BelongsTo
    {
        return $this->belongsTo(HrPayrollRun::class, 'payroll_run_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(HrEmployee::class, 'employee_id');
    }
}
