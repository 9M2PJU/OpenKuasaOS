<?php

namespace App\Http\Controllers\Api\V1\Finance;

use App\Http\Controllers\Concerns\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\FinanceAccount;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    use ApiResponse;

    public function index(): JsonResponse
    {
        $accounts = FinanceAccount::orderBy('name')->get();

        return $this->success($accounts, 'Senarai akaun kewangan berjaya dimuat turun.');
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', 'in:bank,cash,credit_card'],
            'account_number' => ['nullable', 'string', 'max:100'],
            'currency' => ['nullable', 'string', 'size:3'],
            'balance' => ['nullable', 'numeric'],
        ]);

        $account = FinanceAccount::create($validated);

        return $this->success($account, 'Akaun kewangan berjaya ditambah.', null, 201);
    }
}
