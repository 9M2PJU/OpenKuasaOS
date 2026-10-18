<!DOCTYPE html>
<html lang="ms" class="h-full bg-slate-50 dark:bg-slate-950 text-slate-900 dark:text-slate-100 antialiased">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'OpenKuasa' }} — Log Masuk</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full flex items-center justify-center p-6">
    <div class="w-full max-w-md">
        <!-- Logo -->
        <div class="text-center mb-8">
            <div class="inline-flex w-16 h-16 rounded-2xl overflow-hidden items-center justify-center shadow-lg mb-3 ring-2 ring-indigo-500/20 bg-white">
                <img src="{{ asset('images/logo.png') }}" alt="OpenKuasa Logo" class="w-full h-full object-cover">
            </div>
            <h1 class="text-2xl font-bold tracking-tight">Open<span class="text-indigo-600 dark:text-indigo-400">Kuasa</span></h1>
            <p class="text-xs text-slate-500 mt-1">Platform Pengurusan Operasi Perniagaan PKS Malaysia</p>
        </div>

        @if(session('info'))
        <div class="mb-4 bg-blue-50 dark:bg-blue-950/60 border border-blue-200 dark:border-blue-800 rounded-xl p-3 text-xs text-blue-800 dark:text-blue-200">
            {{ session('info') }}
        </div>
        @endif

        @if(session('error'))
        <div class="mb-4 bg-rose-50 dark:bg-rose-950/60 border border-rose-200 dark:border-rose-800 rounded-xl p-3 text-xs text-rose-800 dark:text-rose-200">
            {{ session('error') }}
        </div>
        @endif

        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm p-8">
            @yield('content')
        </div>

        <p class="text-center text-xs text-slate-400 mt-6">
            &copy; {{ date('Y') }} OpenKuasa. Dikuasakan oleh Laravel 13 & PostgreSQL.
        </p>
    </div>
</body>
</html>
