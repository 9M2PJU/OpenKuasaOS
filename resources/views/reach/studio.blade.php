@extends('layouts.app', ['title' => 'Jebat Ad Studio — Penjana Iklan Pintar'])

@section('content')
<div class="space-y-6" x-data="jebatAdStudio()">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('reach.index') }}" class="text-xs text-indigo-600 dark:text-indigo-400 font-semibold hover:underline">&larr; Jebat Pemasaran</a>
                <span class="text-slate-400 text-xs">/</span>
                <span class="text-xs text-slate-500 font-medium">Jebat Ad Studio</span>
            </div>
            <h1 class="text-2xl font-black tracking-tight text-slate-900 dark:text-white mt-1">Jebat &bull; Studio Penjana Iklan AI</h1>
            <p class="text-xs text-slate-500 mt-1">Jana salinan copywriting iklan berkesan tinggi untuk produk jualan (Kasturi) dan iklan jawatan kosong (Lekiu).</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('crm.products') }}" class="px-3.5 py-2 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 hover:bg-slate-50 dark:hover:bg-slate-800 text-xs font-semibold text-slate-700 dark:text-slate-300 transition flex items-center gap-1.5 shadow-2xs">
                <span>📦</span>
                <span>Katalog Produk Kasturi</span>
            </a>
            <a href="{{ route('hire.jobs') }}" class="px-3.5 py-2 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 hover:bg-slate-50 dark:hover:bg-slate-800 text-xs font-semibold text-slate-700 dark:text-slate-300 transition flex items-center gap-1.5 shadow-2xs">
                <span>💼</span>
                <span>Jawatan Kosong Lekiu</span>
            </a>
        </div>
    </div>

    <!-- Main Studio Grid: Left Form, Right Generated Copy -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- Left: Configuration Form -->
        <div class="lg:col-span-5 space-y-4">
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-5 shadow-2xs space-y-5">
                <!-- Step 1: Select Type (Product vs Job) -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-2">1. Pilih Jenis Iklan</label>
                    <div class="grid grid-cols-2 gap-2 p-1 bg-slate-100 dark:bg-slate-800 rounded-2xl">
                        <button type="button" @click="setType('product')" :class="type === 'product' ? 'bg-white dark:bg-slate-900 text-indigo-600 dark:text-indigo-400 shadow-2xs font-bold' : 'text-slate-500 font-medium'" class="py-2 px-3 rounded-xl text-xs transition flex items-center justify-center gap-2 cursor-pointer">
                            <span>📦</span>
                            <span>Iklan Produk / Servis</span>
                        </button>
                        <button type="button" @click="setType('job')" :class="type === 'job' ? 'bg-white dark:bg-slate-900 text-indigo-600 dark:text-indigo-400 shadow-2xs font-bold' : 'text-slate-500 font-medium'" class="py-2 px-3 rounded-xl text-xs transition flex items-center justify-center gap-2 cursor-pointer">
                            <span>💼</span>
                            <span>Iklan Jawatan Kosong</span>
                        </button>
                    </div>
                </div>

                <!-- Step 2: Target Item Selection -->
                <div x-show="type === 'product'">
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">2. Pilih Produk dari Katalog</label>
                        <a href="{{ route('crm.products') }}" class="text-[10px] text-indigo-600 dark:text-indigo-400 hover:underline">+ Urus Katalog</a>
                    </div>
                    <select x-model="productId" @change="onProductChange()" class="w-full px-3 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs font-medium text-slate-800 dark:text-slate-200">
                        @foreach($products as $prod)
                        <option value="{{ $prod->id }}" {{ ($selectedProduct && $selectedProduct->id === $prod->id) ? 'selected' : '' }}>
                            {{ $prod->name }} (RM {{ number_format($prod->price, 2) }})
                        </option>
                        @endforeach
                    </select>

                    <!-- Selected Product USP Preview Card -->
                    <template x-if="currentProduct">
                        <div class="mt-3 p-3 rounded-2xl bg-indigo-50/70 dark:bg-indigo-950/40 border border-indigo-100 dark:border-indigo-900/60 text-xs text-indigo-950 dark:text-indigo-200 space-y-1">
                            <div class="flex items-center justify-between font-bold">
                                <span x-text="currentProduct.name"></span>
                                <span class="text-indigo-600 dark:text-indigo-400" x-text="'RM ' + Number(currentProduct.price).toFixed(2)"></span>
                            </div>
                            <p class="text-[11px] text-slate-600 dark:text-slate-400 line-clamp-2" x-text="currentProduct.usp || currentProduct.description || 'Tiada maklumat USP'"></p>
                        </div>
                    </template>
                </div>

                <div x-show="type === 'job'" style="display: none;">
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">2. Pilih Jawatan Kosong (Lekiu)</label>
                        <a href="{{ route('hire.jobs') }}" class="text-[10px] text-indigo-600 dark:text-indigo-400 hover:underline">+ Buka Jawatan</a>
                    </div>
                    <select x-model="jobId" @change="onJobChange()" class="w-full px-3 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs font-medium text-slate-800 dark:text-slate-200">
                        @forelse($jobs as $j)
                        <option value="{{ $j->id }}" {{ ($selectedJob && $selectedJob->id === $j->id) ? 'selected' : '' }}>
                            {{ $j->title }} ({{ $j->department ?: 'Umum' }})
                        </option>
                        @empty
                        <option value="">Tiada jawatan kosong aktif dibuka</option>
                        @endforelse
                    </select>

                    <!-- Selected Job Preview Card -->
                    <template x-if="currentJob">
                        <div class="mt-3 p-3 rounded-2xl bg-purple-50/70 dark:bg-purple-950/40 border border-purple-100 dark:border-purple-900/60 text-xs text-purple-950 dark:text-purple-200 space-y-1">
                            <div class="flex items-center justify-between font-bold">
                                <span x-text="currentJob.title"></span>
                                <span class="text-purple-600 dark:text-purple-400" x-text="currentJob.department"></span>
                            </div>
                            <p class="text-[11px] text-slate-600 dark:text-slate-400 line-clamp-2" x-text="currentJob.description || currentJob.requirements || 'Kemasukan segera.'"></p>
                        </div>
                    </template>
                </div>

                <!-- Step 3: Platform Selection -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-2">3. Platform Sasaran Iklan</label>
                    <div class="grid grid-cols-2 gap-2">
                        <button type="button" @click="platform = 'facebook'" :class="platform === 'facebook' ? 'border-indigo-600 bg-indigo-50/50 dark:bg-indigo-950/40 text-indigo-700 dark:text-indigo-300 font-bold' : 'border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300'" class="p-3 rounded-2xl border text-xs text-left transition flex items-center gap-2 cursor-pointer">
                            <span class="text-base">📘</span>
                            <div>
                                <div class="font-bold">Meta / FB / IG</div>
                                <div class="text-[10px] text-slate-400">Post &amp; Feed Ads</div>
                            </div>
                        </button>
                        <button type="button" @click="platform = 'tiktok'" :class="platform === 'tiktok' ? 'border-pink-600 bg-pink-50/50 dark:bg-pink-950/40 text-pink-700 dark:text-pink-300 font-bold' : 'border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300'" class="p-3 rounded-2xl border text-xs text-left transition flex items-center gap-2 cursor-pointer">
                            <span class="text-base">🎵</span>
                            <div>
                                <div class="font-bold">TikTok Script</div>
                                <div class="text-[10px] text-slate-400">Video 15-30 saat</div>
                            </div>
                        </button>
                        <button type="button" @click="platform = 'whatsapp'" :class="platform === 'whatsapp' ? 'border-emerald-600 bg-emerald-50/50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 font-bold' : 'border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300'" class="p-3 rounded-2xl border text-xs text-left transition flex items-center gap-2 cursor-pointer">
                            <span class="text-base">💬</span>
                            <div>
                                <div class="font-bold">WhatsApp Broadcast</div>
                                <div class="text-[10px] text-slate-400">Hebahan Pelanggan</div>
                            </div>
                        </button>
                        <button type="button" @click="platform = 'google'" :class="platform === 'google' ? 'border-amber-600 bg-amber-50/50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-300 font-bold' : 'border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300'" class="p-3 rounded-2xl border text-xs text-left transition flex items-center gap-2 cursor-pointer">
                            <span class="text-base">🔍</span>
                            <div>
                                <div class="font-bold">Google Search</div>
                                <div class="text-[10px] text-slate-400">Headlines &amp; Snippets</div>
                            </div>
                        </button>
                    </div>
                </div>

                <!-- Step 4: Promo / Discount Details (Optional) -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">4. Tawaran Tambahan / Kod Promo (Pilihan)</label>
                    <input type="text" x-model="promoText" placeholder="cth: Diskaun 15% Kod 'MERDEKA' atau Percuma Penghantaran"
                        class="w-full px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs">
                </div>

                <!-- Submit Button -->
                <div class="pt-2">
                    <button type="button" @click="generateCopy()" :disabled="isLoading" class="w-full py-3 px-4 rounded-2xl bg-gradient-to-r from-pink-600 via-rose-600 to-indigo-600 hover:from-pink-700 hover:to-indigo-700 text-white font-bold text-xs shadow-md transition flex items-center justify-center gap-2 cursor-pointer disabled:opacity-50">
                        <span x-show="!isLoading">⚡ Jana Salinan Iklan (Jebat AI)</span>
                        <span x-show="isLoading" class="flex items-center gap-2">
                            <svg class="animate-spin w-4 h-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                            <span>Jebat AI Sedang Menulis...</span>
                        </span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Right: Generated Ad Copy Preview -->
        <div class="lg:col-span-7">
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 shadow-2xs flex flex-col justify-between h-full min-h-[460px]">
                <div>
                    <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-slate-800">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-pink-500 animate-pulse"></span>
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300">Hasil Salinan Iklan (Generated Copy)</span>
                        </div>
                        <template x-if="generatedCopy">
                            <button type="button" @click="copyToClipboard()" class="px-3 py-1.5 rounded-xl border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 text-xs font-semibold text-slate-700 dark:text-slate-200 transition flex items-center gap-1.5 cursor-pointer">
                                <span x-show="!copied">📋 Salin Teks</span>
                                <span x-show="copied" class="text-emerald-600 font-bold">✓ Disalin!</span>
                            </button>
                        </template>
                    </div>

                    <!-- Output Content -->
                    <div class="mt-4">
                        <template x-if="!generatedCopy && !isLoading">
                            <div class="py-20 text-center text-slate-400 space-y-3">
                                <span class="text-4xl block">✍️</span>
                                <h3 class="text-sm font-bold text-slate-700 dark:text-slate-300">Studio sedia beroperasi</h3>
                                <p class="text-xs max-w-sm mx-auto text-slate-500">Pilih produk atau jawatan di sebelah kiri, pilih platform sasaran dan tekan butang <strong>"Jana Salinan Iklan"</strong> untuk menghasilkan iklan berkualiti tinggi.</p>
                            </div>
                        </template>

                        <template x-if="isLoading">
                            <div class="py-20 text-center space-y-3">
                                <div class="w-10 h-10 border-4 border-pink-500 border-t-transparent rounded-full animate-spin mx-auto"></div>
                                <h3 class="text-xs font-bold text-slate-800 dark:text-slate-200">Menganalisis kelebihan produk &amp; merangka copywriting...</h3>
                            </div>
                        </template>

                        <template x-if="generatedCopy && !isLoading">
                            <div class="space-y-4">
                                <!-- Headline Box -->
                                <div class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-slate-800">
                                    <span class="text-[10px] font-bold uppercase text-slate-400 block mb-1">Tajuk / Hook Utama</span>
                                    <div class="text-sm font-black text-slate-900 dark:text-white" x-text="generatedCopy.headline"></div>
                                </div>

                                <!-- Body Copy Box -->
                                <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-slate-800 space-y-2">
                                    <span class="text-[10px] font-bold uppercase text-slate-400 block">Teks Iklan Penuh</span>
                                    <div class="text-xs leading-relaxed text-slate-800 dark:text-slate-200 whitespace-pre-line font-mono select-all" x-text="generatedCopy.body"></div>
                                </div>

                                <!-- Career Application Portal Link Card if Job Ad -->
                                <template x-if="generatedCopy.apply_url">
                                    <div class="p-3.5 rounded-2xl bg-emerald-50/70 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/60 flex items-center justify-between gap-3">
                                        <div class="min-w-0">
                                            <span class="text-[10px] font-bold text-emerald-600 dark:text-emerald-400 uppercase flex items-center gap-1.5">
                                                <span>🔗</span>
                                                <span>Pautan Rasmi Calon (Auto-Saring AI)</span>
                                            </span>
                                            <a :href="generatedCopy.apply_url" target="_blank" class="text-xs font-mono font-medium text-emerald-900 dark:text-emerald-200 hover:underline truncate block mt-0.5" x-text="generatedCopy.apply_url"></a>
                                        </div>
                                        <div class="flex items-center gap-1.5 shrink-0">
                                            <a :href="generatedCopy.apply_url" target="_blank" class="px-2.5 py-1.5 rounded-xl border border-emerald-300 dark:border-emerald-700 text-emerald-700 dark:text-emerald-300 text-xs font-semibold hover:bg-emerald-100/50 dark:hover:bg-emerald-900/50 transition">
                                                Buka &rarr;
                                            </a>
                                            <button type="button" @click="copyLink(generatedCopy.apply_url)" class="px-3 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition">
                                                <span x-show="!linkCopied">Salin Link</span>
                                                <span x-show="linkCopied">✓ Disalin!</span>
                                            </button>
                                        </div>
                                    </div>
                                </template>

                                <!-- Call To Action & Hashtags -->
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <div class="p-3 rounded-2xl bg-indigo-50/60 dark:bg-indigo-950/40 border border-indigo-100 dark:border-indigo-900/60 text-xs">
                                        <span class="text-[10px] font-bold text-indigo-500 uppercase block mb-1">Cadangan Call-to-Action (CTA)</span>
                                        <div class="font-bold text-indigo-900 dark:text-indigo-200" x-text="generatedCopy.cta"></div>
                                    </div>
                                    <div class="p-3 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-slate-800 text-xs">
                                        <span class="text-[10px] font-bold text-slate-400 uppercase block mb-1">Tanda Pagar (Hashtags)</span>
                                        <div class="font-mono text-slate-600 dark:text-slate-400 truncate" x-text="generatedCopy.hashtags || 'Tiada hashtag diperlukan'"></div>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Footer Quick Action -->
                <template x-if="generatedCopy">
                    <div class="pt-4 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between">
                        <span class="text-[11px] text-slate-400">Sedia untuk siarkan iklan?</span>
                        <a href="{{ route('reach.campaigns') }}" class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold shadow-xs transition">
                            + Lancar Kempen Iklan Baru &rarr;
                        </a>
                    </div>
                </template>
            </div>
        </div>
    </div>
