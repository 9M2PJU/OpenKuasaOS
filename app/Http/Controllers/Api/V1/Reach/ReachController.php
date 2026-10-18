<?php

namespace App\Http\Controllers\Api\V1\Reach;

use App\Http\Controllers\Concerns\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\FormSubmission;
use App\Models\Lead;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReachController extends Controller
{
    use ApiResponse;

    public function campaigns(): JsonResponse
    {
        $campaigns = Campaign::withCount('leads')
            ->orderByDesc('created_at')
            ->get();

        return $this->success($campaigns, 'Senarai kempen pemasaran berjaya dimuat turun.');
    }

    public function storeCampaign(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'platform' => ['required', 'string', 'max:100'],
            'budget' => ['nullable', 'numeric', 'min:0'],
            'status' => ['nullable', 'string', 'in:draft,active,paused,completed'],
        ]);

        $campaign = Campaign::create($validated);

        return $this->success($campaign, 'Kempen berjaya dicipta.', null, 201);
    }

    public function leads(Request $request): JsonResponse
    {
        $query = Lead::with('campaign')->orderByDesc('created_at');

        if ($request->filled('campaign_id')) {
            $query->where('campaign_id', $request->query('campaign_id'));
        }

        $leads = $query->paginate(20);

        return $this->success(
            $leads->items(),
            'Senarai prospek (leads) berjaya diperolehi.',
            [
                'page' => $leads->currentPage(),
                'per_page' => $leads->perPage(),
                'total' => $leads->total(),
            ]
        );
    }

    public function storeLead(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'campaign_id' => ['nullable', 'string', 'exists:campaigns,id'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'source' => ['nullable', 'string'],
        ]);

        $lead = Lead::create($validated);

        return $this->success($lead, 'Prospek baharu berjaya didaftarkan.', null, 201);
    }

    public function submitForm(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'form_id' => ['nullable', 'string'],
            'data' => ['required', 'array'],
        ]);

        $submission = FormSubmission::create($validated);

        // Optionally create lead automatically if name/email present in form
        $data = $validated['data'];
        if (!empty($data['name']) || !empty($data['email'])) {
            Lead::create([
                'name' => $data['name'] ?? 'Form Submitter',
                'email' => $data['email'] ?? null,
                'phone' => $data['phone'] ?? null,
                'source' => 'web_form',
            ]);
        }

        return $this->success($submission, 'Borang permohonan/leads berjaya dihantar.', null, 201);
    }
}
