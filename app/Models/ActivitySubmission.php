<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivitySubmission extends Model
{
    use HasFactory;

    protected $fillable = [
        'registration_id',
        'activity_date',
        'activity_time',
        'distance_km',
        'duration_seconds',
        'calculated_pace',
        'proof_url',
        'notes',
        'is_potential_winner',
        'validation_status',
        'reviewed_by',
        'admin_notes',
    ];

    protected function casts(): array
    {
        return [
            'activity_date' => 'date',
            'distance_km' => 'decimal:2',
            'duration_seconds' => 'integer',
            'calculated_pace' => 'decimal:2',
            'is_potential_winner' => 'boolean',
        ];
    }

    public function registration(): BelongsTo
    {
        return $this->belongsTo(Registration::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function formattedDuration(): string
    {
        $seconds = $this->duration_seconds;
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $secs = $seconds % 60;

        return sprintf('%02d:%02d:%02d', $hours, $minutes, $secs);
    }

    /**
     * Hitung pace menit/km otomatis sebelum simpan.
     */
    public static function calculatePace(float $km, int $seconds): float
    {
        if ($km <= 0 || $seconds <= 0) {
            return 0.0;
        }

        return round(($seconds / 60) / $km, 2);
    }
}
