@extends('layouts.app', ['title' => 'Kalendar Temujanji — Kasturi CRM'])

@section('content')
<div class="space-y-6 pb-16" x-data="{
    createModal: false,
    viewModal: false,
    selectedAppointment: null,
    selectedDate: '{{ now()->format('Y-m-d') }}',
    selectedTime: '10:00',
    openCreate(date = null) {
        if (date) {
            this.selectedDate = date;
        }
        this.createModal = true;
    },
    viewAppt(appt) {
        this.selectedAppointment = appt;
        this.viewModal = true;
    }
}">
    <!-- Header with Breadcrumbs & Action Button -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('crm.index') }}" class="text-xs text-[#00b8d4] hover:underline font-semibold flex items-center gap-1">
                    &larr; Kasturi CRM
                </a>
                <span class="text-slate-500 text-xs">/</span>
                <span class="text-xs text-slate-400 font-medium">Kalendar & Temujanji</span>
            </div>
            <h1 class="text-2xl font-black tracking-tight text-slate-900 dark:text-white mt-1">
                Kalendar Temujanji & Jadual Jualan
            </h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                Pengurusan sesi panggilan penemuan (discovery call), demonstrasi produk dan rundingan penutupan urus niaga pelanggan.
            </p>
        </div>
        <div class="flex items-center gap-3">
            <button type="button" @click="openCreate()" class="px-4 py-2.5 rounded-xl bg-[#00b8d4] hover:bg-cyan-600 text-slate-950 font-bold text-xs shadow-md shadow-cyan-500/20 transition flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                </svg>
                <span>Buat Appointment Baru</span>
            </button>
            <a href="{{ route('crm.deals') }}" class="px-4 py-2.5 rounded-xl border border-slate-700/80 bg-[#121927] hover:bg-slate-800 text-xs font-semibold text-slate-300 transition">
                Papan Pipeline &rarr;
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
        <span class="text-[10px] text-emerald-500/80">Kemas kini disimpan</span>
    </div>
    @endif

    <!-- ==============================================
         ROW 1: TOP 4 KPI CARDS (Matching Screenshot)
         ============================================== -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Card 1: Today (Teal/Cyan Hero Card) -->
        <div class="rounded-2xl p-5 bg-[#175263] border border-cyan-800/50 text-white shadow-lg shadow-cyan-900/20 flex flex-col justify-between min-h-[105px]">
            <span class="text-xs font-semibold text-cyan-200/90">Today</span>
            <span class="text-3xl sm:text-4xl font-black tracking-tight text-white mt-1">{{ $todayCount }}</span>
        </div>

        <!-- Card 2: Next 7 days -->
        <div class="rounded-2xl p-5 bg-[#121927] border border-slate-800/80 text-white shadow-sm flex flex-col justify-between min-h-[105px]">
            <span class="text-xs font-medium text-slate-400">Next 7 days</span>
            <span class="text-3xl sm:text-4xl font-black tracking-tight text-white mt-1">{{ $next7DaysCount }}</span>
        </div>

        <!-- Card 3: In Month Year (e.g. In October 2026) -->
        <div class="rounded-2xl p-5 bg-[#121927] border border-slate-800/80 text-white shadow-sm flex flex-col justify-between min-h-[105px]">
            <span class="text-xs font-medium text-slate-400">In {{ $monthName }}</span>
            <span class="text-3xl sm:text-4xl font-black tracking-tight text-white mt-1">{{ $monthCount }}</span>
        </div>

        <!-- Card 4: Next 30 days -->
        <div class="rounded-2xl p-5 bg-[#121927] border border-slate-800/80 text-white shadow-sm flex flex-col justify-between min-h-[105px]">
            <span class="text-xs font-medium text-slate-400">Next 30 days</span>
            <span class="text-3xl sm:text-4xl font-black tracking-tight text-white mt-1">{{ $next30DaysCount }}</span>
        </div>
    </div>

    <!-- ==============================================
         ROW 2: MAIN CALENDAR & UPCOMING APPOINTMENTS
         ============================================== -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- ==============================================
             LEFT 2 COLS: CALENDAR GRID
             ============================================== -->
        <div class="lg:col-span-2 rounded-2xl p-6 bg-[#121927] border border-slate-800/80 text-white shadow-sm space-y-6">
            <!-- Calendar Navigation Bar (Matching Screenshot) -->
            <div class="flex items-center justify-between pb-3 border-b border-slate-800/80">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-cyan-500/10 border border-cyan-500/20 flex items-center justify-center text-[#00b8d4]">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <h2 class="text-base font-bold text-white tracking-tight">{{ $monthName }}</h2>
                </div>

                <!-- Navigation Buttons: < Today > -->
                <div class="flex items-center gap-2">
                    <a href="{{ route('crm.calendar', ['month' => $prevMonth, 'year' => $prevYear]) }}"
                        class="p-2 rounded-xl border border-slate-700/80 bg-slate-900/80 hover:bg-slate-800 text-slate-300 transition"
                        title="Bulan Sebelumnya">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" />
                        </svg>
                    </a>
                    <a href="{{ route('crm.calendar') }}"
                        class="px-3.5 py-1.5 rounded-xl border border-slate-700/80 bg-slate-900/80 hover:bg-slate-800 text-xs font-semibold text-slate-200 transition">
                        Today
                    </a>
                    <a href="{{ route('crm.calendar', ['month' => $nextMonth, 'year' => $nextYear]) }}"
                        class="p-2 rounded-xl border border-slate-700/80 bg-slate-900/80 hover:bg-slate-800 text-slate-300 transition"
                        title="Bulan Seterusnya">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
                        </svg>
                    </a>
                </div>
            </div>

            <!-- Days of Week Headers -->
            <div class="grid grid-cols-7 gap-2 text-center text-xs font-semibold text-slate-400">
                <span>Sun</span>
                <span>Mon</span>
                <span>Tue</span>
                <span>Wed</span>
                <span>Thu</span>
                <span>Fri</span>
                <span>Sat</span>
            </div>

            <!-- Month Days Grid -->
            <div class="grid grid-cols-7 gap-2">
                <!-- Padding days before 1st of month -->
                @for($p = 0; $p < $startDayOfWeek; $p++)
                <div class="min-h-[86px] rounded-xl border border-transparent bg-slate-900/20 p-2 opacity-30 pointer-events-none"></div>
                @endfor

                <!-- Days in Month (1..31) -->
                @for($day = 1; $day <= $daysInMonth; $day++)
                @php
                    $isToday = ($today->year === $year && $today->month === $month && $today->day === $day);
                    $dayAppts = $monthAppointmentsByDay[$day] ?? [];
                    $dateString = sprintf('%04d-%02d-%02d', $year, $month, $day);
                @endphp
                <div @click="openCreate('{{ $dateString }}')"
                    class="group relative min-h-[86px] rounded-xl border p-2 transition cursor-pointer flex flex-col justify-between
                    {{ $isToday ? 'border-[#00b8d4] ring-1 ring-[#00b8d4]/60 bg-cyan-950/20' : 'border-slate-800/80 bg-slate-900/50 hover:border-slate-700 hover:bg-slate-900/80' }}">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold {{ $isToday ? 'text-[#00b8d4]' : 'text-slate-300' }}">{{ $day }}</span>
                        <!-- Quick Add Plus on hover -->
                        <span class="opacity-0 group-hover:opacity-100 text-[10px] text-slate-500 font-bold transition">+</span>
                    </div>

                    <!-- Appointment Pills inside Day -->
                    <div class="space-y-1 mt-1">
                        @foreach($dayAppts as $appt)
                        <div @click.stop="viewAppt(@js($appt))"
                            class="px-1.5 py-0.5 rounded text-[10px] font-semibold truncate flex items-center gap-1 transition shadow-sm
                            {{ $appt->type === 'appointment' ? 'bg-cyan-500/20 text-cyan-300 hover:bg-cyan-500/30' : '' }}
                            {{ $appt->type === 'follow_up' ? 'bg-amber-500/20 text-amber-300 hover:bg-amber-500/30' : '' }}
                            {{ $appt->type === 'deal_closing' ? 'bg-blue-500/20 text-blue-300 hover:bg-blue-500/30' : '' }}">
                            <span class="w-1.5 h-1.5 rounded-full shrink-0
                                {{ $appt->type === 'appointment' ? 'bg-[#00b8d4]' : '' }}
                                {{ $appt->type === 'follow_up' ? 'bg-[#f59e0b]' : '' }}
                                {{ $appt->type === 'deal_closing' ? 'bg-[#3b82f6]' : '' }}"></span>
                            <span class="truncate">{{ $appt->contact_name }} &bull; {{ $appt->title }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endfor
            </div>

            <!-- Calendar Legend (Matching Screenshot) -->
            <div class="flex items-center gap-6 pt-3 border-t border-slate-800/60 text-xs">
                <div class="flex items-center gap-2 text-slate-300">
                    <span class="w-2.5 h-2.5 rounded-full bg-[#00b8d4]"></span>
                    <span>Appointment</span>
                </div>
                <div class="flex items-center gap-2 text-slate-300">
                    <span class="w-2.5 h-2.5 rounded-full bg-[#f59e0b]"></span>
                    <span>Follow-up</span>
                </div>
                <div class="flex items-center gap-2 text-slate-300">
                    <span class="w-2.5 h-2.5 rounded-full bg-[#3b82f6]"></span>
                    <span>Deal closing</span>
                </div>
            </div>
        </div>

        <!-- ==============================================
             RIGHT 1 COL: UPCOMING APPOINTMENTS
             ============================================== -->
        <div class="rounded-2xl p-6 bg-[#121927] border border-slate-800/80 text-white shadow-sm flex flex-col justify-between space-y-6">
            <div>
                <!-- Upcoming Header (Matching Screenshot) -->
                <div class="flex items-start gap-3 pb-4 border-b border-slate-800/80">
                    <div class="w-8 h-8 rounded-lg bg-cyan-500/10 border border-cyan-500/20 flex items-center justify-center text-[#00b8d4] shrink-0">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-sm font-bold text-white tracking-tight">Upcoming</h2>
                        <p class="text-xs text-slate-400 mt-0.5">Next 30 days</p>
                    </div>
                </div>

                <!-- Upcoming Appointment Cards List -->
                <div class="space-y-3 mt-4">
                    @forelse($upcomingAppointments as $appt)
                    <div @click="viewAppt(@js($appt))"
                        class="p-4 rounded-xl border border-slate-800/80 bg-slate-900/60 hover:border-slate-700 hover:bg-slate-900 transition cursor-pointer space-y-1.5">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full shrink-0
                                {{ $appt->type === 'appointment' ? 'bg-[#00b8d4]' : '' }}
                                {{ $appt->type === 'follow_up' ? 'bg-[#f59e0b]' : '' }}
                                {{ $appt->type === 'deal_closing' ? 'bg-[#3b82f6]' : '' }}"></span>
                            <h3 class="text-xs font-bold text-white truncate">
                                {{ $appt->contact_name }} &bull; {{ $appt->title }}
                            </h3>
                        </div>
                        <div class="flex items-center gap-2 text-[11px] text-slate-400 pl-4">
                            <svg class="w-3.5 h-3.5 text-slate-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>{{ ucfirst($appt->type) }} &bull; {{ $appt->scheduled_at->format('D j M') }} &bull; {{ $appt->scheduled_at->format('g:i a') }}</span>
                        </div>
                    </div>
                    @empty
                    <div class="py-12 text-center text-slate-500 text-xs">
                        Tiada temujanji dijadualkan dalam 30 hari akan datang.
                    </div>
                    @endforelse
                </div>
            </div>

            <!-- Quick Action to Schedule -->
            <div class="pt-4 border-t border-slate-800/60">
                <button type="button" @click="openCreate()" class="w-full py-2.5 rounded-xl border border-dashed border-slate-700 hover:border-[#00b8d4] text-xs font-semibold text-slate-300 hover:text-[#00b8d4] transition flex items-center justify-center gap-2">
                    <span>+ Jadualkan Temujanji Lain</span>
                </button>
            </div>
        </div>
    </div>

    <!-- ==============================================
         CREATE APPOINTMENT MODAL (Alpine.js)
         ============================================== -->
    <div x-show="createModal" class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm z-50 flex items-center justify-center p-4" style="display: none;">
        <div @click.away="createModal = false" class="bg-[#121927] border border-slate-800 rounded-2xl max-w-lg w-full p-6 shadow-2xl text-white space-y-5">
            <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                <div>
                    <h3 class="text-base font-bold text-white">Jadualkan Temujanji Pelanggan</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Tetapkan sesi temuduga atau demonstrasi produk bersama prospek.</p>
                </div>
                <button type="button" @click="createModal = false" class="p-1 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form method="POST" action="{{ route('crm.appointments.store') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Tajuk Sesi</label>
                    <input type="text" name="title" required placeholder="cth: Discovery call / Product demo / Follow-up"
                        class="w-full px-3 py-2 rounded-xl border border-slate-700 bg-slate-900 text-xs text-white focus:border-[#00b8d4] focus:ring-1 focus:ring-[#00b8d4]">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Nama Kontak Pelanggan</label>
                        <input type="text" name="contact_name" required placeholder="cth: Aisyah Rahim"
                            class="w-full px-3 py-2 rounded-xl border border-slate-700 bg-slate-900 text-xs text-white focus:border-[#00b8d4] focus:ring-1 focus:ring-[#00b8d4]">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Pautkan Kontak (Pilihan)</label>
                        <select name="contact_id" class="w-full px-3 py-2 rounded-xl border border-slate-700 bg-slate-900 text-xs text-white focus:border-[#00b8d4] focus:ring-1 focus:ring-[#00b8d4]">
                            <option value="">-- Pilih dari Kontak --</option>
                            @foreach($contacts as $c)
                            <option value="{{ $c->id }}">{{ $c->name }} ({{ $c->company ?? 'Individu' }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Jenis Sesi</label>
                        <select name="type" required class="w-full px-3 py-2 rounded-xl border border-slate-700 bg-slate-900 text-xs text-white focus:border-[#00b8d4] focus:ring-1 focus:ring-[#00b8d4]">
                            <option value="appointment">Appointment (Awal)</option>
                            <option value="follow_up">Follow-up (Susulan)</option>
                            <option value="deal_closing">Deal closing (Penutupan)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Tarikh</label>
                        <input type="date" name="scheduled_date" required :value="selectedDate"
                            class="w-full px-3 py-2 rounded-xl border border-slate-700 bg-slate-900 text-xs text-white focus:border-[#00b8d4] focus:ring-1 focus:ring-[#00b8d4]">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Masa</label>
                        <input type="time" name="scheduled_time" required :value="selectedTime"
                            class="w-full px-3 py-2 rounded-xl border border-slate-700 bg-slate-900 text-xs text-white focus:border-[#00b8d4] focus:ring-1 focus:ring-[#00b8d4]">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Lokasi / Saluran</label>
                        <input type="text" name="location" placeholder="Google Meet / WhatsApp / Pejabat"
                            class="w-full px-3 py-2 rounded-xl border border-slate-700 bg-slate-900 text-xs text-white focus:border-[#00b8d4] focus:ring-1 focus:ring-[#00b8d4]">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Pautkan Transaksi Deal</label>
                        <select name="deal_id" class="w-full px-3 py-2 rounded-xl border border-slate-700 bg-slate-900 text-xs text-white focus:border-[#00b8d4] focus:ring-1 focus:ring-[#00b8d4]">
                            <option value="">-- Tiada Deal --</option>
                            @foreach($deals as $d)
                            <option value="{{ $d->id }}">{{ $d->title }} (RM {{ number_format($d->amount, 0) }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Catatan Tambahan</label>
                    <textarea name="notes" rows="2" placeholder="Objektif panggilan, agenda pembentangan, atau pautan dokumen..."
                        class="w-full px-3 py-2 rounded-xl border border-slate-700 bg-slate-900 text-xs text-white focus:border-[#00b8d4] focus:ring-1 focus:ring-[#00b8d4]"></textarea>
                </div>

                <div class="flex justify-end gap-2 pt-3 border-t border-slate-800">
                    <button type="button" @click="createModal = false" class="px-4 py-2 rounded-xl border border-slate-700 text-xs font-semibold text-slate-400 hover:text-white">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-[#00b8d4] hover:bg-cyan-600 text-slate-950 text-xs font-bold transition shadow-sm">
                        Simpan Temujanji
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ==============================================
         VIEW / MANAGE APPOINTMENT MODAL (Alpine.js)
         ============================================== -->
    <div x-show="viewModal" class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm z-50 flex items-center justify-center p-4" style="display: none;">
        <div @click.away="viewModal = false" class="bg-[#121927] border border-slate-800 rounded-2xl max-w-md w-full p-6 shadow-2xl text-white space-y-5" x-if="selectedAppointment">
            <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-[#00b8d4]"></span>
                        <h3 class="text-base font-bold text-white" x-text="selectedAppointment?.title"></h3>
                    </div>
                    <p class="text-xs text-slate-400 mt-0.5" x-text="selectedAppointment?.contact_name"></p>
                </div>
                <button type="button" @click="viewModal = false" class="p-1 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="space-y-3 text-xs">
                <div class="flex items-center justify-between p-3 rounded-xl bg-slate-900/60 border border-slate-800">
                    <span class="text-slate-400">Tarikh & Masa:</span>
                    <span class="font-bold text-white" x-text="new Date(selectedAppointment?.scheduled_at).toLocaleString('ms-MY', { dateStyle: 'full', timeStyle: 'short' })"></span>
                </div>
                <div class="flex items-center justify-between p-3 rounded-xl bg-slate-900/60 border border-slate-800">
                    <span class="text-slate-400">Lokasi / Saluran:</span>
                    <span class="font-bold text-[#00b8d4]" x-text="selectedAppointment?.location || 'Google Meet'"></span>
                </div>
                <div class="flex items-center justify-between p-3 rounded-xl bg-slate-900/60 border border-slate-800">
                    <span class="text-slate-400">Status Semasa:</span>
                    <span class="font-bold uppercase text-[10px] px-2 py-0.5 rounded-full bg-cyan-500/10 text-cyan-400 border border-cyan-500/20" x-text="selectedAppointment?.status"></span>
                </div>
                <template x-if="selectedAppointment?.notes">
                    <div class="p-3 rounded-xl bg-slate-900/60 border border-slate-800 space-y-1">
                        <span class="text-slate-400 block">Catatan:</span>
                        <p class="text-slate-300 leading-relaxed" x-text="selectedAppointment?.notes"></p>
                    </div>
                </template>
            </div>

            <div class="flex items-center justify-between pt-3 border-t border-slate-800">
                <form method="POST" :action="'/crm/appointments/' + selectedAppointment?.id" onsubmit="return confirm('Padam temujanji ini?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="px-3.5 py-1.5 rounded-xl border border-rose-500/30 bg-rose-500/10 hover:bg-rose-500/20 text-xs font-semibold text-rose-400 transition">
                        Padam
                    </button>
                </form>

                <div class="flex items-center gap-2">
                    <form method="POST" :action="'/crm/appointments/' + selectedAppointment?.id + '/status'">
                        @csrf
                        <input type="hidden" name="status" value="completed">
                        <button type="submit" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition">
                            Tandakan Selesai
                        </button>
                    </form>
                    <button type="button" @click="viewModal = false" class="px-4 py-2 rounded-xl border border-slate-700 text-xs font-semibold text-slate-400 hover:text-white">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