</div>

<script>
function jebatAdStudio() {
    const productsData = @json($products);
    const jobsData = @json($jobs);
    const initialProductId = @json($selectedProduct ? (string)$selectedProduct->id : ($products->first() ? (string)$products->first()->id : ''));
    const initialJobId = @json($selectedJob ? (string)$selectedJob->id : ($jobs->first() ? (string)$jobs->first()->id : ''));
    const defaultType = @json($initialType);

    return {
        type: defaultType,
        platform: 'facebook',
        productId: initialProductId,
        jobId: initialJobId,
        promoText: '',
        isLoading: false,
        copied: false,
        linkCopied: false,
        generatedCopy: null,

        copyLink(url) {
            if (!url) return;
            navigator.clipboard.writeText(url).then(() => {
                this.linkCopied = true;
                setTimeout(() => this.linkCopied = false, 2500);
            });
        },

        get currentProduct() {
            return productsData.find(p => p.id === this.productId) || productsData[0] || null;
        },

        get currentJob() {
            return jobsData.find(j => j.id === this.jobId) || jobsData[0] || null;
        },

        setType(newType) {
            this.type = newType;
            this.generatedCopy = null;
        },

        onProductChange() {
            this.generatedCopy = null;
        },

        onJobChange() {
            this.generatedCopy = null;
        },

        async generateCopy() {
            this.isLoading = true;
            this.copied = false;
            try {
                const endpoint = '{{ route('reach.generate-copy', [], false) }}';
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
                        type: this.type,
                        platform: this.platform,
                        product_id: this.productId,
                        job_id: this.jobId,
                        promo_text: this.promoText
                    })
                });

                if (!res.ok) {
                    throw new Error(`Pelayan membalas status ${res.status}`);
                }

                const data = await res.json();
                if (data.success) {
                    this.generatedCopy = data;
                } else {
                    alert(data.message || 'Gagal menjana iklan.');
                }
            } catch (err) {
                console.error('Ralat penjanaan iklan:', err);
                alert('Berlaku masalah menghubungi pelayan: ' + err.message);
            } finally {
                this.isLoading = false;
            }
        },

        copyToClipboard() {
            if (!this.generatedCopy) return;
            const fullText = `${this.generatedCopy.headline}\n\n${this.generatedCopy.body}\n\nCTA: ${this.generatedCopy.cta}\n${this.generatedCopy.hashtags}`;
            navigator.clipboard.writeText(fullText).then(() => {
                this.copied = true;
                setTimeout(() => this.copied = false, 2500);
            });
        }
    };
}
</script>
@endsection
