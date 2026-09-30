<?php

namespace Database\Seeders;

use App\Models\AddOn;
use App\Models\AddOnVariant;
use App\Models\Category;
use App\Models\Event;
use App\Models\Package;
use App\Models\Setting;
use App\Models\TemplateDesign;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Super Admin Accounts
        User::firstOrCreate(
            ['email' => 'admin@vira.id'],
            [
                'name' => 'VIRA Super Admin',
                'password' => Hash::make('password'),
                'role' => 'SUPER_ADMIN',
            ]
        );

        User::firstOrCreate(
            ['email' => 'bangnahan@gmail.com'],
            [
                'name' => 'Bang Nahan',
                'password' => Hash::make('pekanbaru12'),
                'role' => 'SUPER_ADMIN',
            ]
        );

        // 2. Default System Settings
        $defaultSettings = [
            ['key' => 'app_name', 'value' => 'VIRA Virtual Sport Platform', 'group' => 'app'],
            ['key' => 'app_support_whatsapp', 'value' => '081234567890', 'group' => 'app'],
            ['key' => 'tripay_api_key', 'value' => 'DEV-TRIPAY-API-KEY', 'group' => 'tripay'],
            ['key' => 'tripay_private_key', 'value' => 'DEV-TRIPAY-PRIVATE-KEY', 'group' => 'tripay'],
            ['key' => 'tripay_merchant_code', 'value' => 'T12345', 'group' => 'tripay'],
            ['key' => 'tripay_mode', 'value' => 'sandbox', 'group' => 'tripay'],
            ['key' => 'mailketing_api_token', 'value' => 'DEV-MAILKETING-TOKEN', 'group' => 'mailketing'],
            ['key' => 'mailketing_sender_email', 'value' => 'noreply@vira.id', 'group' => 'mailketing'],
            ['key' => 'mailketing_sender_name', 'value' => 'VIRA Virtual Run', 'group' => 'mailketing'],
            ['key' => 'shipping_api_endpoint', 'value' => 'https://api.internal-ongkir.com/v1', 'group' => 'shipping'],
            ['key' => 'shipping_api_token', 'value' => 'INTERNAL-SHIPPING-SECRET', 'group' => 'shipping'],
        ];

        foreach ($defaultSettings as $s) {
            Setting::firstOrCreate(['key' => $s['key']], $s);
        }

        // 3. Event 1: Merdeka Virtual Run 2026
        $eventRun = Event::firstOrCreate(
            ['slug' => 'merdeka-virtual-run-2026'],
            [
                'title' => 'Merdeka Virtual Run 2026',
                'event_code' => 'MVR26',
                'activity_type' => 'RUN',
                'submission_mode' => 'CUMULATIVE',
                'race_type' => 'CHALLENGE',
                'description' => 'Rayakan semangat kemerdekaan dengan berlari di mana saja, kapan saja! Tuntaskan jarak target pilihanmu secara akumulatif dan raih medali finisher eksklusif.',
                'rules_and_terms' => '1. Aktivitas lari dapat dicicil berkali-kali selama periode race berlangsung.\n2. Submit jarak, durasi, dan link bukti aktivitas lari (Strava/Garmin/GDrive) melalui portal /submit.\n3. Finisher E-Certificate otomatis terbit setelah 100% target jarak tuntas.',
                'registration_start' => now()->subDays(5),
                'registration_end' => now()->addDays(30),
                'race_start' => now(),
                'race_end' => now()->addDays(45),
                'banner_image' => 'events/banners/mvr26-banner.jpg',
                'is_active' => true,
            ]
        );

        // Kategori MVR26
        $cat5k = Category::firstOrCreate(
            ['event_id' => $eventRun->id, 'name' => '5K Fun Run'],
            [
                'target_distance_km' => 5.00,
                'bib_prefix' => '05K',
                'last_bib_sequence' => 0,
                'quota' => 500,
            ]
        );

        $cat10k = Category::firstOrCreate(
            ['event_id' => $eventRun->id, 'name' => '10K Challenge'],
            [
                'target_distance_km' => 10.00,
                'bib_prefix' => '10K',
                'last_bib_sequence' => 0,
                'quota' => 500,
            ]
        );

        $cat21k = Category::firstOrCreate(
            ['event_id' => $eventRun->id, 'name' => '21K Half Marathon Challenge'],
            [
                'target_distance_km' => 21.10,
                'bib_prefix' => '21K',
                'last_bib_sequence' => 0,
                'quota' => 300,
            ]
        );

        // Paket Pendaftaran MVR26
        Package::firstOrCreate(
            ['event_id' => $eventRun->id, 'name' => 'Digital Finisher Pack'],
            [
                'description' => 'Termasuk Nomor e-BIB Visual, Akses Tracker Progres, dan E-Certificate Finisher Digital.',
                'price' => 50000.00,
                'base_weight_grams' => 0,
                'includes_jersey' => false,
                'includes_medal' => false,
                'requires_shipping' => false,
            ]
        );

        Package::firstOrCreate(
            ['event_id' => $eventRun->id, 'name' => 'Complete Race Pack (Jersey & Finisher Medal)'],
            [
                'description' => 'Termasuk Jersey Dry-Fit Eksklusif MVR26, Medali Logam Cor Finisher, e-BIB Card, dan E-Certificate.',
                'price' => 195000.00,
                'base_weight_grams' => 350,
                'includes_jersey' => true,
                'includes_medal' => true,
                'requires_shipping' => true,
            ]
        );

        // Template Designer untuk MVR26 (e-BIB & E-Certificate)
        TemplateDesign::firstOrCreate(
            ['event_id' => $eventRun->id, 'type' => 'BIB'],
            [
                'background_image_path' => 'templates/bib/mvr26-bib-base.png',
                'canvas_width' => 1200,
                'canvas_height' => 800,
                'elements_config' => TemplateDesign::defaultBibConfig(),
            ]
        );

        TemplateDesign::firstOrCreate(
            ['event_id' => $eventRun->id, 'type' => 'CERTIFICATE'],
            [
                'background_image_path' => 'templates/cert/mvr26-cert-base.png',
                'canvas_width' => 1920,
                'canvas_height' => 1080,
                'elements_config' => TemplateDesign::defaultCertificateConfig(),
            ]
        );

        // Add-ons Etalase Cross-Selling untuk MVR26
        $addonJersey = AddOn::firstOrCreate(
            ['slug' => 'jersey-finisher-mvr26'],
            [
                'event_id' => $eventRun->id,
                'name' => 'Jersey Finisher Dry-Fit MVR26 (Tambahan)',
                'description' => 'Jersey lari bahan premium cool-max dry-fit dengan sublimasi warna kemerdekaan.',
                'image_path' => 'addons/jersey-mvr26.jpg',
                'price' => 120000.00,
                'weight_grams' => 180,
                'stock' => 200,
                'has_variants' => true,
                'is_active' => true,
            ]
        );

        foreach (['Ukuran S', 'Ukuran M', 'Ukuran L', 'Ukuran XL', 'Ukuran XXL'] as $varName) {
            AddOnVariant::firstOrCreate(
                ['add_on_id' => $addonJersey->id, 'variant_name' => $varName],
                ['additional_price' => 0.00, 'stock' => 40]
            );
        }

        AddOn::firstOrCreate(
            ['slug' => 'gantungan-kunci-mvr26'],
            [
                'event_id' => $eventRun->id,
                'name' => 'Gantungan Kunci Medali Mini MVR26',
                'description' => 'Gantungan kunci berbahan cor logam miniatur medali finisher.',
                'image_path' => 'addons/keychain-mvr26.jpg',
                'price' => 25000.00,
                'weight_grams' => 50,
                'stock' => 300,
                'has_variants' => false,
                'is_active' => true,
            ]
        );

        AddOn::firstOrCreate(
            ['slug' => 'topi-lari-vira-volt'],
            [
                'event_id' => null, // Global add-on
                'name' => 'Topi Lari Breathable VIRA Volt',
                'description' => 'Topi lari ultralight dengan ventilasi jaring samping dan reflective strip malam.',
                'image_path' => 'addons/cap-vira.jpg',
                'price' => 65000.00,
                'weight_grams' => 80,
                'stock' => 150,
                'has_variants' => false,
                'is_active' => true,
            ]
        );

        // 4. Event 2: Jakarta Virtual Ride 2026
        $eventRide = Event::firstOrCreate(
            ['slug' => 'jakarta-virtual-ride-2026'],
            [
                'title' => 'Jakarta Virtual Ride 2026',
                'event_code' => 'JVR26',
                'activity_type' => 'RIDE',
                'submission_mode' => 'CUMULATIVE',
                'race_type' => 'CHALLENGE',
                'description' => 'Jelajahi aspal secara virtual dari mana saja! Tantang dirimu menaklukkan jarak bersepeda 40K atau Century Ride 100K.',
                'rules_and_terms' => '1. Aktivitas bersepeda outdoor maupun indoor smart trainer.\n2. Submit link Strava/Garmin pada portal /submit.\n3. Akumulasi jarak bebas dicicil selama periode lomba.',
                'registration_start' => now()->subDays(2),
                'registration_end' => now()->addDays(20),
                'race_start' => now(),
                'race_end' => now()->addDays(40),
                'banner_image' => 'events/banners/jvr26-banner.jpg',
                'is_active' => true,
            ]
        );

        Category::firstOrCreate(
            ['event_id' => $eventRide->id, 'name' => '40K City Ride'],
            [
                'target_distance_km' => 40.00,
                'bib_prefix' => '40K',
                'last_bib_sequence' => 0,
                'quota' => 300,
            ]
        );

        Category::firstOrCreate(
            ['event_id' => $eventRide->id, 'name' => '100K Century Ride'],
            [
                'target_distance_km' => 100.00,
                'bib_prefix' => '100K',
                'last_bib_sequence' => 0,
                'quota' => 200,
            ]
        );

        Package::firstOrCreate(
            ['event_id' => $eventRide->id, 'name' => 'Digital Finisher'],
            [
                'description' => 'E-BIB, Live Leaderboard Tracker, dan Finisher Certificate.',
                'price' => 50000.00,
                'base_weight_grams' => 0,
                'includes_jersey' => false,
                'includes_medal' => false,
                'requires_shipping' => false,
            ]
        );

        // 4. SPX Shipping Rates
        $this->call(SpxShippingRateSeeder::class);
    }
}
