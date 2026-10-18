<?php

namespace App\Services\Hire;

use App\Models\AgentConfig;
use App\Models\HireApplication;
use App\Services\Ai\TuahAiService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AiResumeScreeningService
{
    public function __construct(
        protected TuahAiService $tuahAiService
    ) {}

    /**
     * Screen candidate application against job specification.
     */
    public function screen(HireApplication $application): array
    {
        $application->loadMissing(['job', 'candidate']);
        $job = $application->job;

        // 1. Extract text from uploaded CV
        $resumeText = $this->extractResumeText($application->cv_file_url);

        // 2. Compile candidate profile
        $candidateData = [
            'name' => $application->candidate_name,
            'email' => $application->email,
            'phone' => $application->phone,
            'birth_date' => $application->birth_date?->format('Y-m-d'),
            'age' => $application->age,
            'address' => $application->address,
            'cover_letter' => $application->cover_letter,
            'resume_snippet' => mb_substr($resumeText, 0, 3500),
        ];

        $jobData = [
            'title' => $job?->title ?? 'Jawatan Tidak Dinyatakan',
            'department' => $job?->department ?? 'Operasi',
            'employment_type' => $job?->employment_type ?? 'full_time',
            'description' => $job?->description ?? '',
            'requirements' => $job?->requirements ?? '',
            'salary_min' => $job?->salary_min,
            'salary_max' => $job?->salary_max,
        ];

        // 3. Attempt AI analysis via Tuah AI (Gemini or Ollama)
        $aiResult = null;
        try {
            $aiResult = $this->evaluateWithAi($application->org_id, $candidateData, $jobData);
        } catch (\Throwable $e) {
            Log::info("AI Resume Screening API notice: " . $e->getMessage());
        }

        // 4. Fallback to intelligent Malaysian SME heuristic evaluation if AI unavailable
        if (!$aiResult || empty($aiResult['score'])) {
            $aiResult = $this->evaluateWithHeuristic($candidateData, $jobData);
        }

        // 5. Update Application record with AI screening results
        $application->update([
            'ai_score' => (int) $aiResult['score'],
            'ai_status' => $aiResult['status'], // layak, pertimbangan, tidak_layak
            'ai_analysis' => $aiResult,
            'ai_reviewed_at' => now(),
            // Advance stage to screening if qualified and currently just 'applied'
            'stage' => ($application->stage === 'applied' && $aiResult['status'] === 'layak') ? 'screening' : $application->stage,
        ]);

        return $aiResult;
    }

    /**
     * Extract plain text from PDF, DOCX, or TXT resume files.
     */
    public function extractResumeText(?string $cvUrl): string
    {
        if (empty($cvUrl)) {
            return '';
        }

        // Normalize URL to local storage path
        $relativePath = Str::after($cvUrl, '/storage/');
        $relativePath = ltrim($relativePath, '/');

        $filePath = null;

        if (Storage::disk('public')->exists($relativePath)) {
            $extension = strtolower(pathinfo($relativePath, PATHINFO_EXTENSION));
            if (in_array($extension, ['txt', 'md', 'rtf', 'csv'])) {
                return (string) Storage::disk('public')->get($relativePath);
            }
            $filePath = Storage::disk('public')->path($relativePath);
        } else {
            $storagePath = storage_path('app/public/' . $relativePath);
            if (file_exists($storagePath)) {
                $filePath = $storagePath;
            } else {
                $publicPath = public_path(ltrim($cvUrl, '/'));
                if (file_exists($publicPath)) {
                    $filePath = $publicPath;
                } else {
                    return '';
                }
            }
        }

        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        // 1. Plain text or markdown
        if (in_array($extension, ['txt', 'md', 'rtf', 'csv'])) {
            return (string) @file_get_contents($filePath);
        }

        // 2. PDF extraction using pdftotext
        if ($extension === 'pdf') {
            try {
                $escaped = escapeshellarg($filePath);
                $output = shell_exec("pdftotext {$escaped} - 2>/dev/null");
                if (!empty($output)) {
                    return trim($output);
                }
            } catch (\Throwable $e) {
                // Ignore and fallback
            }
        }

        // 3. DOCX extraction via ZipArchive
        if ($extension === 'docx' && class_exists('\ZipArchive')) {
            try {
                $zip = new \ZipArchive();
                if ($zip->open($filePath) === true) {
                    $xml = $zip->getFromName('word/document.xml');
                    $zip->close();
                    if ($xml !== false) {
                        return trim(strip_tags(str_replace(['<w:p>', '</w:p>'], ["\n", "\n"], $xml)));
                    }
                }
            } catch (\Throwable $e) {
                // Ignore and fallback
            }
        }

        return '';
    }

    /**
     * Evaluate candidate with Tuah AI engine (Gemini / Ollama).
     */
    protected function evaluateWithAi(string $orgId, array $candidate, array $job): ?array
    {
        $config = $this->tuahAiService->getConfig($orgId);

        $prompt = <<<PROMPT
Anda adalah Pakar Pengambilan Staf & HR Eksekutif AI bagi perniagaan di Malaysia (Lekir HR & ATS).
Tugasan anda adalah menyemak dan menilai kelayakan calon bagi jawatan berikut berdasarkan maklumat profil dan resume mereka.

JAWATAN YANG DIPOHON:
- Jawatan: {$job['title']}
- Jabatan: {$job['department']}
- Jenis Pekerjaan: {$job['employment_type']}
- Deskripsi Jawatan: {$job['description']}
- Keperluan & Syarat Kelayakan: {$job['requirements']}

PROFIL & RESUME CALON:
- Nama: {$candidate['name']}
- Umur: {$candidate['age']} tahun (Tarikh Lahir: {$candidate['birth_date']})
- Alamat/Lokasi: {$candidate['address']}
- Surat Iringan/Pengenalan: {$candidate['cover_letter']}
- Teks Resume / CV:
{$candidate['resume_snippet']}

PANDUAN PENILAIAN:
1. Skor Kelayakan (0 - 100):
   - 75 hingga 100: 'layak' (Memenuhi majoriti syarat dan kemahiran kritikal)
   - 50 hingga 74: 'pertimbangan' (Memenuhi sebahagian syarat, ada potensi jika dilatih)
   - 0 hingga 49: 'tidak_layak' (Tidak memenuhi syarat asas atau tidak berkaitan)
2. Berikan analisis dalam format JSON tulen TANPA sebarang teks lain atau markdown code block di luar JSON.

FORMAT JSON YANG DIWAJIBKAN:
{
  "score": 85,
  "status": "layak",
  "summary": "Ringkasan penilaian dalam 2-3 ayat profesional Bahasa Melayu...",
  "strengths": [
    "Kelebihan utama 1",
    "Kelebihan utama 2"
  ],
  "gaps": [
    "Kekurangan atau jurang kemahiran 1",
    "Kekurangan atau jurang kemahiran 2"
  ],
  "recommendation": "Disyorkan untuk Sesi Temuduga Pertama",
  "interview_questions": [
    "Soalan temuduga 1 berfokuskan pengalaman calon",
    "Soalan temuduga 2 untuk menguji jurang kemahiran",
    "Soalan temuduga 3 tentang kesesuaian budaya kerja"
  ]
}
PROMPT;

        $rawResponse = null;

        if ($config->provider === 'gemini') {
            $apiKey = $config->gemini_api_key ?: config('services.gemini.api_key');
            $model = $config->gemini_model ?: config('services.gemini.model', 'gemini-2.5-flash');

            if (empty($apiKey)) {
                return null;
            }

            $endpoint = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";
            $response = Http::timeout(20)->post($endpoint, [
                'contents' => [
                    ['role' => 'user', 'parts' => [['text' => $prompt]]],
                ],
                'generationConfig' => [
                    'temperature' => 0.2,
                    'responseMimeType' => 'application/json',
                ],
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $rawResponse = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;
            }
        } elseif ($config->provider === 'ollama') {
            $ollamaUrl = rtrim($config->ollama_url ?: config('services.ollama.url', 'http://127.0.0.1:11434'), '/');
            $model = $config->ollama_model ?: config('services.ollama.model', 'gemma4:e2b');

            $response = Http::timeout(20)->post("{$ollamaUrl}/api/generate", [
                'model' => $model,
                'prompt' => $prompt,
                'stream' => false,
                'format' => 'json',
                'options' => ['temperature' => 0.2],
            ]);

            if ($response->successful()) {
                $rawResponse = $response->json()['response'] ?? null;
            }
        }

        if (empty($rawResponse)) {
            return null;
        }

        // Clean any codeblock markers if model included them
        $cleanJson = preg_replace('/^```(?:json)?\s*/i', '', trim($rawResponse));
        $cleanJson = preg_replace('/```$/', '', trim($cleanJson));

        $parsed = json_decode($cleanJson, true);
        if (!is_array($parsed) || !isset($parsed['score'])) {
            return null;
        }

        $score = max(0, min(100, (int) $parsed['score']));
        $status = $score >= 75 ? 'layak' : ($score >= 50 ? 'pertimbangan' : 'tidak_layak');

        return [
            'score' => $score,
            'status' => $status,
            'summary' => $parsed['summary'] ?? "Penilaian AI merekodkan skor kelayakan {$score}%.",
            'strengths' => is_array($parsed['strengths'] ?? null) ? $parsed['strengths'] : ['Kelayakan asas memenuhi spesifikasi jawatan'],
            'gaps' => is_array($parsed['gaps'] ?? null) ? $parsed['gaps'] : ['Perlu pengesahan kemahiran teknikal lanjutan'],
            'recommendation' => $parsed['recommendation'] ?? ($status === 'layak' ? 'Disyorkan untuk Temuduga' : 'Perlu Semakan Manual HR'),
            'interview_questions' => is_array($parsed['interview_questions'] ?? null) ? $parsed['interview_questions'] : [
                'Bolehkah anda ceritakan pengalaman kerja terdahulu yang paling relevan dengan jawatan ini?',
                'Apakah matlamat kerjaya anda dalam tempoh 1 hingga 2 tahun akan datang?'
            ],
            'engine' => 'tuah_ai_' . $config->provider,
        ];
    }

    /**
     * Malaysian SME Heuristic Keyword & Experience Matching Engine (zero downtime).
     */
    public function evaluateWithHeuristic(array $candidate, array $job): array
    {
        $jobTitle = strtolower($job['title']);
        $jobReqs = strtolower($job['requirements'] . ' ' . $job['description']);
        $resumeCombined = strtolower(
            $candidate['name'] . ' ' .
            $candidate['cover_letter'] . ' ' .
            $candidate['resume_snippet'] . ' ' .
            $candidate['address']
        );

        $score = 50; // Starting baseline
        $strengths = [];
        $gaps = [];

        // 1. Title keyword matching
        $titleKeywords = array_filter(explode(' ', preg_replace('/[^a-zA-Z0-9]/', ' ', $jobTitle)), fn($w) => strlen($w) > 3);
        $titleMatches = 0;
        foreach ($titleKeywords as $keyword) {
            if (str_contains($resumeCombined, $keyword)) {
                $titleMatches++;
            }
        }

        if (!empty($titleKeywords)) {
            $titleRatio = $titleMatches / count($titleKeywords);
            if ($titleRatio >= 0.5) {
                $score += 20;
                $strengths[] = "Profil dan resume mempunyai padanan kata kunci jawatan '{$job['title']}'.";
            } else {
                $gaps[] = "Kata kunci jawatan utama kurang ketara dalam resume.";
            }
        }

        // 2. Qualifications & Education check
        $eduKeywords = ['diploma', 'ijazah', 'degree', 'bachelor', 'master', 'spm', 'sijil', 'certificate', 'universiti', 'kolej'];
        $hasEdu = false;
        foreach ($eduKeywords as $edu) {
            if (str_contains($resumeCombined, $edu)) {
                $hasEdu = true;
                break;
            }
        }
        if ($hasEdu) {
            $score += 10;
            $strengths[] = "Latar belakang pendidikan atau sijil kelayakan dinyatakan dengan jelas.";
        } else {
            $score -= 5;
            $gaps[] = "Sijil kelayakan akademik atau profesional tidak dinyatakan dengan jelas.";
        }

        // 3. Technical / SME Skills Matching
        $skillsChecklist = [
            'laravel', 'php', 'javascript', 'vue', 'react', 'sql', 'mysql', 'postgresql', 'tailwind', 'api',
            'sales', 'jualan', 'marketing', 'pemasaran', 'akaun', 'accounting', 'finance', 'admin', 'kerani',
            'customer service', 'support', 'komunikasi', 'pengurusan', 'leadership', 'canva', 'photoshop', 'video'
        ];

        $matchedSkills = [];
        foreach ($skillsChecklist as $skill) {
            if (str_contains($jobReqs, $skill) && str_contains($resumeCombined, $skill)) {
                $matchedSkills[] = ucfirst($skill);
            }
        }

        if (!empty($matchedSkills)) {
            $score += min(20, count($matchedSkills) * 6);
            $strengths[] = "Kemahiran teknikal padan: " . implode(', ', array_slice($matchedSkills, 0, 4));
        }

        // 4. Experience indicators
        $experienceKeywords = ['tahun', 'years', 'pengalaman', 'experience', 'senior', 'eksekutif', 'manager'];
        $hasExp = false;
        foreach ($experienceKeywords as $exp) {
            if (str_contains($resumeCombined, $exp)) {
                $hasExp = true;
                break;
            }
        }
        if ($hasExp) {
            $score += 10;
            $strengths[] = "Mempunyai rekod pengalaman kerja dan peranan terdahulu.";
        } else {
            $gaps[] = "Pengalaman industri terdahulu kelihatan minimum atau terhad.";
        }

        // 5. Age & Address completeness
        if (!empty($candidate['age']) && $candidate['age'] >= 18) {
            $score += 5;
            $strengths[] = "Memenuhi kriteria umur bekerja sah ({$candidate['age']} tahun).";
        }
        if (!empty($candidate['address'])) {
            $score += 5;
            $strengths[] = "Maklumat alamat kediaman lengkap dibekalkan.";
        }

        // Bound score
        $finalScore = max(25, min(95, $score));
        $status = $finalScore >= 75 ? 'layak' : ($finalScore >= 50 ? 'pertimbangan' : 'tidak_layak');

        $recommendation = match ($status) {
            'layak' => 'Disyorkan untuk Sesi Temuduga (Tahap Kelayakan Tinggi)',
            'pertimbangan' => 'KIV / Simpan untuk pertimbangan temuduga saringan kedua',
            'tidak_layak' => 'Tidak disyorkan buat masa ini kerana kurang menepati syarat utama',
        };

        $summary = "Calon {$candidate['name']} memperoleh skor kelayakan {$finalScore}% bagi posisi {$job['title']}. ";
        if ($status === 'layak') {
            $summary .= "Profil calon menunjukkan padanan kukuh dengan spesifikasi tugasan syarikat.";
        } elseif ($status === 'pertimbangan') {
            $summary .= "Calon mempunyai potensi asas namun memerlukan pengesahan kemahiran semasa temuduga.";
        } else {
            $summary .= "Maklumat resume calon belum memenuhi keperluan kritikal jawatan ini.";
        }

        return [
            'score' => $finalScore,
            'status' => $status,
            'summary' => $summary,
            'strengths' => !empty($strengths) ? $strengths : ['Maklumat pemohon lengkap'],
            'gaps' => !empty($gaps) ? $gaps : ['Tiada jurang kritikal dikesan'],
            'recommendation' => $recommendation,
            'interview_questions' => [
                "Bolehkah anda terangkan pengalaman kerja terdahulu anda dan bagaimana ia relevan dengan peranan {$job['title']}?",
                "Apakah cabaran terbesar yang pernah anda selesaikan dalam pekerjaan sebelum ini?",
                "Berapa lamakah tempoh notis (notice period) yang diperlukan sebelum anda boleh memulakan tugas?"
            ],
            'engine' => 'heuristic_engine',
        ];
    }

    /**
     * Parse resume text to auto-populate candidate form fields.
     */
    public function parseResumeData(string $resumeText, ?string $orgId = null): array
    {
        $aiParsed = null;
        if (!empty($orgId)) {
            try {
                $aiParsed = $this->parseResumeWithAi($resumeText, $orgId);
            } catch (\Throwable $e) {
                Log::info("AI Resume parsing API notice: " . $e->getMessage());
            }
        }

        $heuristic = $this->extractDataWithHeuristic($resumeText);

        return [
            'candidate_name' => !empty($aiParsed['candidate_name']) ? $aiParsed['candidate_name'] : ($heuristic['candidate_name'] ?? null),
            'birth_date' => !empty($aiParsed['birth_date']) ? $aiParsed['birth_date'] : ($heuristic['birth_date'] ?? null),
            'email' => !empty($aiParsed['email']) ? $aiParsed['email'] : ($heuristic['email'] ?? null),
            'phone' => !empty($aiParsed['phone']) ? $aiParsed['phone'] : ($heuristic['phone'] ?? null),
            'address' => !empty($aiParsed['address']) ? $aiParsed['address'] : ($heuristic['address'] ?? null),
            'cover_letter' => !empty($aiParsed['cover_letter']) ? $aiParsed['cover_letter'] : ($heuristic['cover_letter'] ?? null),
        ];
    }

    /**
     * Call AI to parse structured candidate data from resume text.
     */
    protected function parseResumeWithAi(string $resumeText, string $orgId): ?array
    {
        $config = $this->tuahAiService->getConfig($orgId);
        $snippet = mb_substr($resumeText, 0, 3500);

        $prompt = <<<PROMPT
Anda adalah AI pengekstrakan data resume calon kerja di Malaysia.
Sila analisis teks resume di bawah dan ekstrak maklumat peribadi calon ke dalam format JSON:

TEKS RESUME:
{$snippet}

PANDUAN EKSTRAKSI:
1. candidate_name: Nama penuh calon (string atau null).
2. birth_date: Tarikh lahir dalam format YYYY-MM-DD jika dinyatakan (atau null jika tiada).
3. email: Alamat emel rasmi calon (string atau null).
4. phone: Nombor telefon atau WhatsApp calon (string atau null).
5. address: Alamat kediaman lengkap atau bandar/negeri tempat tinggal (string atau null).
6. cover_letter: Ringkasan profesional, objektif kerjaya atau ringkasan pengalaman calon dalam 2-3 ayat Bahasa Melayu (string atau null).

FORMAT JSON TULEN (tiada markdown, tiada teks selain JSON):
{
  "candidate_name": "...",
  "birth_date": "YYYY-MM-DD",
  "email": "...",
  "phone": "...",
  "address": "...",
  "cover_letter": "..."
}
PROMPT;

        $rawResponse = null;

        if ($config->provider === 'gemini') {
            $apiKey = $config->gemini_api_key ?: config('services.gemini.api_key');
            $model = $config->gemini_model ?: config('services.gemini.model', 'gemini-2.5-flash');

            if (empty($apiKey)) {
                return null;
            }

            $endpoint = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";
            $response = Http::timeout(15)->post($endpoint, [
                'contents' => [
                    ['role' => 'user', 'parts' => [['text' => $prompt]]],
                ],
                'generationConfig' => [
                    'temperature' => 0.1,
                    'responseMimeType' => 'application/json',
                ],
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $rawResponse = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;
            }
        } elseif ($config->provider === 'ollama') {
            $ollamaUrl = rtrim($config->ollama_url ?: config('services.ollama.url', 'http://127.0.0.1:11434'), '/');
            $model = $config->ollama_model ?: config('services.ollama.model', 'gemma4:e2b');

            $response = Http::timeout(15)->post("{$ollamaUrl}/api/generate", [
                'model' => $model,
                'prompt' => $prompt,
                'stream' => false,
                'format' => 'json',
                'options' => ['temperature' => 0.1],
            ]);

            if ($response->successful()) {
                $rawResponse = $response->json()['response'] ?? null;
            }
        }

        if (empty($rawResponse)) {
            return null;
        }

        $cleanJson = preg_replace('/^```(?:json)?\s*/i', '', trim($rawResponse));
        $cleanJson = preg_replace('/```$/', '', trim($cleanJson));

        $parsed = json_decode($cleanJson, true);
        return is_array($parsed) ? $parsed : null;
    }

    /**
     * Malaysian heuristic regex extractor for zero-downtime resume parsing.
     */
    public function extractDataWithHeuristic(string $resumeText): array
    {
        $email = null;
        if (preg_match('/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/', $resumeText, $m)) {
            $email = strtolower($m[0]);
        }

        $phone = null;
        if (preg_match('/(?:\+?60\s*|0)[1-9][0-9\-\s]{7,14}/', $resumeText, $m)) {
            $phone = trim($m[0]);
        }

        $birthDate = null;
        if (preg_match('/(?:tarikh\s*lahir|date\s*of\s*birth|dob)[\s:]*([0-9]{1,4}[\/\-\.][0-9]{1,2}[\/\-\.][0-9]{1,4})/i', $resumeText, $m)) {
            $rawDate = trim($m[1]);
            try {
                $birthDate = \Carbon\Carbon::parse(str_replace(['.', '/'], '-', $rawDate))->format('Y-m-d');
            } catch (\Throwable $e) {
                // Ignore invalid date
            }
        } elseif (preg_match('/\b([12][0-9]{3}[\/\-\.](?:0[1-9]|1[0-2])[\/\-\.](?:0[1-9]|[12][0-9]|3[01]))\b/', $resumeText, $m)) {
            $birthDate = str_replace(['.', '/'], '-', $m[1]);
        }

        $name = null;
        if (preg_match('/(?:\b(?:nama\s*penuh|full\s*name|nama|name)\b)\s*[:\-]\s*([^\r\n,]{3,60})/i', $resumeText, $m)) {
            $nameCandidate = trim($m[1]);
            if (!str_contains(strtolower($nameCandidate), '@') && strlen($nameCandidate) > 3) {
                $name = ctype_upper(str_replace(' ', '', $nameCandidate))
                    ? ucwords(strtolower($nameCandidate))
                    : $nameCandidate;
            }
        }
        if (!$name) {
            $lines = array_filter(array_map('trim', explode("\n", $resumeText)));
            foreach (array_slice($lines, 0, 5) as $line) {
                $lower = strtolower($line);
                if (
                    !str_contains($lower, 'resume') &&
                    !str_contains($lower, 'curriculum') &&
                    !str_contains($lower, 'vitae') &&
                    !str_contains($lower, '@') &&
                    !str_contains($lower, 'http') &&
                    !str_contains($lower, 'phone') &&
                    !str_contains($lower, 'email') &&
                    !str_contains($lower, '|') &&
                    !preg_match('/^[0-9+]/', $line) &&
                    preg_match('/^[a-zA-Z\s\.\'@\/\-]+$/', $line) &&
                    strlen($line) >= 3 &&
                    strlen($line) <= 50
                ) {
                    $name = ctype_upper(str_replace(' ', '', $line))
                        ? ucwords(strtolower($line))
                        : $line;
                    break;
                }
            }
        }

        $address = null;
        $locationKeywords = 'kuala lumpur|selangor|johor|penang|pulau pinang|perak|kedah|pahang|negeri sembilan|melaka|kelantan|terengganu|perlis|sabah|sarawak|putrajaya|cyberjaya|petaling jaya|shah alam|subang|cheras|klang|kajang|bangi';
        if (preg_match('/(?:alamat|address)[\s:]*([^\n\r]+)/i', $resumeText, $m)) {
            $address = trim($m[1]);
        } elseif (preg_match('/[^\n\r]*(?:' . $locationKeywords . ')[^\n\r]*/i', $resumeText, $m)) {
            $address = trim($m[0]);
        }

        if ($address && str_contains($address, '|')) {
            $parts = explode('|', $address);
            foreach ($parts as $p) {
                $p = trim($p);
                if (preg_match('/' . $locationKeywords . '/i', $p) && !str_contains($p, '@') && !preg_match('/[0-9]{6,}/', $p)) {
                    $address = $p;
                    break;
                }
            }
        }

        $coverLetter = null;
        if (preg_match('/(?:objektif|ringkasan|profil|summary|objective|about\s*me)[\s:]*([^\r\n]+(?:\r?\n[^\r\n]+){0,3})/i', $resumeText, $m)) {
            $coverLetter = trim(preg_replace('/\s+/', ' ', $m[1]));
        }

        return [
            'candidate_name' => $name,
            'birth_date' => $birthDate,
            'email' => $email,
            'phone' => $phone,
            'address' => $address,
            'cover_letter' => $coverLetter,
        ];
    }
}
