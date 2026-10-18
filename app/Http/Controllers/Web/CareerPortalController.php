<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\HireApplication;
use App\Models\HireCandidate;
use App\Models\HireJob;
use App\Services\Hire\AiResumeScreeningService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CareerPortalController extends Controller
{
    public function __construct(
        protected AiResumeScreeningService $aiScreeningService
    ) {}

    /**
     * Find job safely by either UUID or slug without tenant session restriction.
     */
    protected function findJob(string $idOrSlug): HireJob
    {
        $query = HireJob::withoutGlobalScopes()->with('org');

        if (Str::isUuid($idOrSlug)) {
            return $query->where(function ($q) use ($idOrSlug) {
                $q->where('id', $idOrSlug)->orWhere('slug', $idOrSlug);
            })->firstOrFail();
        }

        return $query->where('slug', $idOrSlug)->firstOrFail();
    }

    /**
     * Display public career opening and application form.
     */
    public function show($idOrSlug)
    {
        $job = $this->findJob($idOrSlug);

        return view('careers.show', compact('job'));
    }

    /**
     * Parse resume file uploaded upfront, extract candidate info with AI to auto-fill form.
     */
    public function parseResume(Request $request, $idOrSlug)
    {
        $job = $this->findJob($idOrSlug);

        $request->validate([
            'cv_file' => ['required', 'file', 'mimes:pdf,doc,docx,txt,rtf', 'max:5120'],
        ], [
            'cv_file.required' => 'Sila pilih fail Resume / CV untuk dimuat naik.',
            'cv_file.mimes' => 'Resume mestilah dalam format PDF, DOC, DOCX, atau TXT.',
            'cv_file.max' => 'Saiz fail resume tidak boleh melebihi 5MB.',
        ]);

        $file = $request->file('cv_file');
        $storedPath = $file->store('resumes', 'public');
        $cvFileUrl = '/storage/' . $storedPath;

        $extractedText = $this->aiScreeningService->extractResumeText($cvFileUrl);
        $candidateData = $this->aiScreeningService->parseResumeData($extractedText, $job->org_id);

        return response()->json([
            'success' => true,
            'message' => 'Resume berjaya dianalisis oleh AI. Sila semak maklumat yang telah diisi.',
            'data' => $candidateData,
            'file_url' => $cvFileUrl,
            'file_name' => $file->getClientOriginalName(),
        ]);
    }

    /**
     * Handle candidate application submission & trigger AI resume screening.
     */
    public function apply(Request $request, $idOrSlug)
    {
        $job = $this->findJob($idOrSlug);

        $hasCvFile = $request->hasFile('cv_file');
        $uploadedCvUrl = $request->input('uploaded_cv_url');

        if (!$hasCvFile && empty($uploadedCvUrl)) {
            return back()->withErrors(['cv_file' => 'Sila muat naik fail Resume atau CV anda.'])->withInput();
        }

        $rules = [
            'candidate_name' => ['required', 'string', 'max:255'],
            'birth_date' => ['required', 'date', 'before:today'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:50'],
            'address' => ['required', 'string', 'max:1000'],
            'cover_letter' => ['nullable', 'string', 'max:3000'],
        ];

        if ($hasCvFile) {
            $rules['cv_file'] = ['file', 'mimes:pdf,doc,docx,txt,rtf', 'max:5120'];
        }

        $validated = $request->validate($rules, [
            'candidate_name.required' => 'Sila masukkan nama penuh anda.',
            'birth_date.required' => 'Tarikh lahir diperlukan bagi pengesahan umur calon.',
            'birth_date.before' => 'Tarikh lahir tidak sah.',
            'email.required' => 'Alamat emel diperlukan untuk pihak HR menghubungi anda.',
            'email.email' => 'Format emel tidak sah.',
            'phone.required' => 'Nombor telefon atau WhatsApp diperlukan.',
            'address.required' => 'Sila nyatakan alamat kediaman lengkap anda.',
            'cv_file.mimes' => 'Resume mestilah dalam format PDF, DOC, DOCX, atau TXT.',
            'cv_file.max' => 'Saiz resume tidak boleh melebihi 5MB.',
        ]);

        // 1. Resolve CV file URL
        if ($hasCvFile) {
            $cvFile = $request->file('cv_file');
            $storedPath = $cvFile->store('resumes', 'public');
            $cvFileUrl = '/storage/' . $storedPath;
        } else {
            $cvFileUrl = $uploadedCvUrl;
        }

        // 2. Create or update candidate record
        $candidate = HireCandidate::withoutGlobalScopes()->firstOrCreate(
            [
                'org_id' => $job->org_id,
                'email' => strtolower(trim($validated['email'])),
            ],
            [
                'name' => trim($validated['candidate_name']),
                'phone' => trim($validated['phone']),
                'birth_date' => $validated['birth_date'],
                'address' => trim($validated['address']),
                'notes' => 'Permohonan melalui portal kerjaya rasmi.',
            ]
        );

        if (!$candidate->wasRecentlyCreated) {
            $candidate->update(array_filter([
                'name' => trim($validated['candidate_name']),
                'phone' => trim($validated['phone']),
                'birth_date' => $validated['birth_date'],
                'address' => trim($validated['address']),
            ]));
        }

        // 3. Create application
        $application = HireApplication::withoutGlobalScopes()->create([
            'org_id' => $job->org_id,
            'job_id' => $job->id,
            'candidate_id' => $candidate->id,
            'candidate_name' => trim($validated['candidate_name']),
            'email' => strtolower(trim($validated['email'])),
            'phone' => trim($validated['phone']),
            'birth_date' => $validated['birth_date'],
            'address' => trim($validated['address']),
            'cover_letter' => $validated['cover_letter'] ?? null,
            'cv_file_url' => $cvFileUrl,
            'stage' => 'applied',
            'status' => 'active',
        ]);

        // 4. Auto-evaluate with AI Resume Screening Service
        $aiResult = $this->aiScreeningService->screen($application);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Permohonan anda berjaya dihantar ke HR.',
                'application_id' => $application->id,
                'ai_status' => $application->ai_status,
                'ai_score' => $application->ai_score,
            ]);
        }

        return view('careers.success', [
            'job' => $job,
            'application' => $application,
            'aiResult' => $aiResult,
        ]);
    }
}
