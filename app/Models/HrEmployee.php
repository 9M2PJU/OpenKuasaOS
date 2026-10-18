<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HrEmployee extends Model
{
    use BelongsToTenant, HasFactory, HasUuids;

    protected $table = 'hr_employees';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'org_id',
        'user_id',
        'employee_number',
        'full_name',
        'ic_passport_number',
        'department_id',
        'position',
        'join_date',
        'basic_salary',
        'epf_rate',
        'status', // active, resigned, terminated
        'annual_leave_balance',
        'medical_leave_balance',
    ];

    protected function casts(): array
    {
        return [
            'join_date' => 'date',
            'basic_salary' => 'decimal:2',
            'epf_rate' => 'decimal:2',
            'annual_leave_balance' => 'decimal:1',
            'medical_leave_balance' => 'decimal:1',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(HrAttendanceDay::class, 'employee_id');
    }

    public function leaveRequests(): HasMany
    {
        return $this->hasMany(HrLeaveRequest::class, 'employee_id');
    }

    public function claims(): HasMany
    {
        return $this->hasMany(HrClaim::class, 'employee_id');
    }

    public function payslips(): HasMany
    {
        return $this->hasMany(HrPayslip::class, 'employee_id');
    }
}
