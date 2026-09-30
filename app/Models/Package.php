<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Package extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'name',
        'description',
        'price',
        'base_weight_grams',
        'includes_jersey',
        'includes_medal',
        'requires_shipping',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'base_weight_grams' => 'integer',
            'includes_jersey' => 'boolean',
            'includes_medal' => 'boolean',
            'requires_shipping' => 'boolean',
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
}
