@extends('layouts.app')

@section('title', 'Universal Submission Portal — VIRA')

@section('content')
<div class="max-w-4xl mx-auto px-3.5 sm:px-6 lg:px-8 py-6 sm:py-10">
    <!-- Header -->
    <div class="text-center mb-6 sm:mb-8">
        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-cyan-950/80 border border-cyan-500/30 text-xs text-cyan-300 font-bold mb-3">
            <span class="w-2 h-2 rounded-full bg-[#00E5FF] animate-pulse"></span>
            <span>Universal Activity Portal</span>
        </div>
        <h1 class="text-2xl sm:text-4xl font-extrabold text-white">Portal Pencatatan Aktivitas Lari</h1>
        <p class="text-xs sm:text-sm text-slate-400 mt-2 max-w-xl mx-auto">
            Satu portal untuk semua event VIRA. Masukkan nomor e-BIB resmi Anda untuk melihat progres dan mengirimkan hasil lari.
        </p>
    </div>

    <!-- BIB Lookup Form -->
    <div class="bg-slate-900/90 border border-slate-800 rounded-2xl sm:rounded-3xl p-4 sm:p-8 shadow-2xl mb-6 sm:mb-8">
        <form action="{{ route('submit.lookup') }}" method="POST" class="flex flex-col sm:flex-row gap-3">
            @csrf
            <div class="relative flex-grow">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-500">
                    <span class="font-athletic text-lg">BIB#</span>
                </div>
                <input type="text" name="bib_number" value="{{ old('bib_number', $bib) }}" required placeholder="Contoh: 1001"
                       class="w-full pl-16 pr-4 py-3 sm:py-3.5 bg-slate-950 border border-slate-700/80 rounded-xl sm:rounded-2xl text-white font-mono-num text-sm sm:text-base uppercase font-bold tracking-wider focus:border-[#00E5FF] focus:ring-0">
            </div>

            <button type="submit" class="w-full sm:w-auto px-7 py-3 sm:py-3.5 rounded-xl sm:rounded-2xl font-bold uppercase tracking-wider text-xs sm:text-sm text-white bg-gradient-to-r from-[#00B4D8] to-[#0077B6] hover:from-[#00E5FF] hover:to-[#00B4D8] transition shadow-lg shadow-cyan-950/50 flex items-center justify-center gap-2">
                <span>Cari / Buka Data</span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </button>
        </form>
    </div>

    <!-- If Registration is Resolved -->
    @if($registration)
        <!-- Event & Participant Hero Card -->
        <div class="bg-gradient-to-b from-slate-900 to-slate-950 border border-slate-800 rounded-2xl sm:rounded-3xl p-4 sm:p-8 shadow-2xl mb-6 sm:mb-8 relative overflow-hidden">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 sm:gap-4 pb-5 sm:pb-6 border-b border-slate-800">
                <div>
                    <div class="flex items-center gap-2 mb-2">
                        <span class="px-2.5 py-0.5 rounded text-[10px] sm:text-[11px] font-extrabold uppercase bg-[#FF5500] text-white">
                            {{ $registration->event->activity_type }}
                        </span>
                        <span class="px-2.5 py-0.5 rounded text-[10px] sm:text-[11px] font-bold bg-slate-800 text-slate-300">
                            {{ $registration->event->submission_mode === 'CUMULATIVE' ? 'Akumulasi Jarak' : 'Single Session' }}
                        </span>
                    </div>
                    <h2 class="text-xl sm:text-2xl font-extrabold text-white">{{ $registration->event->title }}</h2>
                    <p class="text-xs text-slate-400 mt-1">
                        Peserta: <strong class="text-slate-200">{{ $registration->participant->full_name }}</strong> &bull; Kategori: <strong class="text-[#FF5500]">{{ $registration->category->name }} ({{ number_format($registration->category->target_distance_km, 1) }} KM)</strong>
                    </p>
                </div>

                <div class="text-left sm:text-right pt-2 sm:pt-0 border-t sm:border-t-0 border-slate-800/80">
                    <span class="text-[10px] uppercase font-bold tracking-widest text-slate-400 block">Nomor e-BIB:</span>
                    <span class="font-athletic text-3xl sm:text-5xl text-[#00E5FF] tracking-widest block">{{ $registration->bib_number }}</span>
                </div>
            </div>

            <!-- Progress Bar & Status -->
            <div class="py-5 sm:py-6 border-b border-slate-800">
                <div class="flex items-center justify-between text-xs mb-2">
                    <span class="font-bold uppercase tracking-wider text-slate-400">Progres Jarak Tempuh</span>
                    <span class="font-bold text-white font-mono-num text-xs sm:text-sm">
                        {{ number_format($registration->total_distance_km, 2) }} / {{ number_format($registration->category->target_distance_km, 2) }} KM
                        <span class="text-[#00E5FF] ml-1">({{ $registration->calculateProgressPercentage() }}%)</span>
                    </span>
                </div>

                <!-- Bar -->
                <div class="w-full h-3.5 sm:h-4 bg-slate-950 rounded-full overflow-hidden p-0.5 border border-slate-800">
                    <div class="h-full rounded-full bg-gradient-to-r from-[#00E5FF] via-cyan-400 to-emerald-400 transition-all duration-700"
                         style="width: {{ min($registration->calculateProgressPercentage(), 100) }}%"></div>
                </div>

                <!-- 5-Stage Motivation Milestones Indicator -->
                <div class="mt-3.5 sm:mt-4 grid grid-cols-5 gap-1 sm:gap-1.5 text-center">
                    @foreach([20, 40, 60, 80, 100] as $stage)
                        @php
                            $isAchieved = ($registration->calculateProgressPercentage() >= $stage);
                        @endphp
                        <div class="p-1 sm:p-2 rounded-lg sm:rounded-xl border {{ $isAchieved ? 'border-emerald-500/40 bg-emerald-500/10 text-emerald-300' : 'border-slate-800 bg-slate-950/60 text-slate-400' }}">
                            <div class="font-bold font-mono-num text-[11px] sm:text-xs">{{ $stage }}%</div>
                            <div class="text-[8px] sm:text-[9px] uppercase tracking-wider mt-0.5">
                                {{ $stage === 100 ? 'FINISHER' : ($isAchieved ? 'Tembus' : 'Target') }}
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Stats Highlights -->
            <div class="pt-6 grid grid-cols-2 sm:grid-cols-4 gap-4 text-center">
                <div class="p-3 bg-slate-950/60 rounded-xl border border-slate-800">
                    <span class="text-[10px] uppercase font-bold text-slate-400 block mb-1">Total Jarak</span>
                    <span class="font-mono-num text-lg font-bold text-white">{{ number_format($registration->total_distance_km, 2) }} <span class="text-xs text-slate-400">KM</span></span>
                </div>
                <div class="p-3 bg-slate-950/60 rounded-xl border border-slate-800">
                    <span class="text-[10px] uppercase font-bold text-slate-400 block mb-1">Total Waktu</span>
                    <span class="font-mono-num text-lg font-bold text-white">{{ $registration->formattedTotalDuration() }}</span>
                </div>
                <div class="p-3 bg-slate-950/60 rounded-xl border border-slate-800">
                    <span class="text-[10px] uppercase font-bold text-slate-400 block mb-1">Pace Rata-rata</span>
                    <span class="font-mono-num text-lg font-bold text-cyan-400">{{ $registration->averagePace() }}</span>
                </div>
                <div class="p-3 bg-slate-950/60 rounded-xl border border-slate-800">
                    <span class="text-[10px] uppercase font-bold text-slate-400 block mb-1">Status Lomba</span>
                    @if($registration->isFinisher())
                        <span class="font-bold text-xs uppercase px-2 py-0.5 rounded bg-amber-500/20 text-amber-300 border border-amber-500/40">FINISHER 🏆</span>
                    @else
                        <span class="font-bold text-xs uppercase px-2 py-0.5 rounded bg-cyan-500/20 text-cyan-300 border border-cyan-500/40">ON PROGRESS</span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Finisher Banner if Completed -->
        @if($registration->isFinisher())
            <div class="p-6 rounded-3xl bg-gradient-to-r from-amber-950/60 via-slate-900 to-slate-900 border border-amber-500/40 text-center mb-8 shadow-2xl">
                <div class="text-3xl mb-2">🏆</div>
                <h3 class="text-xl font-extrabold text-white">SELAMAT! KAMU RESMI MENJADI FINISHER</h3>
                <p class="text-xs text-slate-300 max-w-md mx-auto mt-1 leading-relaxed">
                    Target jarak {{ $registration->category->target_distance_km }} KM telah tuntas diselesaikan pada {{ $registration->finished_at ? $registration->finished_at->format('d M Y, H:i') : now()->format('d M Y') }}.
                </p>
                <div class="mt-4 flex flex-wrap items-center justify-center gap-3">
                    <a href="{{ route('participant.download.certificate', $registration->bib_number) }}" class="px-6 py-2.5 rounded-xl font-bold text-xs uppercase tracking-wider text-slate-900 bg-gradient-to-r from-[#FFD700] to-amber-400 hover:from-amber-400 hover:to-amber-500 transition shadow-lg shadow-amber-950/50 flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                        <span>Unduh E-Certificate Finisher (.PNG)</span>
                    </a>
                    <a href="{{ route('participant.download.bib', $registration->bib_number) }}" class="px-5 py-2.5 rounded-xl font-bold text-xs uppercase tracking-wider text-slate-300 bg-slate-800 hover:bg-slate-700 border border-slate-700 transition flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                        <span>Unduh Kartu e-BIB (.PNG)</span>
                    </a>
                </div>
            </div>
        @endif

        <!-- Form Submit Aktivitas Baru -->
        <div class="bg-slate-900/90 border border-slate-800 rounded-2xl sm:rounded-3xl p-4 sm:p-8 shadow-2xl mb-6 sm:mb-8">
            <div class="flex items-center gap-3 mb-6">
                <span class="w-8 h-8 rounded-lg bg-[#FF5500]/20 text-[#FF5500] font-athletic text-xl flex items-center justify-center">+</span>
                <div>
                    <h3 class="text-base sm:text-lg font-bold text-white">Submit Catatan Aktivitas Baru</h3>
                    <p class="text-xs text-slate-400">Catat lari hari ini secara manual dan sertakan link bukti rekaman (Strava/GDrive/Garmin).</p>
                </div>
            </div>

            <form action="{{ route('submit.record') }}" method="POST" class="space-y-5 sm:space-y-6">
                @csrf
                <input type="hidden" name="registration_id" value="{{ $registration->id }}">

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-5">
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-400 mb-1.5">Tanggal Aktivitas <span class="text-rose-400">*</span></label>
                        <input type="date" name="activity_date" value="{{ old('activity_date', date('Y-m-d')) }}" max="{{ date('Y-m-d') }}" required
                               class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-3 text-sm text-white focus:border-[#FF5500]">
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-400 mb-1.5">Jarak Tempuh (Kilometer) <span class="text-rose-400">*</span></label>
                        <input type="number" step="0.01" min="0.10" max="200" name="distance_km" value="{{ old('distance_km') }}" required placeholder="Contoh: 5.25"
                               class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-3 text-sm text-white font-mono-num focus:border-[#FF5500]">
                    </div>

                    <!-- Waktu Tempuh: Jam, Menit, Detik -->
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-bold uppercase text-slate-400 mb-1.5">Waktu Tempuh (Durasi) <span class="text-rose-400">*</span></label>
                        <div class="grid grid-cols-3 gap-2 sm:gap-3">
                            <div>
                                <span class="text-[10px] text-slate-400 block mb-1 font-bold">Jam</span>
                                <input type="number" min="0" max="99" name="duration_hours" value="{{ old('duration_hours', 0) }}"
                                       class="w-full bg-slate-950 border border-slate-800 rounded-xl px-2 sm:px-3 py-2.5 text-center text-sm text-white font-mono-num focus:border-[#FF5500]">
                            </div>
                            <div>
                                <span class="text-[10px] text-slate-400 block mb-1 font-bold">Menit</span>
                                <input type="number" min="0" max="59" name="duration_minutes" value="{{ old('duration_minutes', 30) }}" required
                                       class="w-full bg-slate-950 border border-slate-800 rounded-xl px-2 sm:px-3 py-2.5 text-center text-sm text-white font-mono-num focus:border-[#FF5500]">
                            </div>
                            <div>
                                <span class="text-[10px] text-slate-400 block mb-1 font-bold">Detik</span>
                                <input type="number" min="0" max="59" name="duration_seconds" value="{{ old('duration_seconds', 0) }}" required
                                       class="w-full bg-slate-950 border border-slate-800 rounded-xl px-2 sm:px-3 py-2.5 text-center text-sm text-white font-mono-num focus:border-[#FF5500]">
                            </div>
                        </div>
                    </div>

                    <!-- Link Bukti Strava / GDrive -->
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-bold uppercase text-slate-400 mb-1.5">Link Bukti Aktivitas (Strava / GDrive / Garmin / Foto) <span class="text-rose-400">*</span></label>
                        <input type="url" name="proof_url" value="{{ old('proof_url') }}" required placeholder="https://www.strava.com/activities/123456789 atau https://drive.google.com/..."
                               class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-3 text-sm text-white focus:border-[#FF5500]">
                        <span class="text-[11px] text-slate-400 mt-1 block">Pastikan link aktivitas diset ke publik agar bisa diverifikasi panitia jika masuk nominasi juara.</span>
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block text-xs font-bold uppercase text-slate-400 mb-1.5">Catatan Tambahan (Opsional)</label>
                        <textarea name="notes" rows="2" placeholder="Lari pagi di GBK, cuaca cerah..."
                                  class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-3 text-sm text-white focus:border-[#FF5500]">{{ old('notes') }}</textarea>
                    </div>
                </div>

                <div class="pt-4 border-t border-slate-800 flex justify-end">
                    <button type="submit" class="w-full sm:w-auto px-8 py-3.5 rounded-xl font-bold uppercase tracking-wider text-xs sm:text-sm text-white bg-gradient-to-r from-[#FF5500] to-[#FF7700] hover:from-[#FF6600] hover:to-[#FF8800] transition shadow-lg shadow-orange-950/60 glow-orange flex items-center justify-center gap-2">
                        <span>Simpan Catatan Lari</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    </button>
                </div>
            </form>
        </div>

        <!-- Riwayat Catatan Aktivitas -->
        <div class="bg-slate-900/90 border border-slate-800 rounded-2xl sm:rounded-3xl p-4 sm:p-8 shadow-2xl">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm sm:text-base font-bold text-white">Riwayat Catatan Aktivitas Terkirim</h3>
                <span class="text-[10px] text-slate-500 sm:hidden">Geser tabel &rarr;</span>
            </div>

            <div class="overflow-x-auto no-scrollbar">
                <table class="w-full min-w-[480px] text-left text-xs">
                    <thead>
                        <tr class="border-b border-slate-800 text-slate-400 uppercase font-bold text-[10px]">
                            <th class="pb-3">Tanggal</th>
                            <th class="pb-3">Jarak</th>
                            <th class="pb-3">Durasi</th>
                            <th class="pb-3">Pace</th>
                            <th class="pb-3">Bukti</th>
                            <th class="pb-3 text-right">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/60 text-slate-300">
                        @forelse($registration->activitySubmissions as $sub)
                            <tr>
                                <td class="py-3.5 font-semibold text-white">{{ $sub->activity_date->format('d M Y') }}</td>
                                <td class="py-3.5 font-mono-num font-bold text-cyan-400">{{ number_format($sub->distance_km, 2) }} KM</td>
                                <td class="py-3.5 font-mono-num">{{ $sub->formattedDuration() }}</td>
                                <td class="py-3.5 font-mono-num text-slate-400">{{ number_format($sub->calculated_pace, 2) }}'/km</td>
                                <td class="py-3.5">
                                    <a href="{{ $sub->proof_url }}" target="_blank" rel="noopener noreferrer" class="text-[#00E5FF] hover:underline flex items-center gap-1">
                                        <span>Buka Link</span>
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                    </a>
                                </td>
                                <td class="py-3.5 text-right">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/30">
                                        {{ $sub->validation_status }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-8 text-center text-slate-500">
                                    Belum ada catatan aktivitas lari yang disubmit. Masukkan catatan lari pertamamu di atas!
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>
@endsection
