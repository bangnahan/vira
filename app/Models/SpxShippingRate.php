<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SpxShippingRate extends Model
{
    use HasFactory;

    protected $fillable = [
        'origin_city',
        'destination_city',
        'destination_district',
        'rate_hemat',
        'sla_hemat_days',
        'rate_regular',
        'sla_regular_days',
    ];

    protected function casts(): array
    {
        return [
            'rate_hemat' => 'decimal:2',
            'sla_hemat_days' => 'integer',
            'rate_regular' => 'decimal:2',
            'sla_regular_days' => 'integer',
        ];
    }

    /**
     * Hitung ongkir SPX berdasarkan kota, kecamatan, dan berat (gram).
     * Aturan SPX: Minimum 1 kg, pembulatan ke atas (ceil).
     */
    public static function calculateRate(string $city, string $district, int $weightGrams = 1000): ?array
    {
        $rate = static::where('destination_city', $city)
            ->where('destination_district', $district)
            ->first();

        if (! $rate) {
            // Coba pencarian case-insensitive jika exact match tidak ditemukan
            $rate = static::whereRaw('LOWER(destination_city) = ?', [strtolower($city)])
                ->whereRaw('LOWER(destination_district) = ?', [strtolower($district)])
                ->first();
        }

        if (! $rate) {
            return null;
        }

        // Hitung kg pembulatan ke atas, min 1 kg
        $weightKg = max(1, (int) ceil($weightGrams / 1000));

        $costHemat = (float) $rate->rate_hemat * $weightKg;
        $costRegular = (float) $rate->rate_regular * $weightKg;

        return [
            'origin' => $rate->origin_city,
            'destination_city' => $rate->destination_city,
            'destination_district' => $rate->destination_district,
            'weight_grams' => $weightGrams,
            'chargeable_kg' => $weightKg,
            'services' => [
                [
                    'service_code' => 'SPX_HEMAT',
                    'service_name' => 'SPX Hemat (Ekonomi)',
                    'rate_per_kg' => (float) $rate->rate_hemat,
                    'total_cost' => $costHemat,
                    'etd_days' => $rate->sla_hemat_days,
                    'etd_text' => "{$rate->sla_hemat_days} hari",
                ],
                [
                    'service_code' => 'SPX_REGULAR',
                    'service_name' => 'SPX Regular (Standar)',
                    'rate_per_kg' => (float) $rate->rate_regular,
                    'total_cost' => $costRegular,
                    'etd_days' => $rate->sla_regular_days,
                    'etd_text' => "{$rate->sla_regular_days} hari",
                ],
            ],
        ];
    }
}
