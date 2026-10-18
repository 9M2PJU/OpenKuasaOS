@extends('layouts.app', ['title' => 'Tuah — Pusat Kawalan AI'])

@section('content')
<style>
    .tuah-rendered-content p { margin-bottom: 0.65rem; }
    .tuah-rendered-content p:last-child { margin-bottom: 0; }
    .tuah-rendered-content ul, .tuah-rendered-content ol { margin-left: 1.25rem; margin-bottom: 0.65rem; }
    .tuah-rendered-content ul { list-style-type: disc; }
    .tuah-rendered-content ol { list-style-type: decimal; }
    .tuah-rendered-content li { margin-bottom: 0.25rem; }
    .tuah-rendered-content strong { font-weight: 700; color: inherit; }
    .tuah-rendered-content code { background-color: rgba(148, 163, 184, 0.2); padding: 0.15rem 0.35rem; border-radius: 0.375rem; font-size: 0.85em; font-family: monospace; }
    .tuah-rendered-content pre { background-color: rgba(15, 23, 42, 0.9); color: #f8fafc; padding: 0.75rem; border-radius: 0.75rem; overflow-x: auto; margin-bottom: 0.65rem; font-size: 0.85em; }
    .tuah-rendered-content blockquote { border-left: 3px solid #6366f1; padding-left: 0.75rem; margin-bottom: 0.65rem; font-style: italic; opacity: 0.9; }
</style>
<script>
window.tuahCommandComponent = function() {
    return {
        showHistory: window.innerWidth >= 1024,
        showSettings: false,
        provider: @json($agentConfig->provider ?? 'ollama'),
        geminiKey: '',
        geminiModel: @json($agentConfig->gemini_model ?? 'gemini-2.5-flash'),
        ollamaUrl: @json($agentConfig->ollama_url ?? 'http://127.0.0.1:11434'),
        ollamaModel: @json($agentConfig->ollama_model ?? 'gemma4:e2b'),
        geminiModels: @json($geminiModels ?? []),
        ollamaModels: @json($ollamaModels ?? []),
        ollamaCustom: false,
        geminiCustom: false,
        isSubmittingChat: false,
        isThinking: false,
        chatPrompt: '',
        showKey: false,
        testingConnection: false,
        pullingGemini: false,
        pullingOllama: false,
        testStatus: null,
        testMessage: '',
        activeThreadId: @json($activeThread?->id ?? ''),
        messages: @json($initialMessages ?? []),

        scrollToBottom(smooth = true) {
            this.$nextTick(() => {
                const container = document.getElementById('chatMessages');
                if (container) {
                    container.scrollTo({
                        top: container.scrollHeight,
                        behavior: smooth ? 'smooth' : 'auto'
                    });
                }
            });
        },

        async submitChat(e) {
            if (e) e.preventDefault();
            if (this.isSubmittingChat) return false;
            const promptText = (this.chatPrompt || '').trim();
            if (!promptText) return false;

            this.isSubmittingChat = true;
            this.chatPrompt = '';

            // Tunjukkan mesej pengguna serta-merta pada skrin
            this.messages.push({
                id: 'temp-' + Date.now(),
                role: 'user',
                content: promptText,
                rendered_html: null
            });
            this.scrollToBottom(true);

            // Aktifkan indikator pemikiran Tuah AI
            this.isThinking = true;
            this.scrollToBottom(true);

            try {
                const endpoint = '{{ route('command.chat', [], false) }}';
                const csrfToken = document.querySelector('meta[name=csrf-token]')?.content || '';
                const res = await fetch(endpoint, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        prompt: promptText,
                        thread_id: this.activeThreadId || null
                    })
                });

                if (!res.ok) {
                    throw new Error(`Pelayan membalas status HTTP ${res.status}`);
                }

                const data = await res.json();
                if (data.success) {
                    if (data.thread_id) {
                        this.activeThreadId = data.thread_id;
                        const url = new URL(window.location.href);
                        url.searchParams.set('thread_id', data.thread_id);
                        window.history.replaceState({}, '', url.toString());
                    }

                    this.isThinking = false;
                    this.messages.push({
                        id: data.assistant_message.id,
                        role: 'assistant',
                        content: data.assistant_message.content,
                        rendered_html: data.assistant_message.rendered_html
                    });
                    this.scrollToBottom(true);
                } else {
                    throw new Error(data.message || 'Gagal menjana jawapan.');
                }
            } catch (err) {
                console.error('Chat submission error:', err);
                this.isThinking = false;
                this.messages.push({
                    id: 'err-' + Date.now(),
                    role: 'assistant',
                    content: '⚠️ Maaf, berlaku ralat: ' + err.message,
                    rendered_html: '<p class="text-rose-600 font-semibold">⚠️ Maaf, berlaku ralat: ' + err.message + '</p>'
                });
                this.scrollToBottom(true);
            } finally {
                this.isSubmittingChat = false;
                this.isThinking = false;
                this.scrollToBottom(true);
                this.$nextTick(() => {
                    document.querySelector('input[name=prompt]')?.focus();
                });
            }
            return false;
        },

        sendQuickPrompt(text) {
            if (this.isSubmittingChat) return;
            this.chatPrompt = text;
            this.submitChat();
        },

        init() {
            if (this.ollamaModel) {
                if (!this.ollamaModels.includes(this.ollamaModel)) {
                    this.ollamaModels.unshift(this.ollamaModel);
                }
            } else if (this.ollamaModels.length > 0) {
                this.ollamaModel = this.ollamaModels[0];
            }

            if (this.geminiModel) {
                if (!this.geminiModels.includes(this.geminiModel)) {
                    this.geminiModels.unshift(this.geminiModel);
                }
            } else if (this.geminiModels.length > 0) {
                this.geminiModel = this.geminiModels[0];
            }

            // Auto-scroll ke mesej terkini di bawah serta-merta tanpa perlu pengguna skrol manual
            this.scrollToBottom(false);
            setTimeout(() => this.scrollToBottom(false), 80);
            setTimeout(() => this.scrollToBottom(false), 250);
        },

        toggleOllamaCustom(force = null) {
            this.ollamaCustom = force !== null ? force : !this.ollamaCustom;
            if (this.ollamaCustom) {
                if (this.ollamaModel === '__manual__') {
                    this.ollamaModel = '';
                }
            } else {
                if (!this.ollamaModel && this.ollamaModels.length > 0) {
                    this.ollamaModel = this.ollamaModels[0];
                } else if (this.ollamaModel && !this.ollamaModels.includes(this.ollamaModel)) {
                    this.ollamaModels.unshift(this.ollamaModel);
                }
            }
        },

        toggleGeminiCustom(force = null) {
            this.geminiCustom = force !== null ? force : !this.geminiCustom;
            if (this.geminiCustom) {
                if (this.geminiModel === '__manual__') {
                    this.geminiModel = '';
                }
            } else {
                if (!this.geminiModel && this.geminiModels.length > 0) {
                    this.geminiModel = this.geminiModels[0];
                } else if (this.geminiModel && !this.geminiModels.includes(this.geminiModel)) {
                    this.geminiModels.unshift(this.geminiModel);
                }
            }
        },

        async pullGeminiModels() {
            this.pullingGemini = true;
            this.testStatus = null;
            this.testMessage = '';
            try {
                const keyParam = this.geminiKey ? encodeURIComponent(this.geminiKey) : '';
                const endpoint = '{{ route('command.models', [], false) }}' + `?provider=gemini&key=${keyParam}`;
                const res = await fetch(endpoint, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                if (!res.ok) {
                    throw new Error(`Pelayan membalas status HTTP ${res.status}`);
                }

                const data = await res.json();
                if (data.success && data.models && data.models.length > 0) {
                    this.geminiModels = data.models;
                    this.geminiCustom = false;
                    if (!this.geminiModels.includes(this.geminiModel)) {
                        this.geminiModel = this.geminiModels[0];
                    }

                    if (data.live) {
                        this.testStatus = 'success';
                        this.testMessage = data.message || `Berjaya menarik ${data.models.length} model daripada Google Gemini API!`;
                    } else {
                        this.testStatus = 'warning';
                        this.testMessage = data.message || `Kunci API belum diisi. Senarai model rasmi Google dimuatkan.`;
                    }
                } else {
                    this.testStatus = 'error';
                    this.testMessage = data.message || 'Tiada model dijumpai dari Google Gemini API.';
                }
            } catch (err) {
                console.error('Ralat menarik model Gemini:', err);
                this.testStatus = 'error';
                this.testMessage = 'Gagal menghubungi sistem untuk menarik model Gemini: ' + err.message;
            } finally {
                this.pullingGemini = false;
            }
        },

        async pullOllamaModels() {
            this.pullingOllama = true;
            this.testStatus = null;
            this.testMessage = '';
            try {
                const urlParam = encodeURIComponent(this.ollamaUrl);
                const endpoint = '{{ route('command.models', [], false) }}' + `?provider=ollama&url=${urlParam}`;
                const res = await fetch(endpoint, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                if (!res.ok) {
                    throw new Error(`Pelayan membalas status HTTP ${res.status}`);
                }

                const data = await res.json();
                if (data.success && data.models && data.models.length > 0) {
                    this.ollamaModels = data.models;
                    this.ollamaCustom = false;
                    if (!this.ollamaModels.includes(this.ollamaModel)) {
                        this.ollamaModel = this.ollamaModels[0];
                    }

                    if (data.live) {
                        this.testStatus = 'success';
                        this.testMessage = data.message || `Berjaya menarik ${data.models.length} model terus dari pelayan Ollama!`;
                    } else {
                        this.testStatus = 'warning';
                        this.testMessage = data.message || `Pelayan Ollama di ${this.ollamaUrl} tidak aktif. Senarai model cadangan dimuatkan sebagai rujukan.`;
                    }
                } else {
                    this.testStatus = 'error';
                    this.testMessage = data.message || 'Tiada model dijumpai dari pelayan Ollama.';
                }
            } catch (err) {
                console.error('Ralat menarik model Ollama:', err);
                this.testStatus = 'error';
                this.testMessage = 'Gagal menghubungi sistem untuk menarik model Ollama: ' + err.message;
            } finally {
                this.pullingOllama = false;
            }
        },

        async testConnection() {
            this.testingConnection = true;
            this.testStatus = null;
            this.testMessage = '';

            try {
                const endpoint = '{{ route('command.test-connection', [], false) }}';
                const csrfToken = document.querySelector('meta[name=csrf-token]')?.content || '';
                const res = await fetch(endpoint, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        provider: this.provider,
                        gemini_api_key: this.geminiKey,
                        gemini_model: this.geminiModel,
                        ollama_url: this.ollamaUrl,
                        ollama_model: this.ollamaModel
                    })
                });

                if (!res.ok) {
                    throw new Error(`Pelayan membalas status HTTP ${res.status}`);
                }

                const data = await res.json();
                this.testStatus = data.success ? 'success' : 'error';
                this.testMessage = data.message || (data.success ? 'Sambungan berjaya!' : 'Sambungan gagal.');
            } catch (err) {
                this.testStatus = 'error';
                this.testMessage = 'Ralat membuat permintaan ujian: ' + err.message;
            } finally {
                this.testingConnection = false;
            }
        }
    };
};

