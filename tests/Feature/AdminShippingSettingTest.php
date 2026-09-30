<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\SpxShippingRate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminShippingSettingTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'name' => 'Admin Ekspedisi',
            'email' => 'admin.shipping@vira.id',
            'password' => Hash::make('password'),
            'role' => 'SUPER_ADMIN',
        ]);

        SpxShippingRate::create([
            'origin_city' => 'KAB. TANGERANG',
            'destination_city' => 'KOTA ADM. JAKARTA SELATAN',
            'destination_district' => 'CILANDAK',
            'rate_hemat' => 9000,
            'sla_hemat_days' => 3,
            'rate_regular' => 11000,
            'sla_regular_days' => 2,
        ]);
    }

    public function test_guest_cannot_access_shipping_settings(): void
    {
        $response = $this->get(route('admin.shipping-settings.index'));
        $response->assertRedirect(route('admin.login'));
    }

    public function test_admin_can_view_shipping_settings_page(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.shipping-settings.index'));

        $response->assertStatus(200);
        $response->assertSee('Pengaturan Ekspedisi SPX Express');
        $response->assertSee('KAB. TANGERANG');
        $response->assertSee('Semua Aktif (Hemat &amp; Reguler)', false);
    }

    public function test_admin_can_set_mode_to_regular_only(): void
    {
        $response = $this->actingAs($this->admin)->put(route('admin.shipping-settings.update'), [
            'spx_active_services' => 'SPX_REGULAR',
        ]);

        $response->assertRedirect(route('admin.shipping-settings.index'));
        $response->assertSessionHas('success');

        $this->assertEquals('SPX_REGULAR', Setting::get('spx_active_services'));

        // Cek API calculate: hanya SPX_REGULAR yang dikembalikan
        $calcResponse = $this->postJson('/api/shipping/calculate', [
            'destination_city' => 'KOTA ADM. JAKARTA SELATAN',
            'destination_district' => 'CILANDAK',
            'weight_grams' => 1000,
        ]);

        $calcResponse->assertStatus(200);
        $services = $calcResponse->json('data.services');
        $this->assertCount(1, $services);
        $this->assertEquals('SPX_REGULAR', $services[0]['service_code']);
    }

    public function test_admin_can_set_mode_to_hemat_only(): void
    {
        $response = $this->actingAs($this->admin)->put(route('admin.shipping-settings.update'), [
            'spx_active_services' => 'SPX_HEMAT',
        ]);

        $response->assertRedirect(route('admin.shipping-settings.index'));
        $this->assertEquals('SPX_HEMAT', Setting::get('spx_active_services'));

        // Cek API calculate: hanya SPX_HEMAT yang dikembalikan
        $calcResponse = $this->postJson('/api/shipping/calculate', [
            'destination_city' => 'KOTA ADM. JAKARTA SELATAN',
            'destination_district' => 'CILANDAK',
            'weight_grams' => 1000,
        ]);

        $calcResponse->assertStatus(200);
        $services = $calcResponse->json('data.services');
        $this->assertCount(1, $services);
        $this->assertEquals('SPX_HEMAT', $services[0]['service_code']);
    }

    public function test_admin_can_set_mode_to_all_services(): void
    {
        $response = $this->actingAs($this->admin)->put(route('admin.shipping-settings.update'), [
            'spx_active_services' => 'ALL',
        ]);

        $response->assertRedirect(route('admin.shipping-settings.index'));
        $this->assertEquals('ALL', Setting::get('spx_active_services'));

        // Cek API calculate: kedua layanan dikembalikan
        $calcResponse = $this->postJson('/api/shipping/calculate', [
            'destination_city' => 'KOTA ADM. JAKARTA SELATAN',
            'destination_district' => 'CILANDAK',
            'weight_grams' => 1000,
        ]);

        $calcResponse->assertStatus(200);
        $services = $calcResponse->json('data.services');
        $this->assertCount(2, $services);
        $this->assertEquals('SPX_HEMAT', $services[0]['service_code']);
        $this->assertEquals('SPX_REGULAR', $services[1]['service_code']);
    }

    public function test_invalid_service_mode_is_rejected(): void
    {
        $response = $this->actingAs($this->admin)->put(route('admin.shipping-settings.update'), [
            'spx_active_services' => 'INVALID_SERVICE',
        ]);

        $response->assertSessionHasErrors(['spx_active_services']);
    }
}
