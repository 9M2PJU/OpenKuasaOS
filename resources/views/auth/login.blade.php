@extends('layouts.guest', ['title' => 'Log Masuk'])

@section('content')
<div class="mb-6">
    <h2 class="text-xl font-bold tracking-tight text-slate-900 dark:text-white">Log Masuk</h2>
    <p class="text-xs text-slate-500 mt-1">Masukkan emel dan kata laluan untuk mengakses akaun syarikat anda.</p>
</div>

<form method="POST" action="{{ route('login') }}" class="space-y-4">
    @csrf

    <div>
        <label for="email" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Alamat Emel</label>
        <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus
            placeholder="nama@syarikat.my"
            class="w-full px-3.5 py-2.5 rounded-xl border {{ $errors->has('email') ? 'border-rose-400 focus:ring-rose-400' : 'border-slate-300 dark:border-slate-700 focus:ring-indigo-500' }} bg-white dark:bg-slate-800 text-sm focus:outline-none focus:ring-2 transition">
        @error('email')
        <p class="mt-1.5 text-xs text-rose-500">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <div class="flex items-center justify-between mb-1.5">
            <label for="password" class="block text-xs font-semibold text-slate-700 dark:text-slate-300">Kata Laluan</label>
        </div>
        <input type="password" id="password" name="password" required
            placeholder="••••••••"
            class="w-full px-3.5 py-2.5 rounded-xl border {{ $errors->has('password') ? 'border-rose-400 focus:ring-rose-400' : 'border-slate-300 dark:border-slate-700 focus:ring-indigo-500' }} bg-white dark:bg-slate-800 text-sm focus:outline-none focus:ring-2 transition">
        @error('password')
        <p class="mt-1.5 text-xs text-rose-500">{{ $message }}</p>
        @enderror
    </div>

    <div class="flex items-center justify-between text-xs">
        <label class="flex items-center gap-2 cursor-pointer">
            <input type="checkbox" name="remember" class="rounded border-slate-300 dark:border-slate-700 text-indigo-600 focus:ring-indigo-500">
            <span class="text-slate-600 dark:text-slate-400">Ingat peranti ini</span>
        </label>
    </div>

    <button type="submit" class="w-full py-2.5 px-4 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-sm shadow-sm transition">
        Log Masuk ke Dashboard
    </button>
</form>

<div class="mt-6 pt-6 border-t border-slate-100 dark:border-slate-800 text-center text-xs text-slate-500">
    Belum mempunyai akaun?
    <a href="{{ route('register') }}" class="font-semibold text-indigo-600 hover:text-indigo-500 dark:text-indigo-400 ml-1">Daftar Syarikat Baru</a>
</div>
@endsection
