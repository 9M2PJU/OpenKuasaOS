<!DOCTYPE html>
<html lang="ms" class="h-full bg-slate-50 dark:bg-slate-950">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Permohonan Berjaya Dihantar — {{ $job->title }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

    <!-- Tailwind & App Assets via Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body {
            font-family: 'Instrument Sans', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        }
    </style>
</head>
<body class="h-full antialiased text-slate-800 dark:text-slate-100 flex flex-col justify-between">
    <!-- Top Navigation Bar -->
    <header class="border-b border-slate-200/80 dark:border-slate-800/80 bg-white/80 dark:bg-slate-900/80 backdrop-blur sticky top-0 z-40">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 py-3.5 flex items-center justify-between">
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
                    <span class="text-[10px] sm:text-xs text-slate-500 font-medium">Portal Pengambilan &amp; Kerjaya</span>
                </div>
            </div>

            <a href="{{ route('careers.show', $job->slug ?: $job->id) }}" class="text-xs font-semibold text-indigo-600 dark:text-indigo-400 hover:underline">
                &larr; Lihat Iklan Semula
            </a>
        </div>
    </header>

    <main class="flex-grow py-12 sm:py-16 flex items-center justify-center px-4">
        <div class="max-w-lg w-full bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-3xl p-6 sm:p-8 shadow-md text-center space-y-6">
            <div class="w-16 h-16 rounded-full bg-emerald-100 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center mx-auto text-3xl shadow-xs">
                ✓
            </div>

            <div class="space-y-2">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-200/60">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    <span>Permohonan Diterima HR</span>
                </span>
                <h1 class="text-2xl font-black text-slate-900 dark:text-white">
                    Terima Kasih, {{ $application->candidate_name }}!
                </h1>
                <p class="text-xs sm:text-sm text-slate-500">
                    Permohonan anda bagi posisi <strong>{{ $job->title }}</strong> di <strong>{{ $job->org->name ?? 'syarikat kami' }}</strong> telah berjaya dihantar terus kepada bahagian HR.
                </p>
            </div>

            <!-- Application Details Card -->
            <div class="bg-slate-50 dark:bg-slate-800/60 rounded-2xl p-4 border border-slate-100 dark:border-slate-800 text-left text-xs space-y-2.5">
                <div class="flex justify-between items-center pb-2 border-b border-slate-200/60 dark:border-slate-700/60">
                    <span class="text-slate-400">No. Rujukan:</span>
                    <span class="font-mono font-bold text-slate-700 dark:text-slate-200">{{ substr($application->id, 0, 8) }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-slate-400">Emel:</span>
                    <span class="font-semibold text-slate-700 dark:text-slate-200">{{ $application->email }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-slate-400">Telefon / WhatsApp:</span>
                    <span class="font-semibold text-slate-700 dark:text-slate-200">{{ $application->phone }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-slate-400">Status Saringan:</span>
                    <span class="inline-flex items-center gap-1 font-bold text-indigo-600 dark:text-indigo-400">
                        <span>⚡</span>
                        <span>Saringan AI Selesai Disimpan</span>
                    </span>
                </div>
            </div>

            <!-- Next steps -->
            <div class="p-4 rounded-2xl bg-indigo-50/70 dark:bg-indigo-950/40 border border-indigo-100 dark:border-indigo-900/60 text-xs text-indigo-950 dark:text-indigo-200 text-left space-y-1.5">
                <div class="font-bold flex items-center gap-1.5">
                    <span>💡</span>
                    <span>Langkah Seterusnya</span>
                </div>
                <p class="text-[11px] text-slate-600 dark:text-slate-300 leading-relaxed">
                    Pasukan pengrekrutan HR kami akan menyemak laporan resume dan menghubungi calon yang terpilih melalui telefon atau emel untuk penetapan jadual sesi temuduga.
                </p>
            </div>

            <div class="pt-2">
                <a href="{{ route('careers.show', $job->slug ?: $job->id) }}" class="inline-flex items-center justify-center w-full px-5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 text-xs font-semibold text-slate-700 dark:text-slate-200 transition">
                    Kembali ke Halaman Jawatan
                </a>
            </div>
        </div>
    </main>

    <footer class="border-t border-slate-200/80 dark:border-slate-800/80 bg-white dark:bg-slate-900 py-4 text-center text-xs text-slate-400">
        &copy; {{ date('Y') }} {{ $job->org->name ?? 'KerjaAI' }}. Hak cipta terpelihara.
    </footer>
</body>
</html>
