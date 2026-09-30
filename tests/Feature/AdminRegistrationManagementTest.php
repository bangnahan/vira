<?php

namespace Tests\Feature;

use App\Models\AddOn;
use App\Models\AddOnVariant;
use App\Models\Category;
use App\Models\Event;
use App\Models\Package;
use App\Models\Participant;
use App\Models\Payment;
use App\Models\Registration;
use App\Models\RegistrationAddOn;
use App\Models\ShippingAddress;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminRegistrationManagementTest extends TestCase
{
    use RefreshDatabase;

    protected Event $eventA;

    protected Event $eventB;

    protected Category $categoryA;

    protected Package $packageA;

    protected Registration $registrationPaid;

    protected Registration $registrationUnpaid;

    protected function setUp(): void
    {
        parent::setUp();

        $admin = User::create([
            'name' => 'Admin Reg Test',
            'email' => 'adminreg@vira.id',
            'password' => Hash::make('password'),
            'role' => 'SUPER_ADMIN',
        ]);
        $this->actingAs($admin);

        $this->eventA = Event::create([
            'title' => 'Jakarta Night Run 2026',
            'slug' => 'jakarta-night-run-2026',
            'event_code' => 'JNR26',
            'activity_type' => 'RUN',
            'submission_mode' => 'SINGLE',
            'race_start' => now(),
            'race_end' => now()->addDays(14),
            'registration_start' => now()->subDays(5),
            'registration_end' => now()->addDays(5),
            'is_active' => true,
        ]);

        $this->eventB = Event::create([
            'title' => 'Bali Coastal Ride 2026',
            'slug' => 'bali-coastal-ride-2026',
            'event_code' => 'BCR26',
            'activity_type' => 'RIDE',
            'submission_mode' => 'CUMULATIVE',
            'race_start' => now(),
            'race_end' => now()->addDays(30),
            'registration_start' => now()->subDays(5),
            'registration_end' => now()->addDays(10),
            'is_active' => true,
        ]);

        $this->categoryA = Category::create([
            'event_id' => $this->eventA->id,
            'name' => '10K Open',
            'target_distance_km' => 10.0,
            'is_active' => true,
        ]);

        $this->packageA = Package::create([
            'event_id' => $this->eventA->id,
            'name' => 'Paket Medali & Jersey',
            'price' => 250000,
            'is_active' => true,
        ]);

        // Participant 1 (PAID)
        $participant1 = Participant::create([
            'full_name' => 'Budi Santoso',
            'email' => 'budi.santoso@example.com',
            'phone_number' => '081234567890',
            'gender' => 'MALE',
            'date_of_birth' => '1995-05-15',
            'blood_type' => 'O',
        ]);

        $this->registrationPaid = Registration::create([
            'participant_id' => $participant1->id,
            'event_id' => $this->eventA->id,
            'category_id' => $this->categoryA->id,
            'package_id' => $this->packageA->id,
            'bib_number' => '1001',
            'payment_status' => 'PAID',
            'finisher_status' => 'FINISHED',
            'total_distance_km' => 10.5,
        ]);

        Payment::create([
            'registration_id' => $this->registrationPaid->id,
            'tripay_reference' => 'DEV-T99887766',
            'merchant_ref' => 'VIRA-PAY-001',
            'payment_method' => 'QRIS2',
            'payment_name' => 'QRIS',
            'amount' => 250000,
            'admin_fee' => 750,
            'shipping_cost' => 25000,
            'total_amount' => 275750,
            'status' => 'PAID',
            'paid_at' => now(),
        ]);

        ShippingAddress::create([
            'registration_id' => $this->registrationPaid->id,
            'recipient_name' => 'Budi Santoso',
            'recipient_phone' => '081234567890',
            'address_detail' => 'Jl. Sudirman Kav 20 No. 5',
            'province' => 'DKI Jakarta',
            'city' => 'Jakarta Selatan',
            'district' => 'Kebayoran Baru',
            'postal_code' => '12190',
            'jersey_size' => 'L',
            'shipping_cost' => 25000,
        ]);

        $addon = AddOn::create([
            'event_id' => $this->eventA->id,
            'name' => 'Jersey Finisher Tambahan',
            'slug' => 'jersey-finisher-tambahan',
            'price' => 150000,
            'weight_grams' => 200,
            'stock' => 100,
            'has_variants' => true,
            'is_active' => true,
        ]);

        $variant = AddOnVariant::create([
            'add_on_id' => $addon->id,
            'variant_name' => 'Ukuran M',
            'additional_price' => 0,
            'stock' => 50,
        ]);

        RegistrationAddOn::create([
            'registration_id' => $this->registrationPaid->id,
            'add_on_id' => $addon->id,
            'add_on_variant_id' => $variant->id,
            'quantity' => 1,
            'unit_price' => 150000,
            'subtotal' => 150000,
        ]);

        // Participant 2 (UNPAID)
        $participant2 = Participant::create([
            'full_name' => 'Siti Rahmawati',
            'email' => 'siti.rahmawati@example.com',
            'phone_number' => '089876543210',
            'gender' => 'FEMALE',
            'date_of_birth' => '1998-10-20',
            'blood_type' => 'A',
        ]);

        $this->registrationUnpaid = Registration::create([
            'participant_id' => $participant2->id,
            'event_id' => $this->eventA->id,
            'category_id' => $this->categoryA->id,
            'package_id' => $this->packageA->id,
            'bib_number' => '1002',
            'payment_status' => 'UNPAID',
            'finisher_status' => 'IN_PROGRESS',
            'total_distance_km' => 0.0,
        ]);

        Payment::create([
            'registration_id' => $this->registrationUnpaid->id,
            'tripay_reference' => 'DEV-T11223344',
            'merchant_ref' => 'VIRA-PAY-002',
            'payment_method' => 'BCAVA',
            'payment_name' => 'BCA Virtual Account',
            'amount' => 250000,
            'admin_fee' => 4000,
            'shipping_cost' => 0,
            'total_amount' => 254000,
            'status' => 'UNPAID',
        ]);
    }

    public function test_admin_can_view_registration_index_page(): void
    {
        $response = $this->get(route('admin.registrations.index'));

        $response->assertStatus(200);
        $response->assertSee('Data Peserta &amp; Status Pembayaran', false);
        $response->assertSee('Budi Santoso');
        $response->assertSee('budi.santoso@example.com');
        $response->assertSee('1001');
        $response->assertSee('Siti Rahmawati');
        $response->assertSee('1002');
        $response->assertSee('VIRA-PAY-001');
    }

    public function test_admin_can_filter_registrations_by_payment_status(): void
    {
        // Filter PAID
        $responsePaid = $this->get(route('admin.registrations.index', ['payment_status' => 'PAID']));
        $responsePaid->assertStatus(200);
        $responsePaid->assertSee('Budi Santoso');
        $responsePaid->assertDontSee('Siti Rahmawati');

        // Filter UNPAID
        $responseUnpaid = $this->get(route('admin.registrations.index', ['payment_status' => 'UNPAID']));
        $responseUnpaid->assertStatus(200);
        $responseUnpaid->assertSee('Siti Rahmawati');
        $responseUnpaid->assertDontSee('Budi Santoso');
    }

    public function test_admin_can_search_registrations(): void
    {
        $response = $this->get(route('admin.registrations.index', ['search' => 'budi.santoso']));

        $response->assertStatus(200);
        $response->assertSee('Budi Santoso');
        $response->assertDontSee('Siti Rahmawati');
    }

    public function test_admin_can_export_registrations_to_csv(): void
    {
        $response = $this->get(route('admin.registrations.export'));

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');

        $content = $response->streamedContent();

        // Check CSV headers
        $this->assertStringContainsString('ID Registrasi', $content);
        $this->assertStringContainsString('Nama Lengkap', $content);
        $this->assertStringContainsString('Nomor e-BIB', $content);
        $this->assertStringContainsString('Status Pembayaran', $content);
        $this->assertStringContainsString('Total Tagihan', $content);
        $this->assertStringContainsString('Alamat Pengiriman', $content);

        // Check Participant 1 (PAID) data
        $this->assertStringContainsString('Budi Santoso', $content);
        $this->assertStringContainsString('budi.santoso@example.com', $content);
        $this->assertStringContainsString('1001', $content);
        $this->assertStringContainsString('PAID', $content);
        $this->assertStringContainsString('Kebayoran Baru', $content);

        // Check Participant 2 (UNPAID) data
        $this->assertStringContainsString('Siti Rahmawati', $content);
        $this->assertStringContainsString('UNPAID', $content);
    }

    public function test_admin_can_export_registrations_with_logistics_preset(): void
    {
        $response = $this->get(route('admin.registrations.export', ['preset' => 'logistics', 'payment_status' => 'PAID']));

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');

        $content = $response->streamedContent();
        $this->assertStringContainsString('Nama Penerima Paket', $content);
        $this->assertStringContainsString('Ukuran Jersey', $content);
        $this->assertStringContainsString('Budi Santoso', $content);
        // UNPAID participant should not appear when payment_status is PAID
        $this->assertStringNotContainsString('Siti Rahmawati', $content);
    }

    public function test_admin_can_export_registrations_with_finance_preset(): void
    {
        $response = $this->get(route('admin.registrations.export', ['preset' => 'finance']));

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');

        $content = $response->streamedContent();
        $this->assertStringContainsString('Merchant Ref Invoice', $content);
        $this->assertStringContainsString('Biaya Ongkir SPX (Rp)', $content);
        $this->assertStringContainsString('Budi Santoso', $content);
    }

    public function test_admin_can_view_jersey_orders_tab(): void
    {
        $response = $this->get(route('admin.registrations.jersey-orders'));

        $response->assertStatus(200);
        $response->assertSee('Rekapitulasi Order Jersey Siap Produksi');
        $response->assertSee('1. Rekapitulasi Jersey dari Paket Pendaftaran');
        $response->assertSee('2. Rekapitulasi Jersey &amp; Apparel Tambahan dari Fitur Add-ons', false);
        $response->assertSee('GRAND TOTAL KEBUTUHAN PRODUKSI JERSEY');
        $response->assertSee('Paket Medali &amp; Jersey', false);
        $response->assertSee('Jersey Finisher Tambahan');
    }

    public function test_admin_can_export_jersey_orders_spk_csv(): void
    {
        $response = $this->get(route('admin.registrations.jersey-orders.export'));

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');

        $content = $response->streamedContent();
        $this->assertStringContainsString('SURAT PERINTAH KERJA (SPK) REKAPITULASI PRODUKSI JERSEY', $content);
        $this->assertStringContainsString('[BAGIAN 1: REKAPITULASI DARI PAKET PENDAFTARAN EVENT]', $content);
        $this->assertStringContainsString('[BAGIAN 2: REKAPITULASI DARI ITEM ADD-ONS / MERCHANDISE TAMBAHAN]', $content);
        $this->assertStringContainsString('Paket Medali & Jersey', $content);
        $this->assertStringContainsString('Jersey Finisher Tambahan', $content);
    }
}
