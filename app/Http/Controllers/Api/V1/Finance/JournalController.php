<?php

namespace App\Http\Controllers\Api\V1\Finance;

use App\Http\Controllers\Concerns\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\FinanceJournal;
use App\Models\FinanceLedgerAccount;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class JournalController extends Controller
{
    use ApiResponse;

    public function index(): JsonResponse
    {
        $journals = FinanceJournal::with(['lines.ledgerAccount'])
            ->orderByDesc('entry_date')
            ->orderByDesc('created_at')
            ->paginate(20);

        return $this->success(
            $journals->items(),
            'Senarai jurnal berjaya diperolehi.',
            [
                'page' => $journals->currentPage(),
                'per_page' => $journals->perPage(),
                'total' => $journals->total(),
            ]
        );
    }

    public function ledgerAccounts(): JsonResponse
    {
        $accounts = FinanceLedgerAccount::orderBy('code')->get();

        return $this->success($accounts, 'Senarai carta akaun (COA) berjaya dimuat turun.');
    }
}
