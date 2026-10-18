<?php

namespace App\Http\Controllers\Api\V1\People;

use App\Http\Controllers\Concerns\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\People\ClockInRequest;
use App\Models\HrAttendanceDay;
use App\Models\HrEmployee;
use App\Services\Tenancy\OrganizationContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $query = HrAttendanceDay::with('employee')
            ->orderByDesc('date')
            ->orderByDesc('created_at');

        if ($request->filled('date')) {
            $query->where('date', $request->query('date'));
        }

        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->query('employee_id'));
        }

        $attendances = $query->paginate(20);

        return $this->success(
            $attendances->items(),
            'Senarai rekod kehadiran berjaya dimuat turun.',
            [
                'page' => $attendances->currentPage(),
                'per_page' => $attendances->perPage(),
                'total' => $attendances->total(),
            ]
        );
    }

    public function clockIn(ClockInRequest $request): JsonResponse
    {
        $user = $request->user();
        $employee = HrEmployee::where('user_id', $user->id)->first()
            ?? HrEmployee::first(); // Fallback for testing/demo

        if (!$employee) {
            return $this->error('Rekod profil staf tidak dijumpai untuk akaun ini.', 'EMPLOYEE_NOT_FOUND', null, 404);
        }

        $today = now()->toDateString();
        $currentTime = now()->toTimeString();

        $attendance = HrAttendanceDay::firstOrNew([
            'org_id' => OrganizationContext::getId(),
            'employee_id' => $employee->id,
            'date' => $today,
        ]);

        if ($attendance->clock_in) {
            return $this->error('Anda sudah merakamkan punch card masuk (clock-in) untuk hari ini.', 'ALREADY_CLOCKED_IN', null, 422);
        }

        $attendance->clock_in = $currentTime;
        $attendance->status = (now()->format('H:i') > '09:00') ? 'late' : 'present';
        $attendance->latitude = $request->input('latitude');
        $attendance->longitude = $request->input('longitude');
        $attendance->selfie_image = $request->input('selfie_image');
        $attendance->save();

        return $this->success($attendance, 'Punch card masuk (clock-in) berjaya direkodkan.');
    }

    public function clockOut(Request $request): JsonResponse
    {
        $user = $request->user();
        $employee = HrEmployee::where('user_id', $user->id)->first()
            ?? HrEmployee::first();

        if (!$employee) {
            return $this->error('Rekod profil staf tidak dijumpai.', 'EMPLOYEE_NOT_FOUND', null, 404);
        }

        $today = now()->toDateString();
        $attendance = HrAttendanceDay::where('employee_id', $employee->id)
            ->where('date', $today)
            ->first();

        if (!$attendance || !$attendance->clock_in) {
            return $this->error('Anda perlu clock-in terlebih dahulu sebelum clock-out.', 'NOT_CLOCKED_IN', null, 422);
        }

        $attendance->clock_out = now()->toTimeString();
        $attendance->save();

        return $this->success($attendance, 'Punch card keluar (clock-out) berjaya direkodkan.');
    }
}
