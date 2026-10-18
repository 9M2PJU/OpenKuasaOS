@extends('layouts.app', ['title' => 'Lekiu — Pengumuman Syarikat'])

@section('content')
<div x-data="{
    showAddModal: false,
    showEditModal: false,
    editForm: {
        id: '',
        title: '',
        content: '',
        category: 'general',
        status: 'published',
        effective_date: ''
    },
    openEditModal(item) {
        this.editForm = {
            id: item.id,
            title: item.title,
            content: item.content,
            category: item.category,
            status: item.status,
            effective_date: item.effective_date ? item.effective_date.substring(0, 10) : ''
        };
        this.showEditModal = true;
    }
}" class="space-y-6 pb-16">

    <!-- Header & Action Bar -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('people.index') }}" class="text-xs font-semibold text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200 flex items-center gap-1 transition">
                    &larr; Lekiu People
                </a>
            </div>
            <h1 class="text-2xl font-black tracking-tight text-slate-900 dark:text-white mt-1">
                Announcements
            </h1>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-0.5">
                Company-wide updates for your team.
            </p>
        </div>
        <div class="flex items-center gap-3">
            <button @click="showAddModal = true" type="button" class="px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-500 text-white text-xs font-semibold shadow-sm transition flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Pengumuman Baharu</span>
            </button>
        </div>
    </div>

    <!-- Alert Notifications -->
    @if(session('success'))
        <div class="p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-xs sm:text-sm flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <svg class="w-5 h-5 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span class="font-medium">{{ session('success') }}</span>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="p-4 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-400 text-xs sm:text-sm flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <svg class="w-5 h-5 text-rose-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                <span class="font-medium">{{ session('error') }}</span>
            </div>
        </div>
    @endif

    <!-- Top 3 KPI Cards (Matching Screenshot) -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <!-- Card 1: Announcements (Hero Blue Card) -->
        <div class="rounded-2xl p-6 bg-[#3b82f6] text-white shadow-sm flex flex-col justify-between">
            <span class="text-xs font-semibold text-blue-100">Announcements</span>
            <div class="flex items-baseline gap-3 mt-2">
                <span class="text-4xl font-extrabold text-white tracking-tight">{{ $publishedCount }}</span>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-[#2563eb] text-blue-100 border border-blue-400/30">
                    {{ $draftCount }} draft not counted
                </span>
            </div>
        </div>

        <!-- Card 2: This month -->
        <div class="rounded-2xl p-6 bg-[#131b2e] border border-slate-800 text-white shadow-sm flex flex-col justify-between">
            <span class="text-xs font-semibold text-slate-400">This month</span>
            <span class="text-4xl font-extrabold text-white tracking-tight mt-2">{{ $thisMonthCount }}</span>
        </div>

        <!-- Card 3: Categories -->
        <div class="rounded-2xl p-6 bg-[#131b2e] border border-slate-800 text-white shadow-sm flex flex-col justify-between">
            <span class="text-xs font-semibold text-slate-400">Categories</span>
            <span class="text-4xl font-extrabold text-white tracking-tight mt-2">{{ $categoriesCount }}</span>
        </div>
    </div>

    <!-- Main Section: 2 Columns (By Category & Latest Announcements) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- Left Column: By category Donut Chart (Matching Screenshot) -->
        <div class="lg:col-span-4">
            <div class="rounded-2xl bg-[#131b2e] border border-slate-800 p-6 shadow-sm flex flex-col h-full">
                <!-- Header -->
                <div class="flex items-start gap-3">
                    <div class="p-2 rounded-xl bg-blue-950/60 text-blue-400 shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-white">By category</h2>
                        <p class="text-xs text-slate-400 mt-0.5">Across all published announcements</p>
                    </div>
                </div>

                <!-- Donut Chart Container -->
                <div class="mt-8 relative h-56 w-full flex items-center justify-center">
                    <canvas id="byCategoryChart"></canvas>
                    <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
                        <span class="text-3xl font-black text-white">{{ $publishedCount }}</span>
                        <span class="text-xs text-slate-400 font-medium">posts</span>
                    </div>
                </div>

                <!-- Custom Legend -->
                <div class="mt-8 flex flex-wrap items-center justify-center gap-x-4 gap-y-2 pt-4 border-t border-slate-800/80 text-xs font-semibold text-slate-300">
                    <div class="flex items-center gap-1.5">
                        <span class="w-2.5 h-2.5 rounded-sm bg-[#3b82f6]"></span>
                        <span>General</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span class="w-2.5 h-2.5 rounded-sm bg-[#06b6d4]"></span>
                        <span>Holiday</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span class="w-2.5 h-2.5 rounded-sm bg-[#f59e0b]"></span>
                        <span>Benefits</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span class="w-2.5 h-2.5 rounded-sm bg-[#f43f5e]"></span>
                        <span>Strategy</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span class="w-2.5 h-2.5 rounded-sm bg-[#10b981]"></span>
                        <span>Policy</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column: Latest announcements (Matching Screenshot) -->
        <div class="lg:col-span-8">
            <div class="rounded-2xl bg-[#131b2e] border border-slate-800 p-6 shadow-sm">
                <!-- Header -->
                <div class="flex items-start gap-3 pb-4 border-b border-slate-800/80">
                    <div class="p-2 rounded-xl bg-blue-950/60 text-blue-400 shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-white">Latest announcements</h2>
                        <p class="text-xs text-slate-400 mt-0.5">Drafts first, then newest</p>
                    </div>
                </div>

                <!-- Announcements List -->
                <div class="divide-y divide-slate-800/80">
                    @forelse($announcements as $announcement)
                        <div class="py-4.5 first:pt-4 last:pb-2 flex items-start justify-between gap-4">
                            <div class="flex items-start gap-3.5 min-w-0 flex-1">
                                <!-- Avatar / Icon -->
                                @if($announcement->status === 'draft')
                                    <div class="w-9 h-9 rounded-full bg-slate-800/90 text-slate-400 flex items-center justify-center shrink-0 border border-slate-700/60 mt-0.5">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                    </div>
                                @else
                                    <div class="w-9 h-9 rounded-full bg-slate-800 text-blue-400 font-bold text-sm flex items-center justify-center shrink-0 border border-slate-700/60 mt-0.5">
                                        {{ strtoupper(substr($announcement->author_name ?? 'S', 0, 1)) }}
                                    </div>
                                @endif

                                <!-- Body Text -->
                                <div class="min-w-0 flex-1">
                                    <h3 class="text-sm font-bold text-white leading-snug">
                                        {{ $announcement->title }}
                                    </h3>
                                    <p class="text-xs text-slate-400 mt-1 leading-relaxed">
                                        {{ $announcement->content }}
                                    </p>
                                    
                                    <!-- Meta line -->
                                    <div class="flex items-center gap-2 mt-2 text-[11px] text-slate-400 flex-wrap">
                                        <svg class="w-3.5 h-3.5 text-slate-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                                        </svg>
                                        @if($announcement->status === 'draft')
                                            <span>Not published yet</span>
                                        @else
                                            @php
                                                $daysOld = $announcement->created_at ? $announcement->created_at->diffInDays(now()) : 0;
                                                if ($daysOld >= 30) {
                                                    $timeLabel = $announcement->created_at->format('d M Y');
                                                } else {
                                                    $timeLabel = $announcement->created_at->diffForHumans();
                                                }
                                            @endphp
                                            <span>{{ $announcement->author_name }} &bull; {{ $timeLabel }}</span>
                                        @endif

                                        @if($announcement->effective_date)
                                            <span class="text-slate-600">&bull;</span>
                                            <span class="text-slate-400">Tarikh: {{ \Carbon\Carbon::parse($announcement->effective_date)->format('d M Y') }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <!-- Badges & Action Buttons -->
                            <div class="flex items-center gap-2 shrink-0">
                                @if($announcement->status === 'draft')
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-950/60 text-amber-500 border border-amber-800/50">
                                        Draft
                                    </span>
                                @endif

                                <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-800/90 text-slate-300 border border-slate-700 capitalize">
                                    {{ $announcement->category }}
                                </span>

                                <!-- Action Buttons (or locked badge if date has passed) -->
                                @if($announcement->canBeModified())
                                    <div class="flex items-center gap-1 ml-1">
                                        <button @click="openEditModal({{ json_encode($announcement) }})" type="button" class="p-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800 transition" title="Sunting Pengumuman">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        </button>
                                        
                                        <form method="POST" action="{{ route('people.announcements.destroy', $announcement->id) }}" onsubmit="return confirm('Adakah anda pasti mahu memadam pengumuman ini?')" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1.5 rounded-lg text-rose-400 hover:text-rose-300 hover:bg-rose-950/40 transition" title="Padam Pengumuman">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </form>
                                    </div>
                                @else
                                    <!-- Locked: Lepas Tarikh (Past Date) -->
                                    <div class="flex items-center gap-1.5 px-2 py-1 rounded-lg bg-amber-950/30 border border-amber-800/40 text-amber-500 text-[11px] font-medium ml-1" title="Pengumuman ini telah lepas tarikh dan tidak boleh disunting atau dipadam.">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                        <span>Telah Lepas Tarikh (Kunci)</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="py-12 text-center text-slate-500 text-sm">
                            Tiada pengumuman ditemui. Sila klik "Pengumuman Baharu" untuk mula.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- ============================================================== -->
    <!-- MODAL: ADD ANNOUNCEMENT                                        -->
    <!-- ============================================================== -->
    <div x-show="showAddModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70 backdrop-blur-sm">
        <div @click.away="showAddModal = false" class="bg-[#131b2e] border border-slate-800 rounded-2xl w-full max-w-lg p-6 text-white shadow-2xl">
            <div class="flex items-center justify-between pb-4 border-b border-slate-800">
                <h3 class="text-base font-bold text-white flex items-center gap-2">
                    <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>Cipta Pengumuman Baharu</span>
                </h3>
                <button @click="showAddModal = false" type="button" class="text-slate-400 hover:text-white transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form method="POST" action="{{ route('people.announcements.store') }}" class="mt-4 space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Tajuk Pengumuman *</label>
                    <input type="text" name="title" required placeholder="Contoh: Office closed for National Day" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-slate-700 text-white text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Kategori *</label>
                        <select name="category" required class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-slate-700 text-white text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none">
                            <option value="general">General</option>
                            <option value="holiday">Holiday</option>
                            <option value="benefits">Benefits</option>
                            <option value="strategy">Strategy</option>
                            <option value="policy">Policy</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Status *</label>
                        <select name="status" required class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-slate-700 text-white text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none">
                            <option value="published">Diterbitkan (Published)</option>
                            <option value="draft">Draf (Draft)</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Tarikh Pengumuman / Berkuatkuasa *</label>
                    <input type="date" name="effective_date" value="{{ date('Y-m-d') }}" required class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-slate-700 text-white text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    <p class="text-[11px] text-slate-500 mt-1">Nota: Pengumuman yang melepasi tarikh ini tidak akan boleh disunting atau dipadam.</p>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Kandungan / Perincian *</label>
                    <textarea name="content" rows="4" required placeholder="Tulis mesej atau maklumat pengumuman di sini..." class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-slate-700 text-white text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none leading-relaxed"></textarea>
                </div>

                <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-800">
                    <button @click="showAddModal = false" type="button" class="px-4 py-2 rounded-xl border border-slate-700 text-xs text-slate-300 hover:bg-slate-800 transition">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-blue-600 hover:bg-blue-500 text-xs font-semibold text-white shadow-sm transition">
                        Simpan Pengumuman
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ============================================================== -->
    <!-- MODAL: EDIT ANNOUNCEMENT                                       -->
    <!-- ============================================================== -->
    <div x-show="showEditModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70 backdrop-blur-sm">
        <div @click.away="showEditModal = false" class="bg-[#131b2e] border border-slate-800 rounded-2xl w-full max-w-lg p-6 text-white shadow-2xl">
            <div class="flex items-center justify-between pb-4 border-b border-slate-800">
                <h3 class="text-base font-bold text-white flex items-center gap-2">
                    <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    <span>Sunting Pengumuman</span>
                </h3>
                <button @click="showEditModal = false" type="button" class="text-slate-400 hover:text-white transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form method="POST" :action="'{{ url('people/announcements') }}/' + editForm.id" class="mt-4 space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Tajuk Pengumuman *</label>
                    <input type="text" name="title" x-model="editForm.title" required class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-slate-700 text-white text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Kategori *</label>
                        <select name="category" x-model="editForm.category" required class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-slate-700 text-white text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none">
                            <option value="general">General</option>
                            <option value="holiday">Holiday</option>
                            <option value="benefits">Benefits</option>
                            <option value="strategy">Strategy</option>
                            <option value="policy">Policy</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Status *</label>
                        <select name="status" x-model="editForm.status" required class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-slate-700 text-white text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none">
                            <option value="published">Diterbitkan (Published)</option>
                            <option value="draft">Draf (Draft)</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Tarikh Pengumuman / Berkuatkuasa *</label>
                    <input type="date" name="effective_date" x-model="editForm.effective_date" required class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-slate-700 text-white text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    <p class="text-[11px] text-slate-500 mt-1">Nota: Pengumuman yang melepasi tarikh ini tidak akan boleh disunting atau dipadam.</p>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Kandungan / Perincian *</label>
                    <textarea name="content" x-model="editForm.content" rows="4" required class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-slate-700 text-white text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none leading-relaxed"></textarea>
                </div>

                <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-800">
                    <button @click="showEditModal = false" type="button" class="px-4 py-2 rounded-xl border border-slate-700 text-xs text-slate-300 hover:bg-slate-800 transition">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-blue-600 hover:bg-blue-500 text-xs font-semibold text-white shadow-sm transition">
                        Kemaskini Pengumuman
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ==============================================
     CHART.JS SCRIPT (By Category Donut Chart)
     ============================================== -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    const chartJs = window.Chart;
    if (!chartJs) return;

    const donutCtx = document.getElementById('byCategoryChart');
    if (donutCtx) {
        new chartJs(donutCtx, {
            type: 'doughnut',
            data: {
                labels: ['General', 'Holiday', 'Benefits', 'Strategy', 'Policy'],
                datasets: [
                    {
                        data: [
                            {{ $categoryStats['general'] ?? 0 }},
                            {{ $categoryStats['holiday'] ?? 0 }},
                            {{ $categoryStats['benefits'] ?? 0 }},
                            {{ $categoryStats['strategy'] ?? 0 }},
                            {{ $categoryStats['policy'] ?? 0 }},
                        ],
                        backgroundColor: [
                            '#3b82f6', // General (blue)
                            '#06b6d4', // Holiday (cyan)
                            '#f59e0b', // Benefits (amber)
                            '#f43f5e', // Strategy (rose)
                            '#10b981', // Policy (emerald)
                        ],
                        borderColor: '#131b2e',
                        borderWidth: 2,
                        hoverOffset: 4,
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '72%',
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#0f172a',
                        titleColor: '#f8fafc',
                        bodyColor: '#cbd5e1',
                        borderColor: '#334155',
                        borderWidth: 1,
                        padding: 8,
                    }
                }
            }
        });
    }
});
</script>
@endsection
