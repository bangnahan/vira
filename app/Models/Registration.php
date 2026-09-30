<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class Registration extends Model
{
    use HasFactory;

    protected $fillable = [
        'access_token',
        'event_id',
        'category_id',
        'package_id',
        'participant_id',
        'bib_number',
        'bib_image_path',
        'certificate_path',
        'payment_status',
        'total_distance_km',
        'total_duration_seconds',
        'last_milestone_notified',
        'finisher_status',
        'finished_at',
    ];

    protected function casts(): array
    {
        return [
            'total_distance_km' => 'decimal:2',
            'total_duration_seconds' => 'integer',
            'last_milestone_notified' => 'integer',
            'finished_at' => 'datetime',
        ];
    }

    protected $appends = [
        'registration_number',
    ];

    public function getRegistrationNumberAttribute(): string
    {
        if ($this->bib_number) {
            return 'REG-'.$this->bib_number;
        }

        return 'ORD-'.str_pad((string) $this->id, 6, '0', STR_PAD_LEFT);
    }

    protected static function booted(): void
    {
        static::creating(function (Registration $registration) {
            if (empty($registration->access_token)) {
                $registration->access_token = (string) Str::uuid();
            }
        });
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    public function participant(): BelongsTo
    {
        return $this->belongsTo(Participant::class);
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }

    public function shippingAddress(): HasOne
    {
        return $this->hasOne(ShippingAddress::class);
    }

    public function activitySubmissions(): HasMany
    {
        return $this->hasMany(ActivitySubmission::class);
    }

    public function registrationAddOns(): HasMany
    {
        return $this->hasMany(RegistrationAddOn::class);
    }

    public function isPaid(): bool
    {
        return $this->payment_status === 'PAID';
    }

    public function isFinisher(): bool
    {
        return $this->finisher_status === 'FINISHED';
    }

    /**
     * Hitung persentase target jarak (maks 100%).
     */
    public function calculateProgressPercentage(): float
    {
        $target = (float) ($this->category->target_distance_km ?? 0);
        if ($target <= 0) {
            return 0.0;
        }

        $percentage = ((float) $this->total_distance_km / $target) * 100;

        return min(round($percentage, 1), 100.0);
    }

    /**
     * Hitung milestone motivasi kelipatan 20% yang belum terkirim.
     */
    public function checkEligibleMotivationMilestone(): ?int
    {
        $target = (float) ($this->category->target_distance_km ?? 0);
        if ($target <= 0) {
            return null;
        }

        $percentage = ((float) $this->total_distance_km / $target) * 100;
        $milestones = [20, 40, 60, 80, 100];
        $eligible = null;

        foreach ($milestones as $m) {
            if ($percentage >= $m && $this->last_milestone_notified < $m) {
                $eligible = $m;
            }
        }

        return $eligible;
    }

    /**
     * Format durasi detik ke format jam:menit:detik (HH:MM:SS).
     */
    public function formattedTotalDuration(): string
    {
        $seconds = $this->total_duration_seconds;
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $secs = $seconds % 60;

        return sprintf('%02d:%02d:%02d', $hours, $minutes, $secs);
    }

    /**
     * Hitung Pace rata-rata (menit per KM).
     */
    public function averagePace(): string
    {
        $km = (float) $this->total_distance_km;
        if ($km <= 0 || $this->total_duration_seconds <= 0) {
            return "0'00\" /km";
        }

        $paceSecondsPerKm = $this->total_duration_seconds / $km;
        $paceMin = floor($paceSecondsPerKm / 60);
        $paceSec = round($paceSecondsPerKm % 60);

        return sprintf("%d'%02d\" /km", $paceMin, $paceSec);
    }

    /**
     * Generate nomor e-BIB berikutnya yang unik dan berurutan secara global (murni angka mulai dari 1001).
     * Jika melampaui 9999, nomor lanjut ke 5 digit (10000) dan seterusnya.
     * Tidak mengubah nomor BIB historis non-numerik yang sudah ada sebelumnya.
     */
    public static function generateNextBibNumber(): string
    {
        return (string) DB::transaction(function () {
            // Ambil nomor BIB numerik tertinggi
            // Lock row untuk mencegah race condition pada concurrent webhook/payment confirmation
            $recentBibs = self::whereNotNull('bib_number')
                ->lockForUpdate()
                ->latest('id')
                ->take(1000)
                ->pluck('bib_number');

            $maxNumber = 0;
            foreach ($recentBibs as $bib) {
                $val = trim((string) $bib);
                if (ctype_digit($val)) {
                    $num = (int) $val;
                    if ($num > $maxNumber) {
                        $maxNumber = $num;
                    }
                }
            }

            // Jika belum ditemukan nomor numerik pada 1000 baris terakhir, scan semua baris
            if ($maxNumber === 0) {
                $allBibs = self::whereNotNull('bib_number')->pluck('bib_number');
                foreach ($allBibs as $bib) {
                    $val = trim((string) $bib);
                    if (ctype_digit($val)) {
                        $num = (int) $val;
                        if ($num > $maxNumber) {
                            $maxNumber = $num;
                        }
                    }
                }
            }

            $next = ($maxNumber >= 1001) ? $maxNumber + 1 : 1001;

            // Pastikan tidak ada collision
            while (self::where('bib_number', (string) $next)->exists()) {
                $next++;
            }

            return (string) $next;
        });
    }
}
