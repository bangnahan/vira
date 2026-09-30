<?php

namespace Tests\Feature;

use App\Models\SpxShippingRate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SpxShippingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        SpxShippingRate::create([
            'origin_city' => 'KAB. TANGERANG',
            'destination_city' => 'KAB. BADUNG',
            'destination_district' => 'KUTA',
            'rate_hemat' => 19300.00,
            'sla_hemat_days' => 10,
            'rate_regular' => 30000.00,
            'sla_regular_days' => 6,
        ]);
    }

    public function test_can_fetch_destination_cities(): void
    {
        $response = $this->getJson('/api/shipping/cities?q=BADUNG');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'data' => ['KAB. BADUNG'],
            ]);
    }

    public function test_can_fetch_districts_by_city(): void
    {
        $response = $this->getJson('/api/shipping/districts?city=KAB. BADUNG');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'city' => 'KAB. BADUNG',
                'data' => ['KUTA'],
            ]);
    }

    public function test_can_calculate_spx_shipping_rate(): void
    {
        $response = $this->postJson('/api/shipping/calculate', [
            'destination_city' => 'KAB. BADUNG',
            'destination_district' => 'KUTA',
            'weight_grams' => 1500, // 2 kg
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'origin' => 'KAB. TANGERANG',
                    'destination_city' => 'KAB. BADUNG',
                    'destination_district' => 'KUTA',
                    'weight_grams' => 1500,
                    'chargeable_kg' => 2,
                    'services' => [
                        [
                            'service_code' => 'SPX_HEMAT',
                            'total_cost' => 38600.00,
                        ],
                        [
                            'service_code' => 'SPX_REGULAR',
                            'total_cost' => 60000.00,
                        ],
                    ],
                ],
            ]);
    }
}
