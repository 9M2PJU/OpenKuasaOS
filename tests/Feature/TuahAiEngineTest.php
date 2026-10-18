<?php

namespace Tests\Feature;

use App\Models\AgentConfig;
use App\Models\AgentRun;
use App\Models\ChatMessage;
use App\Models\ChatThread;
use App\Models\Org;
use App\Models\OrgMember;
use App\Models\User;
use App\Services\Tenancy\OrganizationContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class TuahAiEngineTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Org $org;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::create([
            'name' => 'Dato Farhan',
            'email' => 'farhan@megaholding.my',
            'password' => bcrypt('password123'),
        ]);

        $this->org = Org::create([
            'name' => 'Maju Holding Sdn Bhd',
            'slug' => 'maju-holding',
        ]);

        OrgMember::create([
            'org_id' => $this->org->id,
            'user_id' => $this->user->id,
            'role' => 'owner',
        ]);

        OrganizationContext::set($this->org);
    }

    public function test_ai_credentials_are_stored_encrypted_in_database(): void
    {
        $rawGeminiKey = 'AIzaSyD_Secret_Gemini_Key_999';
        $rawOllamaUrl = 'http://192.168.1.100:11434';

        $config = AgentConfig::create([
            'org_id' => $this->org->id,
            'agent_name' => 'Tuah',
            'provider' => 'gemini',
            'gemini_api_key' => $rawGeminiKey,
            'gemini_model' => 'gemini-2.0-flash',
            'ollama_url' => $rawOllamaUrl,
            'ollama_model' => 'llama3',
            'model' => 'gemini-2.0-flash',
            'temperature' => 0.70,
        ]);

        // 1. Verify that raw database values are encrypted (ciphertext)
        $rawRow = DB::table('agent_configs')->where('id', $config->id)->first();

        $this->assertNotEquals($rawGeminiKey, $rawRow->gemini_api_key);
        $this->assertStringNotContainsString('Secret_Gemini_Key', $rawRow->gemini_api_key);

        $this->assertNotEquals($rawOllamaUrl, $rawRow->ollama_url);
        $this->assertStringNotContainsString('192.168.1.100', $rawRow->ollama_url);

        // 2. Verify that Eloquent automatically decrypts them when accessed
        $loaded = AgentConfig::find($config->id);
        $this->assertEquals($rawGeminiKey, $loaded->gemini_api_key);
        $this->assertEquals($rawOllamaUrl, $loaded->ollama_url);
    }

    public function test_tuah_screen_renders_with_ai_configuration(): void
    {
        $this->actingAs($this->user);

        $response = $this->get('/command');
        $response->assertStatus(200);
        $response->assertSee('Tuah Enjin Kepintaran Buatan');
        $response->assertSee('Tetapan Enjin AI');
        $response->assertSee('Google Gemini');
        $response->assertSee('Ollama');
    }

    public function test_tuah_screen_renders_safely_with_complex_thread_messages_without_html_attribute_collapse(): void
    {
        $this->actingAs($this->user);

        $thread = ChatThread::create([
            'org_id' => $this->org->id,
            'user_id' => $this->user->id,
            'title' => 'Analisis Staf',
            'context_module' => 'general',
        ]);

        ChatMessage::create([
            'thread_id' => $thread->id,
            'role' => 'user',
            'content' => 'buatkan saya kod html untuk "prestasi jualan"',
        ]);

        ChatMessage::create([
            'thread_id' => $thread->id,
            'role' => 'assistant',
            'content' => "### ANALISIS PRESTASI STAF\n\nBerdasarkan maklumat: **kakitangan aktif** 'Sabily Enterprise'.\n\n```html\n<div class=\"table\"><tr><td>Jualan</td></tr></div>\n```",
        ]);

        $response = $this->get('/command?thread_id=' . $thread->id);
        $response->assertStatus(200);
        $response->assertSee('x-data="tuahCommandComponent()"', false);
        $response->assertSee('window.tuahCommandComponent = function()', false);
        $response->assertDontSee('x-data="{ messages:', false);
    }

    public function test_business_owner_can_update_ai_settings_to_gemini_with_encrypted_key(): void
    {
        $this->actingAs($this->user);

        $secretKey = 'AIzaSyCustomKeyProvidedByOwner';

        $response = $this->post('/command/settings', [
            'provider' => 'gemini',
            'gemini_api_key' => $secretKey,
            'gemini_model' => 'gemini-1.5-pro',
            'system_prompt' => 'Anda adalah penasihat perniagaan eksekutif Maju Holding.',
            'temperature' => 0.5,
        ]);

        $response->assertRedirect('/command');
        $response->assertSessionHas('success');

        // Verify updated in database with encryption
        $config = AgentConfig::where('org_id', $this->org->id)->where('agent_name', 'Tuah')->first();
        $this->assertNotNull($config);
        $this->assertEquals('gemini', $config->provider);
        $this->assertEquals('gemini-1.5-pro', $config->gemini_model);
        $this->assertEquals($secretKey, $config->gemini_api_key);

        // Raw database check
        $rawRow = DB::table('agent_configs')->where('id', $config->id)->first();
        $this->assertNotEquals($secretKey, $rawRow->gemini_api_key);
    }

    public function test_business_owner_can_switch_ai_provider_to_ollama_with_encrypted_url(): void
    {
        $this->actingAs($this->user);

        $ollamaUrl = 'http://ollama-internal.lan:11434';
        $ollamaModel = 'deepseek-r1:8b';

        $response = $this->post('/command/settings', [
            'provider' => 'ollama',
            'ollama_url' => $ollamaUrl,
            'ollama_model' => $ollamaModel,
            'system_prompt' => 'Mod privasi penuh Ollama diaktifkan.',
            'temperature' => 0.7,
        ]);

        $response->assertRedirect('/command');
        $response->assertSessionHas('success');

        $config = AgentConfig::where('org_id', $this->org->id)->where('agent_name', 'Tuah')->first();
        $this->assertEquals('ollama', $config->provider);
        $this->assertEquals($ollamaUrl, $config->ollama_url);
        $this->assertEquals($ollamaModel, $config->ollama_model);

        // Raw database check for encrypted URL
        $rawRow = DB::table('agent_configs')->where('id', $config->id)->first();
        $this->assertNotEquals($ollamaUrl, $rawRow->ollama_url);
        $this->assertStringNotContainsString('ollama-internal.lan', $rawRow->ollama_url);
    }

    public function test_connection_testing_endpoint_with_gemini(): void
    {
        $this->actingAs($this->user);

        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [['text' => 'OK']],
                        ],
                    ],
                ],
            ], 200),
        ]);

        $response = $this->postJson('/command/test-connection', [
            'provider' => 'gemini',
            'gemini_api_key' => 'AIzaSyTestKey123',
            'gemini_model' => 'gemini-2.0-flash',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'provider' => 'gemini',
        ]);
    }

    public function test_connection_testing_endpoint_with_ollama(): void
    {
        $this->actingAs($this->user);

        Http::fake([
            '127.0.0.1:11434/api/tags' => Http::response([
                'models' => [
                    ['name' => 'llama3:latest'],
                    ['name' => 'mistral:latest'],
                ],
            ], 200),
        ]);

        $response = $this->postJson('/command/test-connection', [
            'provider' => 'ollama',
            'ollama_url' => 'http://127.0.0.1:11434',
            'ollama_model' => 'llama3',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'provider' => 'ollama',
        ]);
    }

    public function test_tuah_chat_generates_response_with_gemini_api(): void
    {
        $this->actingAs($this->user);

        // Configure Gemini for this org
        AgentConfig::create([
            'org_id' => $this->org->id,
            'agent_name' => 'Tuah',
            'provider' => 'gemini',
            'gemini_api_key' => 'AIzaSyValidGeminiKey',
            'gemini_model' => 'gemini-2.0-flash',
            'model' => 'gemini-2.0-flash',
        ]);

        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                ['text' => 'Berdasarkan data kewangan syarikat anda, baki tertunggak adalah dalam keadaan terkawal.'],
                            ],
                            'role' => 'model',
                        ],
                    ],
                ],
                'usageMetadata' => [
                    'totalTokenCount' => 180,
                ],
            ], 200),
        ]);

        $response = $this->post('/command/chat', [
            'prompt' => 'Berapa baki invois belum dibayar?',
        ]);

        $response->assertRedirect();

        $message = ChatMessage::where('role', 'assistant')->latest()->first();
        $this->assertNotNull($message);
        $this->assertStringContainsString('baki tertunggak', $message->content);
        $this->assertEquals('gemini', $message->metadata['provider']);

        $run = AgentRun::where('org_id', $this->org->id)->latest()->first();
        $this->assertNotNull($run);
        $this->assertEquals('completed', $run->status);
        $this->assertEquals(180, $run->tokens_used);
    }

    public function test_tuah_chat_generates_response_with_ollama_api(): void
    {
        $this->actingAs($this->user);

        // Configure Ollama for this org
        AgentConfig::create([
            'org_id' => $this->org->id,
            'agent_name' => 'Tuah',
            'provider' => 'ollama',
            'ollama_url' => 'http://127.0.0.1:11434',
            'ollama_model' => 'llama3',
            'model' => 'llama3',
        ]);

        Http::fake([
            '127.0.0.1:11434/api/chat' => Http::response([
                'model' => 'llama3',
                'message' => [
                    'role' => 'assistant',
                    'content' => 'Salam sejahtera dari model Llama 3 tempatan. Semua operasi kakitangan berjalan lancar.',
                ],
                'prompt_eval_count' => 20,
                'eval_count' => 45,
            ], 200),
        ]);

        $response = $this->post('/command/chat', [
            'prompt' => 'Semak status operasi syarikat',
        ]);

        $response->assertRedirect();

        $message = ChatMessage::where('role', 'assistant')->latest()->first();
        $this->assertNotNull($message);
        $this->assertStringContainsString('Llama 3 tempatan', $message->content);
        $this->assertEquals('ollama', $message->metadata['provider']);
    }

    public function test_tuah_api_config_endpoints(): void
    {
        $this->actingAs($this->user);

        // Set custom Gemini key
        $this->postJson('/api/v1/command/config', [
            'provider' => 'gemini',
            'gemini_api_key' => 'AIzaSyTopSecret123456789',
            'gemini_model' => 'gemini-2.0-flash',
        ])->assertStatus(200);

        // Fetch config via API: verify masked key is returned and not raw secret
        $response = $this->getJson('/api/v1/command/config');
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'data' => [
                'provider' => 'gemini',
                'has_custom_gemini_key' => true,
            ],
        ]);

        $data = $response->json('data');
        $this->assertStringContainsString('••••••••', $data['gemini_api_key_masked']);
        $this->assertStringNotContainsString('TopSecret', $data['gemini_api_key_masked']);
    }

    public function test_default_provider_is_ollama(): void
    {
        $service = app(\App\Services\Ai\TuahAiService::class);
        $config = $service->getConfig($this->org->id);

        $this->assertEquals('ollama', $config->provider);
        $this->assertEquals('gemma4:e2b', $config->ollama_model);
    }

    public function test_fetch_models_endpoints_for_gemini_and_ollama(): void
    {
        $this->actingAs($this->user);

        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'models' => [
                    ['name' => 'models/gemini-2.5-flash', 'supportedGenerationMethods' => ['generateContent']],
                    ['name' => 'models/gemini-2.5-pro', 'supportedGenerationMethods' => ['generateContent']],
                    ['name' => 'models/text-embedding-004', 'supportedGenerationMethods' => ['embedContent']],
                ],
            ], 200),
            '127.0.0.1:11434/api/tags' => Http::response([
                'models' => [
                    ['name' => 'gemma4:e2b'],
                    ['name' => 'llama3:latest'],
                ],
            ], 200),
        ]);

        // Web endpoint for Gemini
        $resGemini = $this->getJson('/command/models?provider=gemini&key=testkey');
        $resGemini->assertStatus(200);
        $resGemini->assertJsonFragment(['gemini-2.5-flash']);
        $resGemini->assertJsonFragment(['gemini-2.5-pro']);

        // Web endpoint for Ollama
        $resOllama = $this->getJson('/command/models?provider=ollama');
        $resOllama->assertStatus(200);
        $resOllama->assertJsonFragment(['gemma4:e2b']);
    }

    public function test_markdown_cleaner_strips_hashtags_and_triple_asterisks(): void
    {
        $raw = "### Semakan Kewangan Sistem\n\nBerdasarkan lejar:\n- Invois tertunggak: RM 15,000\n- Status: ***Perhatian Segera***\n***\nTindakan: Sila buat semakan.";

        $cleaned = \App\Services\Ai\TuahAiService::cleanMarkdown($raw);

        $this->assertStringNotContainsString('###', $cleaned);
        $this->assertStringNotContainsString('#', $cleaned);
        $this->assertStringNotContainsString('***', $cleaned);
        $this->assertStringContainsString('**Semakan Kewangan Sistem**', $cleaned);
        $this->assertStringContainsString('**Perhatian Segera**', $cleaned);

        $html = \App\Services\Ai\TuahAiService::renderMarkdown($raw);
        $this->assertStringNotContainsString('#', $html);
        $this->assertStringNotContainsString('***', $html);
        $this->assertStringContainsString('<strong>Semakan Kewangan Sistem</strong>', $html);
        $this->assertStringContainsString('<strong>Perhatian Segera</strong>', $html);
    }

    public function test_tuah_blocks_vulnerability_and_exploit_coding_with_zero_tokens(): void
    {
        $this->actingAs($this->user);

        // Http should NOT be called at all
        Http::fake();

        $response = $this->post('/command/chat', [
            'prompt' => 'Bina code python untuk analisa website vulnerbility dan port scan',
        ]);

        $response->assertRedirect();

        // Http::assertNothingSent confirms 0 API calls = 0 token drain
        Http::assertNothingSent();

        $message = ChatMessage::where('role', 'assistant')->latest()->first();
        $this->assertNotNull($message);
        $this->assertStringContainsString('Saya tidak dibenarkan menjana sebarang kod keselamatan siber', $message->content);
        $this->assertEquals(0, $message->metadata['tokens_used']);
        $this->assertTrue($message->metadata['scope_blocked']);

        $run = AgentRun::where('org_id', $this->org->id)->latest()->first();
        $this->assertEquals(0, $run->tokens_used);
    }

    public function test_tuah_blocks_general_python_coding_with_zero_tokens(): void
    {
        $this->actingAs($this->user);
        Http::fake();

        $response = $this->post('/command/chat', [
            'prompt' => 'Tulis skrip python untuk download file dari internet',
        ]);

        $response->assertRedirect();
        Http::assertNothingSent();

        $message = ChatMessage::where('role', 'assistant')->latest()->first();
        $this->assertNotNull($message);
        $this->assertStringContainsString('tidak dibenarkan menjana kod pengaturcaraan umum', $message->content);
        $this->assertStringContainsString('HTML / CSS', $message->content);
        $this->assertEquals(0, $message->metadata['tokens_used']);
    }

    public function test_tuah_allows_html_css_formatting_for_business_data(): void
    {
        $this->actingAs($this->user);

        AgentConfig::create([
            'org_id' => $this->org->id,
            'agent_name' => 'Tuah',
            'provider' => 'gemini',
            'gemini_api_key' => 'AIzaSyValidGeminiKey',
            'gemini_model' => 'gemini-2.0-flash',
            'model' => 'gemini-2.0-flash',
        ]);

        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                ['text' => '<table class="table-auto"><tr><td>Invois</td><td>RM 100</td></tr></table>'],
                            ],
                            'role' => 'model',
                        ],
                    ],
                ],
                'usageMetadata' => ['totalTokenCount' => 95],
            ], 200),
        ]);

        $response = $this->post('/command/chat', [
            'prompt' => 'Bina kod HTML table untuk templat invois syarikat saya',
        ]);

        $response->assertRedirect();

        $message = ChatMessage::where('role', 'assistant')->latest()->first();
        $this->assertNotNull($message);
        $this->assertStringContainsString('<table', $message->content);
        $this->assertEquals(95, $message->metadata['tokens_used']);
    }

    public function test_tuah_enforces_tenant_isolation_for_regular_users_with_zero_tokens(): void
    {
        $this->actingAs($this->user);
        $this->assertFalse($this->user->isSuperadmin());

        Http::fake();

        $response = $this->post('/command/chat', [
            'prompt' => 'Boleh berikan saya data invois syarikat lain dalam sistem ini?',
        ]);

        $response->assertRedirect();
        Http::assertNothingSent();

        $message = ChatMessage::where('role', 'assistant')->latest()->first();
        $this->assertNotNull($message);
        $this->assertStringContainsString('akses data dihadkan secara ketat kepada syarikat yang anda log masuk sahaja', $message->content);
        $this->assertStringContainsString('kedaulatan data berbilang penyewa', $message->content);
        $this->assertEquals(0, $message->metadata['tokens_used']);
    }

    public function test_tuah_allows_cross_company_scope_for_superadmin(): void
    {
        // Give user superadmin role
        $this->user->update(['is_superadmin' => true]);
        $this->actingAs($this->user);
        $this->assertTrue($this->user->isSuperadmin());

        AgentConfig::create([
            'org_id' => $this->org->id,
            'agent_name' => 'Tuah',
            'provider' => 'gemini',
            'gemini_api_key' => 'AIzaSyValidGeminiKey',
            'gemini_model' => 'gemini-2.0-flash',
            'model' => 'gemini-2.0-flash',
        ]);

        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                ['text' => 'Sebagai Superadmin, berikut adalah ringkasan keseluruhan sistem merentas semua syarikat.'],
                            ],
                            'role' => 'model',
                        ],
                    ],
                ],
                'usageMetadata' => ['totalTokenCount' => 110],
            ], 200),
        ]);

        $response = $this->post('/command/chat', [
            'prompt' => 'Papar status semua syarikat dalam sistem',
        ]);

        $response->assertRedirect();

        // Should NOT be blocked by tenant boundary interceptor; goes to LLM with superadmin context
        $message = ChatMessage::where('role', 'assistant')->latest()->first();
        $this->assertNotNull($message);
        $this->assertStringContainsString('Sebagai Superadmin', $message->content);
        $this->assertEquals(110, $message->metadata['tokens_used']);
    }

    public function test_tuah_chat_via_ajax_returns_json_for_realtime_smooth_scrolling(): void
    {
        $this->actingAs($this->user);

        AgentConfig::create([
            'org_id' => $this->org->id,
            'agent_name' => 'Tuah',
            'provider' => 'gemini',
            'gemini_api_key' => 'AIzaSyValidGeminiKey',
            'gemini_model' => 'gemini-2.0-flash',
            'model' => 'gemini-2.0-flash',
        ]);

        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                ['text' => 'Analisa kewangan selesai: semua invois diproses.'],
                            ],
                            'role' => 'model',
                        ],
                    ],
                ],
                'usageMetadata' => ['totalTokenCount' => 75],
            ], 200),
        ]);

        // Request with XMLHttpRequest / JSON header
        $response = $this->postJson('/command/chat', [
            'prompt' => 'Ringkaskan status kewangan semasa',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'thread_id',
            'user_message' => ['id', 'role', 'content'],
            'assistant_message' => ['id', 'role', 'content', 'rendered_html'],
        ]);

        $data = $response->json();
        $this->assertTrue($data['success']);
        $this->assertEquals('Ringkaskan status kewangan semasa', $data['user_message']['content']);
        $this->assertStringContainsString('Analisa kewangan selesai', $data['assistant_message']['content']);
    }

    public function test_tuah_command_new_session_parameter_resets_active_thread(): void
    {
        $this->actingAs($this->user);

        // Create an existing thread with messages
        $thread = ChatThread::create([
            'org_id' => $this->org->id,
            'user_id' => $this->user->id,
            'title' => 'Sembang Terdahulu',
        ]);
        ChatMessage::create([
            'thread_id' => $thread->id,
            'org_id' => $this->org->id,
            'user_id' => $this->user->id,
            'role' => 'user',
            'content' => 'Soalan lama',
        ]);

        // Standard index loads the thread by default
        $response = $this->get('/command');
        $response->assertStatus(200);
        $response->assertViewHas('activeThread', function ($viewThread) use ($thread) {
            return $viewThread && $viewThread->id === $thread->id;
        });
        $response->assertSee('Sembang Terdahulu');

        // Visiting with ?new=1 starts a fresh session
        $responseNew = $this->get('/command?new=1');
        $responseNew->assertStatus(200);
        $responseNew->assertViewHas('activeThread', null);
        $responseNew->assertViewHas('initialMessages', []);
    }

    public function test_tuah_command_deletes_thread_and_cascades_messages(): void
    {
        $this->actingAs($this->user);

        $thread = ChatThread::create([
            'org_id' => $this->org->id,
            'user_id' => $this->user->id,
            'title' => 'Sesi Nak Dipadam',
        ]);
        $message = ChatMessage::create([
            'thread_id' => $thread->id,
            'org_id' => $this->org->id,
            'user_id' => $this->user->id,
            'role' => 'user',
            'content' => 'Mesej sesi ini',
        ]);

        $response = $this->delete("/command/threads/{$thread->id}");
        $response->assertRedirect(route('command.index', ['new' => 1]));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('chat_threads', ['id' => $thread->id]);
        $this->assertDatabaseMissing('chat_messages', ['id' => $message->id]);
    }

    public function test_tuah_command_cannot_delete_other_organization_thread(): void
    {
        $this->actingAs($this->user);

        $otherOrg = Org::create([
            'name' => 'Syarikat Sebelah Sdn Bhd',
            'slug' => 'syarikat-sebelah',
        ]);
        $otherUser = User::create([
            'name' => 'Ahmad Competitor',
            'email' => 'ahmad@syarikatsebelah.my',
            'password' => bcrypt('password123'),
        ]);

        // Create thread belonging to otherOrg
        $otherThread = ChatThread::create([
            'org_id' => $otherOrg->id,
            'user_id' => $otherUser->id,
            'title' => 'Sesi Syarikat Sebelah',
        ]);

        $response = $this->delete("/command/threads/{$otherThread->id}");
        $response->assertRedirect(route('command.index', ['new' => 1]));

        // Thread in otherOrg MUST NOT be deleted
        $this->assertDatabaseHas('chat_threads', ['id' => $otherThread->id]);
    }
}

