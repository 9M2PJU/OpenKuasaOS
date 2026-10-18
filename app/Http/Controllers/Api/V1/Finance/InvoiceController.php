<?php

namespace App\Http\Controllers\Api\V1\Finance;

use App\Http\Controllers\Concerns\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Finance\RecordPaymentRequest;
use App\Http\Requests\Finance\StoreInvoiceRequest;
use App\Http\Resources\InvoiceResource;
use App\Models\FinanceAccount;
use App\Models\FinanceInvoice;
use App\Models\FinanceInvoiceLine;
use App\Services\Finance\BendaharaPostingService;
use App\Services\Finance\SstService;
use App\Services\Tenancy\OrganizationContext;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class InvoiceController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected BendaharaPostingService $postingService
    ) {}

    /**
     * Display a listing of invoices.
     */
    public function index(Request $request): JsonResponse
    {
        $query = QueryBuilder::for(FinanceInvoice::class)
            ->allowedFilters(
                'status',
                AllowedFilter::partial('invoice_number'),
                AllowedFilter::partial('customer_name')
            )
            ->with(['lines'])
            ->orderByDesc('created_at');

        if ($request->filled('search')) {
            $search = $request->query('search');
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'ILIKE', "%{$search}%")
                    ->orWhere('customer_name', 'ILIKE', "%{$search}%");
            });
        }

        $perPage = (int) $request->query('per_page', 20);
        $paginated = $query->paginate($perPage);

        return $this->success(
            InvoiceResource::collection($paginated->items()),
            'Senarai invois berjaya dimuat turun.',
            [
                'page' => $paginated->currentPage(),
                'per_page' => $paginated->perPage(),
                'total' => $paginated->total(),
            ]
        );
    }

    /**
     * Store a newly created draft invoice.
     */
    public function store(StoreInvoiceRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $invoice = DB::transaction(function () use ($validated) {
            $subtotal = 0.00;
            $lineItems = [];

            foreach ($validated['lines'] as $line) {
                $qty = (float) $line['quantity'];
                $price = (float) $line['unit_price'];
                $lineTotal = round($qty * $price, 2);
                $subtotal += $lineTotal;

                $lineItems[] = [
                    'product_id' => $line['product_id'] ?? null,
                    'description' => $line['description'],
                    'quantity' => $qty,
                    'unit_price' => $price,
                    'tax_code' => $line['tax_code'] ?? null,
                    'total' => $lineTotal,
                ];
            }

            $sstRate = (float) ($validated['sst_rate'] ?? 0.00);
            $calc = SstService::calculateTotal($subtotal, $sstRate);

            $invoice = FinanceInvoice::create([
                'org_id' => OrganizationContext::getId(),
                'customer_id' => $validated['customer_id'] ?? null,
                'customer_name' => $validated['customer_name'] ?? null,
                'customer_email' => $validated['customer_email'] ?? null,
                'status' => 'draft',
                'issue_date' => $validated['issue_date'],
                'due_date' => $validated['due_date'],
                'subtotal' => $calc['subtotal'],
                'sst_rate' => $calc['sst_rate'],
                'sst_amount' => $calc['sst_amount'],
                'total_amount' => $calc['total_amount'],
                'notes' => $validated['notes'] ?? null,
            ]);

            foreach ($lineItems as $item) {
                FinanceInvoiceLine::create([
                    'org_id' => $invoice->org_id,
                    'invoice_id' => $invoice->id,
                    'product_id' => $item['product_id'],
                    'description' => $item['description'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'tax_code' => $item['tax_code'],
                    'total' => $item['total'],
                ]);
            }

            return $invoice->load('lines');
        });

        return $this->success(
            new InvoiceResource($invoice),
            'Draf invois berjaya dicipta.',
            null,
            201
        );
    }

    /**
     * Display the specified invoice.
     */
    public function show(string $id): JsonResponse
    {
        $invoice = FinanceInvoice::with(['lines', 'receipts'])->findOrFail($id);

        return $this->success(
            new InvoiceResource($invoice),
            'Maklumat invois berjaya dimuat turun.'
        );
    }

    /**
     * Post and lock the invoice, assigning official sequential number & creating journal entries.
     */
    public function post(string $id): JsonResponse
    {
        $invoice = FinanceInvoice::with('lines')->findOrFail($id);

        try {
            $posted = $this->postingService->postInvoice($invoice);
            return $this->success(
                new InvoiceResource($posted->load('lines')),
                'Invois berjaya dipos dan dicatat ke dalam lejar am.'
            );
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 'POSTING_FAILED', null, 422);
        }
    }

    /**
     * Record payment receipt for the invoice.
     */
    public function recordPayment(RecordPaymentRequest $request, string $id): JsonResponse
    {
        $invoice = FinanceInvoice::findOrFail($id);
        $account = FinanceAccount::findOrFail($request->input('account_id'));
        $amount = (float) $request->input('amount');

        try {
            $receipt = $this->postingService->recordPayment(
                $invoice,
                $account,
                $amount,
                $request->only(['payment_date', 'payment_method', 'reference', 'notes'])
            );

            return $this->success([
                'receipt' => $receipt,
                'invoice' => new InvoiceResource($invoice->fresh(['lines', 'receipts'])),
            ], 'Bayaran invois berjaya direkodkan.');
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 'PAYMENT_FAILED', null, 422);
        }
    }

    /**
     * Void the invoice and reverse ledger entries if posted.
     */
    public function void(Request $request, string $id): JsonResponse
    {
        $invoice = FinanceInvoice::findOrFail($id);
        $reason = $request->input('reason', 'Dibatalkan oleh pentadbir.');

        try {
            $voided = $this->postingService->voidInvoice($invoice, $reason);

            return $this->success(
                new InvoiceResource($voided->fresh(['lines', 'receipts'])),
                'Invois telah berjaya dibatalkan.'
            );
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 'VOID_FAILED', null, 422);
        }
    }
}
