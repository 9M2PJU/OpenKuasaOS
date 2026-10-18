<?php

namespace App\Services\HR;

use App\Exceptions\InsufficientLeaveBalanceException;
use App\Exceptions\LeaveOverlapException;
use App\Models\HrEmployee;
use App\Models\HrLeaveRequest;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;

class LeaveApprovalService
{
    /**
     * Submit a new leave request after checking for date overlaps and quota availability.
     */
    public function requestLeave(
        HrEmployee $employee,
        string $leaveType,
        string $startDate,
        string $endDate,
        ?string $reason = null
    ): HrLeaveRequest {
        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->startOfDay();

        if ($end->lt($start)) {
            throw new Exception("Tarikh akhir cuti tidak boleh mendahului tarikh mula.");
        }

        // Calculate days count
        $daysCount = $start->diffInDays($end) + 1;

        // Check for date overlaps with existing pending or approved requests
        $hasOverlap = HrLeaveRequest::withoutGlobalScopes()
            ->where('employee_id', $employee->id)
            ->whereIn('status', ['pending', 'approved'])
            ->where(function ($query) use ($startDate, $endDate) {
                $query->where('start_date', '<=', $endDate)
                    ->where('end_date', '>=', $startDate);
            })
            ->exists();

        if ($hasOverlap) {
            throw new LeaveOverlapException("Terdapat permohonan cuti sedia ada yang bertindih dengan tarikh yang dipilih.");
        }

        // Check quota balance
        if ($leaveType === 'annual' && (float) $employee->annual_leave_balance < $daysCount) {
            throw new InsufficientLeaveBalanceException("Baki cuti tahunan (RM {$employee->annual_leave_balance} hari) tidak mencukupi untuk memohon {$daysCount} hari.");
        }

        if ($leaveType === 'medical' && (float) $employee->medical_leave_balance < $daysCount) {
            throw new InsufficientLeaveBalanceException("Baki cuti sakit (RM {$employee->medical_leave_balance} hari) tidak mencukupi untuk memohon {$daysCount} hari.");
        }

        return HrLeaveRequest::withoutGlobalScopes()->create([
            'org_id' => $employee->org_id,
            'employee_id' => $employee->id,
            'leave_type' => $leaveType,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'days_count' => $daysCount,
            'reason' => $reason,
            'status' => 'pending',
        ]);
    }

    /**
     * Approve a pending leave request and deduct the employee's leave balance.
     */
    public function approveLeave(HrLeaveRequest $request, ?string $approverId = null): HrLeaveRequest
    {
        return DB::transaction(function () use ($request, $approverId) {
            $lockedRequest = HrLeaveRequest::withoutGlobalScopes()
                ->where('id', $request->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedRequest->status !== 'pending') {
                throw new Exception("Hanya permohonan cuti berstatus [pending] boleh diluluskan.");
            }

            $employee = HrEmployee::withoutGlobalScopes()
                ->where('id', $lockedRequest->employee_id)
                ->lockForUpdate()
                ->firstOrFail();

            $days = (float) $lockedRequest->days_count;

            if ($lockedRequest->leave_type === 'annual') {
                if ((float) $employee->annual_leave_balance < $days) {
                    throw new InsufficientLeaveBalanceException("Baki cuti tahunan tidak mencukupi semasa kelulusan.");
                }
                $employee->decrement('annual_leave_balance', $days);
            } elseif ($lockedRequest->leave_type === 'medical') {
                if ((float) $employee->medical_leave_balance < $days) {
                    throw new InsufficientLeaveBalanceException("Baki cuti sakit tidak mencukupi semasa kelulusan.");
                }
                $employee->decrement('medical_leave_balance', $days);
            }

            $lockedRequest->status = 'approved';
            $lockedRequest->approver_id = $approverId;
            $lockedRequest->save();

            return $lockedRequest;
        });
    }

    /**
     * Reject a pending leave request with a stated reason.
     */
    public function rejectLeave(HrLeaveRequest $request, string $reason, ?string $approverId = null): HrLeaveRequest
    {
        return DB::transaction(function () use ($request, $reason, $approverId) {
            $lockedRequest = HrLeaveRequest::withoutGlobalScopes()
                ->where('id', $request->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedRequest->status !== 'pending') {
                throw new Exception("Hanya permohonan cuti berstatus [pending] boleh ditolak.");
            }

            $lockedRequest->status = 'rejected';
            $lockedRequest->rejection_reason = $reason;
            $lockedRequest->approver_id = $approverId;
            $lockedRequest->save();

            return $lockedRequest;
        });
    }
}
