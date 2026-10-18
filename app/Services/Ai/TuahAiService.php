<?php

namespace App\Services\Ai;

use App\Models\AgentConfig;
use App\Models\AgentRun;
use App\Models\ChatMessage;
use App\Models\ChatThread;
use App\Models\CrmDeal;
use App\Models\FinanceInvoice;
use App\Models\HireJob;
use App\Models\HrEmployee;
use App\Models\Org;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TuahAiService
{
    /**
     * Get or create Tuah agent configuration for the organization.
     * Default provider is Ollama.
     */
    public function getConfig(string $orgId): AgentConfig
    {
        $defaultProvider = config('services.ai.default_provider', 'ollama');
        $defaultOllamaUrl = config('services.ollama.url', 'http://127.0.0.1:11434');
        $defaultOllamaModel = config('services.ollama.model', 'gemma4:e2b');
        $defaultGeminiModel = config('services.gemini.model', 'gemini-2.5-flash');

        return AgentConfig::firstOrCreate(
            [
                'org_id' => $orgId,
                'agent_name' => 'Tuah',
            ],
            [
                'provider' => $defaultProvider,
                'gemini_model' => $defaultGeminiModel,
                'gemini_api_key' => null, // null falls back to config('services.gemini.api_key')
                'ollama_url' => $defaultOllamaUrl,
                'ollama_model' => $defaultOllamaModel,
                'model' => $defaultProvider === 'ollama' ? $defaultOllamaModel : $defaultGeminiModel,
                'system_prompt' => "Anda adalah Tuah AI, penasihat dan pembantu operasi perniagaan PKS Malaysia bagi platform OpenKuasa. Anda fasih menyelaraskan modul Bendahara (Kewangan), Lekiu (Kakitangan), Kasturi (CRM), Lekir (Pengrekrutan), dan Jebat (Pemasaran).

PANDUAN FORMAT MAKLUM BALAS:
- Formatkan jawapan anda dengan gaya tulisan yang kemas, profesional, dan mudah dibaca.
- JANGAN gunakan tanda pagar (#, ##, ###) untuk tajuk. Sebaliknya gunakan teks tebal (**Tajuk**) atau huruf besar yang jelas.
- JANGAN gunakan tanda tiga bintang (***). Gunakan teks tebal (dua bintang **) untuk penegasan penting.
- Gunakan senarai titik ringkas (-) atau nombor (1, 2, 3) untuk susunan fakta.
- Berikan jawapan yang tepat, padat, berfokuskan penyelesaian perniagaan, dan gunakan Bahasa Melayu standard atau dwibahasa Malaysia yang profesional.",
                'temperature' => 0.70,
                'tools_enabled' => ['finance', 'people', 'crm', 'reach', 'hire'],
                'is_active' => true,
            ]
        );
    }

    /**
     * Update Tuah agent configuration for the organization.
     */
    public function updateConfig(string $orgId, array $data): AgentConfig
    {
        $config = $this->getConfig($orgId);

        $updateData = [];

        if (isset($data['provider'])) {
            $updateData['provider'] = in_array($data['provider'], ['gemini', 'ollama']) ? $data['provider'] : 'ollama';
        }

        if (array_key_exists('gemini_api_key', $data)) {
            // If empty string submitted, reset to null to use system default
            $updateData['gemini_api_key'] = !empty(trim($data['gemini_api_key'] ?? '')) ? trim($data['gemini_api_key']) : null;
        }

        if (isset($data['gemini_model'])) {
            $updateData['gemini_model'] = trim($data['gemini_model']) ?: 'gemini-2.5-flash';
        }

        if (array_key_exists('ollama_url', $data)) {
            $url = trim($data['ollama_url'] ?? '');
            $updateData['ollama_url'] = !empty($url) ? rtrim($url, '/') : 'http://127.0.0.1:11434';
        }

        if (isset($data['ollama_model'])) {
            $updateData['ollama_model'] = trim($data['ollama_model']) ?: 'gemma4:e2b';
        }

        if (isset($data['system_prompt'])) {
            $updateData['system_prompt'] = $data['system_prompt'];
        }

        if (isset($data['temperature'])) {
            $updateData['temperature'] = (float) $data['temperature'];
        }

        if (isset($data['is_active'])) {
            $updateData['is_active'] = (bool) $data['is_active'];
        }

        // Set general model identifier based on active provider
        if (($updateData['provider'] ?? $config->provider) === 'ollama') {
            $updateData['model'] = $updateData['ollama_model'] ?? $config->ollama_model;
        } else {
            $updateData['model'] = $updateData['gemini_model'] ?? $config->gemini_model;
        }

        $config->update($updateData);

        return $config->fresh();
    }

    /**
     * Pull all models dynamically from Google Gemini API without restrictions.
     */
    public function fetchGeminiModels(?string $apiKey = null): array
    {
        $key = $apiKey ?: config('services.gemini.api_key');

        $fallbackModels = [
            'gemini-2.5-flash',
            'gemini-2.5-pro',
            'gemma-4-31b-it',
            'gemma-4-26b-a4b-it',
            'gemini-flash-latest',
            'gemini-pro-latest',
            'gemini-2.5-flash-lite',
            'gemini-2.0-flash',
            'gemini-2.0-flash-lite',
            'gemini-1.5-flash',
            'gemini-1.5-pro',
            'gemini-1.5-flash-8b',
        ];

        if (empty($key)) {
            return $fallbackModels;
        }

        try {
            $response = Http::timeout(10)->get("https://generativelanguage.googleapis.com/v1beta/models?key={$key}");

            if ($response->successful()) {
                $rawModels = $response->json()['models'] ?? [];
                $pulled = collect($rawModels)
                    ->filter(fn($m) => in_array('generateContent', $m['supportedGenerationMethods'] ?? []))
                    ->pluck('name')
                    ->map(fn($n) => str_replace('models/', '', $n))
                    ->values()
                    ->toArray();

                if (!empty($pulled)) {
                    return $pulled;
                }
            }
        } catch (\Throwable $e) {
            Log::info("Fetch Gemini Models Notice: " . $e->getMessage());
        }

        return $fallbackModels;
    }

    /**
     * Pull all models installed on the Ollama server.
     */
    public function fetchOllamaModels(?string $url = null): array
    {
        $ollamaUrl = rtrim($url ?: config('services.ollama.url', 'http://127.0.0.1:11434'), '/');

        $defaultModels = [
            'gemma4:e2b',
            'llama3',
            'mistral',
            'deepseek-r1:8b',
            'qwen2.5:7b',
            'phi3',
            'gemma2',
        ];

        try {
            $response = Http::timeout(5)->get("{$ollamaUrl}/api/tags");

            if ($response->successful()) {
                $rawModels = $response->json()['models'] ?? [];
                $pulled = collect($rawModels)->pluck('name')->toArray();

                if (!empty($pulled)) {
                    return $pulled;
                }
            }
        } catch (\Throwable $e) {
            Log::info("Fetch Ollama Models Notice: " . $e->getMessage());
        }

        return $defaultModels;
    }

    /**
     * Test connection to selected provider.
     */
    public function testConnection(
        string $orgId,
        ?string $provider = null,
        ?string $geminiKey = null,
        ?string $geminiModel = null,
        ?string $ollamaUrl = null,
        ?string $ollamaModel = null
    ): array {
        $config = $this->getConfig($orgId);
        $activeProvider = $provider ?: $config->provider;

        if ($activeProvider === 'ollama') {
            $url = $ollamaUrl ?: ($config->ollama_url ?: config('services.ollama.url', 'http://127.0.0.1:11434'));
            $url = rtrim($url, '/');
            $model = $ollamaModel ?: ($config->ollama_model ?: config('services.ollama.model', 'gemma4:e2b'));

            try {
                $response = Http::timeout(5)->get("{$url}/api/tags");

                if ($response->successful()) {
                    $data = $response->json();
                    $models = collect($data['models'] ?? [])->pluck('name')->toArray();
                    $hasModel = in_array($model, $models) || in_array("{$model}:latest", $models);

                    return [
                        'success' => true,
                        'provider' => 'ollama',
                        'message' => $hasModel
                            ? "Berjaya menyambung ke Ollama ({$url}). Model '{$model}' sedia digunakan."
                            : "Berjaya menyambung ke Ollama ({$url}). Model sedia ada: " . (empty($models) ? 'Tiada model dimuat turun lagi. Sila jalankan `ollama pull ' . $model . '`' : implode(', ', $models)),
                        'details' => [
                            'url' => $url,
                            'model' => $model,
                            'available_models' => $models,
                        ],
                    ];
                }

                return [
                    'success' => false,
                    'provider' => 'ollama',
                    'message' => "Pelayan Ollama membalas dengan status HTTP {$response->status()}.",
                    'details' => ['status' => $response->status()],
                ];
            } catch (\Throwable $e) {
                return [
                    'success' => false,
                    'provider' => 'ollama',
                    'message' => "Tidak dapat menyambung ke pelayan Ollama di '{$url}'. Sila pastikan pelayan Ollama sedang berjalan (`ollama serve`).",
                    'details' => ['error' => $e->getMessage()],
                ];
            }
        }

        // Gemini connection test
        $apiKey = $geminiKey ?: ($config->gemini_api_key ?: config('services.gemini.api_key'));
        $model = $geminiModel ?: ($config->gemini_model ?: config('services.gemini.model', 'gemini-2.5-flash'));

        if (empty($apiKey)) {
            return [
                'success' => false,
                'provider' => 'gemini',
                'message' => "Kunci Gemini API belum ditetapkan. Sila masukkan Kunci API syarikat anda atau tetapkan GEMINI_API_KEY di fail persekitaran pelayan (.env).",
                'details' => ['error' => 'API key missing'],
            ];
        }

        try {
            $endpoint = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";
            $response = Http::timeout(10)->post($endpoint, [
                'contents' => [
                    [
                        'role' => 'user',
                        'parts' => [['text' => 'Ping connection test. Reply with OK.']],
                    ],
                ],
                'generationConfig' => [
                    'maxOutputTokens' => 10,
                ],
            ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'provider' => 'gemini',
                    'message' => "Sambungan ke Google Gemini API ({$model}) berjaya disahkan! Kunci API sah dan sedia beroperasi.",
                    'details' => ['model' => $model],
                ];
            }

            $errorBody = $response->json();
            $errorMessage = $errorBody['error']['message'] ?? "Ralat HTTP {$response->status()} daripada Google Gemini API";

            return [
                'success' => false,
                'provider' => 'gemini',
                'message' => "Sambungan ke Gemini gagal: {$errorMessage}",
                'details' => $errorBody,
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'provider' => 'gemini',
                'message' => "Ralat sambungan ke Google Gemini: {$e->getMessage()}",
                'details' => ['error' => $e->getMessage()],
            ];
        }
    }

    /**
     * Send chat prompt to Tuah AI and obtain response.
     * Strictly calls only the active provider (Gemini OR Ollama). Never both.
     */
    public function chat(
        string $orgId,
        string $prompt,
        ?string $threadId = null,
        ?string $contextModule = 'general',
        ?User $user = null
    ): array {
        $config = $this->getConfig($orgId);

        // Resolve or create thread
        if ($threadId) {
            $thread = ChatThread::where('org_id', $orgId)->find($threadId);
        } else {
            $thread = null;
        }

        if (!$thread) {
            $thread = ChatThread::create([
                'org_id' => $orgId,
                'user_id' => $user?->id,
                'title' => mb_strimwidth($prompt, 0, 40, '...'),
                'context_module' => $contextModule ?: 'general',
            ]);
        }

        // Save User Message
        $userMessage = ChatMessage::create([
            'thread_id' => $thread->id,
            'role' => 'user',
            'content' => $prompt,
            'metadata' => [
                'context_module' => $contextModule,
            ],
        ]);

        // Build live SME business context
        $businessContext = $this->gatherBusinessContext($orgId);

        // Fetch recent messages in current thread
        $previousMessages = ChatMessage::where('thread_id', $thread->id)
            ->where('id', '!=', $userMessage->id)
            ->orderByDesc('created_at')
            ->take(6)
            ->get()
            ->reverse()
            ->values();

        // Strictly call only the chosen provider
        if ($config->provider === 'ollama') {
            $aiResponse = $this->callOllama($config, $prompt, $businessContext, $previousMessages);
        } else {
            $aiResponse = $this->callGemini($config, $prompt, $businessContext, $previousMessages);
        }

        $assistantText = static::cleanMarkdown($aiResponse['content']);
        $tokensUsed = $aiResponse['tokens_used'];
        $providerUsed = $aiResponse['provider'];
        $modelUsed = $aiResponse['model'];

        // Save Assistant Message
        $assistantMessage = ChatMessage::create([
            'thread_id' => $thread->id,
            'role' => 'assistant',
            'content' => $assistantText,
            'metadata' => [
                'provider' => $providerUsed,
                'model' => $modelUsed,
                'tokens_used' => $tokensUsed,
                'context_module' => $contextModule,
            ],
        ]);

        // Record Agent Run
        $run = AgentRun::create([
            'org_id' => $orgId,
            'agent_config_id' => $config->id,
            'thread_id' => $thread->id,
            'prompt' => $prompt,
            'output' => $assistantText,
            'status' => 'completed',
            'tokens_used' => $tokensUsed,
        ]);

        return [
            'thread_id' => $thread->id,
            'thread' => $thread,
            'user_message' => $userMessage,
            'assistant_message' => $assistantMessage,
            'run_id' => $run->id,
            'provider' => $providerUsed,
            'model' => $modelUsed,
        ];
    }

    /**
     * Execute Gemini API call.
     */
    protected function callGemini(AgentConfig $config, string $prompt, string $businessContext, $previousMessages): array
    {
        $apiKey = $config->gemini_api_key ?: config('services.gemini.api_key');
        $model = $config->gemini_model ?: config('services.gemini.model', 'gemini-2.5-flash');

        if (empty($apiKey)) {
            return [
                'content' => "Kunci Google Gemini API belum ditetapkan. Sila masukkan Kunci API syarikat anda melalui menu 'Tetapan Enjin AI' di atas atau tetapkan GEMINI_API_KEY di fail persekitaran pelayan (.env).",
                'tokens_used' => 50,
                'provider' => 'gemini',
                'model' => $model,
            ];
        }

        try {
            $endpoint = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";

            $systemInstruction = trim(($config->system_prompt ?? '') . "\n\n" . $businessContext);

            $contents = [];
            foreach ($previousMessages as $msg) {
                $contents[] = [
                    'role' => $msg->role === 'assistant' ? 'model' : 'user',
                    'parts' => [['text' => $msg->content]],
                ];
            }

            $contents[] = [
                'role' => 'user',
                'parts' => [['text' => $prompt]],
            ];

            $payload = [
                'contents' => $contents,
                'systemInstruction' => [
                    'parts' => [['text' => $systemInstruction]],
                ],
                'generationConfig' => [
                    'temperature' => (float) ($config->temperature ?: 0.70),
                    'maxOutputTokens' => 2048,
                ],
            ];

            $response = Http::timeout(25)->post($endpoint, $payload);

            if ($response->successful()) {
                $data = $response->json();
                $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;
                $tokens = $data['usageMetadata']['totalTokenCount'] ?? 150;

                if ($text) {
                    return [
                        'content' => trim($text),
                        'tokens_used' => $tokens,
                        'provider' => 'gemini',
                        'model' => $model,
                    ];
                }
            }

            $errMsg = $response->json()['error']['message'] ?? "HTTP {$response->status()}";
            Log::warning("Tuah Gemini API Error: {$errMsg}");

            return [
                'content' => "⚠️ Ralat Google Gemini ({$model}): {$errMsg}. Sila semak semula model atau kuota Kunci API anda di Tetapan Enjin AI.",
                'tokens_used' => 60,
                'provider' => 'gemini',
                'model' => $model,
            ];
        } catch (\Throwable $e) {
            Log::error("Tuah Gemini Exception: {$e->getMessage()}");

            return [
                'content' => "⚠️ Tidak dapat berhubung dengan Google Gemini API ({$model}): {$e->getMessage()}. Sila pastikan pelayan mempunyai akses internet atau semak Kunci API anda.",
                'tokens_used' => 50,
                'provider' => 'gemini',
                'model' => $model,
            ];
        }
    }

    /**
     * Execute Ollama API call.
     */
    protected function callOllama(AgentConfig $config, string $prompt, string $businessContext, $previousMessages): array
    {
        $url = rtrim($config->ollama_url ?: config('services.ollama.url', 'http://127.0.0.1:11434'), '/');
        $model = $config->ollama_model ?: config('services.ollama.model', 'gemma4:e2b');

        $systemInstruction = trim(($config->system_prompt ?? '') . "\n\n" . $businessContext);

        $messages = [
            ['role' => 'system', 'content' => $systemInstruction],
        ];

        foreach ($previousMessages as $msg) {
            $messages[] = [
                'role' => $msg->role,
                'content' => $msg->content,
            ];
        }

        $messages[] = [
            'role' => 'user',
            'content' => $prompt,
        ];

        try {
            $response = Http::timeout(45)->post("{$url}/api/chat", [
                'model' => $model,
                'messages' => $messages,
                'stream' => false,
                'options' => [
                    'temperature' => (float) ($config->temperature ?: 0.70),
                ],
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $reply = $data['message']['content'] ?? null;
                $tokens = ($data['prompt_eval_count'] ?? 0) + ($data['eval_count'] ?? 0);

                if ($reply) {
                    return [
                        'content' => trim($reply),
                        'tokens_used' => $tokens ?: 150,
                        'provider' => 'ollama',
                        'model' => $model,
                    ];
                }
            }

            $errMsg = "HTTP {$response->status()}";
            Log::warning("Tuah Ollama API Error: {$errMsg}");

            return [
                'content' => "⚠️ Pelayan Ollama ({$url}) membalas dengan status ralat: {$errMsg}. Sila pastikan model '{$model}' telah selesai dimuat turun (`ollama run {$model}`).",
                'tokens_used' => 60,
                'provider' => 'ollama',
                'model' => $model,
            ];
        } catch (\Throwable $e) {
            Log::warning("Tuah Ollama Connection Error: {$e->getMessage()}");

            return [
                'content' => "⚠️ Tidak dapat berhubung dengan pelayan Ollama di '{$url}'. Sila pastikan servis Ollama sedang berjalan di pelayan anda (`ollama serve`) dan URL boleh diakses.",
                'tokens_used' => 50,
                'provider' => 'ollama',
                'model' => $model,
            ];
        }
    }

    /**
     * Gather live SME business metrics for Malaysian operational context.
     */
    protected function gatherBusinessContext(string $orgId): string
    {
        try {
            $org = Org::find($orgId);
            $orgName = $org?->name ?? 'Syarikat';

            $unpaidInvoices = FinanceInvoice::where('org_id', $orgId)
                ->whereIn('status', ['unpaid', 'partially_paid'])
                ->count();

            $totalEmployees = HrEmployee::where('org_id', $orgId)
                ->where('status', 'active')
                ->count();

            $openDeals = CrmDeal::where('org_id', $orgId)
                ->whereNotIn('stage_id', ['won', 'lost'])
                ->count();

            $openJobs = HireJob::where('org_id', $orgId)
                ->where('status', 'published')
                ->count();

            return "KONTEKS OPERASI PERNIAGAAN SEMASA:
- Nama Syarikat: {$orgName}
- Kewangan (Bendahara): {$unpaidInvoices} invois belum dijelaskan sepenuhnya.
- Kakitangan (Lekiu): {$totalEmployees} kakitangan aktif berdaftar.
- Jualan / CRM (Kasturi): {$openDeals} urus niaga (deals) dalam proses saluran paip (pipeline).
- Pengrekrutan (Lekir): {$openJobs} iklan kekosongan jawatan aktif dibuka.";
        } catch (\Throwable) {
            return "KONTEKS OPERASI: Platform OpenKuasa bersepadu merentas Kewangan, Kakitangan, CRM, Pemasaran & Pengrekrutan.";
        }
    }

    /**
     * Clean raw markdown to eliminate hashtags (#) and triple asterisks (***),
     * transforming headers into clean bold statements and stripping ugly artifacts.
     */
    public static function cleanMarkdown(?string $text): string
    {
        if (empty($text)) {
            return '';
        }

        // 1. Remove isolated divider lines made of *** or ---
        $text = preg_replace('/^\s*[\*\-]{3,}\s*$/m', '', $text);

        // 2. Convert markdown headers (# Tajuk, ## Tajuk, ### Tajuk) into clean bold lines
        $text = preg_replace('/^#{1,6}\s*(.+)$/m', "**$1**\n", $text);

        // 3. Convert any inline triple asterisks (***teks***) to standard bold (**teks**)
        $text = str_replace('***', '**', $text);

        // 4. Clean up any excess asterisks (****+)
        $text = preg_replace('/\*{4,}/', '**', $text);

        return trim($text);
    }

    /**
     * Render markdown cleanly into semantic HTML without raw # or *** symbols.
     */
    public static function renderMarkdown(?string $text): string
    {
        if (empty($text)) {
            return '';
        }

        $cleaned = static::cleanMarkdown($text);

        return \Illuminate\Support\Str::markdown($cleaned);
    }
}
