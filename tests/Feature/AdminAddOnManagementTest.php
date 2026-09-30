<?php

namespace Tests\Feature;

use App\Models\AddOn;
use App\Models\Category;
use App\Models\Event;
use App\Models\Package;
use App\Models\Participant;
use App\Models\Registration;
use App\Models\RegistrationAddOn;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminAddOnManagementTest extends TestCase
{
    use RefreshDatabase;

    protected Event $event;

    protected function setUp(): void
    {
        parent::setUp();

        $admin = User::create([
            'name' => 'Admin Addon Test',
            'email' => 'adminaddon@vira.id',
            'password' => Hash::make('password'),
            'role' => 'SUPER_ADMIN',
        ]);
        $this->actingAs($admin);

        $this->event = Event::create([
            'title' => 'Bromo Sky Marathon 2026',
            'slug' => 'bromo-sky-marathon-2026',
            'event_code' => 'BSM26',
            'activity_type' => 'RUN',
            'submission_mode' => 'SINGLE',
            'race_type' => 'CHALLENGE',
            'registration_start' => now()->subDays(5),
            'registration_end' => now()->addDays(20),
            'race_start' => now()->addDays(25),
            'race_end' => now()->addDays(30),
            'is_active' => true,
        ]);
    }

    public function test_admin_can_view_addons_list_and_stats(): void
    {
        AddOn::create([
            'event_id' => $this->event->id,
            'name' => 'Jersey Bromo Volcanic',
            'slug' => 'jersey-bromo-volcanic',
            'price' => 125000,
            'weight_grams' => 160,
            'stock' => 50,
            'has_variants' => false,
            'is_active' => true,
        ]);

        AddOn::create([
            'event_id' => null, // Global
            'name' => 'Topi Lari VIRA Volt',
            'slug' => 'topi-lari-vira-volt',
            'price' => 75000,
            'weight_grams' => 70,
            'stock' => 30,
            'has_variants' => false,
            'is_active' => false,
        ]);

        $response = $this->get(route('admin.addons.index'));

        $response->assertStatus(200);
        $response->assertSee('Pengaturan Item Add-ons &amp; Merchandise', false);
        $response->assertSee('Jersey Bromo Volcanic');
        $response->assertSee('Topi Lari VIRA Volt');
        $response->assertSee('Rp 125.000');
        $response->assertSee('BSM26');
        $response->assertSee('Semua Event');
    }

    public function test_admin_can_filter_addons_by_event_and_status(): void
    {
        $addon1 = AddOn::create([
            'event_id' => $this->event->id,
            'name' => 'Jersey Khusus Bromo',
            'slug' => 'jersey-khusus-bromo',
            'price' => 150000,
            'weight_grams' => 150,
            'stock' => 20,
            'is_active' => true,
        ]);

        $addon2 = AddOn::create([
            'event_id' => null,
            'name' => 'Stiker VIRA Global',
            'slug' => 'stiker-vira-global',
            'price' => 10000,
            'weight_grams' => 10,
            'stock' => 100,
            'is_active' => false,
        ]);

        // Filter: global only
        $resGlobal = $this->get(route('admin.addons.index', ['event_id' => 'global']));
        $resGlobal->assertStatus(200);
        $resGlobal->assertSee('Stiker VIRA Global');
        $resGlobal->assertDontSee('Jersey Khusus Bromo');

        // Filter: active only
        $resActive = $this->get(route('admin.addons.index', ['status' => 'active']));
        $resActive->assertStatus(200);
        $resActive->assertSee('Jersey Khusus Bromo');
        $resActive->assertDontSee('Stiker VIRA Global');
    }

    public function test_admin_can_view_create_page(): void
    {
        $response = $this->get(route('admin.addons.create'));

        $response->assertStatus(200);
        $response->assertSee('Tambah Item Add-on Baru');
        $response->assertSee('BSM26');
        $response->assertSee('⚡ Isi Preset Ukuran (XS - 5XL)', false);
    }

    public function test_admin_can_create_addon_without_variants(): void
    {
        Storage::fake('public');

        $image = UploadedFile::fake()->image('gantungan-kunci.jpg', 400, 400);

        $payload = [
            'name' => 'Gantungan Kunci BSM26',
            'slug' => 'gantungan-kunci-bsm26',
            'event_id' => $this->event->id,
            'price' => 35000,
            'weight_grams' => 45,
            'stock' => 200,
            'description' => 'Miniatur medali logam berkualitas tinggi.',
            'image' => $image,
            'is_active' => '1',
        ];

        $response = $this->post(route('admin.addons.store'), $payload);

        $response->assertRedirect(route('admin.addons.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('add_ons', [
            'name' => 'Gantungan Kunci BSM26',
            'slug' => 'gantungan-kunci-bsm26',
            'event_id' => $this->event->id,
            'price' => 35000.00,
            'weight_grams' => 45,
            'stock' => 200,
            'has_variants' => false,
            'is_active' => true,
        ]);

        $addon = AddOn::where('slug', 'gantungan-kunci-bsm26')->first();
        $this->assertNotNull($addon->image_path);
        Storage::disk('public')->assertExists($addon->image_path);
        $this->assertStringContainsString('storage/addons', $addon->image_url);
    }

    public function test_admin_can_create_addon_with_multiple_variants(): void
    {
        $payload = [
            'name' => 'Jersey Finisher Dry-Fit',
            'slug' => 'jersey-finisher-dry-fit',
            'event_id' => $this->event->id,
            'price' => 130000,
            'weight_grams' => 170,
            'stock' => 150,
            'has_variants' => '1',
            'is_active' => '1',
            'variants' => [
                ['variant_name' => 'Ukuran S', 'additional_price' => 0, 'stock' => 40],
                ['variant_name' => 'Ukuran M', 'additional_price' => 0, 'stock' => 50],
                ['variant_name' => 'Ukuran L', 'additional_price' => 0, 'stock' => 60],
                ['variant_name' => 'Ukuran 3XL', 'additional_price' => 15000, 'stock' => 20],
            ],
        ];

        $response = $this->post(route('admin.addons.store'), $payload);

        $response->assertRedirect(route('admin.addons.index'));

        $addon = AddOn::where('slug', 'jersey-finisher-dry-fit')->with('variants')->first();
        $this->assertNotNull($addon);
        $this->assertTrue($addon->has_variants);
        $this->assertCount(4, $addon->variants);
        // Total variant stock is 40 + 50 + 60 + 20 = 170
        $this->assertEquals(170, $addon->stock);
        $this->assertEquals(170, $addon->total_stock);

        $this->assertDatabaseHas('add_on_variants', [
            'add_on_id' => $addon->id,
            'variant_name' => 'Ukuran 3XL',
            'additional_price' => 15000.00,
            'stock' => 20,
        ]);
    }

    public function test_admin_can_edit_and_update_addon(): void
    {
        $addon = AddOn::create([
            'name' => 'Topi Lari Original',
            'slug' => 'topi-lari-original',
            'event_id' => null,
            'price' => 50000,
            'weight_grams' => 80,
            'stock' => 50,
            'has_variants' => false,
            'is_active' => true,
        ]);

        $editView = $this->get(route('admin.addons.edit', $addon));
        $editView->assertStatus(200);
        $editView->assertSee('Topi Lari Original');

        $updatePayload = [
            'name' => 'Topi Lari Pro Breathable',
            'slug' => 'topi-lari-pro-breathable',
            'event_id' => $this->event->id,
            'price' => 65000,
            'weight_grams' => 85,
            'stock' => 100,
            'description' => 'Edisi upgrade dengan reflective malam.',
            'has_variants' => '0',
            'is_active' => '1',
        ];

        $resUpdate = $this->put(route('admin.addons.update', $addon), $updatePayload);
        $resUpdate->assertRedirect(route('admin.addons.index'));

        $this->assertDatabaseHas('add_ons', [
            'id' => $addon->id,
            'name' => 'Topi Lari Pro Breathable',
            'slug' => 'topi-lari-pro-breathable',
            'event_id' => $this->event->id,
            'price' => 65000.00,
            'stock' => 100,
        ]);
    }

    public function test_admin_can_delete_addon_without_orders(): void
    {
        $addon = AddOn::create([
            'name' => 'Sticker Pack Tester',
            'slug' => 'sticker-pack-tester',
            'price' => 10000,
            'weight_grams' => 10,
            'stock' => 20,
            'is_active' => true,
        ]);

        $response = $this->delete(route('admin.addons.destroy', $addon));

        $response->assertRedirect(route('admin.addons.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('add_ons', [
            'id' => $addon->id,
        ]);
    }

    public function test_admin_cannot_delete_addon_that_has_been_ordered_by_participants(): void
    {
        $category = Category::create([
            'event_id' => $this->event->id,
            'name' => '10K Race',
            'target_distance_km' => 10.0,
            'bib_prefix' => '10K',
            'current_bib_number' => 1,
            'is_active' => true,
        ]);

        $package = Package::create([
            'event_id' => $this->event->id,
            'name' => 'Paket Lengkap',
            'price' => 150000,
            'package_items' => ['EBIB', 'Jersey'],
            'weight_grams' => 300,
            'requires_shipping' => true,
            'is_active' => true,
        ]);

        $addon = AddOn::create([
            'event_id' => $this->event->id,
            'name' => 'Jersey Finisher Ekstra',
            'slug' => 'jersey-finisher-ekstra',
            'price' => 120000,
            'weight_grams' => 180,
            'stock' => 50,
            'is_active' => true,
        ]);

        $participant = Participant::create([
            'full_name' => 'Ahmad Pelari',
            'email' => 'ahmad@example.com',
            'phone_number' => '081234567890',
        ]);

        $registration = Registration::create([
            'event_id' => $this->event->id,
            'category_id' => $category->id,
            'package_id' => $package->id,
            'participant_id' => $participant->id,
            'registration_number' => 'REG-BSM26-0001',
            'bib_number' => '10K-0001',
            'payment_status' => 'UNPAID',
            'race_status' => 'REGISTERED',
        ]);

        RegistrationAddOn::create([
            'registration_id' => $registration->id,
            'add_on_id' => $addon->id,
            'variant_id' => null,
            'quantity' => 1,
            'unit_price' => 120000,
            'subtotal' => 120000,
        ]);

        $response = $this->delete(route('admin.addons.destroy', $addon));

        $response->assertRedirect(route('admin.addons.index'));
        $response->assertSessionHas('error');

        // Addon should still exist in database for historical and invoice consistency
        $this->assertDatabaseHas('add_ons', [
            'id' => $addon->id,
        ]);
    }

    public function test_admin_can_uncheck_is_active_to_hide_addon_from_storefront_and_event(): void
    {
        $addon = AddOn::create([
            'event_id' => $this->event->id,
            'name' => 'Jersey Spesial Disembunyikan',
            'slug' => 'jersey-spesial-disembunyikan',
            'price' => 150000,
            'weight_grams' => 200,
            'stock' => 50,
            'is_active' => true,
        ]);

        // 1. Awalnya aktif: tampil di etalase dan halaman pendaftaran
        $resStorefrontActive = $this->get(route('etalase.index'));
        $resStorefrontActive->assertSee('Jersey Spesial Disembunyikan');

        $resRegisterActive = $this->get(route('events.register', $this->event->slug));
        $resRegisterActive->assertSee('Jersey Spesial Disembunyikan');

        // 2. Admin meng-uncheck checkbox is_active (dikirim value 0 atau tanpa key is_active)
        $updatePayload = [
            'name' => 'Jersey Spesial Disembunyikan',
            'slug' => 'jersey-spesial-disembunyikan',
            'event_id' => $this->event->id,
            'price' => 150000,
            'weight_grams' => 200,
            'stock' => 50,
            'is_active' => '0',
            'has_variants' => '0',
        ];

        $resUpdate = $this->put(route('admin.addons.update', $addon), $updatePayload);
        $resUpdate->assertRedirect(route('admin.addons.index'));

        // Cek database harus tersimpan false (0)
        $addon->refresh();
        $this->assertFalse($addon->is_active);

        // 3. Setelah dinonaktifkan: tersembunyi dari etalase & form pendaftaran event
        $this->flushSession();

        $resStorefrontHidden = $this->get(route('etalase.index'));
        $resStorefrontHidden->assertDontSee('Jersey Spesial Disembunyikan');

        $resRegisterHidden = $this->get(route('events.register', $this->event->slug));
        $resRegisterHidden->assertDontSee('Jersey Spesial Disembunyikan');
    }

    public function test_admin_can_toggle_addon_status_via_toggle_status_route(): void
    {
        $addon = AddOn::create([
            'event_id' => null,
            'name' => 'Topi Quick Toggle',
            'slug' => 'topi-quick-toggle',
            'price' => 50000,
            'weight_grams' => 70,
            'stock' => 25,
            'is_active' => true,
        ]);

        // Toggle dari aktif ke nonaktif
        $response = $this->patch(route('admin.addons.toggle-status', $addon));
        $response->assertRedirect();
        $response->assertSessionHas('success');

        $addon->refresh();
        $this->assertFalse($addon->is_active);

        // Toggle kembali dari nonaktif ke aktif
        $response2 = $this->patch(route('admin.addons.toggle-status', $addon));
        $response2->assertRedirect();

        $addon->refresh();
        $this->assertTrue($addon->is_active);
    }
}
