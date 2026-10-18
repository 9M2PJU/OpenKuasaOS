@extends('layouts.app', ['title' => 'Tetapan Iklan & Sambungan — Jebat'])

@section('content')
<div class="space-y-6 pb-16" x-data="{
    connectModal: false,
    activeProvider: null,
    providerTitles: {
        'meta': 'Meta Business Suite (Facebook & Instagram)',
        'google': 'Google Ads (Search, YouTube & PMax)',
        'tiktok': 'TikTok Ads Manager',
        'whatsapp': 'WhatsApp Cloud API'
    },
    openConnect(provider) {
        this.activeProvider = provider;
        this.connectModal = true;
    }
}">
    <!-- Header & Breadcrumbs -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('reach.index') }}" class="text-xs text-[#ff5376] hover:underline font-semibold flex items-center gap-1">
                    &larr; Jebat Pemasaran
                </a>
                <span class="text-slate-500 text-xs">/</span>
                <span class="text-xs text-slate-400 font-medium">Tetapan Iklan (Ad Settings)</span>
            </div>
            <h1 class="text-2xl font-black tracking-tight text-slate-900 dark:text-white mt-1">
                Ad Settings &bull; Sambungan Pembekal Iklan
            </h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                Urus sambungan rasmi platform iklan (Meta, Google, TikTok & WhatsApp), had bajet harian, peraturan automasi & semakan kesihatan sistem.
            </p>
        </div>
        <div class="flex items-center gap-3">
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                AI Ad Engine Aktif
            </span>
            <a href="{{ route('reach.index') }}" class="px-4 py-2 rounded-xl border border-slate-700/80 bg-[#121927] hover:bg-slate-800 text-xs font-semibold text-slate-300 transition">
                Kembali ke Dashboard &rarr;
            </a>
        </div>
    </div>

    @if(session('success'))
    <div class="p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-xs font-semibold flex items-center justify-between">
        <div class="flex items-center gap-2">
            <svg class="w-4 h-4 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
            <span>{{ session('success') }}</span>
        </div>
        <span class="text-[10px] text-emerald-500/80">Kemas kini berjaya disimpan</span>
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- ==============================================
             LEFT 2 COLUMNS: Settings & Providers
             ============================================== -->
        <div class="lg:col-span-2 space-y-6">

            <!-- CARD 1: Connected account -->
            <div class="rounded-2xl p-6 bg-[#121927] border border-slate-800/80 text-white shadow-sm space-y-6">
                <div>
                    <div class="flex items-center justify-between">
                        <h2 class="text-base font-bold text-white tracking-tight flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-[#00b8d4]"></span>
                            Connected account
                        </h2>
                        <span class="text-xs text-slate-400">4 Platform Pengiklanan</span>
                    </div>
                    <p class="text-xs text-slate-400 mt-1">
                        Pautkan akaun pengiklanan digital anda untuk membolehkan penjejakan penukaran, prospek segera dan sinkronisasi audiens secara automatik.
                    </p>
                </div>

                <div class="divide-y divide-slate-800/60">
                    <!-- 1. Meta Business (Facebook & Instagram) -->
                    @php $meta = $settings['providers']['meta'] ?? []; @endphp
                    <div class="py-4 first:pt-0 last:pb-0 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div class="flex items-start sm:items-center gap-3.5">
                            <div class="w-10 h-10 rounded-xl bg-blue-600/10 border border-blue-500/20 flex items-center justify-center shrink-0 text-blue-400">
                                <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24">
                                    <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                                </svg>
                            </div>
                            <div>
                                <div class="flex items-center gap-2">
                                    <h3 class="text-sm font-bold text-white">Meta Business</h3>
                                    @if(!empty($meta['connected']))
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                            Connected
                                        </span>
                                    @else
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-500/10 text-rose-400 border border-rose-500/20">
                                            Action needed
                                        </span>
                                    @endif
                                </div>
                                <p class="text-xs text-slate-400 mt-0.5">
                                    @if(!empty($meta['connected']))
                                        Akaun: <span class="font-mono text-slate-200">{{ $meta['account_id'] ?? 'act_82910481203' }}</span> &bull; Halaman: <span class="text-slate-200">{{ $meta['page_name'] ?? 'OpenKuasa Official' }}</span>
                                    @else
                                        Hubungkan Facebook Page & Pengurus Iklan Meta
                                    @endif
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 self-end sm:self-center">
                            @if(!empty($meta['connected']))
                                <button type="button" @click="openConnect('meta')" class="px-3 py-1.5 rounded-xl border border-slate-700 hover:bg-slate-800 text-xs font-semibold text-slate-300 transition">
                                    Konfigurasi
                                </button>
                                <form method="POST" action="{{ route('reach.settings.disconnect') }}">
                                    @csrf
                                    <input type="hidden" name="provider" value="meta">
                                    <button type="submit" onclick="return confirm('Adakah anda pasti mahu memutuskan sambungan Meta Business?')" class="px-3 py-1.5 rounded-xl border border-rose-500/30 bg-rose-500/10 hover:bg-rose-500/20 text-xs font-semibold text-rose-400 transition">
                                        Putuskan
                                    </button>
                                </form>
                            @else
                                <button type="button" @click="openConnect('meta')" class="px-4 py-1.5 rounded-xl bg-[#ff5376] hover:bg-rose-600 text-xs font-bold text-white transition shadow-sm">
                                    Sambung Akaun
                                </button>
                            @endif
                        </div>
                    </div>

                    <!-- 2. Google Ads -->
                    @php $google = $settings['providers']['google'] ?? []; @endphp
                    <div class="py-4 first:pt-0 last:pb-0 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div class="flex items-start sm:items-center gap-3.5">
                            <div class="w-10 h-10 rounded-xl bg-amber-500/10 border border-amber-500/20 flex items-center justify-center shrink-0 text-amber-400">
                                <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24">
                                    <path d="M12.48 10.92v3.28h7.84c-.24 1.84-.853 3.187-1.787 4.133-1.147 1.147-2.933 2.4-6.053 2.4-4.827 0-8.6-3.893-8.6-8.72s3.773-8.72 8.6-8.72c2.6 0 4.507 1.027 5.907 2.347l2.307-2.307C18.747 1.44 16.067 0 12.48 0 5.867 0 .307 5.387.307 12s5.56 12 12.173 12c3.573 0 6.267-1.173 8.373-3.36 2.16-2.16 2.84-5.213 2.84-7.667 0-.76-.053-1.467-.173-2.053H12.48z"/>
                                </svg>
                            </div>
                            <div>
                                <div class="flex items-center gap-2">
                                    <h3 class="text-sm font-bold text-white">Google Ads</h3>
                                    @if(!empty($google['connected']))
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                            Connected
                                        </span>
                                    @else
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-800 text-slate-400 border border-slate-700">
                                            Not Connected
                                        </span>
                                    @endif
                                </div>
                                <p class="text-xs text-slate-400 mt-0.5">
                                    @if(!empty($google['connected']))
                                        Customer ID: <span class="font-mono text-slate-200">{{ $google['customer_id'] ?? '982-123-4567' }}</span> &bull; Tag: <span class="text-slate-200">{{ $google['conversion_tag_id'] ?? 'AW-982123456' }}</span>
                                    @else
                                        Pautkan Google Ads untuk Search, Performance Max & YouTube
                                    @endif
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 self-end sm:self-center">
                            @if(!empty($google['connected']))
                                <button type="button" @click="openConnect('google')" class="px-3 py-1.5 rounded-xl border border-slate-700 hover:bg-slate-800 text-xs font-semibold text-slate-300 transition">
                                    Konfigurasi
                                </button>
                                <form method="POST" action="{{ route('reach.settings.disconnect') }}">
                                    @csrf
                                    <input type="hidden" name="provider" value="google">
                                    <button type="submit" onclick="return confirm('Putuskan sambungan Google Ads?')" class="px-3 py-1.5 rounded-xl border border-rose-500/30 bg-rose-500/10 hover:bg-rose-500/20 text-xs font-semibold text-rose-400 transition">
                                        Putuskan
                                    </button>
                                </form>
                            @else
                                <button type="button" @click="openConnect('google')" class="px-4 py-1.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-xs font-bold text-white transition shadow-sm">
                                    Sambung Akaun
                                </button>
                            @endif
                        </div>
                    </div>

                    <!-- 3. TikTok Ads -->
                    @php $tiktok = $settings['providers']['tiktok'] ?? []; @endphp
                    <div class="py-4 first:pt-0 last:pb-0 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div class="flex items-start sm:items-center gap-3.5">
                            <div class="w-10 h-10 rounded-xl bg-cyan-500/10 border border-cyan-500/20 flex items-center justify-center shrink-0 text-cyan-400">
                                <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24">
                                    <path d="M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.93-.01 2.92.01 5.84-.02 8.75-.08 1.4-.54 2.79-1.35 3.94-1.31 1.92-3.58 3.17-5.91 3.21-1.43.08-2.86-.31-4.08-1.03-2.02-1.19-3.44-3.37-3.65-5.71-.02-.5-.03-1-.01-1.49.18-1.9 1.12-3.72 2.58-4.96 1.66-1.44 3.98-2.13 6.15-1.72.02 1.48-.04 2.96-.04 4.44-.99-.32-2.15-.23-3.02.37-.63.41-1.11 1.04-1.36 1.75-.21.51-.24 1.07-.14 1.61.24 1.64 1.82 2.89 3.48 2.78 1.47-.02 2.74-1.09 3.01-2.54.14-.64.15-1.31.14-1.97V.02z"/>
                                </svg>
                            </div>
                            <div>
                                <div class="flex items-center gap-2">
                                    <h3 class="text-sm font-bold text-white">TikTok Ads Manager</h3>
                                    @if(!empty($tiktok['connected']))
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                            Connected
                                        </span>
                                    @else
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-800 text-slate-400 border border-slate-700">
                                            Not Connected
                                        </span>
                                    @endif
                                </div>
                                <p class="text-xs text-slate-400 mt-0.5">
                                    @if(!empty($tiktok['connected']))
                                        Advertiser ID: <span class="font-mono text-slate-200">{{ $tiktok['advertiser_id'] ?? 'adv_994827104' }}</span> &bull; Pixel: <span class="text-slate-200">{{ $tiktok['pixel_id'] ?? 'tt_px_192837' }}</span>
                                    @else
                                        Sambungkan kempen video viral & borang segera TikTok
                                    @endif
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 self-end sm:self-center">
                            @if(!empty($tiktok['connected']))
                                <button type="button" @click="openConnect('tiktok')" class="px-3 py-1.5 rounded-xl border border-slate-700 hover:bg-slate-800 text-xs font-semibold text-slate-300 transition">
                                    Konfigurasi
                                </button>
                                <form method="POST" action="{{ route('reach.settings.disconnect') }}">
                                    @csrf
                                    <input type="hidden" name="provider" value="tiktok">
                                    <button type="submit" onclick="return confirm('Putuskan sambungan TikTok Ads?')" class="px-3 py-1.5 rounded-xl border border-rose-500/30 bg-rose-500/10 hover:bg-rose-500/20 text-xs font-semibold text-rose-400 transition">
                                        Putuskan
                                    </button>
                                </form>
                            @else
                                <button type="button" @click="openConnect('tiktok')" class="px-4 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-xs font-bold text-white transition shadow-sm">
                                    Sambung Akaun
                                </button>
                            @endif
                        </div>
                    </div>

                    <!-- 4. WhatsApp Cloud API -->
                    @php $whatsapp = $settings['providers']['whatsapp'] ?? []; @endphp
                    <div class="py-4 first:pt-0 last:pb-0 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div class="flex items-start sm:items-center gap-3.5">
                            <div class="w-10 h-10 rounded-xl bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center shrink-0 text-emerald-400">
                                <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24">
                                    <path d="M12.031 0C5.396 0 .029 5.367.029 12.002c0 2.118.552 4.186 1.603 6.009L0 24l6.166-1.618a11.954 11.954 0 005.865 1.528h.005c6.635 0 12.002-5.367 12.002-12.002C24.038 5.367 18.67 0 12.031 0zm0 21.904h-.004a9.923 9.923 0 01-5.06-1.385l-.363-.215-3.76.986.999-3.666-.236-.376a9.897 9.897 0 01-1.517-5.246c0-5.467 4.449-9.916 9.921-9.916 2.65 0 5.14 1.032 7.014 2.906a9.855 9.855 0 012.903 7.01c0 5.468-4.448 9.916-9.921 9.916z"/>
                                </svg>
                            </div>
                            <div>
                                <div class="flex items-center gap-2">
                                    <h3 class="text-sm font-bold text-white">WhatsApp Cloud API</h3>
                                    @if(!empty($whatsapp['connected']))
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                            Verified
                                        </span>
                                    @else
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-800 text-slate-400 border border-slate-700">
                                            Not Connected
                                        </span>
                                    @endif
                                </div>
                                <p class="text-xs text-slate-400 mt-0.5">
                                    @if(!empty($whatsapp['connected']))
                                        Nombor: <span class="font-mono text-slate-200">{{ $whatsapp['phone_number'] ?? '+60 11-2345 6789' }}</span> &bull; WABA ID: <span class="text-slate-200">{{ $whatsapp['waba_id'] ?? 'waba_99182374' }}</span>
                                    @else
                                        Integrasi siaran pemasaran dan balasan AI terus ke WhatsApp
                                    @endif
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 self-end sm:self-center">
                            @if(!empty($whatsapp['connected']))
                                <button type="button" @click="openConnect('whatsapp')" class="px-3 py-1.5 rounded-xl border border-slate-700 hover:bg-slate-800 text-xs font-semibold text-slate-300 transition">
                                    Konfigurasi
                                </button>
                                <form method="POST" action="{{ route('reach.settings.disconnect') }}">
                                    @csrf
                                    <input type="hidden" name="provider" value="whatsapp">
                                    <button type="submit" onclick="return confirm('Putuskan sambungan WhatsApp Cloud API?')" class="px-3 py-1.5 rounded-xl border border-rose-500/30 bg-rose-500/10 hover:bg-rose-500/20 text-xs font-semibold text-rose-400 transition">
                                        Putuskan
                                    </button>
                                </form>
                            @else
                                <button type="button" @click="openConnect('whatsapp')" class="px-4 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-xs font-bold text-white transition shadow-sm">
                                    Sambung Akaun
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- FORM: BUDGET & SPEND, AUTOMATION, NOTIFICATIONS -->
            <form method="POST" action="{{ route('reach.settings.update') }}" class="space-y-6">
                @csrf

                <!-- CARD 2: Budget & spend -->
                <div class="rounded-2xl p-6 bg-[#121927] border border-slate-800/80 text-white shadow-sm space-y-5">
                    <div>
                        <h2 class="text-base font-bold text-white tracking-tight flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-[#ff5376]"></span>
                            Budget & spend
                        </h2>
                        <p class="text-xs text-slate-400 mt-1">
                            Kawal peruntukan had belanjawan pengiklanan digital bagi mengelakkan lonjakan kos tanpa kawalan.
                        </p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <!-- Daily Budget Cap -->
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1.5">
                                Daily budget cap
                            </label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-xs font-bold text-slate-400 pointer-events-none">
                                    RM
                                </span>
                                <input type="number" step="1" min="0" name="daily_budget_cap" required
                                    value="{{ old('daily_budget_cap', $settings['daily_budget_cap'] ?? 150) }}"
                                    class="w-full pl-10 pr-3 py-2.5 rounded-xl border border-slate-700 bg-slate-900/90 text-white font-mono text-sm font-bold focus:border-[#ff5376] focus:ring-1 focus:ring-[#ff5376]">
                            </div>
                            <span class="text-[11px] text-slate-500 mt-1 block">Maksimum perbelanjaan harian</span>
                        </div>

                        <!-- Monthly Budget Cap -->
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1.5">
                                Monthly budget cap
                            </label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-xs font-bold text-slate-400 pointer-events-none">
                                    RM
                                </span>
                                <input type="number" step="1" min="0" name="monthly_budget_cap" required
                                    value="{{ old('monthly_budget_cap', $settings['monthly_budget_cap'] ?? 3000) }}"
                                    class="w-full pl-10 pr-3 py-2.5 rounded-xl border border-slate-700 bg-slate-900/90 text-white font-mono text-sm font-bold focus:border-[#ff5376] focus:ring-1 focus:ring-[#ff5376]">
                            </div>
                            <span class="text-[11px] text-slate-500 mt-1 block">Had kumulatif sebulan</span>
                        </div>

                        <!-- Currency Selector -->
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1.5">
                                Currency
                            </label>
                            <select name="currency" class="w-full px-3 py-2.5 rounded-xl border border-slate-700 bg-slate-900/90 text-white text-xs font-semibold focus:border-[#ff5376] focus:ring-1 focus:ring-[#ff5376]">
                                <option value="MYR" {{ ($settings['currency'] ?? 'MYR') === 'MYR' ? 'selected' : '' }}>MYR (Ringgit Malaysia)</option>
                                <option value="USD" {{ ($settings['currency'] ?? '') === 'USD' ? 'selected' : '' }}>USD ($ US Dollar)</option>
                                <option value="SGD" {{ ($settings['currency'] ?? '') === 'SGD' ? 'selected' : '' }}>SGD (S$ Singapore)</option>
                                <option value="IDR" {{ ($settings['currency'] ?? '') === 'IDR' ? 'selected' : '' }}>IDR (Rp Rupiah)</option>
                            </select>
                            <span class="text-[11px] text-slate-500 mt-1 block">Mata wang pelaporan kempen</span>
                        </div>
                    </div>
                </div>

                <!-- CARD 3: Automation -->
                <div class="rounded-2xl p-6 bg-[#121927] border border-slate-800/80 text-white shadow-sm space-y-5">
                    <div>
                        <h2 class="text-base font-bold text-white tracking-tight flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-[#00b8d4]"></span>
                            Automation
                        </h2>
                        <p class="text-xs text-slate-400 mt-1">
                            Kuasakan peraturan pintar Jebat AI untuk melindungi bajet dan memaksimumkan ROI iklan anda.
                        </p>
                    </div>

                    <div class="space-y-4">
                        <!-- Rule 1: Pause ads below CTR threshold -->
                        <div class="p-4 rounded-xl bg-slate-900/60 border border-slate-800/80 flex items-start justify-between gap-4">
                            <div class="space-y-0.5">
                                <h3 class="text-xs font-bold text-white">Pause ads below CTR threshold</h3>
                                <p class="text-[11px] text-slate-400 leading-relaxed">
                                    Jeda iklan secara automatik sekiranya Click-Through Rate (CTR) jatuh di bawah paras minimum 0.8% selama 48 jam berturut-turut.
                                </p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer shrink-0 mt-0.5">
                                <input type="checkbox" name="pause_low_ctr" value="1" {{ !empty($settings['pause_low_ctr']) ? 'checked' : '' }} class="sr-only peer">
                                <div class="w-11 h-6 bg-slate-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#ff5376]"></div>
                            </label>
                        </div>

                        <!-- Rule 2: Auto-optimise budget to top performers -->
                        <div class="p-4 rounded-xl bg-slate-900/60 border border-slate-800/80 flex items-start justify-between gap-4">
                            <div class="space-y-0.5">
                                <h3 class="text-xs font-bold text-white">Auto-optimise budget to top performers</h3>
                                <p class="text-[11px] text-slate-400 leading-relaxed">
                                    Agihkan secara dinamik baki bajet harian kepada ad set dengan kos per prospek (CPL) paling rendah dalam masa nyata.
                                </p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer shrink-0 mt-0.5">
                                <input type="checkbox" name="auto_optimize_budget" value="1" {{ !empty($settings['auto_optimize_budget']) ? 'checked' : '' }} class="sr-only peer">
                                <div class="w-11 h-6 bg-slate-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#ff5376]"></div>
                            </label>
                        </div>

                        <!-- Rule 3: Stop spend at daily budget cap -->
                        <div class="p-4 rounded-xl bg-slate-900/60 border border-slate-800/80 flex items-start justify-between gap-4">
                            <div class="space-y-0.5">
                                <h3 class="text-xs font-bold text-white">Stop spend at daily budget cap</h3>
                                <p class="text-[11px] text-slate-400 leading-relaxed">
                                    Hentikan tayangan kempen serta-merta apabila jumlah perbelanjaan mencecah had harian yang ditetapkan (RM {{ number_format($settings['daily_budget_cap'] ?? 150, 0) }}).
                                </p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer shrink-0 mt-0.5">
                                <input type="checkbox" name="stop_daily_cap" value="1" {{ !empty($settings['stop_daily_cap']) ? 'checked' : '' }} class="sr-only peer">
                                <div class="w-11 h-6 bg-slate-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#ff5376]"></div>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- CARD 4: Notifications -->
                <div class="rounded-2xl p-6 bg-[#121927] border border-slate-800/80 text-white shadow-sm space-y-5">
                    <div>
                        <h2 class="text-base font-bold text-white tracking-tight flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-amber-400"></span>
                            Notifications
                        </h2>
                        <p class="text-xs text-slate-400 mt-1">
                            Tetapan sistem amaran pantas untuk pemantauan prestasi dan keselamatan kewangan iklan.
                        </p>
                    </div>

                    <div class="space-y-4">
                        <!-- Notification 1: Alert me on budget overspend -->
                        <div class="p-4 rounded-xl bg-slate-900/60 border border-slate-800/80 flex items-start justify-between gap-4">
                            <div class="space-y-0.5">
                                <h3 class="text-xs font-bold text-white">Alert me on budget overspend</h3>
                                <p class="text-[11px] text-slate-400 leading-relaxed">
                                    Hantar amaran segera melalui emel dan dashboard apabila perbelanjaan mencapai 90% daripada bajet harian.
                                </p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer shrink-0 mt-0.5">
                                <input type="checkbox" name="alert_budget_overspend" value="1" {{ !empty($settings['alert_budget_overspend']) ? 'checked' : '' }} class="sr-only peer">
                                <div class="w-11 h-6 bg-slate-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#ff5376]"></div>
                            </label>
                        </div>

                        <!-- Notification 2: Email me a weekly performance summary -->
                        <div class="p-4 rounded-xl bg-slate-900/60 border border-slate-800/80 flex items-start justify-between gap-4">
                            <div class="space-y-0.5">
                                <h3 class="text-xs font-bold text-white">Email me a weekly performance summary</h3>
                                <p class="text-[11px] text-slate-400 leading-relaxed">
                                    Ringkasan mingguan setiap hari Isnin jam 9:00 pagi merangkumi jumlah prospek, kos per prospek (CPL), dan analisis cadangan AI.
                                </p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer shrink-0 mt-0.5">
                                <input type="checkbox" name="weekly_summary_email" value="1" {{ !empty($settings['weekly_summary_email']) ? 'checked' : '' }} class="sr-only peer">
                                <div class="w-11 h-6 bg-slate-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#ff5376]"></div>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Save Action Button -->
                <div class="flex items-center justify-end gap-3 pt-2">
                    <a href="{{ route('reach.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-700 text-xs font-semibold text-slate-400 hover:text-white transition">
                        Batal
                    </a>
                    <button type="submit" class="px-6 py-2.5 rounded-xl bg-[#ff5376] hover:bg-rose-600 text-white text-xs font-bold shadow-md shadow-rose-500/20 transition flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Simpan Semua Tetapan
                    </button>
                </div>
            </form>
        </div>

        <!-- ==============================================
             RIGHT COLUMN: Card 5 Health check & Assistance
             ============================================== -->
        <div class="space-y-6">

            <!-- CARD 5: Health check (Matching Screenshot) -->
            <div class="rounded-2xl p-6 bg-[#121927] border border-slate-800/80 text-white shadow-sm space-y-6">
                <div>
                    <h2 class="text-base font-bold text-white tracking-tight flex items-center justify-between">
                        <span>Health check</span>
                        <span class="text-[11px] font-semibold text-emerald-400 flex items-center gap-1">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                            Live Monitor
                        </span>
                    </h2>
                    <p class="text-xs text-slate-400 mt-1">
                        Status kesediaan penyepaduan dan piawaian keselamatan akaun pengiklanan.
                    </p>
                </div>

                <!-- Radial / Circular Progress Gauge -->
                <div class="flex flex-col items-center justify-center py-2">
                    <div class="relative w-36 h-36 flex items-center justify-center">
                        @php
                            $circumference = 2 * 3.14159 * 54;
                            $strokeDashoffset = $circumference - ($healthScore / 100) * $circumference;
                        @endphp
                        <svg class="w-full h-full transform -rotate-90" viewBox="0 0 120 120">
                            <!-- Background Circle -->
                            <circle cx="60" cy="60" r="54" stroke="#1f293d" stroke-width="10" fill="transparent"/>
                            <!-- Progress Arc -->
                            <circle cx="60" cy="60" r="54"
                                stroke="{{ $healthScore >= 80 ? '#ff5376' : ($healthScore >= 50 ? '#00b8d4' : '#ef4444') }}"
                                stroke-width="10"
                                stroke-dasharray="{{ $circumference }}"
                                stroke-dashoffset="{{ $strokeDashoffset }}"
                                stroke-linecap="round"
                                fill="transparent"
                                class="transition-all duration-1000 ease-out"/>
                        </svg>
                        <div class="absolute inset-0 flex flex-col items-center justify-center text-center">
                            <span class="text-3xl font-black text-white tracking-tight">{{ $healthScore }}%</span>
                            <span class="text-[10px] uppercase font-bold text-slate-400 tracking-wider">
                                {{ $healthScore >= 80 ? 'Ready' : ($healthScore >= 50 ? 'Suboptimal' : 'Action needed') }}
                            </span>
                        </div>
                    </div>
                    <p class="text-[11px] text-slate-400 mt-3 text-center">
                        @if($healthScore >= 80)
                            Enjin pengiklanan bersedia sepenuhnya untuk penjejakan dan peruntukan bajet optimum.
                        @else
                            Tindakan diperlukan pada sambungan Meta atau borang prospek untuk mengelakkan gangguan kempen.
                        @endif
                    </p>
                </div>

                <!-- Checklist Items -->
                <div class="space-y-3 pt-2 border-t border-slate-800/80">
                    @foreach($checklist as $item)
                    <div class="p-3 rounded-xl bg-slate-900/60 border border-slate-800/80 flex items-center justify-between gap-3">
                        <div class="flex items-center gap-2.5 min-w-0">
                            @if($item['status'] === 'healthy')
                                <div class="w-5 h-5 rounded-full bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 flex items-center justify-center shrink-0">
                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                                    </svg>
                                </div>
                            @elseif($item['status'] === 'warning')
                                <div class="w-5 h-5 rounded-full bg-amber-500/10 border border-amber-500/20 text-amber-400 flex items-center justify-center shrink-0">
                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                    </svg>
                                </div>
                            @else
                                <div class="w-5 h-5 rounded-full bg-rose-500/10 border border-rose-500/20 text-rose-400 flex items-center justify-center shrink-0">
                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </div>
                            @endif
                            <div class="truncate">
                                <span class="block text-xs font-semibold text-white truncate">{{ $item['title'] }}</span>
                                <span class="block text-[10px] text-slate-400 truncate">{{ $item['subtitle'] }}</span>
                            </div>
                        </div>

                        <div>
                            @if($item['status'] === 'healthy')
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 shrink-0">
                                    Healthy
                                </span>
                            @elseif($item['status'] === 'warning')
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-500/10 text-amber-400 border border-amber-500/20 shrink-0">
                                    Warning
                                </span>
                            @else
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-500/10 text-rose-400 border border-rose-500/20 shrink-0">
                                    Action needed
                                </span>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- Help & Documentation Card -->
            <div class="rounded-2xl p-5 bg-[#121927] border border-slate-800/80 text-white shadow-sm space-y-3">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-[#00b8d4]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Panduan Integrasi API
                </h3>
                <ul class="text-xs text-slate-400 space-y-2 leading-relaxed">
                    <li class="flex items-start gap-2">
                        <span class="text-[#ff5376] font-bold">&bull;</span>
                        <span><strong>Meta System User:</strong> Gunakan token kekal (Permanent Token) dengan skop <code>ads_management</code> & <code>leads_retrieval</code>.</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="text-[#00b8d4] font-bold">&bull;</span>
                        <span><strong>Google Ads Manager:</strong> Pautkan akaun pengurus MCC anda untuk penyelarasan tag penukaran automatik.</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="text-emerald-400 font-bold">&bull;</span>
                        <span><strong>Pematuhan Privasi:</strong> Semua data prospek disimpan secara selamat mematuhi akta PDPA Malaysia 2010.</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <!-- ==============================================
         CONNECT PROVIDER MODAL (Alpine.js)
         ============================================== -->
    <div x-show="connectModal" class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm z-50 flex items-center justify-center p-4" style="display: none;">
        <div @click.away="connectModal = false" class="bg-[#121927] border border-slate-800 rounded-2xl max-w-lg w-full p-6 shadow-2xl text-white space-y-5">
            <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                <div>
                    <h3 class="text-base font-bold text-white" x-text="providerTitles[activeProvider] || 'Sambung Akaun Iklan'"></h3>
                    <p class="text-xs text-slate-400 mt-0.5">Konfigurasikan maklumat pengesahan API rasmi.</p>
                </div>
                <button type="button" @click="connectModal = false" class="p-1 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form method="POST" action="{{ route('reach.settings.connect') }}" class="space-y-4">
                @csrf
                <input type="hidden" name="provider" :value="activeProvider">

                <!-- META FIELDS -->
                <template x-if="activeProvider === 'meta'">
                    <div class="space-y-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1">Meta Ad Account ID</label>
                            <input type="text" name="account_id" placeholder="act_82910481203" value="{{ $meta['account_id'] ?? '' }}"
                                class="w-full px-3 py-2 rounded-xl border border-slate-700 bg-slate-900 text-xs font-mono text-white focus:border-[#ff5376] focus:ring-1 focus:ring-[#ff5376]">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1">Meta Pixel ID</label>
                            <input type="text" name="pixel_id" placeholder="px_4829103948" value="{{ $meta['pixel_id'] ?? '' }}"
                                class="w-full px-3 py-2 rounded-xl border border-slate-700 bg-slate-900 text-xs font-mono text-white focus:border-[#ff5376] focus:ring-1 focus:ring-[#ff5376]">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1">Facebook Page Name</label>
                            <input type="text" name="page_name" placeholder="OpenKuasa Official" value="{{ $meta['page_name'] ?? '' }}"
                                class="w-full px-3 py-2 rounded-xl border border-slate-700 bg-slate-900 text-xs text-white focus:border-[#ff5376] focus:ring-1 focus:ring-[#ff5376]">
                        </div>
                    </div>
                </template>

                <!-- GOOGLE FIELDS -->
                <template x-if="activeProvider === 'google'">
                    <div class="space-y-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1">Google Ads Customer ID</label>
                            <input type="text" name="customer_id" placeholder="982-123-4567" value="{{ $google['customer_id'] ?? '' }}"
                                class="w-full px-3 py-2 rounded-xl border border-slate-700 bg-slate-900 text-xs font-mono text-white focus:border-[#ff5376] focus:ring-1 focus:ring-[#ff5376]">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1">Conversion Tracking Tag ID</label>
                            <input type="text" name="conversion_tag_id" placeholder="AW-982123456" value="{{ $google['conversion_tag_id'] ?? '' }}"
                                class="w-full px-3 py-2 rounded-xl border border-slate-700 bg-slate-900 text-xs font-mono text-white focus:border-[#ff5376] focus:ring-1 focus:ring-[#ff5376]">
                        </div>
                    </div>
                </template>

                <!-- TIKTOK FIELDS -->
                <template x-if="activeProvider === 'tiktok'">
                    <div class="space-y-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1">TikTok Advertiser ID</label>
                            <input type="text" name="advertiser_id" placeholder="adv_994827104" value="{{ $tiktok['advertiser_id'] ?? '' }}"
                                class="w-full px-3 py-2 rounded-xl border border-slate-700 bg-slate-900 text-xs font-mono text-white focus:border-[#ff5376] focus:ring-1 focus:ring-[#ff5376]">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1">TikTok Pixel ID</label>
                            <input type="text" name="pixel_id" placeholder="tt_px_192837" value="{{ $tiktok['pixel_id'] ?? '' }}"
                                class="w-full px-3 py-2 rounded-xl border border-slate-700 bg-slate-900 text-xs font-mono text-white focus:border-[#ff5376] focus:ring-1 focus:ring-[#ff5376]">
                        </div>
                    </div>
                </template>

                <!-- WHATSAPP FIELDS -->
                <template x-if="activeProvider === 'whatsapp'">
                    <div class="space-y-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1">WhatsApp Business Phone Number</label>
                            <input type="text" name="phone_number" placeholder="+60 11-2345 6789" value="{{ $whatsapp['phone_number'] ?? '' }}"
                                class="w-full px-3 py-2 rounded-xl border border-slate-700 bg-slate-900 text-xs font-mono text-white focus:border-[#ff5376] focus:ring-1 focus:ring-[#ff5376]">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1">WhatsApp Business Account (WABA) ID</label>
                            <input type="text" name="waba_id" placeholder="waba_99182374" value="{{ $whatsapp['waba_id'] ?? '' }}"
                                class="w-full px-3 py-2 rounded-xl border border-slate-700 bg-slate-900 text-xs font-mono text-white focus:border-[#ff5376] focus:ring-1 focus:ring-[#ff5376]">
                        </div>
                    </div>
                </template>

                <div class="flex justify-end gap-2 pt-4 border-t border-slate-800">
                    <button type="button" @click="connectModal = false" class="px-4 py-2 rounded-xl border border-slate-700 text-xs font-semibold text-slate-400 hover:text-white">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-[#ff5376] hover:bg-rose-600 text-white text-xs font-bold transition shadow-sm">
                        Sahkan & Sambungkan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
