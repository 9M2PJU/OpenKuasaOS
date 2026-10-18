<?php

namespace App\Http\Controllers\Api\V1\Crm;

use App\Http\Controllers\Concerns\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Crm\StoreDealRequest;
use App\Http\Resources\DealResource;
use App\Models\CrmDeal;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DealController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $query = CrmDeal::with('contact')
            ->orderBy('order')
            ->orderByDesc('created_at');

        if ($request->filled('stage_id')) {
            $query->where('stage_id', $request->query('stage_id'));
        }

        $deals = $query->get();

        return $this->success(
            DealResource::collection($deals),
            'Senarai urus niaga (deals) berjaya diperolehi.'
        );
    }

    public function store(StoreDealRequest $request): JsonResponse
    {
        $deal = CrmDeal::create($request->validated());

        return $this->success(
            new DealResource($deal->load('contact')),
            'Urus niaga (deal) berjaya dicipta.',
            null,
            201
        );
    }

    public function updateStage(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'stage_id' => ['required', 'string'],
            'order' => ['nullable', 'integer'],
        ]);

        $deal = CrmDeal::findOrFail($id);
        $deal->stage_id = $validated['stage_id'];
        if (isset($validated['order'])) {
            $deal->order = $validated['order'];
        }
        $deal->save();

        return $this->success(
            new DealResource($deal->fresh('contact')),
            'Peringkat urus niaga berjaya dikemaskini.'
        );
    }
}
