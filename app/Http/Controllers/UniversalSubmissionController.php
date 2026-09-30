<?php

namespace App\Http\Controllers;

use App\Models\ActivitySubmission;
use App\Models\Registration;
use App\Services\MailketingService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class UniversalSubmissionController extends Controller
{
    public function __construct(
        protected MailketingService $mailketingService
    ) {}

    /**
     * Halaman Universal Submission Portal satu pintu untuk seluruh event.
     */
    public function index(Request $request): View
    {
        $bib = trim($request->query('bib', ''));
        $registration = null;

        if ($bib !== '') {
            $registration = Registration::with([
                'event',
                'category',
                'participant',
                'activitySubmissions' => fn ($q) => $q->orderBy('activity_date', 'desc')->orderBy('created_at', 'desc'),
            ])
                ->where('bib_number', $bib)
                ->first();
        }

        return view('submit.index', compact('bib', 'registration'));
    }

    /**
     * Cari dan verifikasi nomor e-BIB.
     */
    public function lookup(Request $request): RedirectResponse
    {
        $request->validate([
            'bib_number' => ['required', 'string'],
        ]);

        $bib = strtoupper(trim($request->input('bib_number')));

        $registration = Registration::where('bib_number', $bib)->first();

        if (! $registration) {
            return redirect()->route('submit.index')
                ->withInput()
                ->with('error', "Nomor e-BIB '{$bib}' tidak ditemukan dalam sistem. Harap periksa kembali nomor e-BIB Anda.");
        }

        if (! $registration->isPaid()) {
            return redirect()->route('submit.index')
                ->withInput()
                ->with('error', "Nomor e-BIB '{$bib}' belum aktif karena pembayaran belum terkonfirmasi.");
        }

        return redirect()->route('submit.index', ['bib' => $bib]);
    }

    /**
     * Simpan catatan aktivitas lari/ride/walk baru.
     */
    public function record(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'registration_id' => ['required', 'exists:registrations,id'],
            'activity_date' => ['required', 'date', 'before_or_equal:today'],
            'activity_time' => ['nullable', 'string'],
            'distance_km' => ['required', 'numeric', 'min:0.1', 'max:500'],
            'duration_hours' => ['nullable', 'integer', 'min:0', 'max:99'],
            'duration_minutes' => ['nullable', 'integer', 'min:0', 'max:59'],
            'duration_seconds' => ['nullable', 'integer', 'min:0', 'max:59'],
            'proof_url' => ['required', 'url', 'max:1000'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $registration = Registration::with(['event', 'category'])->findOrFail($validated['registration_id']);

        if (! $registration->isPaid()) {
            return redirect()->route('submit.index')
                ->with('error', 'Aktivitas hanya dapat dicatat untuk pendaftaran yang status pembayarannya telah lunas/aktif.');
        }

        if ($registration->event) {
            $event = $registration->event;
            $activityDate = Carbon::parse($validated['activity_date'])->startOfDay();

            if ($event->race_start && $activityDate->lt($event->race_start->startOfDay())) {
                return back()->withInput()->with('error', 'Tanggal aktivitas tidak boleh mendahului periode dimulainya lomba (mulai '.$event->race_start->translatedFormat('d F Y').').');
            }

            if ($event->race_end && $activityDate->gt($event->race_end->endOfDay())) {
                return back()->withInput()->with('error', 'Tanggal aktivitas melebihi batas akhir periode lomba (berakhir '.$event->race_end->translatedFormat('d F Y').').');
            }
        }

        $hours = (int) ($validated['duration_hours'] ?? 0);
        $minutes = (int) ($validated['duration_minutes'] ?? 0);
        $seconds = (int) ($validated['duration_seconds'] ?? 0);
        $totalDurationSec = ($hours * 3600) + ($minutes * 60) + $seconds;

        if ($totalDurationSec <= 0) {
            return back()->withInput()->with('error', 'Waktu tempuh aktivitas harus lebih dari 0 detik.');
        }

        $distanceKm = (float) $validated['distance_km'];
        $calculatedPace = ActivitySubmission::calculatePace($distanceKm, $totalDurationSec);

        $motivationMessage = null;

        $eligibleMilestone = null;

        DB::transaction(function () use (
            $registration,
            $validated,
            $distanceKm,
            $totalDurationSec,
            $calculatedPace,
            &$motivationMessage,
            &$eligibleMilestone
        ) {
            // 1. Simpan Activity Submission
            ActivitySubmission::create([
                'registration_id' => $registration->id,
                'activity_date' => $validated['activity_date'],
                'activity_time' => $validated['activity_time'] ?? now()->format('H:i:s'),
                'distance_km' => $distanceKm,
                'duration_seconds' => $totalDurationSec,
                'calculated_pace' => $calculatedPace,
                'proof_url' => $validated['proof_url'],
                'notes' => $validated['notes'] ?? null,
                'is_potential_winner' => false,
                'validation_status' => 'VALID',
            ]);

            // 2. Akumulasi Jarak & Waktu (Fleksibel per event: Single vs Cumulative)
            $isCumulative = ($registration->event->submission_mode === 'CUMULATIVE');

            if ($isCumulative) {
                $newTotalDistance = (float) $registration->total_distance_km + $distanceKm;
                $newTotalDuration = $registration->total_duration_seconds + $totalDurationSec;
            } else {
                // Single session: ambil yang tertinggi atau sesi ini
                $newTotalDistance = max((float) $registration->total_distance_km, $distanceKm);
                $newTotalDuration = $totalDurationSec;
            }

            $targetDistance = (float) $registration->category->target_distance_km;
            $isFinisher = ($newTotalDistance >= $targetDistance);

            // 3. Evaluasi 5-Stage Progressive Motivation Email (tiap 20% milestone)
            $percentage = ($targetDistance > 0) ? ($newTotalDistance / $targetDistance) * 100 : 0;
            $milestones = [20, 40, 60, 80, 100];

            foreach ($milestones as $m) {
                if ($percentage >= $m && $registration->last_milestone_notified < $m) {
                    $eligibleMilestone = $m;
                }
            }

            $quotes = [
                20 => 'Awal yang luar biasa! 20% target telah kamu taklukkan. Perjalanan ribuan kilometer dimulai dari langkah pertama!',
                40 => 'Momentummu tak terbendung! 40% target telah tercapai. Terus jaga ritme konsistensimu!',
                60 => 'Lebih dari separuh jalan! 60% jarak telah kamu selesaikan. Garis finish kini semakin nyata di depan mata!',
                80 => 'Tinggal sedikit lagi! 80% target telah terlampaui. Kumpulkan sisa tenaga untuk dorongan terakhir!',
                100 => 'LUAR BIASA! 100% Target tuntas — Selamat, kamu resmi menjadi FINISHER! 🏆',
            ];

            if ($eligibleMilestone !== null) {
                $registration->last_milestone_notified = $eligibleMilestone;
                $motivationMessage = $quotes[$eligibleMilestone] ?? null;
            }

            $registration->total_distance_km = $newTotalDistance;
            $registration->total_duration_seconds = $newTotalDuration;

            if ($isFinisher && $registration->finisher_status !== 'FINISHED') {
                $registration->finisher_status = 'FINISHED';
                $registration->finished_at = now();
            }

            $registration->save();
        });

        // 4. Kirim Progressive Motivation Email via Mailketing
        if ($eligibleMilestone !== null && $motivationMessage) {
            try {
                $this->mailketingService->sendMilestoneEmail($registration->fresh(), $eligibleMilestone, $motivationMessage);
            } catch (\Exception $e) {
                Log::warning('Mailketing milestone progress email warning: '.$e->getMessage());
            }
        }

        $successMsg = 'Aktivitas berhasil dicatat! Jarak total kamu kini '.$registration->fresh()->total_distance_km.' km.';
        if ($motivationMessage) {
            $successMsg .= ' '.$motivationMessage;
        }

        return redirect()->route('submit.index', ['bib' => $registration->bib_number])
            ->with('success', $successMsg);
    }
}
