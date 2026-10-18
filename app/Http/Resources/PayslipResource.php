<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PayslipResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'org_id' => $this->org_id,
            'payroll_run_id' => $this->payroll_run_id,
            'employee_id' => $this->employee_id,
            'employee_name' => $this->employee?->full_name,
            'gross_pay' => (float) $this->gross_pay,
            'epf_employee' => (float) $this->epf_employee,
            'epf_employer' => (float) $this->epf_employer,
            'socso_employee' => (float) $this->socso_employee,
            'socso_employer' => (float) $this->socso_employer,
            'eis_employee' => (float) $this->eis_employee,
            'eis_employer' => (float) $this->eis_employer,
            'pcb_amount' => (float) $this->pcb_amount,
            'net_pay' => (float) $this->net_pay,
            'status' => $this->status,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
