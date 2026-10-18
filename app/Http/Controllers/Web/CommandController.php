<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\ChatThread;
use App\Services\Ai\TuahAiService;
use App\Services\Tenancy\OrganizationContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommandController extends Controller
{
    public function __construct(
        protected TuahAiService $tuahAiService
    ) {}

    public function index(Request $request)
    {
        $orgId = OrganizationContext::id();
        $threads = ChatThread::where('org_id', $orgId)
            ->latest()
            ->get();

        $activeThread = null;
        if ($request->has('new')) {
            $activeThread = null;
        } elseif ($request->filled('thread_id')) {
            $activeThread = ChatThread::with('messages')
                ->where('org_id', $orgId)
                ->find($request->thread_id);
        } elseif ($threads->isNotEmpty()) {
            $activeThread = ChatThread::with('messages')
                ->where('org_id', $orgId)
                ->find($threads->first()->id);
        }

        $agentConfig = $this->tuahAiService->getConfig($orgId);

        // Preload available models for datalist
        $geminiModels = $this->tuahAiService->fetchGeminiModels($agentConfig->gemini_api_key);
        $ollamaModels = $this->tuahAiService->fetchOllamaModels($agentConfig->ollama_url);

        $initialMessages = [];
        if ($activeThread && $activeThread->messages) {
            foreach ($activeThread->messages as $msg) {
                $initialMessages[] = [
                    'id' => (string) $msg->id,
                    'role' => $msg->role,
                    'content' => $msg->content,
                    'rendered_html' => $msg->role === 'assistant'
                        ? \App\Services\Ai\TuahAiService::renderMarkdown($msg->content)
                        : null,
                ];
            }
        }

        return view('command.index', compact('threads', 'activeThread', 'agentConfig', 'geminiModels', 'ollamaModels', 'initialMessages'));
    }

    public function sendPrompt(Request $request)
    {
        $validated = $request->validate([
            'prompt' => ['required', 'string'],
            'thread_id' => ['nullable', 'uuid'],
        ]);

        $orgId = OrganizationContext::id();
        $user = Auth::user();

        $result = $this->tuahAiService->chat(
            $orgId,
            $validated['prompt'],
            $validated['thread_id'] ?? null,
            'general',
            $user
        );

        if ($request->expectsJson() || $request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'thread_id' => $result['thread_id'],
                'user_message' => [
                    'id' => $result['user_message']->id,
                    'role' => 'user',
                    'content' => $result['user_message']->content,
                ],
                'assistant_message' => [
                    'id' => $result['assistant_message']->id,
                    'role' => 'assistant',
                    'content' => $result['assistant_message']->content,
                    'rendered_html' => \App\Services\Ai\TuahAiService::renderMarkdown($result['assistant_message']->content),
                ],
            ]);
        }

        return redirect()->route('command.index', ['thread_id' => $result['thread_id']]);
    }

    public function updateSettings(Request $request)
    {
        $validated = $request->validate([
            'provider' => ['required', 'in:gemini,ollama'],
            'gemini_api_key' => ['nullable', 'string', 'max:255'],
            'gemini_model' => ['nullable', 'string', 'max:100'],
            'ollama_url' => ['nullable', 'string', 'max:255'],
            'ollama_model' => ['nullable', 'string', 'max:100'],
            'system_prompt' => ['nullable', 'string', 'max:3000'],
            'temperature' => ['nullable', 'numeric', 'min:0', 'max:2'],
        ]);

        $orgId = OrganizationContext::id();
        $this->tuahAiService->updateConfig($orgId, $validated);

        return redirect()->route('command.index')
            ->with('success', 'Tetapan Tuah AI berjaya dikemaskini. Kunci API dan URL telah disimpan dengan penyulitan rahsia (encryption).');
    }

    public function fetchModels(Request $request)
    {
        $orgId = OrganizationContext::id();
        $provider = $request->query('provider', 'gemini');

        if ($provider === 'ollama') {
            $url = $request->query('url');
            if (empty($url) && $orgId) {
                $agentConfig = $this->tuahAiService->getConfig($orgId);
                $url = $agentConfig->ollama_url;
            }
            $result = $this->tuahAiService->fetchOllamaModelsWithDetails($url);
        } else {
            $key = $request->query('key');
            if (empty($key) && $orgId) {
                $agentConfig = $this->tuahAiService->getConfig($orgId);
                $key = $agentConfig->gemini_api_key;
            }
            $result = $this->tuahAiService->fetchGeminiModelsWithDetails($key);
        }

        return response()->json([
            'success' => true,
            'provider' => $provider,
            'models' => $result['models'],
            'live' => $result['live'] ?? true,
            'message' => $result['message'] ?? null,
        ]);
    }

    public function testConnection(Request $request)
    {
        $orgId = OrganizationContext::id();

        $result = $this->tuahAiService->testConnection(
            $orgId,
            $request->input('provider'),
            $request->input('gemini_api_key'),
            $request->input('gemini_model'),
            $request->input('ollama_url'),
            $request->input('ollama_model')
        );

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json($result);
        }

        if ($result['success']) {
            return redirect()->back()->with('success', $result['message']);
        }

        return redirect()->back()->with('error', $result['message']);
    }

    public function deleteThread(string $id)
    {
        $orgId = OrganizationContext::id();
        $thread = ChatThread::where('org_id', $orgId)->find($id);
        if ($thread) {
            $thread->messages()->delete();
            $thread->delete();
        }

        return redirect()->route('command.index', ['new' => 1])
            ->with('success', 'Sesi sembang berjaya dipadam.');
    }
}
