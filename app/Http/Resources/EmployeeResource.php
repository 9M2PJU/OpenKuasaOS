<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'org_id' => $this->org_id,
            'user_id' => $this->user_id,
            'employee_number' => $this->employee_number,
            'full_name' => $this->full_name,
            'ic_passport_number' => $this->ic_passport_number,
            'department_id' => $this->department_id,
            'position' => $this->position,
            'join_date' => $this->join_date?->toDateString(),
            'basic_salary' => (float) $this->basic_salary,
            'epf_rate' => (float) $this->epf_rate,
            'status' => $this->status,
            'annual_leave_balance' => (float) $this->annual_leave_balance,
            'medical_leave_balance' => (float) $this->medical_leave_balance,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
