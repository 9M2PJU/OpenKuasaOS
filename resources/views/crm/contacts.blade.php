@extends('layouts.app', ['title' => 'Buku Kontak — Kasturi'])

@section('content')
<div class="space-y-6" x-data="{ addModal: false }">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('crm.index') }}" class="text-xs text-indigo-600 dark:text-indigo-400 font-semibold hover:underline">&larr; Kasturi CRM</a>
                <span class="text-slate-400 text-xs">/</span>
                <span class="text-xs text-slate-500 font-medium">Kontak</span>
            </div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white mt-1">Buku Kontak Pelanggan & Rakan Niaga</h1>
            <p class="text-xs text-slate-500 mt-1">Pangkalan data direktori hubungan perniagaan dan pembuat keputusan utama.</p>
        </div>
        <div>
            <button @click="addModal = true" class="px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold shadow-sm transition">
                + Tambah Kontak Baru
            </button>
        </div>
    </div>

    <!-- Table -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-800 text-slate-500">
                    <tr>
                        <th class="p-4 font-semibold">Nama Kontak</th>
                        <th class="p-4 font-semibold">Syarikat / Organisasi</th>
                        <th class="p-4 font-semibold">Alamat Emel</th>
                        <th class="p-4 font-semibold">Nombor Telefon</th>
                        <th class="p-4 font-semibold">Tarikh Dicipta</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($contacts as $c)
                    <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/40 transition">
                        <td class="p-4 font-semibold text-slate-800 dark:text-slate-200">{{ $c->name }}</td>
                        <td class="p-4 text-slate-600 dark:text-slate-400">{{ $c->company ?? '-' }}</td>
                        <td class="p-4 text-slate-500">{{ $c->email ?? '-' }}</td>
                        <td class="p-4 text-slate-500 font-mono">{{ $c->phone ?? '-' }}</td>
                        <td class="p-4 text-slate-400">{{ $c->created_at ? $c->created_at->format('d M Y') : '-' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="p-8 text-center text-slate-400 text-xs">Tiada profil kontak dijumpai.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($contacts->hasPages())
        <div class="p-4 border-t border-slate-100 dark:border-slate-800">
            {{ $contacts->links() }}
        </div>
        @endif
    </div>

    <!-- Add Contact Modal -->
    <div x-show="addModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4" style="display: none;">
        <div @click.away="addModal = false" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl max-w-md w-full p-6 shadow-xl">
            <h3 class="text-base font-bold text-slate-900 dark:text-white">Tambah Kontak Baru</h3>
            <p class="text-xs text-slate-500 mt-1">Masukkan maklumat perhubungan prospek atau pelanggan.</p>

            <form method="POST" action="{{ route('crm.contacts.store') }}" class="mt-4 space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Nama Penuh</label>
                    <input type="text" name="name" required placeholder="cth: Ir. Faizul Hadi"
                        class="w-full px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Nama Syarikat</label>
                    <input type="text" name="company" placeholder="cth: Kumpulan Senibina Utara"
                        class="w-full px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Alamat Emel</label>
                    <input type="email" name="email" placeholder="faizul@senibinautara.my"
                        class="w-full px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Nombor Telefon</label>
                    <input type="text" name="phone" placeholder="+6012-3456789"
                        class="w-full px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs">
                </div>

                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="addModal = false" class="px-4 py-2 rounded-xl border border-slate-300 dark:border-slate-700 text-xs font-semibold">Batal</button>
                    <button type="submit" class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold">Simpan Kontak</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
