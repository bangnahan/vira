<?php

namespace Database\Seeders;

use App\Models\SpxShippingRate;
use Illuminate\Database\Seeder;

class SpxShippingRateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (SpxShippingRate::count() >= 7000) {
            $this->command?->info('Data tarif SPX sudah terisi ('.SpxShippingRate::count().' baris). Melewati seeder.');

            return;
        }

        $jsonPath = database_path('data/spx_rates.json');
        if (! file_exists($jsonPath)) {
            $this->command?->error("File data tidak ditemukan di: {$jsonPath}");

            return;
        }

        $rates = json_decode(file_get_contents($jsonPath), true);
        if (! is_array($rates) || empty($rates)) {
            $this->command?->error('File JSON kosong atau format tidak valid.');

            return;
        }

        $now = now();
        $batch = [];
        $total = 0;

        foreach ($rates as $item) {
            $batch[] = [
                'origin_city' => $item['origin_city'] ?? 'KAB. TANGERANG',
                'destination_city' => $item['destination_city'],
                'destination_district' => $item['destination_district'],
                'rate_hemat' => (float) ($item['rate_hemat'] ?? 0),
                'sla_hemat_days' => (int) ($item['sla_hemat_days'] ?? 7),
                'rate_regular' => (float) ($item['rate_regular'] ?? 0),
                'sla_regular_days' => (int) ($item['sla_regular_days'] ?? 3),
                'created_at' => $now,
                'updated_at' => $now,
            ];

            if (count($batch) >= 500) {
                SpxShippingRate::insert($batch);
                $total += count($batch);
                $batch = [];
            }
        }

        if (! empty($batch)) {
            SpxShippingRate::insert($batch);
            $total += count($batch);
        }

        $totalCities = SpxShippingRate::distinct('destination_city')->count();
        $this->command?->info("✓ SpxShippingRateSeeder berhasil mengimpor {$total} tarif SPX ke {$totalCities} Kota/Kabupaten.");
    }
}
