<?php

namespace App\Http\Controllers\Api\V1\People;

use App\Http\Controllers\Concerns\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\People\StoreLeaveRequest;
use App\Http\Resources\LeaveRequestResource;
use App\Models\HrEmployee;
use App\Models\HrLeaveRequest;
use App\Services\HR\LeaveApprovalService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LeaveController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected LeaveApprovalService $leaveService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $query = HrLeaveRequest::with('employee')
            ->orderByDesc('created_at');

        if ($request->filled('status')) {
            $query->where('status', $request->query('status'));
        }

        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->query('employee_id'));
        }

        $requests = $query->paginate(20);

        return $this->success(
            LeaveRequestResource::collection($requests->items()),
            'Senarai permohonan cuti berjaya dimuat turun.',
            [
                'page' => $requests->currentPage(),
                'per_page' => $requests->perPage(),
                'total' => $requests->total(),
            ]
        );
    }

    public function store(StoreLeaveRequest $request): JsonResponse
    {
        $user = $request->user();
        $employeeId = $request->input('employee_id');

        $employee = null;
        if ($employeeId) {
            $employee = HrEmployee::findOrFail($employeeId);
        } else {
            $employee = HrEmployee::where('user_id', $user->id)->first() ?? HrEmployee::first();
        }

        if (!$employee) {
            return $this->error('Rekod pekerja tidak ditemui.', 'EMPLOYEE_NOT_FOUND', null, 404);
        }

        try {
            $leave = $this->leaveService->requestLeave(
                $employee,
                $request->input('leave_type'),
                $request->input('start_date'),
                $request->input('end_date'),
                $request->input('reason')
            );

            return $this->success(
                new LeaveRequestResource($leave->load('employee')),
                'Permohonan cuti berjaya dihantar.',
                null,
                201
            );
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 'LEAVE_REQUEST_FAILED', null, 422);
        }
    }

    public function approve(Request $request, string $id): JsonResponse
    {
        $leave = HrLeaveRequest::findOrFail($id);

        try {
            $approved = $this->leaveService->approveLeave($leave, $request->user()?->id);

            return $this->success(
                new LeaveRequestResource($approved->load('employee')),
                'Permohonan cuti telah diluluskan dan baki cuti telah ditolak.'
            );
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 'LEAVE_APPROVAL_FAILED', null, 422);
        }
    }

    public function reject(Request $request, string $id): JsonResponse
    {
        $leave = HrLeaveRequest::findOrFail($id);
        $reason = $request->input('reason', 'Permohonan ditolak oleh pentadbir.');

        try {
            $rejected = $this->leaveService->rejectLeave($leave, $reason, $request->user()?->id);

            return $this->success(
                new LeaveRequestResource($rejected->load('employee')),
                'Permohonan cuti telah ditolak.'
            );
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 'LEAVE_REJECTION_FAILED', null, 422);
        }
    }
}
