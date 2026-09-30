<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Event;
use App\Models\Package;
use App\Models\Participant;
use App\Models\Payment;
use App\Models\Registration;
use App\Models\User;
use App\Services\TripayService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use Tests\TestCase;

class SecurityHardeningTest extends TestCase
{
    use RefreshDatabase;

    protected Event $event;

    protected Category $category;

    protected Package $package;

    protected function setUp(): void
    {
        parent::setUp();

        $this->event = Event::create([
            'title' => 'Security Audit Marathon',
            'slug' => 'security-audit-marathon-'.Str::random(5),
            'event_code' => 'SAM'.rand(10, 99),
            'activity_type' => 'RUN',
            'submission_mode' => 'CUMULATIVE',
            'race_type' => 'CHALLENGE',
            'registration_start' => now()->subDays(2),
            'registration_end' => now()->addDays(10),
            'race_start' => now()->subDay(),
            'race_end' => now()->addDays(15),
            'is_active' => true,
        ]);

        $this->category = Category::create([
            'event_id' => $this->event->id,
            'name' => '10K Category',
            'target_distance_km' => 10.0,
            'bib_prefix' => '10K',
            'last_bib_sequence' => 0,
        ]);

        $this->package = Package::create([
            'event_id' => $this->event->id,
            'name' => 'Regular Package',
            'price' => 150000,
            'base_weight_grams' => 300,
            'requires_shipping' => false,
        ]);
    }

    public function test_unpaid_registration_cannot_record_activity_submission(): void
    {
        $participant = Participant::create([
            'full_name' => 'Unpaid Runner',
            'email' => 'unpaid@example.com',
            'phone_number' => '081234567890',
        ]);

        $registration = Registration::create([
            'access_token' => (string) Str::uuid(),
            'event_id' => $this->event->id,
            'category_id' => $this->category->id,
            'package_id' => $this->package->id,
            'participant_id' => $participant->id,
            'payment_status' => 'UNPAID',
            'total_distance_km' => 0,
            'total_duration_seconds' => 0,
            'last_milestone_notified' => 0,
            'finisher_status' => 'IN_PROGRESS',
        ]);

        $response = $this->post(route('submit.record'), [
            'registration_id' => $registration->id,
            'activity_date' => now()->format('Y-m-d'),
            'distance_km' => 5.0,
            'duration_hours' => 0,
            'duration_minutes' => 30,
            'duration_seconds' => 0,
            'proof_url' => 'https://strava.com/activities/123456',
        ]);

        $response->assertRedirect(route('submit.index'));
        $response->assertSessionHas('error');

        $registration->refresh();
        $this->assertEquals(0, (float) $registration->total_distance_km);
    }

    public function test_cannot_record_activity_before_race_start(): void
    {
        $futureEvent = Event::create([
            'title' => 'Future Event',
            'slug' => 'future-event-'.Str::random(5),
            'event_code' => 'FUT'.rand(10, 99),
            'activity_type' => 'RUN',
            'submission_mode' => 'CUMULATIVE',
            'race_type' => 'CHALLENGE',
            'registration_start' => now()->subDays(5),
            'registration_end' => now()->addDays(5),
            'race_start' => now()->addDays(3),
            'race_end' => now()->addDays(20),
            'is_active' => true,
        ]);

        $participant = Participant::create([
            'full_name' => 'Early Runner',
            'email' => 'early@example.com',
            'phone_number' => '081234567891',
        ]);

        $registration = Registration::create([
            'access_token' => (string) Str::uuid(),
            'event_id' => $futureEvent->id,
            'category_id' => $this->category->id,
            'package_id' => $this->package->id,
            'participant_id' => $participant->id,
            'payment_status' => 'PAID',
            'bib_number' => '9999',
            'total_distance_km' => 0,
            'total_duration_seconds' => 0,
            'last_milestone_notified' => 0,
            'finisher_status' => 'IN_PROGRESS',
        ]);

        $response = $this->post(route('submit.record'), [
            'registration_id' => $registration->id,
            'activity_date' => now()->format('Y-m-d'),
            'distance_km' => 5.0,
            'duration_hours' => 0,
            'duration_minutes' => 30,
            'duration_seconds' => 0,
            'proof_url' => 'https://strava.com/activities/999',
        ]);

        $response->assertSessionHas('error');
        $this->assertEquals(0, (float) $registration->fresh()->total_distance_km);
    }

