<?php

namespace App\Http\Controllers\Api\V1\People;

use App\Http\Controllers\Concerns\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\EmployeeResource;
use App\Models\HrEmployee;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $query = HrEmployee::orderBy('full_name');

        if ($request->filled('status')) {
            $query->where('status', $request->query('status'));
        }

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->query('department_id'));
        }

        $employees = $query->paginate(20);

        return $this->success(
            EmployeeResource::collection($employees->items()),
            'Senarai staf berjaya dimuat turun.',
            [
                'page' => $employees->currentPage(),
                'per_page' => $employees->perPage(),
                'total' => $employees->total(),
            ]
        );
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'employee_number' => ['required', 'string', 'max:50'],
            'full_name' => ['required', 'string', 'max:255'],
            'ic_passport_number' => ['required', 'string', 'max:50'],
            'department_id' => ['nullable', 'string'],
            'position' => ['required', 'string', 'max:255'],
            'join_date' => ['required', 'date'],
            'basic_salary' => ['required', 'numeric', 'min:0'],
            'epf_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'status' => ['nullable', 'string', 'in:active,resigned,terminated'],
            'annual_leave_balance' => ['nullable', 'numeric'],
            'medical_leave_balance' => ['nullable', 'numeric'],
        ]);

        $employee = HrEmployee::create($validated);

        return $this->success(
            new EmployeeResource($employee),
            'Pekerja berjaya didaftarkan.',
            null,
            201
        );
    }

    public function show(string $id): JsonResponse
    {
        $employee = HrEmployee::findOrFail($id);

        return $this->success(
            new EmployeeResource($employee),
            'Maklumat staf berjaya diperolehi.'
        );
    }
}
