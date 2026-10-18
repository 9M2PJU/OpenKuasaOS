<?php

namespace App\Http\Controllers\Api\V1\Hire;

use App\Http\Controllers\Concerns\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Hire\StoreApplicationRequest;
use App\Http\Requests\Hire\StoreJobRequest;
use App\Http\Resources\JobResource;
use App\Models\HireApplication;
use App\Models\HireCandidate;
use App\Models\HireInterview;
use App\Models\HireJob;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HireController extends Controller
{
    use ApiResponse;

    public function jobs(Request $request): JsonResponse
    {
        $query = HireJob::withCount('applications')->orderByDesc('created_at');

        if ($request->filled('status')) {
            $query->where('status', $request->query('status'));
        }

        $jobs = $query->paginate(20);

        return $this->success(
            JobResource::collection($jobs->items()),
            'Senarai jawatan kosong berjaya dimuat turun.',
            [
                'page' => $jobs->currentPage(),
                'per_page' => $jobs->perPage(),
                'total' => $jobs->total(),
            ]
        );
    }

    public function storeJob(StoreJobRequest $request): JsonResponse
    {
        $job = HireJob::create($request->validated());

        return $this->success(
            new JobResource($job),
            'Tawaran kerja berjaya dicipta.',
            null,
            201
        );
    }

    public function showJob(string $id): JsonResponse
    {
        $job = HireJob::with('applications')->findOrFail($id);

        return $this->success(
            new JobResource($job),
            'Maklumat tawaran kerja berjaya diperolehi.'
        );
    }

    public function applications(Request $request): JsonResponse
    {
        $query = HireApplication::with(['job', 'candidate', 'interviews'])
            ->orderByDesc('created_at');

        if ($request->filled('job_id')) {
            $query->where('job_id', $request->query('job_id'));
        }

        if ($request->filled('stage')) {
            $query->where('stage', $request->query('stage'));
        }

        $apps = $query->paginate(20);

        return $this->success(
            $apps->items(),
            'Senarai permohonan kerja berjaya dimuat turun.',
            [
                'page' => $apps->currentPage(),
                'per_page' => $apps->perPage(),
                'total' => $apps->total(),
            ]
        );
    }

    public function storeApplication(StoreApplicationRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $app = DB::transaction(function () use ($validated, $request) {
            $job = HireJob::withoutGlobalScopes()->findOrFail($validated['job_id']);

            // Find or create candidate
            $candidate = HireCandidate::firstOrCreate(
                [
                    'org_id' => $job->org_id,
                    'email' => $validated['email'],
                ],
                [
                    'name' => $validated['candidate_name'],
                    'phone' => $validated['phone'] ?? null,
                ]
            );

            return HireApplication::create([
                'org_id' => $job->org_id,
                'job_id' => $job->id,
                'candidate_id' => $candidate->id,
                'candidate_name' => $validated['candidate_name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? null,
                'cv_file_url' => $validated['cv_file_url'] ?? null,
                'stage' => 'applied',
                'status' => 'active',
            ]);
        });

        return $this->success($app->load(['job', 'candidate']), 'Permohonan kerja berjaya dihantar.', null, 201);
    }

    public function updateApplicationStage(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'stage' => ['required', 'string', 'in:applied,screening,interview,offered,hired,rejected'],
        ]);

        $app = HireApplication::findOrFail($id);
        $app->stage = $validated['stage'];
        $app->save();

        return $this->success($app, 'Peringkat permohonan calon berjaya dikemaskini.');
    }

    public function interviews(Request $request): JsonResponse
    {
        $query = HireInterview::with(['application.job', 'interviewer'])
            ->orderBy('scheduled_at');

        $interviews = $query->paginate(20);

        return $this->success(
            $interviews->items(),
            'Senarai sesi temu duga berjaya diperolehi.',
            [
                'page' => $interviews->currentPage(),
                'per_page' => $interviews->perPage(),
                'total' => $interviews->total(),
            ]
        );
    }

    public function storeInterview(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'application_id' => ['required', 'string', 'exists:hire_applications,id'],
            'interviewer_id' => ['nullable', 'string', 'exists:users,id'],
            'scheduled_at' => ['required', 'date'],
            'mode' => ['nullable', 'string', 'in:online,in_person'],
            'meeting_link' => ['nullable', 'string'],
        ]);

        $interview = HireInterview::create($validated);

        return $this->success($interview, 'Jadual temu duga berjaya disimpan.', null, 201);
    }
}