    public function test_guest_cannot_register_if_registration_is_closed(): void
    {
        $closedEvent = Event::create([
            'title' => 'Expired Registration Event',
            'slug' => 'expired-reg-event-'.Str::random(5),
            'event_code' => 'EXP'.rand(10, 99),
            'activity_type' => 'RUN',
            'submission_mode' => 'CUMULATIVE',
            'race_type' => 'CHALLENGE',
            'registration_start' => now()->subDays(30),
            'registration_end' => now()->subDays(1),
            'race_start' => now(),
            'race_end' => now()->addDays(30),
            'is_active' => true,
        ]);

        $cat = Category::create([
            'event_id' => $closedEvent->id,
            'name' => '5K',
            'target_distance_km' => 5.0,
            'bib_prefix' => '5K',
            'last_bib_sequence' => 0,
        ]);

        $pkg = Package::create([
            'event_id' => $closedEvent->id,
            'name' => 'Basic',
            'price' => 100000,
            'base_weight_grams' => 0,
            'requires_shipping' => false,
        ]);

        // Attempt GET register page
        $getResponse = $this->get(route('events.register', $closedEvent->slug));
        $getResponse->assertRedirect(route('events.show', $closedEvent->slug));
        $getResponse->assertSessionHas('error');

        // Attempt POST register
        $postResponse = $this->post(route('events.register.store', $closedEvent->slug), [
            'full_name' => 'Late Runner',
            'email' => 'late@example.com',
            'phone_number' => '081234567899',
            'category_id' => $cat->id,
            'package_id' => $pkg->id,
        ]);

        $postResponse->assertSessionHas('error');
        $this->assertDatabaseMissing('participants', ['email' => 'late@example.com']);
    }

    public function test_user_is_admin_method_works_without_error(): void
    {
        $superAdmin = User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@example.com',
            'password' => 'secret123',
            'role' => 'SUPER_ADMIN',
        ]);

        $raceAdmin = User::create([
            'name' => 'Race Admin',
            'email' => 'raceadmin@example.com',
            'password' => 'secret123',
            'role' => 'RACE_ADMIN',
        ]);

        $this->assertTrue($superAdmin->isAdmin());
        $this->assertTrue($superAdmin->isRaceAdmin());
        $this->assertTrue($superAdmin->isSuperAdmin());

        $this->assertTrue($raceAdmin->isAdmin());
        $this->assertTrue($raceAdmin->isRaceAdmin());
        $this->assertFalse($raceAdmin->isSuperAdmin());
    }

    public function test_event_sanitized_description_strips_scripts_and_event_handlers(): void
    {
        $dirtyHtml = '<h2>Informasi Penting</h2><p>Selamat berlari!</p><script>alert("XSS")</script><img src="x" onerror="alert(1)"><a href="javascript:stealCookie()">Klik Disini</a>';

        $event = new Event([
            'description' => $dirtyHtml,
        ]);

        $sanitized = $event->sanitized_description;

        $this->assertStringNotContainsString('<script>', $sanitized);
        $this->assertStringNotContainsString('onerror', $sanitized);
        $this->assertStringNotContainsString('javascript:', $sanitized);
        $this->assertStringContainsString('<h2>Informasi Penting</h2>', $sanitized);
        $this->assertStringContainsString('<p>Selamat berlari!</p>', $sanitized);
    }

    public function test_simulate_pay_forbidden_in_production_mode_for_guests(): void
    {
        Config::set('tripay.sandbox', false);

        $participant = Participant::create([
            'full_name' => 'Prod Guest',
            'email' => 'guest@example.com',
            'phone_number' => '081234567890',
        ]);

        $registration = Registration::create([
            'access_token' => (string) Str::uuid(),
            'event_id' => $this->event->id,
            'category_id' => $this->category->id,
            'package_id' => $this->package->id,
            'participant_id' => $participant->id,
            'payment_status' => 'UNPAID',
            'total_distance_km' => 0,
            'total_duration_seconds' => 0,
            'last_milestone_notified' => 0,
            'finisher_status' => 'IN_PROGRESS',
        ]);

        $payment = Payment::create([
            'registration_id' => $registration->id,
            'merchant_ref' => 'TEST-PROD-REF-'.Str::random(5),
            'payment_method' => 'QRIS2',
            'amount' => 150000,
            'admin_fee' => 0,
            'shipping_cost' => 0,
            'total_amount' => 150000,
            'status' => 'UNPAID',
        ]);

        $response = $this->post(route('payment.simulate', $payment->merchant_ref));
        $response->assertStatus(403);
    }

    public function test_tripay_webhook_rejects_empty_private_key(): void
    {
        Config::set('tripay.private_key', '');

        $tripayService = new TripayService;
        $isValid = $tripayService->verifyWebhookSignature('payload-test', 'some-signature');

        $this->assertFalse($isValid);
    }
}
