<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Event;
use App\Models\Package;
use App\Models\Participant;
use App\Models\Payment;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminEventAndMetaCapiTest extends TestCase
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

        $admin = User::create([
            'name' => 'Admin Test',
            'email' => 'admintest@vira.id',
            'password' => Hash::make('password'),
            'role' => 'SUPER_ADMIN',
        ]);
        $this->actingAs($admin);

        $this->event = Event::create([
            'title' => 'Bali Sunset Marathon 2026',
            'slug' => 'bali-sunset-marathon-2026',
            'event_code' => 'BSM26',
            'activity_type' => 'RUN',
            'submission_mode' => 'CUMULATIVE',
            'race_type' => 'CHALLENGE',
            'description' => 'Maraton pesisir pantai Bali.',
            'registration_start' => now()->subDay(),
            'registration_end' => now()->addDays(30),
            'race_start' => now(),
            'race_end' => now()->addDays(30),
            'meta_pixel_id' => '987654321012345',
            'meta_capi_token' => 'EAAG_TEST_TOKEN_XYZ',
            'meta_test_code' => 'TEST12345',
            'is_meta_capi_enabled' => true,
            'is_active' => true,
        ]);

        $this->category = Category::create([
            'event_id' => $this->event->id,
            'name' => 'Half Marathon 21K',
            'target_distance_km' => 21.00,
            'bib_prefix' => '21K',
            'last_bib_sequence' => 0,
        ]);

        $this->package = Package::create([
            'event_id' => $this->event->id,
            'name' => 'Jersey + Finisher Medal',
            'price' => 250000.00,
            'base_weight_grams' => 400,
            'requires_shipping' => true,
        ]);

        $this->participant = Participant::create([
            'full_name' => 'Rina Wijaya',
            'email' => 'rina.wijaya@example.com',
            'phone_number' => '081298765432',
            'gender' => 'FEMALE',
            'date_of_birth' => '1998-03-21',
        ]);

        $this->registration = Registration::create([
            'access_token' => 'rina-token-uuid-1',
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
            'merchant_ref' => 'VIRA-BSM-001',
            'payment_method' => 'TRIPAY_QRIS',
            'amount' => 250000.00,
            'admin_fee' => 0.00,
            'shipping_cost' => 20000.00,
            'total_amount' => 270000.00,
            'status' => 'UNPAID',
            'expired_at' => now()->addHours(24),
        ]);
    }

    public function test_admin_can_view_event_list(): void
    {
        $response = $this->get('/admin/events');
        $response->assertStatus(200);
        $response->assertSee('Bali Sunset Marathon 2026');
        $response->assertSee('BSM26');
        $response->assertSee('CAPI AKTIF');
        $response->assertSee('987654321012345');
    }

    public function test_admin_can_create_event_with_meta_capi_configuration(): void
    {
        $payload = [
            'title' => 'Surabaya Heritage Run 2026',
            'slug' => 'surabaya-heritage-run-2026',
            'event_code' => 'SHR26',
            'activity_type' => 'RUN',
            'submission_mode' => 'CUMULATIVE',
            'race_type' => 'CHALLENGE',
            'description' => 'Lari keliling cagar budaya Surabaya.',
            'registration_start' => now()->toDateTimeString(),
            'registration_end' => now()->addDays(20)->toDateTimeString(),
            'race_start' => now()->addDays(5)->toDateTimeString(),
            'race_end' => now()->addDays(25)->toDateTimeString(),
            'meta_pixel_id' => '112233445566778',
            'meta_capi_token' => 'EAAG_SURABAYA_TOKEN',
            'meta_test_code' => 'TEST99887',
            'is_meta_capi_enabled' => 1,
            'is_active' => 1,
            'category_name' => '5K Fun Run',
            'target_distance_km' => 5.0,
            'bib_prefix' => '05K',
            'package_name' => 'Medali Saja',
            'package_price' => 100000,
        ];

        $response = $this->post('/admin/events', $payload);
        $response->assertRedirect('/admin/events');

        $this->assertDatabaseHas('events', [
            'event_code' => 'SHR26',
            'meta_pixel_id' => '112233445566778',
            'meta_test_code' => 'TEST99887',
            'is_meta_capi_enabled' => 1,
        ]);

        $newEvent = Event::where('event_code', 'SHR26')->first();
        $this->assertNotNull($newEvent);
        $this->assertCount(1, $newEvent->categories);
        $this->assertCount(1, $newEvent->packages);
        $this->assertEquals(2, $newEvent->templateDesigns()->count()); // BIB & Certificate templates
    }

    public function test_admin_can_update_event_and_meta_capi_settings(): void
    {
        $payload = [
            'title' => 'Bali Sunset Marathon 2026 - Updated Edition',
            'slug' => 'bali-sunset-marathon-2026',
            'event_code' => 'BSM26',
            'activity_type' => 'RUN',
            'submission_mode' => 'CUMULATIVE',
            'race_type' => 'CHALLENGE',
            'registration_start' => now()->subDay()->toDateTimeString(),
            'registration_end' => now()->addDays(30)->toDateTimeString(),
            'race_start' => now()->toDateTimeString(),
            'race_end' => now()->addDays(30)->toDateTimeString(),
            'meta_pixel_id' => '555444333222111',
            'meta_capi_token' => 'EAAG_NEW_UPDATED_TOKEN',
            'meta_test_code' => 'TEST_NEW_CODE',
            'is_meta_capi_enabled' => 1,
            'is_active' => 1,
            'categories' => [
                [
                    'id' => $this->category->id,
                    'name' => 'Half Marathon 21K Pro',
                    'target_distance_km' => 21.1,
                    'bib_prefix' => '21K',
                    'quota' => 500,
                ],
                [
                    'id' => null,
                    'name' => 'Full Marathon 42K',
                    'target_distance_km' => 42.195,
                    'bib_prefix' => '42K',
                    'quota' => 200,
                ],
            ],
            'packages' => [
                [
                    'id' => $this->package->id,
                    'name' => 'Jersey + Finisher Medal Exclusive',
                    'description' => 'e-BIB Digital, E-Sertifikat Finisher, Jersey Event, Medali',
                    'price' => 300000,
                    'base_weight_grams' => 450,
                    'requires_shipping' => 1,
                    'includes_medal' => 1,
                    'includes_jersey' => 1,
                ],
            ],
        ];

        $response = $this->put('/admin/events/'.$this->event->id, $payload);
        $response->assertRedirect('/admin/events/'.$this->event->id.'/edit');

        $this->event->refresh();
        $this->assertEquals('555444333222111', $this->event->meta_pixel_id);
        $this->assertEquals('EAAG_NEW_UPDATED_TOKEN', $this->event->meta_capi_token);
        $this->assertEquals('TEST_NEW_CODE', $this->event->meta_test_code);

        // Assert Kategori diperbarui & kategori baru bertambah
        $this->assertDatabaseHas('categories', [
            'id' => $this->category->id,
            'name' => 'Half Marathon 21K Pro',
            'target_distance_km' => 21.1,
            'quota' => 500,
        ]);
        $this->assertDatabaseHas('categories', [
            'event_id' => $this->event->id,
            'name' => 'Full Marathon 42K',
            'bib_prefix' => '42K',
        ]);
        $this->assertEquals(2, $this->event->categories()->count());

        // Assert Paket diperbarui
        $this->assertDatabaseHas('packages', [
            'id' => $this->package->id,
            'name' => 'Jersey + Finisher Medal Exclusive',
            'description' => 'e-BIB Digital, E-Sertifikat Finisher, Jersey Event, Medali',
            'price' => 300000,
        ]);
    }

    public function test_admin_can_send_test_event_to_meta_capi(): void
    {
        Http::fake([
            'https://graph.facebook.com/*' => Http::response([
                'events_received' => 1,
                'messages' => [],
                'fbtrace_id' => 'FbTrace123',
            ], 200),
        ]);

        $response = $this->post('/admin/events/'.$this->event->id.'/test-capi', [
            'test_event_code' => 'TEST12345',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHas('success');

        Http::assertSent(function (Request $request) {
            return str_contains($request->url(), '987654321012345/events') &&
                $request['test_event_code'] === 'TEST12345' &&
                $request['data'][0]['event_name'] === 'Purchase';
        });
    }

    public function test_purchase_event_sent_via_capi_strictly_when_payment_paid(): void
    {
        Http::fake([
            'https://graph.facebook.com/*' => Http::response([
                'events_received' => 1,
                'messages' => [],
            ], 200),
            'https://api.mailketing.co.id/*' => Http::response([
                'status' => 'success',
            ], 200),
        ]);

        // 1. Simulasikan bayar sukses
        $response = $this->post('/payment/'.$this->payment->merchant_ref.'/simulate-pay');
        $response->assertStatus(302);

        $this->payment->refresh();
        $this->assertEquals('PAID', $this->payment->status);

        // 2. Verifikasi Purchase event dikirim ke Meta CAPI dengan data yang tepat
        Http::assertSent(function (Request $request) {
            if (! str_contains($request->url(), '987654321012345/events')) {
                return false;
            }

            $event = $request['data'][0];

            $hasPurchaseName = ($event['event_name'] === 'Purchase');
            $hasOrderId = ($event['event_id'] === 'VIRA-BSM-001');
            $hasCorrectValue = ($event['custom_data']['value'] == 270000.0);
            $hasCurrencyIdr = ($event['custom_data']['currency'] === 'IDR');
            $hasHashedEmail = ! empty($event['user_data']['em']);
            $hasHashedPhone = ! empty($event['user_data']['ph']);
            $hasTestCode = ($request['test_event_code'] === 'TEST12345');

            return $hasPurchaseName && $hasOrderId && $hasCorrectValue && $hasCurrencyIdr && $hasHashedEmail && $hasHashedPhone && $hasTestCode;
        });
    }

    public function test_admin_can_create_event_without_optional_category_and_bib_fields(): void
    {
        $payload = [
            'title' => 'Bandung Ultra Run 2026',
            'slug' => 'bandung-ultra-run-2026',
            'event_code' => 'BUR26',
            'activity_type' => 'RUN',
            'submission_mode' => 'CUMULATIVE',
            'race_type' => 'CHALLENGE',
            'registration_start' => now()->toDateTimeString(),
            'registration_end' => now()->addDays(20)->toDateTimeString(),
            'race_start' => now()->addDays(5)->toDateTimeString(),
            'race_end' => now()->addDays(25)->toDateTimeString(),
        ];

        $response = $this->post('/admin/events', $payload);
        $response->assertRedirect('/admin/events');

        $event = Event::where('event_code', 'BUR26')->first();
        $this->assertNotNull($event);
        $this->assertEquals(1, $event->categories()->count());
        $this->assertEquals('10K', $event->categories()->first()->bib_prefix);
        $this->assertEquals(1, $event->packages()->count());
        $this->assertEquals(2, $event->templateDesigns()->count());
    }

    public function test_admin_can_upload_hero_image_for_event(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('banner_hero.jpg', 1920, 800);

        $payload = [
            'title' => 'Malang Night Trail Run 2026',
            'slug' => 'malang-night-trail-run-2026',
            'event_code' => 'MNTR26',
            'activity_type' => 'RUN',
            'submission_mode' => 'CUMULATIVE',
            'race_type' => 'CHALLENGE',
            'registration_start' => now()->toDateTimeString(),
            'registration_end' => now()->addDays(20)->toDateTimeString(),
            'race_start' => now()->addDays(5)->toDateTimeString(),
            'race_end' => now()->addDays(25)->toDateTimeString(),
            'hero_image' => $file,
        ];

        $response = $this->post('/admin/events', $payload);
        $response->assertRedirect('/admin/events');

        $event = Event::where('event_code', 'MNTR26')->first();
        $this->assertNotNull($event);
        $this->assertNotNull($event->banner_image);
        $this->assertStringContainsString('/storage/events/banners/', $event->banner_image);

        // Verify stored file
        $storedPath = str_replace('/storage/', '', $event->banner_image);
        Storage::disk('public')->assertExists($storedPath);
    }

    public function test_admin_can_upload_description_image(): void
    {
        Storage::fake('public');

        $image = UploadedFile::fake()->image('infographic.png', 800, 600);

        $response = $this->postJson('/admin/events/upload-description-image', [
            'image' => $image,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
            ]);

        $url = $response->json('url');
        $this->assertNotNull($url);
        $this->assertStringContainsString('/storage/events/descriptions/', $url);

        $storedPath = str_replace('/storage/', '', $url);
        Storage::disk('public')->assertExists($storedPath);
    }

    public function test_event_show_page_renders_rich_html_description(): void
    {
        $richDescription = '<h2>Highlight Rute</h2><p>Lari dengan <strong>semangat membara</strong> dan <span class="font-light-sub">udara sejuk pegunungan</span>.</p><img src="/storage/events/descriptions/sample.jpg" alt="Rute">';

        $this->event->update([
            'description' => $richDescription,
        ]);

        $response = $this->get('/event/'.$this->event->slug);
        $response->assertStatus(200);
        $response->assertSee('<h2>Highlight Rute</h2>', false);
        $response->assertSee('<strong>semangat membara</strong>', false);
        $response->assertSee('<span class="font-light-sub">udara sejuk pegunungan</span>', false);
        $response->assertSee('<img src="/storage/events/descriptions/sample.jpg" alt="Rute">', false);
    }

    public function test_admin_can_delete_event(): void
    {
        $eventId = $this->event->id;
        $categoryId = $this->category->id;
        $packageId = $this->package->id;

        $response = $this->delete(route('admin.events.destroy', $this->event));

        $response->assertRedirect(route('admin.events.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('events', ['id' => $eventId]);
        $this->assertDatabaseMissing('categories', ['id' => $categoryId]);
        $this->assertDatabaseMissing('packages', ['id' => $packageId]);
    }
}
