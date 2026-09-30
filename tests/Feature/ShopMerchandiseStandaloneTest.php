<?php

namespace Tests\Feature;

use App\Models\AddOn;
use App\Models\AddOnVariant;
use App\Models\Event;
use App\Models\Registration;
use App\Models\SpxShippingRate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ShopMerchandiseStandaloneTest extends TestCase
{
    use RefreshDatabase;

    protected Event $event;

    protected AddOn $addonJersey;

    protected AddOn $addonHat;

    protected AddOnVariant $variantM;

    protected function setUp(): void
    {
        parent::setUp();

        $this->event = Event::create([
            'title' => 'Jakarta Night Run 2026',
            'slug' => 'jakarta-night-run-2026',
            'event_code' => 'JNR26',
            'activity_type' => 'RUN',
            'submission_mode' => 'SINGLE',
            'race_type' => 'RACE',
            'registration_start' => now()->subDays(2),
            'registration_end' => now()->addDays(15),
            'race_start' => now()->addDays(20),
            'race_end' => now()->addDays(25),
            'is_active' => true,
        ]);

        // 1. Jersey with variants
        $this->addonJersey = AddOn::create([
            'event_id' => $this->event->id,
            'name' => 'Jersey Finisher JNR26 Dry-Fit',
            'slug' => 'jersey-finisher-jnr26-dry-fit',
            'price' => 125000,
            'weight_grams' => 180,
            'stock' => 100,
            'has_variants' => true,
            'is_active' => true,
        ]);

        $this->variantM = AddOnVariant::create([
            'add_on_id' => $this->addonJersey->id,
            'variant_name' => 'Ukuran M',
            'additional_price' => 0,
            'stock' => 30,
        ]);

        // 2. Global Topi Lari
        $this->addonHat = AddOn::create([
            'event_id' => null, // Global
            'name' => 'Topi Lari Breathable VIRA Volt',
            'slug' => 'topi-lari-breathable-vira-volt',
            'price' => 65000,
            'weight_grams' => 80,
            'stock' => 50,
            'has_variants' => false,
            'is_active' => true,
        ]);

        // SPX Rate Seed
        SpxShippingRate::create([
            'origin_city' => 'KAB. TANGERANG',
            'destination_city' => 'KOTA JAKARTA SELATAN',
            'destination_district' => 'KEBAYORAN BARU',
            'rate_regular' => 12000,
            'sla_regular_days' => 2,
            'rate_hemat' => 10000,
            'sla_hemat_days' => 3,
        ]);

        // Mock Tripay API
        Http::fake([
            '*/merchant/payment-channel' => Http::response([
                'success' => true,
                'data' => [
                    ['code' => 'QRIS2', 'name' => 'QRIS', 'group' => 'E-Wallet', 'active' => true],
                    ['code' => 'BCAVA', 'name' => 'BCA Virtual Account', 'group' => 'Virtual Account', 'active' => true],
                ],
            ], 200),
            '*/transaction/create' => Http::response([
                'success' => true,
                'data' => [
                    'reference' => 'DEV-TRIPAY-SHOP-001',
                    'merchant_ref' => 'VIRA-SHOP-TEST-001',
                    'payment_method' => 'QRIS2',
                    'checkout_url' => 'https://tripay.co.id/checkout/DEV-TRIPAY-SHOP-001',
                    'qr_url' => 'https://tripay.co.id/qr/DEV-TRIPAY-SHOP-001.png',
                    'pay_code' => 'QRIS_CODE_DATA',
                    'status' => 'UNPAID',
                ],
            ], 200),
        ]);
    }

    public function test_user_can_view_shop_merchandise_catalog(): void
    {
        $response = $this->get(route('shop.index'));

        $response->assertStatus(200);
        $response->assertSee('VIRA Official Store');
        $response->assertSee('Katalog Merchandise &amp; Official Gear', false);
        $response->assertSee('Jersey Finisher JNR26 Dry-Fit');
        $response->assertSee('Topi Lari Breathable VIRA Volt');
        $response->assertSee('Rp 125.000');
        $response->assertSee('Beli Sekarang');
        $response->assertSee('<select name="payment_channel"', false);
        $response->assertSee('BCA Virtual Account');
        $response->assertSee('QRIS');
        $response->assertDontSee('payment-icon');
    }

    public function test_user_can_filter_shop_merchandise(): void
    {
        // Filter: global only
        $resGlobal = $this->get(route('shop.index', ['event_id' => 'global']));
        $resGlobal->assertStatus(200);
        $resGlobal->assertSee('Topi Lari Breathable VIRA Volt');
        $resGlobal->assertDontSee('Jersey Finisher JNR26 Dry-Fit');

        // Search keyword
        $resSearch = $this->get(route('shop.index', ['search' => 'Topi']));
        $resSearch->assertStatus(200);
        $resSearch->assertSee('Topi Lari Breathable VIRA Volt');
        $resSearch->assertDontSee('Jersey Finisher JNR26 Dry-Fit');
    }

    public function test_user_can_buy_merchandise_standalone_without_event(): void
    {
        $payload = [
            'full_name' => 'Rina Shopaholic',
            'email' => 'rina.shop@example.com',
            'phone_number' => '081298765432',
            'destination_city' => 'KOTA JAKARTA SELATAN',
            'destination_district' => 'KEBAYORAN BARU',
            'address_detail' => 'Apartemen Senopati Tower 2 No. 8B',
            'postal_code' => '12190',
            'spx_service' => 'SPX_REGULAR',
            'payment_channel' => 'QRIS2',
            'items' => [
                [
                    'id' => $this->addonJersey->id,
                    'variant_id' => $this->variantM->id,
                    'quantity' => 2,
                ],
                [
                    'id' => $this->addonHat->id,
                    'variant_id' => null,
                    'quantity' => 1,
                ],
            ],
        ];

        $response = $this->post(route('shop.order.store'), $payload);

        $response->assertRedirect();
        $this->assertDatabaseHas('participants', [
            'email' => 'rina.shop@example.com',
            'full_name' => 'Rina Shopaholic',
        ]);

        // Assert Registration created as standalone shop order (null event, null category, null bib)
        $this->assertDatabaseHas('registrations', [
            'event_id' => null,
            'category_id' => null,
            'package_id' => null,
            'bib_number' => null,
            'payment_status' => 'UNPAID',
        ]);

        $order = Registration::whereNull('event_id')->first();
        $this->assertNotNull($order);
        $this->assertCount(2, $order->registrationAddOns);

        // Check stock decrements:
        // Jersey variant M: 30 - 2 = 28
        // Hat: 50 - 1 = 49
        $this->variantM->refresh();
        $this->addonHat->refresh();
        $this->assertEquals(28, $this->variantM->stock);
        $this->assertEquals(49, $this->addonHat->stock);

        // Subtotal items: (125.000 * 2) + (65.000 * 1) = 250.000 + 65.000 = 315.000
        // Weight: (180g * 2) + (80g * 1) = 440g (<= 1kg, base rate 12.000)
        // Grand Total: 315.000 + 12.000 = 327.000
        $this->assertDatabaseHas('payments', [
            'registration_id' => $order->id,
            'amount' => 315000.00,
            'shipping_cost' => 12000.00,
            'total_amount' => 327000.00,
            'status' => 'UNPAID',
        ]);

        $this->assertDatabaseHas('shipping_addresses', [
            'registration_id' => $order->id,
            'recipient_name' => 'Rina Shopaholic',
            'city' => 'KOTA JAKARTA SELATAN',
            'district' => 'KEBAYORAN BARU',
            'total_weight_grams' => 440,
            'shipping_cost' => 12000.00,
        ]);

        // Check that payment page loads cleanly for standalone merchandise order
        $payment = $order->payment;
        $payResponse = $this->get(route('payment.show', ['merchant_ref' => $payment->merchant_ref]));
        $payResponse->assertStatus(200);
        $payResponse->assertSee('Pembelian Merchandise VIRA');
        $payResponse->assertSee('Rina Shopaholic');
        $payResponse->assertSee('Rp 327.000');
    }

    public function test_user_cannot_buy_more_than_available_stock(): void
    {
        $payload = [
            'full_name' => 'Budi Pemborong',
            'email' => 'budi.borong@example.com',
            'phone_number' => '081211112222',
            'destination_city' => 'KOTA JAKARTA SELATAN',
            'destination_district' => 'KEBAYORAN BARU',
            'address_detail' => 'Jl. Jenderal Sudirman No. 1',
            'items' => [
                [
                    'id' => $this->addonHat->id, // Stock is 50
                    'variant_id' => null,
                    'quantity' => 999, // Exceeds 50
                ],
            ],
        ];

        $response = $this->from(route('shop.index'))->post(route('shop.order.store'), $payload);

        $response->assertRedirect(route('shop.index'));
        $response->assertSessionHas('error');

        // Stock should remain unchanged
        $this->addonHat->refresh();
        $this->assertEquals(50, $this->addonHat->stock);
    }

    public function test_simulate_pay_works_for_standalone_merchandise_order_via_get_and_post(): void
    {
        $payload = [
            'full_name' => 'Doni Merchandise Buyer',
            'email' => 'doni.merch@example.com',
            'phone_number' => '081299887766',
            'destination_city' => 'KOTA JAKARTA SELATAN',
            'destination_district' => 'KEBAYORAN BARU',
            'address_detail' => 'Jl. Senopati No. 45',
            'spx_service' => 'SPX_REGULAR',
            'items' => [
                [
                    'id' => $this->addonHat->id,
                    'variant_id' => null,
                    'quantity' => 1,
                ],
            ],
        ];

        $this->post(route('shop.order.store'), $payload);

        $order = Registration::where('event_id', null)->latest()->first();
        $this->assertNotNull($order);
        $payment = $order->payment;
        $this->assertNotNull($payment);

        // Test GET simulate-pay
        $simResponse = $this->get('/payment/'.$payment->merchant_ref.'/simulate-pay');
        $simResponse->assertRedirect(route('payment.show', ['merchant_ref' => $payment->merchant_ref]));
        $simResponse->assertSessionHas('success');

        // Confirm database status
        $payment->refresh();
        $order->refresh();
        $this->assertEquals('PAID', $payment->status);
        $this->assertEquals('PAID', $order->payment_status);
        $this->assertNull($order->bib_number);

        // Confirm payment page renders without error for paid merchandise order
        $viewResponse = $this->get(route('payment.show', ['merchant_ref' => $payment->merchant_ref]));
        $viewResponse->assertStatus(200);
        $viewResponse->assertSee('Pesanan Merchandise Anda Sedang Diproses!');
        $viewResponse->assertSee('PEMBAYARAN LUNAS (PAID)');
    }
}
