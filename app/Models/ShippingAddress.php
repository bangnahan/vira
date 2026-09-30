<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShippingAddress extends Model
{
    use HasFactory;

    protected $fillable = [
        'registration_id',
        'recipient_name',
        'recipient_phone',
        'province',
        'city',
        'district',
        'postal_code',
        'address_detail',
        'jersey_size',
        'total_weight_grams',
        'courier_name',
        'shipping_cost',
        'tracking_number',
        'shipping_status',
        'shipped_at',
    ];

    protected function casts(): array
    {
        return [
            'total_weight_grams' => 'integer',
            'shipping_cost' => 'decimal:2',
            'shipped_at' => 'datetime',
        ];
    }

    public function registration(): BelongsTo
    {
        return $this->belongsTo(Registration::class);
    }
}