document.addEventListener('alpine:init', () => {
    Alpine.data('tuahCommand', window.tuahCommandComponent);
});
</script>

<div class="h-full flex flex-col max-w-7xl mx-auto space-y-4" x-data="tuahCommandComponent()">

    <!-- Flash Alert Notifications -->
    @if(session('success'))
    <div class="p-3.5 bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-200 dark:border-emerald-800 rounded-2xl text-xs text-emerald-800 dark:text-emerald-200 flex items-center justify-between shadow-sm">
        <div class="flex items-center gap-2">
            <span>✅</span>
            <span>{{ session('success') }}</span>
        </div>
        <button type="button" @click="$el.parentElement.remove()" class="text-emerald-600 hover:text-emerald-900 font-bold">&times;</button>
    </div>
    @endif

    @if(session('error'))
    <div class="p-3.5 bg-rose-50 dark:bg-rose-950/60 border border-rose-200 dark:border-rose-800 rounded-2xl text-xs text-rose-800 dark:text-rose-200 flex items-center justify-between shadow-sm">
        <div class="flex items-center gap-2">
            <span>⚠️</span>
            <span>{{ session('error') }}</span>
        </div>
        <button type="button" @click="$el.parentElement.remove()" class="text-rose-600 hover:text-rose-900 font-bold">&times;</button>
    </div>
    @endif

    <!-- Header Section (Clean & Uncluttered) -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Tuah Enjin Kepintaran Buatan (AI Command)</span>
            </div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white mt-0.5">Pusat Arahan Pintar Merentas Modul</h1>
        </div>

        <div class="flex items-center gap-2 flex-wrap">
            <!-- Toggle History Sidebar Button -->
            <button type="button" @click="showHistory = !showHistory" class="px-3 py-1.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 text-xs font-semibold text-slate-700 dark:text-slate-200 transition flex items-center gap-1.5 shadow-2xs cursor-pointer" title="Papar atau sembunyi sejarah sesi sembang">
                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"/></svg>
                <span>Sejarah Sesi</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10px] font-bold bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300">{{ $threads->count() }}</span>
            </button>

            <!-- Settings Modal Button -->
            <button type="button" @click="showSettings = true" class="px-3.5 py-1.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 text-xs font-semibold text-slate-700 dark:text-slate-200 transition flex items-center gap-1.5 shadow-2xs cursor-pointer">
                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                <span>Tetapan Enjin AI</span>
            </button>

            <!-- New Session Button (Opens genuine clean new session) -->
            <a href="{{ route('command.index', ['new' => 1]) }}" class="px-3.5 py-1.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-xs transition shadow-2xs flex items-center gap-1.5 cursor-pointer" title="Buka sesi sembang baharu yang kosong">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>+ Sesi Sembang Baru</span>
            </a>
        </div>
    </div>

    <!-- Main Content Flex (History Sidebar + Chat Container) -->
    <div class="flex-1 flex flex-col lg:flex-row gap-4 min-h-0 overflow-hidden">
        <!-- Sessions History Sidebar -->
        <div x-show="showHistory" x-cloak
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 -translate-x-4"
            x-transition:enter-end="opacity-100 translate-x-0"
            class="w-full lg:w-72 flex-shrink-0 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-4 shadow-sm flex flex-col justify-between overflow-hidden max-h-[500px] lg:max-h-none">
            
            <div class="flex flex-col h-full overflow-hidden">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-bold text-slate-800 dark:text-slate-200">Sejarah Sesi</span>
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400">
                            {{ $threads->count() }}
                        </span>
                    </div>
                    <button type="button" @click="showHistory = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 lg:hidden p-1 text-sm cursor-pointer">&times;</button>
                </div>

                <!-- New Session Primary Button Inside Sidebar -->
                <div class="pt-3 pb-2">
                    <a href="{{ route('command.index', ['new' => 1]) }}" class="w-full py-2.5 px-3 rounded-2xl bg-indigo-50 dark:bg-indigo-950/60 hover:bg-indigo-100 dark:hover:bg-indigo-900/80 text-indigo-700 dark:text-indigo-300 border border-indigo-200/80 dark:border-indigo-800/80 text-xs font-semibold flex items-center justify-center gap-2 transition cursor-pointer shadow-2xs">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        <span>+ Mulakan Sesi Baru</span>
                    </a>
                </div>

                <!-- Threads List -->
                <div class="flex-1 overflow-y-auto space-y-1 pr-1 mt-1">
                    @forelse($threads as $t)
                    @php $isActive = ($activeThread && $activeThread->id === $t->id); @endphp
                    <div class="group relative flex items-center justify-between p-2.5 rounded-2xl transition {{ $isActive ? 'bg-indigo-600 text-white shadow-xs' : 'hover:bg-slate-50 dark:hover:bg-slate-800/60 text-slate-700 dark:text-slate-300' }}">
                        <a href="{{ route('command.index', ['thread_id' => $t->id]) }}" class="flex-1 min-w-0 pr-2 block">
                            <div class="flex items-center gap-1.5 truncate">
                                <span class="text-xs {{ $isActive ? 'text-white' : 'text-slate-400' }}">💬</span>
                                <span class="text-xs truncate block font-medium {{ $isActive ? 'text-white' : 'text-slate-800 dark:text-slate-200' }}">
                                    {{ $t->title ?: 'Sesi Sembang' }}
                                </span>
                            </div>
                            <span class="text-[10px] block mt-0.5 {{ $isActive ? 'text-indigo-200' : 'text-slate-400 dark:text-slate-500' }}">
                                {{ $t->created_at ? $t->created_at->diffForHumans() : '' }}
                            </span>
                        </a>

                        <form method="POST" action="{{ route('command.thread.delete', $t->id) }}" onsubmit="return confirm('Adakah anda pasti mahu memadam sesi sembang ini?');" class="flex-shrink-0 {{ $isActive ? 'opacity-90 hover:opacity-100' : 'opacity-0 group-hover:opacity-100' }} transition">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="p-1 rounded-lg {{ $isActive ? 'text-indigo-200 hover:text-white' : 'text-slate-400 hover:text-rose-600' }} transition cursor-pointer" title="Padam Sesi">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </form>
                    </div>
                    @empty
                    <div class="py-8 text-center text-xs text-slate-400 dark:text-slate-500">
                        Tiada sejarah sesi lagi.<br>Mulakan soalan pertama anda!
                    </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Chat Box Container -->
        <div class="flex-1 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 shadow-sm flex flex-col justify-between overflow-hidden min-h-[520px]">
            <!-- Messages Area -->
            <div class="flex-1 overflow-y-auto space-y-4 pr-2 scroll-smooth" id="chatMessages">
                <!-- Initial Welcome Message from Tuah (Only shown when there are NO messages yet) -->
                <div x-show="messages.length === 0" class="flex items-start gap-3">
                    <div class="w-8 h-8 rounded-xl bg-gradient-to-tr from-amber-500 to-indigo-600 flex items-center justify-center text-white text-xs font-bold flex-shrink-0 shadow-sm">
                        ⚡
                    </div>
                    <div class="bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-slate-800 rounded-2xl rounded-tl-sm p-4 text-xs text-slate-800 dark:text-slate-200 max-w-xl space-y-2">
                        <p class="font-semibold text-slate-900 dark:text-white">Salam sejahtera! Saya Tuah, pembantu operasi perniagaan pintar KerjaAI anda.</p>
                        <p class="text-slate-600 dark:text-slate-400">Saya sedia membantu anda menyelaraskan keputusan merentas kewangan &amp; invois (*Lekir*), modal insan &amp; pengambilan staf (*Lekiu*), jualan &amp; produk (*Kasturi*), serta pengiklanan (*Jebat*).</p>
                        <div class="pt-2 border-t border-slate-100 dark:border-slate-800/80 flex flex-wrap gap-1.5">
                            <span class="text-[10px] text-slate-400">Cuba klik arahan pantas:</span>
                            <button type="button" @click="sendQuickPrompt('Berapa baki tertunggak invois bulan ini?')" :disabled="isSubmittingChat" class="text-[11px] px-2 py-0.5 rounded-lg bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 hover:bg-indigo-100 font-medium transition cursor-pointer disabled:opacity-50">
                                "Berapa baki tertunggak invois bulan ini?"
                            </button>
                            <button type="button" @click="sendQuickPrompt('Jana analisa prestasi staf dan kedatangan')" :disabled="isSubmittingChat" class="text-[11px] px-2 py-0.5 rounded-lg bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 hover:bg-indigo-100 font-medium transition cursor-pointer disabled:opacity-50">
                                "Jana analisa prestasi staf"
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Conversation Messages -->
                <template x-for="msg in messages" :key="msg.id">
                    <div class="flex items-start gap-3" :class="msg.role === 'user' ? 'flex-row-reverse' : ''">
                        <div class="w-8 h-8 rounded-xl flex items-center justify-center text-xs font-bold flex-shrink-0 shadow-sm"
                            :class="msg.role === 'user' ? 'bg-indigo-600 text-white' : 'bg-gradient-to-tr from-amber-500 to-indigo-600 text-white'">
                            <span x-text="msg.role === 'user' ? 'U' : '⚡'"></span>
                        </div>
                        <div class="rounded-2xl p-4 text-xs max-w-xl space-y-2"
                            :class="msg.role === 'user' ? 'bg-indigo-600 text-white rounded-tr-sm shadow-xs' : 'bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-slate-800 rounded-tl-sm text-slate-800 dark:text-slate-200'">
                            <template x-if="msg.role === 'assistant'">
                                <div class="tuah-rendered-content text-xs leading-relaxed text-slate-800 dark:text-slate-200" x-html="msg.rendered_html || msg.content"></div>
                            </template>
                            <template x-if="msg.role === 'user'">
                                <div class="whitespace-pre-wrap leading-relaxed" x-text="msg.content"></div>
                            </template>
                        </div>
                    </div>
                </template>

                <!-- Live Thinking Indicator -->
                <div x-show="isThinking" x-cloak class="flex items-start gap-3">
                    <div class="w-8 h-8 rounded-xl bg-gradient-to-tr from-amber-500 to-indigo-600 flex items-center justify-center text-white text-xs font-bold flex-shrink-0 shadow-sm animate-pulse">
                        ⚡
                    </div>
                    <div class="bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-slate-800 rounded-2xl rounded-tl-sm p-3.5 text-xs text-slate-800 dark:text-slate-200 flex items-center gap-2.5">
                        <svg class="animate-spin w-4 h-4 text-indigo-600" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span class="text-slate-600 dark:text-slate-400 font-medium">Tuah sedang memproses arahan & menyemak data perniagaan...</span>
                    </div>
                </div>
            </div>

            <!-- Prompt Input Form -->
            <div class="pt-4 border-t border-slate-100 dark:border-slate-800 mt-4">
                <form id="chatForm" method="POST" action="{{ route('command.chat', [], false) }}"
                    @submit.prevent="submitChat($event)"
                    class="flex items-center gap-3">
                    @csrf
                    <input type="hidden" name="thread_id" :value="activeThreadId">

                    <div class="flex-1 relative">
                        <input type="text" name="prompt" x-model="chatPrompt" required
                            :readonly="isSubmittingChat"
                            :class="isSubmittingChat ? 'opacity-60 cursor-not-allowed bg-slate-100 dark:bg-slate-800' : ''"
                            placeholder="Tanya soalan operasi perniagaan, semak lejar kewangan, staf, invois syarikat..." autofocus
                            class="w-full px-4 py-3 rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50/70 dark:bg-slate-800/60 text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500 transition">
                    </div>

                    <button type="submit" 
                        :disabled="isSubmittingChat || !chatPrompt.trim()"
                        class="px-5 py-3 rounded-2xl bg-indigo-600 hover:bg-indigo-700 disabled:bg-indigo-400 disabled:opacity-60 disabled:cursor-not-allowed text-white font-semibold text-xs shadow-sm transition flex items-center gap-2 cursor-pointer">
                        <span x-show="!isSubmittingChat" class="flex items-center gap-2">
                            <span>Hantar</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </span>
                        <span x-show="isSubmittingChat" x-cloak class="flex items-center gap-2">
                            <svg class="animate-spin w-4 h-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            <span>Memproses...</span>
                        </span>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- AI Settings Modal -->
    <div x-show="showSettings" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" @click="showSettings = false"></div>

        <div class="flex min-h-full items-center justify-center p-4">
            <div class="relative w-full max-w-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl shadow-2xl p-6 sm:p-8 overflow-hidden z-10 space-y-6">
                <!-- Modal Header -->
                <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-slate-800">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-indigo-500"></span>
                            <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Konfigurasi Enjin Tuah AI</span>
                        </div>
                        <h2 class="text-xl font-bold tracking-tight text-slate-900 dark:text-white mt-0.5">Tetapan Model Bahasa & Kunci Enkripsi</h2>
                    </div>
                    <button type="button" @click="showSettings = false" class="p-2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <!-- Test Connection Live Banner -->
                <div x-show="testStatus !== null" x-cloak class="p-3.5 rounded-2xl text-xs flex items-center justify-between border"
                    :class="{
                        'bg-emerald-50 dark:bg-emerald-950/60 border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-200': testStatus === 'success',
                        'bg-amber-50 dark:bg-amber-950/60 border-amber-200 dark:border-amber-800 text-amber-800 dark:text-amber-200': testStatus === 'warning',
                        'bg-rose-50 dark:bg-rose-950/60 border-rose-200 dark:border-rose-800 text-rose-800 dark:text-rose-200': testStatus === 'error'
                    }">
                    <div class="flex items-start gap-2">
                        <span x-text="testStatus === 'success' ? '✅' : (testStatus === 'warning' ? 'ℹ️' : '❌')"></span>
                        <span x-text="testMessage"></span>
                    </div>
                    <button type="button" @click="testStatus = null" class="font-bold cursor-pointer">&times;</button>
                </div>

                <!-- Settings Form -->
                <form method="POST" action="{{ route('command.settings.update') }}" class="space-y-6">
                    @csrf

                    <!-- Provider Selection (Tabs / Cards) - DEFAULT IS OLLAMA -->
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-2">Pilih Penyedia Enjin AI (AI Provider)</label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <!-- Option 1: Ollama Local (DEFAULT) -->
                            <label class="relative flex flex-col p-4 rounded-2xl border cursor-pointer transition"
                                :class="provider === 'ollama' ? 'border-amber-600 dark:border-amber-500 bg-amber-50/50 dark:bg-amber-950/30 ring-2 ring-amber-500/20' : 'border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800/40'">
                                <div class="flex items-center justify-between mb-1.5">
                                    <div class="flex items-center gap-2 font-bold text-sm text-slate-900 dark:text-white">
                                        <span>🦙</span>
                                        <span>Ollama (Lalai Tempatan)</span>
                                    </div>
                                    <input type="radio" name="provider" value="ollama" x-model="provider" class="text-amber-600 focus:ring-amber-500">
                                </div>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400">Pelayan Ollama sendiri (Localhost / LAN). 100% data sulit kekal dalam premis anda (Gemma, Llama, Mistral, DeepSeek).</p>
                                <span class="inline-block mt-2 text-[10px] font-semibold text-amber-600 dark:text-amber-400">Pilihan Lalai: Privasi Penuh & Data Sovereign</span>
                            </label>

                            <!-- Option 2: Google Gemini -->
                            <label class="relative flex flex-col p-4 rounded-2xl border cursor-pointer transition"
                                :class="provider === 'gemini' ? 'border-indigo-600 dark:border-indigo-500 bg-indigo-50/50 dark:bg-indigo-950/30 ring-2 ring-indigo-500/20' : 'border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800/40'">
                                <div class="flex items-center justify-between mb-1.5">
                                    <div class="flex items-center gap-2 font-bold text-sm text-slate-900 dark:text-white">
                                        <span>⚡</span>
                                        <span>Google Gemini</span>
                                    </div>
                                    <input type="radio" name="provider" value="gemini" x-model="provider" class="text-indigo-600 focus:ring-indigo-500">
                                </div>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400">Model awan berkelajuan tinggi dari Google. Sokongan semua model Gemini (Flash, Pro, Gemma) tanpa had.</p>
                                <span class="inline-block mt-2 text-[10px] font-semibold text-indigo-600 dark:text-indigo-400">Awan Berkelajuan Tinggi</span>
                            </label>
                        </div>
                    </div>

                    <!-- Ollama Specific Fields -->
                    <div x-show="provider === 'ollama'" x-cloak class="space-y-4 p-4 rounded-2xl bg-amber-50/50 dark:bg-amber-950/20 border border-amber-200/60 dark:border-amber-900/40">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-amber-900 dark:text-amber-200">Konfigurasi Pelayan Ollama</span>
                            <span class="text-[10px] px-2 py-0.5 rounded-full bg-amber-200/60 dark:bg-amber-900/60 text-amber-900 dark:text-amber-200 font-mono">URL Disulitkan (Encrypted) 🔒</span>
                        </div>

                        <!-- Ollama URL -->
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">URL Pelayan Ollama (Ollama Base URL)</label>
                            <input type="text" name="ollama_url" x-model="ollamaUrl" placeholder="http://127.0.0.1:11434"
                                class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-xs focus:ring-2 focus:ring-amber-500 focus:outline-none">
                            <p class="text-[11px] text-slate-500 mt-1">
                                ℹ️ Contoh: <code class="text-[10px] bg-slate-200 dark:bg-slate-700 px-1 py-0.5 rounded">http://192.168.1.99:11434</code> atau <code class="text-[10px] bg-slate-200 dark:bg-slate-700 px-1 py-0.5 rounded">http://127.0.0.1:11434</code>.
                            </p>
                        </div>

                        <!-- Ollama Model Selection -->
                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <label class="text-xs font-semibold text-slate-700 dark:text-slate-300">Model Ollama</label>
                                <div class="flex items-center gap-3">
                                    <button type="button" @click="pullOllamaModels()" :disabled="pullingOllama" class="text-[11px] text-amber-700 dark:text-amber-400 hover:underline flex items-center gap-1 cursor-pointer font-medium disabled:opacity-50">
                                        <span x-show="!pullingOllama" class="flex items-center gap-1">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                            <span>Tarik Model dari Pelayan</span>
                                        </span>
                                        <span x-show="pullingOllama" class="flex items-center gap-1">
                                            <svg class="animate-spin w-3.5 h-3.5" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                            <span>Menarik Model...</span>
                                        </span>
                                    </button>
                                    <span class="text-slate-300 dark:text-slate-600 text-xs">|</span>
                                    <button type="button" @click="toggleOllamaCustom()" class="text-[11px] text-slate-600 dark:text-slate-400 hover:text-amber-700 dark:hover:text-amber-400 hover:underline cursor-pointer">
                                        <span x-show="!ollamaCustom">Taip Manual</span>
                                        <span x-show="ollamaCustom">Pilih Dropdown</span>
                                    </button>
                                </div>
                            </div>

                            <!-- Dropdown Select Mode -->
                            <div x-show="!ollamaCustom">
                                <div class="relative">
                                    <select :disabled="ollamaCustom" name="ollama_model" x-model="ollamaModel"
                                        @change="if ($event.target.value === '__manual__') { toggleOllamaCustom(true); }"
                                        class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-xs text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-amber-500 focus:outline-none font-mono cursor-pointer appearance-none pr-10">
                                        <template x-for="m in ollamaModels" :key="m">
                                            <option :value="m" x-text="m" :selected="m === ollamaModel"></option>
                                        </template>
                                        <option value="__manual__">+ Taip model lain secara manual...</option>
                                    </select>
                                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-slate-500">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                    </div>
                                </div>
                                <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-1.5 flex items-center justify-between">
                                    <span>Model aktif: <code class="text-[10px] bg-slate-200 dark:bg-slate-700 px-1 py-0.5 rounded font-mono font-semibold" x-text="ollamaModel"></code></span>
                                    <span class="text-[10px] text-slate-400 dark:text-slate-500" x-text="ollamaModels.length + ' model sedia ada'"></span>
                                </div>
                            </div>

                            <!-- Manual Text Input Mode -->
                            <div x-show="ollamaCustom" x-cloak class="space-y-1.5">
                                <div class="relative">
                                    <input :disabled="!ollamaCustom" type="text" name="ollama_model" x-model="ollamaModel" placeholder="Contoh: gemma4:e2b atau llama3:latest"
                                        class="w-full px-3.5 py-2.5 rounded-xl border border-amber-300 dark:border-amber-700 bg-white dark:bg-slate-900 text-xs focus:ring-2 focus:ring-amber-500 focus:outline-none font-mono">
                                </div>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400">
                                    Mod manual: Anda boleh menaip sebarang tag model yang dipasang pada pelayan Ollama anda (contoh: <code class="text-[10px] bg-slate-200 dark:bg-slate-700 px-1 py-0.5 rounded font-mono">gemma4:e2b</code>, <code class="text-[10px] bg-slate-200 dark:bg-slate-700 px-1 py-0.5 rounded font-mono">llama3:latest</code>).
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Gemini Specific Fields -->
                    <div x-show="provider === 'gemini'" x-cloak class="space-y-4 p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/50 border border-slate-100 dark:border-slate-800">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-slate-800 dark:text-slate-200">Konfigurasi Google Gemini</span>
                            <span class="text-[10px] px-2 py-0.5 rounded-full bg-indigo-100 dark:bg-indigo-900/60 text-indigo-700 dark:text-indigo-300 font-mono">Enkripsi AES-256 Aktif 🔒</span>
                        </div>

                        <!-- Gemini API Key -->
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label class="text-xs font-semibold text-slate-700 dark:text-slate-300">Kunci API Gemini Syarikat (Gemini API Key)</label>
                                <button type="button" @click="showKey = !showKey" class="text-[11px] text-indigo-600 hover:underline cursor-pointer">
                                    <span x-text="showKey ? 'Sembunyi Kunci' : 'Papar Kunci'"></span>
                                </button>
                            </div>
                            <div class="relative">
                                <input :type="showKey ? 'text' : 'password'" name="gemini_api_key" x-model="geminiKey"
                                    placeholder="{{ !empty($agentConfig->gemini_api_key) ? '•••••••••••••••• (Kunci Tersimpan & Disulitkan)' : 'Masukkan Kunci API Gemini (atau kosongkan untuk guna .env)' }}"
                                    class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                            </div>
                            <p class="text-[11px] text-slate-500 mt-1">
                                ℹ️ Kunci ini <strong>dienkripsi secara selamat</strong> dalam pangkalan data. Jika dikosongkan, Tuah akan menggunakan kunci lalai sistem pelayan (<code class="text-[10px] bg-slate-200 dark:bg-slate-700 px-1 py-0.5 rounded">GEMINI_API_KEY</code> di .env).
                            </p>
                        </div>

                        <!-- Gemini Model Selection -->
                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <label class="text-xs font-semibold text-slate-700 dark:text-slate-300">Model Gemini</label>
                                <div class="flex items-center gap-3">
                                    <button type="button" @click="pullGeminiModels()" :disabled="pullingGemini" class="text-[11px] text-indigo-600 dark:text-indigo-400 hover:underline flex items-center gap-1 cursor-pointer font-medium disabled:opacity-50">
                                        <span x-show="!pullingGemini" class="flex items-center gap-1">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                            <span>Tarik Model dari Google</span>
                                        </span>
                                        <span x-show="pullingGemini" class="flex items-center gap-1">
                                            <svg class="animate-spin w-3.5 h-3.5" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                            <span>Menarik Model...</span>
                                        </span>
                                    </button>
                                    <span class="text-slate-300 dark:text-slate-600 text-xs">|</span>
                                    <button type="button" @click="toggleGeminiCustom()" class="text-[11px] text-slate-600 dark:text-slate-400 hover:text-indigo-600 dark:hover:text-indigo-400 hover:underline cursor-pointer">
                                        <span x-show="!geminiCustom">Taip Manual</span>
                                        <span x-show="geminiCustom">Pilih Dropdown</span>
                                    </button>
                                </div>
                            </div>

                            <!-- Dropdown Select Mode -->
                            <div x-show="!geminiCustom">
                                <div class="relative">
                                    <select :disabled="geminiCustom" name="gemini_model" x-model="geminiModel"
                                        @change="if ($event.target.value === '__manual__') { toggleGeminiCustom(true); }"
                                        class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-xs text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500 focus:outline-none font-mono cursor-pointer appearance-none pr-10">
                                        <template x-for="m in geminiModels" :key="m">
                                            <option :value="m" x-text="m" :selected="m === geminiModel"></option>
                                        </template>
                                        <option value="__manual__">+ Taip model lain secara manual...</option>
                                    </select>
                                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-slate-500">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                    </div>
                                </div>
                                <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-1.5 flex items-center justify-between">
                                    <span>Model aktif: <code class="text-[10px] bg-slate-200 dark:bg-slate-700 px-1 py-0.5 rounded font-mono font-semibold" x-text="geminiModel"></code></span>
                                    <span class="text-[10px] text-slate-400 dark:text-slate-500" x-text="geminiModels.length + ' model sedia ada'"></span>
                                </div>
                            </div>

                            <!-- Manual Text Input Mode -->
                            <div x-show="geminiCustom" x-cloak class="space-y-1.5">
                                <div class="relative">
                                    <input :disabled="!geminiCustom" type="text" name="gemini_model" x-model="geminiModel" placeholder="Contoh: gemini-2.5-flash atau gemma-4-31b-it"
                                        class="w-full px-3.5 py-2.5 rounded-xl border border-indigo-300 dark:border-indigo-700 bg-white dark:bg-slate-900 text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none font-mono">
                                </div>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400">
                                    Mod manual: Anda boleh menaip sebarang nama model Google Gemini rasmi secara bebas (contoh: <code class="text-[10px] bg-slate-200 dark:bg-slate-700 px-1 py-0.5 rounded font-mono">gemini-2.5-flash</code>, <code class="text-[10px] bg-slate-200 dark:bg-slate-700 px-1 py-0.5 rounded font-mono">gemini-2.5-pro</code>, <code class="text-[10px] bg-slate-200 dark:bg-slate-700 px-1 py-0.5 rounded font-mono">gemma-4-31b-it</code>).
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- System Prompt & Temperature (Advanced) -->
                    <div x-data="{ openAdvanced: false }" class="border border-slate-100 dark:border-slate-800 rounded-2xl p-4">
                        <button type="button" @click="openAdvanced = !openAdvanced" class="w-full flex items-center justify-between text-xs font-semibold text-slate-700 dark:text-slate-300 cursor-pointer">
                            <span>Tetapan Tambahan (Arahan Sistem & Kreativiti)</span>
                            <span x-text="openAdvanced ? '▲ Tutup' : '▼ Papar'"></span>
                        </button>
                        <div x-show="openAdvanced" x-cloak class="mt-4 space-y-3 pt-3 border-t border-slate-100 dark:border-slate-800">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Arahan Sistem (System Prompt)</label>
                                <textarea name="system_prompt" rows="3" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none">{{ $agentConfig->system_prompt ?? '' }}</textarea>
                                <p class="text-[10px] text-slate-500 mt-0.5">Menetapkan nada dan peranan Tuah sebagai pembantu pintar perniagaan Malaysia.</p>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Tahap Kreativiti (Temperature: 0.0 - 1.0)</label>
                                <input type="number" step="0.05" min="0" max="1" name="temperature" value="{{ $agentConfig->temperature ?? 0.7 }}"
                                    class="w-32 px-3.5 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex flex-col sm:flex-row items-center justify-between gap-3 pt-4 border-t border-slate-100 dark:border-slate-800">
                        <!-- Test Connection Trigger -->
                        <button type="button" @click="testConnection()" :disabled="testingConnection"
                            class="w-full sm:w-auto px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700 text-xs font-semibold text-slate-800 dark:text-slate-200 transition flex items-center justify-center gap-2 cursor-pointer disabled:opacity-50">
                            <span x-show="!testingConnection">⚡ Uji Sambungan Sekarang</span>
                            <span x-show="testingConnection" class="inline-flex items-center gap-1.5">
                                <svg class="animate-spin h-3.5 w-3.5 text-indigo-600" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                <span>Menguji Sambungan...</span>
                            </span>
                        </button>

                        <div class="w-full sm:w-auto flex items-center justify-end gap-2">
                            <button type="button" @click="showSettings = false" class="px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-800 text-xs font-semibold text-slate-600 dark:text-slate-400 transition cursor-pointer">
                                Batal
                            </button>
                            <button type="submit" class="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-xs shadow-sm transition flex items-center gap-2 cursor-pointer">
                                <span>Simpan Tetapan AI</span>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
