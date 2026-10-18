<!DOCTYPE html>
<html lang="ms" class="h-full bg-slate-50 dark:bg-slate-950">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $job->title }} — Peluang Kerjaya {{ $job->org->name ?? 'KerjaAI' }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

    <!-- Tailwind & App Assets via Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        body {
            font-family: 'Instrument Sans', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        }
        [x-cloak] {
            display: none !important;
        }
    </style>
</head>
<body class="h-full antialiased text-slate-800 dark:text-slate-100 flex flex-col justify-between">
    <!-- Top Navigation Bar -->
    <header class="border-b border-slate-200/80 dark:border-slate-800/80 bg-white/80 dark:bg-slate-900/80 backdrop-blur sticky top-0 z-40">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 py-3.5 flex items-center justify-between">
            <div class="flex items-center gap-3">
                @if($job->org && $job->org->logo_url)
                    <img src="{{ $job->org->logo_url }}" alt="{{ $job->org->name }}" class="h-8 w-auto max-w-[120px] object-contain rounded-lg">
                @else
                    <div class="w-8 h-8 rounded-xl bg-indigo-600 text-white font-black flex items-center justify-center text-sm shadow-sm">
                        {{ substr($job->org->name ?? 'OK', 0, 1) }}
                    </div>
                @endif
                <div>
                    <span class="font-bold text-xs sm:text-sm text-slate-900 dark:text-white block leading-tight">{{ $job->org->name ?? 'Syarikat' }}</span>
                    <span class="text-[10px] sm:text-xs text-slate-500 font-medium">Portal Pengambilan &amp; Kerjaya Rasmi</span>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-200/60 dark:border-emerald-800/60">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span>Permohonan Dibuka</span>
                </span>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="flex-grow py-8 sm:py-12">
        <div class="max-w-5xl mx-auto px-4 sm:px-6">
            <!-- Job Overview Banner -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-3xl p-6 sm:p-8 shadow-xs mb-8">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6 pb-6 border-b border-slate-100 dark:border-slate-800">
                    <div>
                        <div class="flex items-center gap-2 flex-wrap mb-2">
                            <span class="px-2.5 py-1 rounded-lg text-xs font-bold uppercase tracking-wider bg-indigo-50 text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-300 border border-indigo-200/60 dark:border-indigo-800/60">
                                {{ $job->department ?: 'Operasi' }}
                            </span>
                            <span class="px-2.5 py-1 rounded-lg text-xs font-semibold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300">
                                {{ str_replace('_', ' ', ucfirst($job->employment_type)) }}
                            </span>
                            <span class="px-2.5 py-1 rounded-lg text-xs font-semibold bg-purple-50 text-purple-700 dark:bg-purple-950/60 dark:text-purple-300">
                                ⚡ Saringan Auto AI
                            </span>
                        </div>
                        <h1 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white tracking-tight">
                            {{ $job->title }}
                        </h1>
                        <p class="text-xs sm:text-sm text-slate-500 mt-1">
                            Dianjurkan oleh <strong>{{ $job->org->name ?? 'Syarikat Kami' }}</strong> &bull; Kemasukan segera &bull; Sila hantar permohonan anda di bawah.
                        </p>
                    </div>

                    @if($job->salary_min && $job->salary_max)
                    <div class="bg-slate-50 dark:bg-slate-800/60 p-4 rounded-2xl border border-slate-100 dark:border-slate-800 text-left md:text-right shrink-0">
                        <span class="text-[10px] font-bold uppercase text-slate-400 block">Pakej Tawaran Gaji</span>
                        <span class="text-lg sm:text-xl font-black text-emerald-600 dark:text-emerald-400">
                            RM {{ number_format($job->salary_min) }} - RM {{ number_format($job->salary_max) }}
                        </span>
                        <span class="text-[11px] text-slate-400 block">+ KWSP / PERKESO / Elaun</span>
                    </div>
                    @endif
                </div>

                <!-- Job Details Tabs / Sections -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                    <div>
                        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-2 flex items-center gap-1.5">
                            <span>📋</span>
                            <span>Deskripsi Tugas &amp; Peranan</span>
                        </h3>
                        <div class="text-xs sm:text-sm leading-relaxed text-slate-700 dark:text-slate-300 whitespace-pre-line bg-slate-50/60 dark:bg-slate-800/40 p-4 rounded-2xl border border-slate-100 dark:border-slate-800">
                            {{ $job->description ?: 'Melaksanakan tanggungjawab harian selaras dengan objektif jabatan dan piawaian syarikat.' }}
                        </div>
                    </div>

                    <div>
                        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-2 flex items-center gap-1.5">
                            <span>🎯</span>
                            <span>Syarat Kelayakan &amp; Kemahiran</span>
                        </h3>
                        <div class="text-xs sm:text-sm leading-relaxed text-slate-700 dark:text-slate-300 whitespace-pre-line bg-slate-50/60 dark:bg-slate-800/40 p-4 rounded-2xl border border-slate-100 dark:border-slate-800">
                            {{ $job->requirements ?: "• Berdisiplin, menepati masa dan komited dalam tugasan.\n• Berkemahiran berkomunikasi dengan baik dan bekerja dalam pasukan.\n• Sedia belajar dan mempunyai inisiatif perkembangan diri." }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Application Form -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-3xl p-6 sm:p-8 shadow-xs" x-data="careerApplicationForm()">
                <div class="pb-6 border-b border-slate-100 dark:border-slate-800 mb-6">
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold bg-indigo-50 text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-300 border border-indigo-200/60 mb-2">
                        <span>⚡</span>
                        <span>Dikuasakan AI Lekir &bull; Pengisian Auto Pintar</span>
                    </span>
                    <h2 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white">
                        Hantar Permohonan Anda Sekarang
                    </h2>
                    <p class="text-xs text-slate-500 mt-1">
                        Muat naik resume anda terlebih dahulu di Langkah 1. Enjin AI Lekir akan membaca resume dan mengisi maklumat peribadi anda secara automatik. Anda boleh menyunting apa jua maklumat sekiranya perlu sebelum menghantar permohonan.
                    </p>
                </div>

                @if($errors->any())
                <div class="mb-6 p-4 rounded-2xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-900/60 text-xs text-rose-700 dark:text-rose-300 space-y-1">
                    <div class="font-bold flex items-center gap-1.5">
                        <span>⚠️</span>
                        <span>Sila semak maklumat yang dimasukkan:</span>
                    </div>
                    <ul class="list-disc list-inside space-y-0.5 pl-1">
                        @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                <form method="POST" action="{{ route('careers.apply', $job->slug ?: $job->id, false) }}" enctype="multipart/form-data" @submit="handleSubmit($event)" class="space-y-8">
                    @csrf
                    <input type="hidden" name="uploaded_cv_url" :value="uploadedCvUrl">

                    <!-- LANGKAH 1: UPLOAD RESUME DIAWAL (AI AUTO-ISI) -->
                    <div class="p-6 rounded-2xl bg-gradient-to-br from-indigo-50/70 via-white to-purple-50/50 dark:from-slate-800/80 dark:via-slate-900/90 dark:to-indigo-950/30 border-2 border-indigo-200/70 dark:border-indigo-800/60 shadow-xs space-y-4">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="flex items-center justify-center w-6 h-6 rounded-full bg-indigo-600 text-white font-bold text-xs shrink-0">1</span>
                                    <h3 class="text-sm sm:text-base font-bold text-slate-900 dark:text-white">
                                        Langkah 1: Muat Naik Fail Resume / CV Anda Dahulu
                                    </h3>
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-purple-100 text-purple-700 dark:bg-purple-900/60 dark:text-purple-300">
                                        AI Auto-Isi
                                    </span>
                                </div>
                                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 pl-8">
                                    Pilih fail resume anda (PDF, DOCX, TXT). AI akan membaca maklumat dan terus mengisi borang di bawah untuk memudahkan anda.
                                </p>
                            </div>

                            <span class="text-[11px] font-semibold text-rose-500 dark:text-rose-400 pl-8 sm:pl-0">
                                * Fail Resume Diperlukan
                            </span>
                        </div>

                        <!-- Dropzone & File Action Container -->
                        <div class="relative">
                            <input type="file"
                                   x-ref="resumeInput"
                                   id="cv_file_input"
                                   name="cv_file"
                                   accept=".pdf,.doc,.docx,.txt,.rtf"
                                   @change="handleCvUpload($event)"
                                   class="sr-only">

                            <!-- State 1: Ready to choose file -->
                            <div x-show="!isUploading && !uploadedCvUrl" class="border-2 border-dashed border-indigo-300 dark:border-indigo-700/80 hover:border-indigo-500 rounded-2xl p-6 sm:p-8 text-center bg-white/70 dark:bg-slate-800/50 transition">
                                <div class="max-w-md mx-auto space-y-3">
                                    <div class="w-12 h-12 rounded-2xl bg-indigo-100 dark:bg-indigo-900/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center mx-auto text-2xl shadow-xs">
                                        📄
                                    </div>
                                    <div>
                                        <button type="button"
                                                @click="$refs.resumeInput.click()"
                                                class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 active:scale-95 text-white font-bold text-xs sm:text-sm shadow-md transition cursor-pointer">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                                            <span>Pilih &amp; Muat Naik Resume Sekarang</span>
                                        </button>
                                        <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-2">
                                            atau klik di mana-mana dalam kotak ini &bull; PDF, DOC, DOCX, TXT (Maksimum 5MB)
                                        </p>
                                    </div>
                                    <div class="pt-2 border-t border-slate-100 dark:border-slate-800 text-[11px] text-slate-400">
                                        💡 <strong>Sistem Pintar:</strong> Sebaik sahaja fail dipilih, AI Lekir membaca resume dan mengisi borang di Langkah 2 secara automatik.
                                    </div>
                                </div>
                                <div class="absolute inset-0 cursor-pointer" @click="$refs.resumeInput.click()"></div>
                            </div>

                            <!-- State 2: Uploading & AI Parsing Progress -->
                            <div x-show="isUploading" class="border-2 border-indigo-400 dark:border-indigo-600 rounded-2xl p-6 sm:p-8 text-center bg-white dark:bg-slate-800 shadow-sm animate-pulse" style="display: none;">
                                <div class="max-w-md mx-auto space-y-3">
                                    <div class="inline-flex p-3 rounded-full bg-indigo-50 dark:bg-indigo-950 text-indigo-600 dark:text-indigo-400">
                                        <svg class="animate-spin w-8 h-8 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                        </svg>
                                    </div>
                                    <div class="text-sm font-bold text-slate-900 dark:text-white">
                                        🤖 AI Lekir sedang membaca fail Resume &amp; mengekstrak maklumat...
                                    </div>
                                    <p class="text-xs text-slate-500 dark:text-slate-400">
                                        Mengenal pasti nama, tarikh lahir, emel, nombor telefon dan alamat anda untuk mengisi borang secara automatik...
                                    </p>
                                </div>
                            </div>

                            <!-- State 3: Successfully Uploaded & AI Parsed -->
                            <div x-show="!isUploading && uploadedCvUrl" class="border-2 border-emerald-300 dark:border-emerald-700/80 rounded-2xl p-5 sm:p-6 bg-emerald-50/60 dark:bg-emerald-950/30" style="display: none;">
                                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                                    <div class="flex items-start gap-3">
                                        <div class="w-10 h-10 rounded-xl bg-emerald-100 dark:bg-emerald-900/60 text-emerald-600 dark:text-emerald-300 flex items-center justify-center text-xl shrink-0">
                                            ✓
                                        </div>
                                        <div>
                                            <div class="flex items-center gap-2 flex-wrap">
                                                <span class="text-xs sm:text-sm font-bold text-emerald-900 dark:text-emerald-200">
                                                    Resume Berjaya Dimuat Naik &amp; Dianalisis AI
                                                </span>
                                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-200/70 text-emerald-800 dark:bg-emerald-900 dark:text-emerald-300">
                                                    Sedia Dihantar
                                                </span>
                                            </div>
                                            <p class="text-xs text-emerald-700 dark:text-emerald-400 mt-0.5 font-medium flex items-center gap-1.5">
                                                <span>📎</span>
                                                <span x-text="fileName || 'Fail Resume Telah Dimuat Naik'"></span>
                                            </p>
                                            <p class="text-[11px] text-emerald-600/90 dark:text-emerald-400/90 mt-1">
                                                ✨ Maklumat peribadi telah dimasukkan ke borang di Langkah 2 di bawah. Anda bebas menyunting atau membetulkan sebarang maklumat.
                                            </p>
                                        </div>
                                    </div>

                                    <div class="shrink-0 flex items-center gap-2 pl-12 sm:pl-0">
                                        <button type="button"
                                                @click="$refs.resumeInput.click()"
                                                class="px-3.5 py-1.5 rounded-xl border border-emerald-300 dark:border-emerald-700 bg-white dark:bg-slate-800 text-emerald-800 dark:text-emerald-300 font-bold text-xs hover:bg-emerald-50 dark:hover:bg-slate-700 transition cursor-pointer">
                                            🔄 Tukar Resume Lain
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Error Message Banner -->
                            <div x-show="uploadError" class="mt-3 p-3.5 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-900/60 text-xs text-rose-700 dark:text-rose-300 flex items-start gap-2" style="display: none;">
                                <span class="text-sm">⚠️</span>
                                <div class="flex-grow">
                                    <span class="font-bold">Perhatian: </span>
                                    <span x-text="uploadError"></span>
                                    <span class="block mt-0.5 text-slate-500 dark:text-slate-400">
                                        Anda masih boleh melengkapkan borang secara manual di Langkah 2 di bawah.
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- LANGKAH 2: SEMAK & LENGKAPKAN MAKLUMAT CALON (BOLEH DISUNTING) -->
                    <div class="space-y-5 pt-2">
                        <div class="border-b border-slate-100 dark:border-slate-800 pb-3">
                            <div class="flex items-center gap-2">
                                <span class="flex items-center justify-center w-6 h-6 rounded-full bg-slate-800 dark:bg-slate-700 text-white font-bold text-xs shrink-0">2</span>
                                <h3 class="text-sm sm:text-base font-bold text-slate-900 dark:text-white">
                                    Langkah 2: Semak &amp; Lengkapkan Maklumat Calon (Boleh Disunting)
                                </h3>
                            </div>
                            <p class="text-xs text-slate-500 mt-1 pl-8">
                                Maklumat yang diekstrak oleh AI dipaparkan di bawah. Jika ada medan yang kosong, sila isi sendiri. Anda bebas menyunting mana-mana maklumat sekiranya terdapat kesilapan.
                            </p>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                            <!-- Nama Penuh -->
                            <div>
                                <div class="flex items-center justify-between mb-1.5">
                                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">
                                        Nama Penuh (seperti dalam MyKad) <span class="text-rose-500">*</span>
                                    </label>
                                    <span x-show="aiFilled.candidate_name" class="text-[10px] text-emerald-600 dark:text-emerald-400 font-semibold flex items-center gap-1" style="display: none;">
                                        <span>✨</span> Diisi oleh AI
                                    </span>
                                </div>
                                <input type="text"
                                       name="candidate_name"
                                       x-model="form.candidate_name"
                                       required
                                       placeholder="cth: Ahmad Fadhil bin Razali"
                                       class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs sm:text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
                            </div>

                            <!-- Tarikh Lahir -->
                            <div>
                                <div class="flex items-center justify-between mb-1.5">
                                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">
                                        Tarikh Lahir <span class="text-rose-500">*</span>
                                    </label>
                                    <span x-show="aiFilled.birth_date" class="text-[10px] text-emerald-600 dark:text-emerald-400 font-semibold flex items-center gap-1" style="display: none;">
                                        <span>✨</span> Diisi oleh AI
                                    </span>
                                </div>
                                <input type="date"
                                       name="birth_date"
                                       x-model="form.birth_date"
                                       required
                                       class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs sm:text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
                                <p class="text-[10px] text-slate-400 mt-1">
                                    Jika resume tidak menyatakan tarikh lahir, sila pilih tarikh lahir anda di sini.
                                </p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                            <!-- Emel -->
                            <div>
                                <div class="flex items-center justify-between mb-1.5">
                                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">
                                        Alamat Emel Rasmi <span class="text-rose-500">*</span>
                                    </label>
                                    <span x-show="aiFilled.email" class="text-[10px] text-emerald-600 dark:text-emerald-400 font-semibold flex items-center gap-1" style="display: none;">
                                        <span>✨</span> Diisi oleh AI
                                    </span>
                                </div>
                                <input type="email"
                                       name="email"
                                       x-model="form.email"
                                       required
                                       placeholder="cth: ahmad.fadhil@gmail.com"
                                       class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs sm:text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
                            </div>

                            <!-- Nombor Telefon / WhatsApp -->
                            <div>
                                <div class="flex items-center justify-between mb-1.5">
                                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">
                                        Nombor Telefon / WhatsApp <span class="text-rose-500">*</span>
                                    </label>
                                    <span x-show="aiFilled.phone" class="text-[10px] text-emerald-600 dark:text-emerald-400 font-semibold flex items-center gap-1" style="display: none;">
                                        <span>✨</span> Diisi oleh AI
                                    </span>
                                </div>
                                <input type="tel"
                                       name="phone"
                                       x-model="form.phone"
                                       required
                                       placeholder="cth: 012-3456789"
                                       class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs sm:text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
                            </div>
                        </div>

                        <!-- Alamat Kediaman -->
                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">
                                    Alamat Kediaman Lengkap <span class="text-rose-500">*</span>
                                </label>
                                <span x-show="aiFilled.address" class="text-[10px] text-emerald-600 dark:text-emerald-400 font-semibold flex items-center gap-1" style="display: none;">
                                    <span>✨</span> Diisi oleh AI
                                </span>
                            </div>
                            <textarea name="address"
                                      x-model="form.address"
                                      rows="2"
                                      required
                                      placeholder="No. 12, Jalan Keramat Saujana, Taman Saujana Indah, 54000 Kuala Lumpur"
                                      class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs sm:text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500"></textarea>
                        </div>

                        <!-- Surat Iringan / Pengenalan Tambahan (Pilihan) -->
                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">
                                    Surat Iringan / Pengenalan Diri Ringkas (Pilihan)
                                </label>
                                <span x-show="aiFilled.cover_letter" class="text-[10px] text-indigo-600 dark:text-indigo-400 font-semibold flex items-center gap-1" style="display: none;">
                                    <span>✨</span> Cadangan AI
                                </span>
                            </div>
                            <textarea name="cover_letter"
                                      x-model="form.cover_letter"
                                      rows="3"
                                      placeholder="Kongsikan secara ringkas mengapa anda berminat dengan jawatan ini dan kemahiran utama yang boleh anda sumbangkan..."
                                      class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs sm:text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500"></textarea>
                            <p class="text-[10px] text-slate-400 mt-1">
                                Anda bebas mengubah atau menambah teks di atas sebelum menghantar permohonan.
                            </p>
                        </div>
                    </div>

                    <!-- LANGKAH 3: PENGESAHAN & HANTAR PERMOHONAN -->
                    <div class="pt-6 border-t border-slate-100 dark:border-slate-800 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                        <div class="text-[11px] text-slate-400 leading-tight max-w-md">
                            🔒 <strong>Privasi Dijamin:</strong> Maklumat peribadi dan resume anda dilindungi dan hanya digunakan bagi tujuan saringan permohonan kerja oleh pasukan HR syarikat.
                        </div>

                        <button type="submit"
                                :disabled="submitting || isUploading"
                                class="w-full sm:w-auto px-7 py-3.5 rounded-2xl bg-indigo-600 hover:bg-indigo-700 active:scale-95 text-white font-bold text-xs sm:text-sm shadow-md transition flex items-center justify-center gap-2 cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed">
                            <span x-show="!submitting">Hantar Permohonan &amp; Saring AI &rarr;</span>
                            <span x-show="submitting" class="flex items-center gap-2" style="display: none;">
                                <svg class="animate-spin w-4 h-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                                <span>Menghantar &amp; Menyaring Resume...</span>
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="border-t border-slate-200/80 dark:border-slate-800/80 bg-white dark:bg-slate-900 py-6">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-slate-400">
            <div>
                &copy; {{ date('Y') }} {{ $job->org->name ?? 'KerjaAI' }}. Hak cipta terpelihara.
            </div>
            <div class="flex items-center gap-2 text-[11px]">
                <span>Dikuasakan oleh</span>
                <span class="font-bold text-indigo-600 dark:text-indigo-400">KerjaAI Lekir &bull; HR &amp; ATS Pintar</span>
            </div>
        </div>
    </footer>

    <script>
        window.careerApplicationForm = function() {
            return {
                isUploading: false,
                uploadSuccess: Boolean(@json(!empty(old('uploaded_cv_url')))),
                uploadMessage: '',
                uploadError: '',
                submitting: false,
                uploadedCvUrl: @json(old('uploaded_cv_url', '')),
                fileName: @json(old('uploaded_cv_url') ? 'Resume telah disimpan' : ''),
                form: {
                    candidate_name: @json(old('candidate_name', '')),
                    birth_date: @json(old('birth_date', '')),
                    email: @json(old('email', '')),
                    phone: @json(old('phone', '')),
                    address: @json(old('address', '')),
                    cover_letter: @json(old('cover_letter', '')),
                },
                aiFilled: {
                    candidate_name: false,
                    birth_date: false,
                    email: false,
                    phone: false,
                    address: false,
                    cover_letter: false,
                },

                async handleCvUpload(event) {
                    const file = event.target.files && event.target.files[0];
                    if (!file) return;

                    this.fileName = file.name;
                    this.isUploading = true;
                    this.uploadError = '';
                    this.uploadSuccess = false;
                    this.uploadMessage = '';

                    const formData = new FormData();
                    formData.append('cv_file', file);
                    formData.append('_token', '{{ csrf_token() }}');

                    try {
                        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
                        const parseUrl = '{{ route('careers.parse-resume', $job->slug ?: $job->id, false) }}';
                        const response = await fetch(parseUrl, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': csrfToken,
                                'Accept': 'application/json'
                            },
                            body: formData
                        });

                        const res = await response.json();

                        if (!response.ok || !res.success) {
                            let errMsg = res.message;
                            if (res.errors) {
                                errMsg = Object.values(res.errors).flat().join(' ');
                            }
                            throw new Error(errMsg || 'Gagal menganalisis fail resume.');
                        }

                        this.uploadedCvUrl = res.file_url;
                        if (res.file_name) this.fileName = res.file_name;
                        this.uploadSuccess = true;

                        // Auto-fill form fields if data is returned
                        const data = res.data || {};
                        let autoFilledCount = 0;

                        if (data.candidate_name) {
                            this.form.candidate_name = data.candidate_name;
                            this.aiFilled.candidate_name = true;
                            autoFilledCount++;
                        }
                        if (data.birth_date) {
                            this.form.birth_date = data.birth_date;
                            this.aiFilled.birth_date = true;
                            autoFilledCount++;
                        }
                        if (data.email) {
                            this.form.email = data.email;
                            this.aiFilled.email = true;
                            autoFilledCount++;
                        }
                        if (data.phone) {
                            this.form.phone = data.phone;
                            this.aiFilled.phone = true;
                            autoFilledCount++;
                        }
                        if (data.address) {
                            this.form.address = data.address;
                            this.aiFilled.address = true;
                            autoFilledCount++;
                        }
                        if (data.cover_letter && !this.form.cover_letter) {
                            this.form.cover_letter = data.cover_letter;
                            this.aiFilled.cover_letter = true;
                        }

                        this.uploadMessage = `AI Lekir berjaya mengekstrak maklumat (${autoFilledCount} medan dikesan). Sila semak & sunting maklumat di bawah.`;
                    } catch (error) {
                        if (error.name === 'TypeError' || error.message?.includes('Failed to fetch')) {
                            this.uploadError = 'Masalah sambungan semasa memuat naik fail. Sila semak rangkaian atau cuba sekali lagi.';
                        } else {
                            this.uploadError = error.message || 'Ralat semasa memuat naik & menganalisis fail. Sila lengkapkan maklumat secara manual.';
                        }
                    } finally {
                        this.isUploading = false;
                    }
                },

                handleSubmit(event) {
                    if (!this.uploadedCvUrl && (!this.$refs.resumeInput || !this.$refs.resumeInput.files.length)) {
                        event.preventDefault();
                        this.uploadError = 'Sila muat naik fail Resume / CV anda di Langkah 1 terlebih dahulu.';
                        if (this.$refs.resumeInput) {
                            this.$refs.resumeInput.click();
                        }
                        return false;
                    }
                    this.submitting = true;
                    return true;
                }
            };
        };
    </script>
</body>
</html>
