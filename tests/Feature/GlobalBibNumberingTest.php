<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Event;
use App\Models\Package;
use App\Models\Participant;
use App\Models\Payment;
use App\Models\Registration;
use App\Services\MailketingService;
use App\Services\MetaCapiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Mockery;
use Tests\TestCase;

class GlobalBibNumberingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $mockMailketing = Mockery::mock(MailketingService::class);
        $mockMailketing->shouldReceive('sendPaymentSuccessEmail')->andReturn(['status' => 'success']);
        $mockMailketing->shouldReceive('sendInvoiceEmail')->andReturn(['status' => 'success']);
        $this->app->instance(MailketingService::class, $mockMailketing);

        $mockMeta = Mockery::mock(MetaCapiService::class);
        $mockMeta->shouldReceive('sendPurchaseEvent')->andReturn(['events_received' => 1]);
        $this->app->instance(MetaCapiService::class, $mockMeta);
    }

    protected function createEvent(string $title = 'Virtual Run', string $code = 'VR'): Event
    {
        return Event::create([
            'title' => $title,
            'slug' => Str::slug($title).'-'.uniqid(),
            'event_code' => $code,
            'activity_type' => 'RUN',
            'submission_mode' => 'CUMULATIVE',
            'race_type' => 'CHALLENGE',
            'registration_start' => now()->subDays(1),
            'registration_end' => now()->addDays(30),
            'race_start' => now(),
            'race_end' => now()->addDays(30),
            'is_active' => true,
        ]);
    }

    protected function createCategory(Event $event, string $name = '10K'): Category
    {
        return Category::create([
            'event_id' => $event->id,
            'name' => $name,
            'target_distance_km' => 10.00,
            'bib_prefix' => '10K',
            'last_bib_sequence' => 0,
        ]);
    }

    protected function createParticipant(string $email = 'user@example.com'): Participant
    {
        return Participant::create([
            'full_name' => 'Peserta Test',
            'email' => $email,
            'phone_number' => '081234567890',
            'gender' => 'MALE',
            'date_of_birth' => '1995-05-05',
        ]);
    }

    public function test_bib_number_generation_starts_at_1001_ignoring_historical_alphanumeric_bibs(): void
    {
        $event = $this->createEvent('Event Lama', 'OLD');
        $category = $this->createCategory($event, '10K');
        $p1 = $this->createParticipant('p1@example.com');
        $p2 = $this->createParticipant('p2@example.com');

        // Historical registrations dengan format lama ber-prefix
        Registration::create([
            'event_id' => $event->id,
            'category_id' => $category->id,
            'participant_id' => $p1->id,
            'bib_number' => 'OLD-10K-0001',
            'payment_status' => 'PAID',
        ]);

        Registration::create([
            'event_id' => $event->id,
            'category_id' => $category->id,
            'participant_id' => $p2->id,
            'bib_number' => 'MVR26-05K-0099',
            'payment_status' => 'PAID',
        ]);

        // Generate bib baru pertama kali harus mulai dari 1001
        $nextBib1 = Registration::generateNextBibNumber();
        $this->assertEquals('1001', $nextBib1);

        // Simpan bib 1001
        $p3 = $this->createParticipant('p3@example.com');
        Registration::create([
            'event_id' => $event->id,
            'category_id' => $category->id,
            'participant_id' => $p3->id,
            'bib_number' => $nextBib1,
            'payment_status' => 'PAID',
        ]);

        // Generate berikutnya harus 1002
        $nextBib2 = Registration::generateNextBibNumber();
        $this->assertEquals('1002', $nextBib2);
    }

    public function test_bib_number_allocated_sequentially_across_multiple_parallel_events(): void
    {
        $eventA = $this->createEvent('Jakarta Marathon', 'JKT');
        $catA = $this->createCategory($eventA, 'Full Marathon');
        $pkgA = Package::create(['event_id' => $eventA->id, 'name' => 'Standard', 'price' => 200000]);

        $eventB = $this->createEvent('Bali Coastal Run', 'BALI');
        $catB = $this->createCategory($eventB, 'Half Marathon');
        $pkgB = Package::create(['event_id' => $eventB->id, 'name' => 'Standard', 'price' => 150000]);

        // Peserta 1 mendaftar di Event A
        $p1 = $this->createParticipant('p1@example.com');
        $reg1 = Registration::create([
            'event_id' => $eventA->id,
            'category_id' => $catA->id,
            'package_id' => $pkgA->id,
            'participant_id' => $p1->id,
            'bib_number' => null,
            'payment_status' => 'PENDING',
        ]);
        $pay1 = Payment::create([
            'registration_id' => $reg1->id,
            'merchant_ref' => 'INV-PARALEL-01',
            'status' => 'UNPAID',
            'payment_method' => 'BCAVA',
            'amount' => 200000,
            'total_amount' => 200000,
        ]);

        // Peserta 2 mendaftar di Event B
        $p2 = $this->createParticipant('p2@example.com');
        $reg2 = Registration::create([
            'event_id' => $eventB->id,
            'category_id' => $catB->id,
            'package_id' => $pkgB->id,
            'participant_id' => $p2->id,
            'bib_number' => null,
            'payment_status' => 'PENDING',
        ]);
        $pay2 = Payment::create([
            'registration_id' => $reg2->id,
            'merchant_ref' => 'INV-PARALEL-02',
            'status' => 'UNPAID',
            'payment_method' => 'BCAVA',
            'amount' => 150000,
            'total_amount' => 150000,
        ]);

        // Peserta 3 mendaftar di Event A lagi
        $p3 = $this->createParticipant('p3@example.com');
        $reg3 = Registration::create([
            'event_id' => $eventA->id,
            'category_id' => $catA->id,
            'package_id' => $pkgA->id,
            'participant_id' => $p3->id,
            'bib_number' => null,
            'payment_status' => 'PENDING',
        ]);
        $pay3 = Payment::create([
            'registration_id' => $reg3->id,
            'merchant_ref' => 'INV-PARALEL-03',
            'status' => 'UNPAID',
            'payment_method' => 'BCAVA',
            'amount' => 200000,
            'total_amount' => 200000,
        ]);

        // 1. Peserta 1 (Event A) bayar duluan -> dapat 1001
        $this->post('/payment/'.$pay1->merchant_ref.'/simulate-pay')->assertStatus(302);
        $reg1->refresh();
        $this->assertEquals('1001', $reg1->bib_number);

        // 2. Peserta 2 (Event B) bayar kedua -> dapat 1002 (walau beda event)
        $this->post('/payment/'.$pay2->merchant_ref.'/simulate-pay')->assertStatus(302);
        $reg2->refresh();
        $this->assertEquals('1002', $reg2->bib_number);

        // 3. Peserta 3 (Event A) bayar ketiga -> dapat 1003
        $this->post('/payment/'.$pay3->merchant_ref.'/simulate-pay')->assertStatus(302);
        $reg3->refresh();
        $this->assertEquals('1003', $reg3->bib_number);
    }

    public function test_bib_number_transitions_smoothly_to_5_digits_when_exceeding_9999(): void
    {
        $event = $this->createEvent('Event 9999', 'E99');
        $category = $this->createCategory($event);
        $p1 = $this->createParticipant('p1@example.com');

        // Simulasikan nomor BIB saat ini sudah mencapai 9999
        Registration::create([
            'event_id' => $event->id,
            'category_id' => $category->id,
            'participant_id' => $p1->id,
            'bib_number' => '9999',
            'payment_status' => 'PAID',
        ]);

        // Generate nomor berikutnya harus otomatis 5 digit 10000
        $nextBib1 = Registration::generateNextBibNumber();
        $this->assertEquals('10000', $nextBib1);

        // Simpan 10000
        $p2 = $this->createParticipant('p2@example.com');
        Registration::create([
            'event_id' => $event->id,
            'category_id' => $category->id,
            'participant_id' => $p2->id,
            'bib_number' => $nextBib1,
            'payment_status' => 'PAID',
        ]);

        // Lanjut ke 10001
        $nextBib2 = Registration::generateNextBibNumber();
        $this->assertEquals('10001', $nextBib2);
    }

    public function test_category_generate_next_bib_delegates_to_global_registration_sequence(): void
    {
        $event = $this->createEvent('Event Delegasi', 'DEL');
        $category = Category::create([
            'event_id' => $event->id,
            'name' => '5K',
            'target_distance_km' => 5.0,
            'bib_prefix' => '5K',
            'last_bib_sequence' => 5,
        ]);

        $bib = $category->generateNextBibNumber();
        $this->assertEquals('1001', $bib);
        $this->assertEquals(6, $category->fresh()->last_bib_sequence);
    }
}
