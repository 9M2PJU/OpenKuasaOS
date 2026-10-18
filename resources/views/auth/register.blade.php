@extends('layouts.guest', ['title' => 'Daftar Syarikat Baru'])

@section('content')
<div class="mb-6">
    <h2 class="text-xl font-bold tracking-tight text-slate-900 dark:text-white">Daftar Akaun Syarikat</h2>
    <p class="text-xs text-slate-500 mt-1">Mulakan pengurusan perniagaan anda dengan ekosistem pintar OpenKuasa.</p>
</div>

<form method="POST" action="{{ route('register') }}" class="space-y-4">
    @csrf

    <div>
        <label for="name" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Nama Penuh Pemilik</label>
        <input type="text" id="name" name="name" value="{{ old('name') }}" required autofocus
            placeholder="Ahmad Zulkifli"
            class="w-full px-3.5 py-2.5 rounded-xl border {{ $errors->has('name') ? 'border-rose-400 focus:ring-rose-400' : 'border-slate-300 dark:border-slate-700 focus:ring-indigo-500' }} bg-white dark:bg-slate-800 text-sm focus:outline-none focus:ring-2 transition">
        @error('name')
        <p class="mt-1.5 text-xs text-rose-500">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="company_name" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Nama Syarikat / Organisasi</label>
        <input type="text" id="company_name" name="company_name" value="{{ old('company_name') }}" required
            placeholder="Rimba Ventures Sdn Bhd"
            class="w-full px-3.5 py-2.5 rounded-xl border {{ $errors->has('company_name') ? 'border-rose-400 focus:ring-rose-400' : 'border-slate-300 dark:border-slate-700 focus:ring-indigo-500' }} bg-white dark:bg-slate-800 text-sm focus:outline-none focus:ring-2 transition">
        @error('company_name')
        <p class="mt-1.5 text-xs text-rose-500">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="email" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Alamat Emel Kerja</label>
        <input type="email" id="email" name="email" value="{{ old('email') }}" required
            placeholder="ahmad@rimbaventures.my"
            class="w-full px-3.5 py-2.5 rounded-xl border {{ $errors->has('email') ? 'border-rose-400 focus:ring-rose-400' : 'border-slate-300 dark:border-slate-700 focus:ring-indigo-500' }} bg-white dark:bg-slate-800 text-sm focus:outline-none focus:ring-2 transition">
        @error('email')
        <p class="mt-1.5 text-xs text-rose-500">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="password" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Kata Laluan</label>
        <input type="password" id="password" name="password" required
            placeholder="Minimum 8 aksara"
            class="w-full px-3.5 py-2.5 rounded-xl border {{ $errors->has('password') ? 'border-rose-400 focus:ring-rose-400' : 'border-slate-300 dark:border-slate-700 focus:ring-indigo-500' }} bg-white dark:bg-slate-800 text-sm focus:outline-none focus:ring-2 transition">
        @error('password')
        <p class="mt-1.5 text-xs text-rose-500">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="password_confirmation" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Sahkan Kata Laluan</label>
        <input type="password" id="password_confirmation" name="password_confirmation" required
            placeholder="Ulang kata laluan"
            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 focus:ring-indigo-500 bg-white dark:bg-slate-800 text-sm focus:outline-none focus:ring-2 transition">
    </div>

    <button type="submit" class="w-full py-2.5 px-4 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-sm shadow-sm transition">
        Daftar & Mula Operasi
    </button>
</form>

<div class="mt-6 pt-6 border-t border-slate-100 dark:border-slate-800 text-center text-xs text-slate-500">
    Sudah mempunyai akaun?
    <a href="{{ route('login') }}" class="font-semibold text-indigo-600 hover:text-indigo-500 dark:text-indigo-400 ml-1">Log Masuk</a>
</div>
@endsection
