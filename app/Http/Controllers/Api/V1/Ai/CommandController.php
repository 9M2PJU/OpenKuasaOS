<?php

namespace App\Http\Controllers\Api\V1\Ai;

use App\Http\Controllers\Concerns\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Ai\ChatRequest;
use App\Models\ChatThread;
use App\Services\Ai\TuahAiService;
use App\Services\Tenancy\OrganizationContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CommandController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected TuahAiService $tuahAiService
    ) {}

    public function threads(Request $request): JsonResponse
    {
        $orgId = OrganizationContext::getId();
        $threads = ChatThread::where('org_id', $orgId)
            ->withCount('messages')
            ->orderByDesc('updated_at')
            ->get();

        return $this->success($threads, 'Senarai perbualan AI berjaya diperolehi.');
    }

    public function storeThread(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['nullable', 'string', 'max:255'],
            'context_module' => ['nullable', 'string', 'max:50'],
        ]);

        $thread = ChatThread::create([
            'org_id' => OrganizationContext::getId(),
            'user_id' => $request->user()?->id,
            'title' => $validated['title'] ?? 'Perbualan Baharu',
            'context_module' => $validated['context_module'] ?? 'general',
        ]);

        return $this->success($thread, 'Sesi perbualan AI dicipta.', null, 201);
    }

    public function chat(ChatRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $user = $request->user();
        $orgId = OrganizationContext::getId();

        $result = $this->tuahAiService->chat(
            $orgId,
            $validated['prompt'],
            $validated['thread_id'] ?? null,
            $validated['context_module'] ?? 'general',
            $user
        );

        return $this->success($result, 'Maklum balas pembantu Tuah AI dijana.');
    }

    public function getConfig(Request $request): JsonResponse
    {
        $orgId = OrganizationContext::getId();
        $config = $this->tuahAiService->getConfig($orgId);

        // Mask encrypted API key for security in response
        $maskedKey = null;
        if (!empty($config->gemini_api_key)) {
            $maskedKey = substr($config->gemini_api_key, 0, 4) . '••••••••' . substr($config->gemini_api_key, -4);
        }

        $data = [
            'provider' => $config->provider,
            'gemini_model' => $config->gemini_model,
            'has_custom_gemini_key' => !empty($config->gemini_api_key),
            'gemini_api_key_masked' => $maskedKey,
            'has_default_gemini_key' => !empty(config('services.gemini.api_key')),
            'ollama_url' => $config->ollama_url,
            'ollama_model' => $config->ollama_model,
            'system_prompt' => $config->system_prompt,
            'temperature' => $config->temperature,
            'is_active' => $config->is_active,
        ];

        return $this->success($data, 'Konfigurasi Tuah AI berjaya diperolehi.');
    }

    public function updateConfig(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'provider' => ['required', 'in:gemini,ollama'],
            'gemini_api_key' => ['nullable', 'string'],
            'gemini_model' => ['nullable', 'string'],
            'ollama_url' => ['nullable', 'string'],
            'ollama_model' => ['nullable', 'string'],
            'system_prompt' => ['nullable', 'string'],
            'temperature' => ['nullable', 'numeric', 'min:0', 'max:2'],
        ]);

        $orgId = OrganizationContext::getId();
        $config = $this->tuahAiService->updateConfig($orgId, $validated);

        return $this->success([
            'provider' => $config->provider,
            'gemini_model' => $config->gemini_model,
            'ollama_url' => $config->ollama_url,
            'ollama_model' => $config->ollama_model,
        ], 'Konfigurasi Tuah AI berjaya dikemaskini.');
    }

    public function testConnection(Request $request): JsonResponse
    {
        $orgId = OrganizationContext::getId();

        $result = $this->tuahAiService->testConnection(
            $orgId,
            $request->input('provider'),
            $request->input('gemini_api_key'),
            $request->input('gemini_model'),
            $request->input('ollama_url'),
            $request->input('ollama_model')
        );

        return response()->json($result, $result['success'] ? 200 : 422);
    }

    public function fetchModels(Request $request): JsonResponse
    {
        $provider = $request->query('provider', 'gemini');

        if ($provider === 'ollama') {
            $url = $request->query('url');
            $models = $this->tuahAiService->fetchOllamaModels($url);
        } else {
            $key = $request->query('key');
            $models = $this->tuahAiService->fetchGeminiModels($key);
        }

        return $this->success([
            'provider' => $provider,
            'models' => $models,
        ], 'Senarai model AI berjaya diperolehi.');
    }
}
