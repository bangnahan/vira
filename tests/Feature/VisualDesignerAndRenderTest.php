<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Event;
use App\Models\Package;
use App\Models\Participant;
use App\Models\Registration;
use App\Models\TemplateDesign;
use App\Models\User;
use App\Services\CanvasRenderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class VisualDesignerAndRenderTest extends TestCase
{
    use RefreshDatabase;

    protected Event $event;

    protected Registration $registration;

    protected function setUp(): void
    {
        parent::setUp();

        $admin = User::create([
            'name' => 'Admin Designer Test',
            'email' => 'admindesigner@vira.id',
            'password' => Hash::make('password'),
            'role' => 'SUPER_ADMIN',
        ]);
        $this->actingAs($admin);

        $this->event = Event::create([
            'title' => 'Merdeka Virtual Run 2026',
            'slug' => 'merdeka-virtual-run-2026',
            'event_code' => 'MVR26',
            'activity_type' => 'RUN',
            'submission_mode' => 'CUMULATIVE',
            'race_type' => 'CHALLENGE',
            'description' => 'Test event.',
            'rules_and_terms' => 'Rules.',
            'registration_start' => now()->subDays(1),
            'registration_end' => now()->addDays(30),
            'race_start' => now(),
            'race_end' => now()->addDays(30),
            'is_active' => true,
        ]);

        $category = Category::create([
            'event_id' => $this->event->id,
            'name' => '10K Challenge',
            'target_distance_km' => 10.00,
            'bib_prefix' => '10K',
            'last_bib_sequence' => 1,
        ]);

        $package = Package::create([
            'event_id' => $this->event->id,
            'name' => 'Digital Pack',
            'price' => 50000.00,
        ]);

        $participant = Participant::create([
            'full_name' => 'Ahmad Wahyudi',
            'email' => 'ahmad@example.com',
            'phone_number' => '081234567890',
            'gender' => 'MALE',
            'date_of_birth' => '1992-08-17',
        ]);

        $this->registration = Registration::create([
            'event_id' => $this->event->id,
            'category_id' => $category->id,
            'package_id' => $package->id,
            'participant_id' => $participant->id,
            'bib_number' => 'MVR26-10K-0001',
            'payment_status' => 'PAID',
            'total_distance_km' => 10.00,
            'total_duration_seconds' => 3120, // 52m 00s
            'finisher_status' => 'FINISHED',
            'finished_at' => now(),
        ]);
    }

    public function test_canvas_render_service_renders_valid_png_for_bib_and_cert(): void
    {
        $service = new CanvasRenderService;

        $templateBib = TemplateDesign::firstOrCreate(
            ['event_id' => $this->event->id, 'type' => 'BIB'],
            [
                'canvas_width' => 1200,
                'canvas_height' => 800,
                'elements_config' => TemplateDesign::defaultBibConfig(),
            ]
        );

        $pngBib = $service->renderBib($templateBib, [
            'bib_number' => 'MVR26-10K-0001',
            'participant_name' => 'AHMAD WAHYUDI',
            'category_name' => '10K Challenge',
            'event_name' => 'MERDEKA VIRTUAL RUN 2026',
            'qr_code' => 'MVR26-10K-0001',
        ]);

        $this->assertNotEmpty($pngBib);
        $this->assertStringStartsWith("\x89PNG\r\n\x1a\n", $pngBib); // PNG Magic bytes

        $templateCert = TemplateDesign::firstOrCreate(
            ['event_id' => $this->event->id, 'type' => 'CERTIFICATE'],
            [
                'canvas_width' => 1920,
                'canvas_height' => 1080,
                'elements_config' => TemplateDesign::defaultCertificateConfig(),
            ]
        );

        $pngCert = $service->renderCertificate($templateCert, [
            'participant_name' => 'AHMAD WAHYUDI',
            'category_name' => '10K Challenge',
            'bib_number' => 'MVR26-10K-0001',
            'total_distance' => '10.00 KM',
            'total_duration' => '00:52:00',
            'average_pace' => "5'12\" /km",
            'finish_date' => now()->format('d F Y'),
            'qr_code' => 'FINISHER-0001',
        ]);

        $this->assertNotEmpty($pngCert);
        $this->assertStringStartsWith("\x89PNG\r\n\x1a\n", $pngCert);
    }

    public function test_admin_can_view_designer_and_preview(): void
    {
        $viewRes = $this->get('/admin/events/'.$this->event->id.'/designer/BIB');
        $viewRes->assertStatus(200)
            ->assertSee('Desainer Kartu e-BIB')
            ->assertSee('Live Preview');

        $previewRes = $this->get('/admin/events/'.$this->event->id.'/designer/BIB/preview');
        $previewRes->assertStatus(200)
            ->assertHeader('Content-Type', 'image/png');
    }

    public function test_admin_can_update_element_coordinates(): void
    {
        $updateRes = $this->post('/admin/events/'.$this->event->id.'/designer/BIB', [
            'elements' => [
                [
                    'key' => 'bib_number',
                    'label' => 'Nomor e-BIB',
                    'x' => 650,
                    'y' => 480,
                    'font_size' => 90,
                    'color' => '#00E5FF',
                    'align' => 'center',
                    'visible' => 1,
                ],
            ],
        ]);

        $updateRes->assertStatus(302);

        $template = TemplateDesign::where('event_id', $this->event->id)->where('type', 'BIB')->first();
        $this->assertNotNull($template);
        $this->assertEquals(650, $template->elements_config[0]['x']);
        $this->assertEquals('#00E5FF', $template->elements_config[0]['color']);
    }

    public function test_participant_can_download_ebib_png(): void
    {
        $res = $this->get('/p/'.$this->registration->bib_number.'/download-bib');
        $res->assertStatus(200)
            ->assertHeader('Content-Type', 'image/png');
    }

    public function test_participant_can_download_certificate_png(): void
    {
        $res = $this->get('/p/'.$this->registration->bib_number.'/download-certificate');
        $res->assertStatus(200)
            ->assertHeader('Content-Type', 'image/png');
    }
}
