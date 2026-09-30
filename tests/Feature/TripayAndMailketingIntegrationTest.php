<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Event;
use App\Models\Package;
use App\Models\Participant;
use App\Models\Payment;
use App\Models\Registration;
use App\Services\MailketingService;
use App\Services\TripayService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class TripayAndMailketingIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected Event $event;

    protected Category $category;

    protected Package $package;

    protected Participant $participant;

    protected Registration $registration;

    protected Payment $payment;

    protected function setUp(): void
    {
        parent::setUp();

        $this->event = Event::create([
            'title' => 'Jakarta Virtual Run 2026',
            'slug' => 'jakarta-virtual-run-2026',
            'event_code' => 'JVR26',
            'activity_type' => 'RUN',
            'submission_mode' => 'CUMULATIVE',
            'race_type' => 'CHALLENGE',
            'description' => 'Event lari Jakarta.',
            'registration_start' => now()->subDay(),
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
            'name' => 'Virtual Only',
            'price' => 50000.00,
            'base_weight_grams' => 0,
            'requires_shipping' => false,
        ]);

        $this->participant = Participant::create([
            'full_name' => 'Aditya Pratama',
            'email' => 'aditya@example.com',
            'phone_number' => '081234567890',
            'gender' => 'MALE',
            'date_of_birth' => '1996-05-12',
        ]);

        $this->registration = Registration::create([
            'access_token' => 'test-token-12345',
            'event_id' => $this->event->id,
            'category_id' => $this->category->id,
            'package_id' => $this->package->id,
            'participant_id' => $this->participant->id,
            'payment_status' => 'UNPAID',
            'total_distance_km' => 0.00,
            'total_duration_seconds' => 0,
            'last_milestone_notified' => 0,
            'finisher_status' => 'IN_PROGRESS',
        ]);

        $this->payment = Payment::create([
            'registration_id' => $this->registration->id,
            'merchant_ref' => 'VIRA-TEST-REF-99',
            'payment_method' => 'TRIPAY_QRIS',
            'amount' => 50000.00,
            'admin_fee' => 0.00,
            'shipping_cost' => 0.00,
            'total_amount' => 50000.00,
            'status' => 'UNPAID',
            'expired_at' => now()->addHours(24),
        ]);
    }

    public function test_tripay_service_creates_closed_transaction(): void
    {
        Http::fake([
            '*/transaction/create' => Http::response([
                'success' => true,
                'data' => [
                    'reference' => 'DEV-T3943099999',
                    'merchant_ref' => 'VIRA-TEST-REF-99',
                    'amount' => 50000,
                    'total_fee' => 750,
                    'checkout_url' => 'https://tripay.co.id/checkout/DEV-T3943099999',
                    'qr_url' => 'https://tripay.co.id/qr/DEV-T3943099999',
                    'pay_code' => null,
                    'status' => 'UNPAID',
                    'expired_time' => now()->addHours(24)->timestamp,
                ],
            ], 200),
        ]);

        $tripayService = app(TripayService::class);
        $data = $tripayService->createClosedTransaction($this->registration, 'QRIS2');

        $this->assertEquals('DEV-T3943099999', $data['reference']);

        $this->payment->refresh();
        $this->assertEquals('DEV-T3943099999', $this->payment->tripay_reference);
        $this->assertEquals('https://tripay.co.id/checkout/DEV-T3943099999', $this->payment->checkout_url);
        $this->assertEquals('https://tripay.co.id/qr/DEV-T3943099999', $this->payment->qr_code_url);
    }

    public function test_tripay_webhook_rejects_invalid_signature(): void
    {
        $payload = json_encode([
            'merchant_ref' => $this->payment->merchant_ref,
            'status' => 'PAID',
        ]);

        $response = $this->call(
            'POST',
            '/api/tripay/callback',
            [],
            [],
            [],
            [
                'HTTP_X_CALLBACK_EVENT' => 'payment_status',
                'HTTP_X_CALLBACK_SIGNATURE' => 'invalid-signature-hash',
                'CONTENT_TYPE' => 'application/json',
            ],
            $payload
        );

        $response->assertStatus(403);
        $response->assertJson(['success' => false, 'message' => 'Invalid signature']);
    }

    public function test_tripay_webhook_processes_paid_status_and_assigns_bib(): void
    {
        Http::fake([
            'https://api.mailketing.co.id/*' => Http::response([
                'status' => 'success',
                'response' => 'Mail Sent',
            ], 200),
        ]);

        $payloadArray = [
            'reference' => 'DEV-T3943099999',
            'merchant_ref' => $this->payment->merchant_ref,
            'payment_method' => 'QRIS2',
            'total_amount' => 50750,
            'status' => 'PAID',
            'paid_at' => now()->timestamp,
        ];

        $rawPayload = json_encode($payloadArray);
        $privateKey = config('tripay.private_key');
        $validSignature = hash_hmac('sha256', $rawPayload, $privateKey);

        $response = $this->call(
            'POST',
            '/api/tripay/callback',
            [],
            [],
            [],
            [
                'HTTP_X_CALLBACK_EVENT' => 'payment_status',
                'HTTP_X_CALLBACK_SIGNATURE' => $validSignature,
                'CONTENT_TYPE' => 'application/json',
            ],
            $rawPayload
        );

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        // Verifikasi payment status berubah jadi PAID
        $this->payment->refresh();
        $this->assertEquals('PAID', $this->payment->status);
        $this->assertNotNull($this->payment->paid_at);

        // Verifikasi e-BIB otomatis terbit
        $this->registration->refresh();
        $this->assertEquals('PAID', $this->registration->payment_status);
        $this->assertEquals('1001', $this->registration->bib_number);

        // Verifikasi Mailketing dipanggil untuk mengirim e-BIB
        Http::assertSent(function (Request $request) {
            return str_contains($request->url(), 'mailketing.co.id') &&
                $request['recipient'] === 'aditya@example.com' &&
                str_contains($request['subject'], '1001');
        });
    }

    public function test_mailketing_service_sends_invoice_email(): void
    {
        Http::fake([
            'https://api.mailketing.co.id/*' => Http::response([
                'status' => 'success',
                'response' => 'Mail Sent',
            ], 200),
        ]);

        $mailketing = app(MailketingService::class);
        $res = $mailketing->sendInvoiceEmail($this->registration);

        $this->assertEquals('success', $res['status']);

        Http::assertSent(function (Request $request) {
            return str_contains($request->url(), 'mailketing.co.id') &&
                $request['from_email'] === 'hi@jelatix.com' &&
                $request['recipient'] === 'aditya@example.com' &&
                str_contains($request['subject'], 'Menunggu Pembayaran');
        });
    }

    public function test_progressive_5_stage_motivation_emails_via_submission(): void
    {
        Http::fake([
            'https://api.mailketing.co.id/*' => Http::response([
                'status' => 'success',
                'response' => 'Mail Sent',
            ], 200),
        ]);

        // Set registration sudah lunas dan punya e-BIB
        $this->registration->update([
            'payment_status' => 'PAID',
            'bib_number' => 'JVR26-10K-0001',
        ]);

        // 1. Submit Lari 2.0 KM (20% Target dari 10.0 KM)
        $this->post('/submit/record', [
            'registration_id' => $this->registration->id,
            'activity_date' => now()->toDateString(),
            'distance_km' => 2.0,
            'duration_hours' => 0,
            'duration_minutes' => 12,
            'duration_seconds' => 30,
            'proof_url' => 'https://strava.com/activities/10001',
        ])->assertStatus(302);

        $this->registration->refresh();
        $this->assertEquals(2.0, (float) $this->registration->total_distance_km);
        $this->assertEquals(20, $this->registration->last_milestone_notified);

        // Verifikasi email milestone 20% dikirim
        Http::assertSent(function (Request $request) {
            return str_contains($request->url(), 'mailketing.co.id') &&
                str_contains($request['subject'], '20%');
        });

        // 2. Submit Tambahan 4.0 KM (Total 6.0 KM = 60% Target, melompati 40% & 60%)
        $this->post('/submit/record', [
            'registration_id' => $this->registration->id,
            'activity_date' => now()->toDateString(),
            'distance_km' => 4.0,
            'duration_hours' => 0,
            'duration_minutes' => 25,
            'duration_seconds' => 0,
            'proof_url' => 'https://strava.com/activities/10002',
        ])->assertStatus(302);

        $this->registration->refresh();
        $this->assertEquals(6.0, (float) $this->registration->total_distance_km);
        $this->assertEquals(60, $this->registration->last_milestone_notified);

        // 3. Submit Tambahan 4.0 KM (Total 10.0 KM = 100% FINISHER!)
        $this->post('/submit/record', [
            'registration_id' => $this->registration->id,
            'activity_date' => now()->toDateString(),
            'distance_km' => 4.0,
            'duration_hours' => 0,
            'duration_minutes' => 24,
            'duration_seconds' => 10,
            'proof_url' => 'https://strava.com/activities/10003',
        ])->assertStatus(302);

        $this->registration->refresh();
        $this->assertEquals(10.0, (float) $this->registration->total_distance_km);
        $this->assertEquals(100, $this->registration->last_milestone_notified);
        $this->assertEquals('FINISHED', $this->registration->finisher_status);
        $this->assertNotNull($this->registration->finished_at);

        // Verifikasi email Finisher 100% dikirim
        Http::assertSent(function (Request $request) {
            return str_contains($request->url(), 'mailketing.co.id') &&
                str_contains($request['subject'], 'FINISHER') &&
                str_contains($request['content'], 'Unduh E-Sertifikat Finisher');
        });
    }

    public function test_simulation_button_hidden_and_endpoint_forbidden_in_production_mode(): void
    {
        // 1. Pada mode sandbox, tombol simulasi tampil dan dapat diklik
        config(['tripay.sandbox' => true]);
        $responseSandbox = $this->get(route('payment.show', $this->payment->merchant_ref));
        $responseSandbox->assertStatus(200);
        $responseSandbox->assertSee('Simulasikan Pembayaran Sukses (Mode Sandbox)');

        // 2. Pada mode produksi, tombol simulasi tersembunyi total dari halaman tagihan
        config(['tripay.sandbox' => false]);
        $responseProd = $this->get(route('payment.show', $this->payment->merchant_ref));
        $responseProd->assertStatus(200);
        $responseProd->assertDontSee('Simulasikan Pembayaran Sukses (Mode Sandbox)');

        // 3. Pada mode produksi, percobaan akses rute simulasi langsung ditolak (403 Forbidden)
        $postRes = $this->post(route('payment.simulate', $this->payment->merchant_ref));
        $postRes->assertStatus(403);
    }
}
