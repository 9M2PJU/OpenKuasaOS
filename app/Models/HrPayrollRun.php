<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HrPayrollRun extends Model
{
    use BelongsToTenant, HasFactory, HasUuids;

    protected $table = 'hr_payroll_runs';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'org_id',
        'period_month',
        'period_year',
        'status', // draft, approved, paid
        'total_gross',
        'total_statutory',
        'total_net',
    ];

    protected function casts(): array
    {
        return [
            'period_month' => 'integer',
            'period_year' => 'integer',
            'total_gross' => 'decimal:2',
            'total_statutory' => 'decimal:2',
            'total_net' => 'decimal:2',
        ];
    }

    public function payslips(): HasMany
    {
        return $this->hasMany(HrPayslip::class, 'payroll_run_id');
    }
}
