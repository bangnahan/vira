<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'name',
        'target_distance_km',
        'bib_prefix',
        'last_bib_sequence',
        'quota',
        'registered_count',
    ];

    protected function casts(): array
    {
        return [
            'target_distance_km' => 'decimal:2',
            'last_bib_sequence' => 'integer',
            'quota' => 'integer',
            'registered_count' => 'integer',
        ];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(Registration::class);
    }

    /**
     * Generate nomor e-BIB berikutnya yang unik dan berurutan secara global (murni angka mulai dari 1001).
     */
    public function generateNextBibNumber(): string
    {
        $this->increment('last_bib_sequence');

        return Registration::generateNextBibNumber();
    }
}
