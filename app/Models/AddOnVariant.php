<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AddOnVariant extends Model
{
    use HasFactory;

    protected $fillable = [
        'add_on_id',
        'variant_name',
        'additional_price',
        'stock',
    ];

    protected function casts(): array
    {
        return [
            'additional_price' => 'decimal:2',
            'stock' => 'integer',
        ];
    }

    public function addOn(): BelongsTo
    {
        return $this->belongsTo(AddOn::class);
    }

    public function registrationAddOns(): HasMany
    {
        return $this->hasMany(RegistrationAddOn::class);
    }
}
