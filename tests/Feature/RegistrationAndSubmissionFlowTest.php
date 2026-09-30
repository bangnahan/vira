<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Event;
use App\Models\Package;
use App\Models\Participant;
use App\Models\Registration;
use App\Models\SpxShippingRate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class RegistrationAndSubmissionFlowTest extends TestCase
{
    use RefreshDatabase;

    protected Event $event;

    protected Category $category;

    protected Package $package;

    protected function setUp(): void
    {
        parent::setUp();

        Http::fake([
            '*/merchant/payment-channel' => Http::response([
                'success' => true,
                'data' => [
                    ['code' => 'QRIS2', 'name' => 'QRIS', 'group' => 'E-Wallet', 'active' => true],
                ],
            ], 200),
            '*/transaction/create' => Http::response([
                'success' => true,
                'data' => [
                    'reference' => 'DEV-T3943012345',
                    'merchant_ref' => 'VIRA-MOCK',
                    'amount' => 180000,
                    'total_fee' => 0,
                    'checkout_url' => 'https://tripay.co.id/checkout/DEV-T3943012345',
                    'qr_url' => 'https://tripay.co.id/qr/DEV-T3943012345',
                    'pay_code' => null,
                    'status' => 'UNPAID',
                ],
            ], 200),
            'https://api.mailketing.co.id/*' => Http::response([
                'status' => 'success',
                'response' => 'Mail Sent',
            ], 200),
        ]);

        // 1. Buat Data Event

        $this->event = Event::create([
            'title' => 'Merdeka Virtual Run 2026',
            'slug' => 'merdeka-virtual-run-2026',
            'event_code' => 'MVR26',
            'activity_type' => 'RUN',
            'submission_mode' => 'CUMULATIVE',
            'race_type' => 'CHALLENGE',
            'description' => 'Event lari kemerdekaan.',
            'rules_and_terms' => 'Aturan lari.',
            'registration_start' => now()->subDays(1),
            'registration_end' => now()->addDays(30),
            'race_start' => now(),
            'race_end' => now()->addDays(30),
            'is_active' => true,
        ]);

        $this->category = Category::create([
            'event_id' => $this->event->id,
            'name' => '10K Challenge',
            'target_distance_km' => 10.00,
            'bib_prefix' => '10K',
            'last_bib_sequence' => 0,
        ]);

        $this->package = Package::create([
            'event_id' => $this->event->id,
            'name' => 'Race Pack + Medal',
            'price' => 150000.00,
            'base_weight_grams' => 300,
            'requires_shipping' => true,
        ]);

        // 2. Data Tarif SPX
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

    public function test_homepage_loads_successfully(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200)
            ->assertSee('Merdeka Virtual Run 2026')
            ->assertSee('VIRA');
    }

    public function test_event_show_page_loads(): void
    {
        $response = $this->get('/event/'.$this->event->slug);
        $response->assertStatus(200)
            ->assertSee($this->event->title)
            ->assertSee('10K Challenge');
    }

    public function test_registration_form_loads(): void
    {
        $response = $this->get('/event/'.$this->event->slug.'/register');
        $response->assertStatus(200)
            ->assertSee('Form Pendaftaran Guest')
            ->assertSee('citySearchInput')
            ->assertSee('Metode Pembayaran (Tripay)')
            ->assertSee('<select name="payment_channel"', false)
            ->assertDontSee('<img src="https://assets.tripay.co.id', false)
            ->assertSee('QRIS');
    }

    public function test_guest_can_register_and_simulate_payment(): void
    {
        // 1. Submit Registration Form
        $payload = [
            'full_name' => 'John Doe',
            'email' => 'johndoe@example.com',
            'phone_number' => '08123456789',
            'gender' => 'MALE',
            'date_of_birth' => '1995-05-15',
            'category_id' => $this->category->id,
            'package_id' => $this->package->id,
            'recipient_name' => 'John Doe',
            'recipient_phone' => '08123456789',
            'destination_city' => 'KAB. BADUNG',
            'destination_district' => 'KUTA',
            'address_detail' => 'Jl. Sunset Road No. 88',
            'postal_code' => '80361',
            'spx_service' => 'SPX_REGULAR',
        ];

        $postRes = $this->post('/event/'.$this->event->slug.'/register', $payload);
        $postRes->assertStatus(302);
        $postRes->assertRedirect('https://tripay.co.id/checkout/DEV-T3943012345');

        $registration = Registration::where('event_id', $this->event->id)->first();

        $this->assertNotNull($registration);
        $this->assertEquals('UNPAID', $registration->payment_status);

        $payment = $registration->payment;
        $this->assertNotNull($payment);
        $this->assertEquals(30000.00, $payment->shipping_cost); // SPX Regular 1 kg
        $this->assertEquals(180000.00, $payment->total_amount); // 150.000 + 30.000

        // 2. Simulasikan Pembayaran Sukses
        $simRes = $this->post('/payment/'.$payment->merchant_ref.'/simulate-pay');
        $simRes->assertStatus(302);

        $registration->refresh();
        $this->assertEquals('PAID', $registration->payment_status);
        $this->assertEquals('1001', $registration->bib_number);

        // 3. Test Universal Submission Portal Lookup
        $lookupRes = $this->post('/submit/lookup', [
            'bib_number' => '1001',
        ]);
        $lookupRes->assertRedirect('/submit?bib=1001');

        $portalRes = $this->get('/submit?bib=1001');
        $portalRes->assertStatus(200)
            ->assertSee('John Doe')
            ->assertSee('1001');

        // 4. Record Activity 1 (5 KM) -> Menembus milestone 20% dan 40%
        $actRes = $this->post('/submit/record', [
            'registration_id' => $registration->id,
            'activity_date' => now()->format('Y-m-d'),
            'distance_km' => 5.00,
            'duration_hours' => 0,
            'duration_minutes' => 28,
            'duration_seconds' => 30,
            'proof_url' => 'https://www.strava.com/activities/99887766',
        ]);
        $actRes->assertRedirect('/submit?bib=1001');

        $registration->refresh();
        $this->assertEquals(5.00, (float) $registration->total_distance_km);
        $this->assertEquals(40, $registration->last_milestone_notified); // 50% target > 40% milestone
        $this->assertEquals('IN_PROGRESS', $registration->finisher_status);

        // 5. Record Activity 2 (5.5 KM) -> Mencapai 10.5 KM (>= 10 KM) -> FINISHER!
        $actRes2 = $this->post('/submit/record', [
            'registration_id' => $registration->id,
            'activity_date' => now()->format('Y-m-d'),
            'distance_km' => 5.50,
            'duration_hours' => 0,
            'duration_minutes' => 30,
            'duration_seconds' => 0,
            'proof_url' => 'https://www.strava.com/activities/99887767',
        ]);

        $registration->refresh();
        $this->assertEquals(10.50, (float) $registration->total_distance_km);
        $this->assertEquals(100, $registration->last_milestone_notified);
        $this->assertEquals('FINISHED', $registration->finisher_status);
        $this->assertNotNull($registration->finished_at);
    }

    public function test_guest_can_register_with_bcava_channel(): void
    {
        $payload = [
            'full_name' => 'Jane Doe',
            'email' => 'janedoe@example.com',
            'phone_number' => '081987654321',
            'gender' => 'FEMALE',
            'date_of_birth' => '1998-08-18',
            'category_id' => $this->category->id,
            'package_id' => $this->package->id,
            'recipient_name' => 'Jane Doe',
            'recipient_phone' => '081987654321',
            'destination_city' => 'KAB. BADUNG',
            'destination_district' => 'KUTA',
            'address_detail' => 'Jl. Legian No. 12',
            'postal_code' => '80361',
            'spx_service' => 'SPX_REGULAR',
            'payment_channel' => 'BCAVA',
        ];

        $postRes = $this->post('/event/'.$this->event->slug.'/register', $payload);
        $postRes->assertStatus(302);

        $registration = Registration::where('event_id', $this->event->id)->whereHas('participant', fn ($q) => $q->where('email', 'janedoe@example.com'))->first();
        $this->assertNotNull($registration);
        $this->assertEquals('BCAVA', $registration->payment->payment_method);
    }

    public function test_guest_can_register_with_zero_or_empty_addons_without_validation_errors(): void
    {
        $payload = [
            'full_name' => 'Michael Runner',
            'email' => 'michael@example.com',
            'phone_number' => '081211112222',
            'gender' => 'MALE',
            'date_of_birth' => '1992-04-10',
            'category_id' => $this->category->id,
            'package_id' => $this->package->id,
            'recipient_name' => 'Michael Runner',
            'recipient_phone' => '081211112222',
            'destination_city' => 'KAB. BADUNG',
            'destination_district' => 'KUTA',
            'address_detail' => 'Jl. Sunset No. 99',
            'postal_code' => '80361',
            'spx_service' => 'SPX_REGULAR',
            'payment_channel' => 'QRIS2',
            // Addons submitted with 0 or empty values as from standard browser forms
            'addons' => [
                ['id' => 9999, 'quantity' => 0],
                ['id' => 9999, 'quantity' => ''],
                ['id' => 9999, 'quantity' => '0'],
            ],
        ];

        $response = $this->post('/event/'.$this->event->slug.'/register', $payload);
        $response->assertStatus(302);
        $response->assertSessionHasNoErrors();

        $registration = Registration::where('event_id', $this->event->id)
            ->whereHas('participant', fn ($q) => $q->where('email', 'michael@example.com'))
            ->first();

        $this->assertNotNull($registration);
        $this->assertCount(0, $registration->registrationAddOns);
    }

    public function test_guest_can_register_without_gender_date_of_birth_and_blood_type(): void
    {
        $payload = [
            'full_name' => 'Ferry Virtual Runner',
            'email' => 'ferry@example.com',
            'phone_number' => '081299887766',
            // No gender, date_of_birth, or blood_type provided
            'category_id' => $this->category->id,
            'package_id' => $this->package->id,
            'recipient_name' => 'Ferry Virtual Runner',
            'recipient_phone' => '081299887766',
            'destination_city' => 'KAB. BADUNG',
            'destination_district' => 'KUTA',
            'address_detail' => 'Jl. Legian No. 123',
            'postal_code' => '80361',
            'spx_service' => 'SPX_REGULAR',
            'jersey_size' => 'XL',
            'payment_channel' => 'QRIS2',
        ];

        $response = $this->post('/event/'.$this->event->slug.'/register', $payload);
        $response->assertStatus(302);
        $response->assertSessionHasNoErrors();

        $participant = Participant::where('email', 'ferry@example.com')->first();
        $this->assertNotNull($participant);
        $this->assertNull($participant->gender);
        $this->assertNull($participant->date_of_birth);
        $this->assertNull($participant->blood_type);
    }
}
