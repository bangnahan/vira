<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\TemplateDesign;
use App\Services\CanvasRenderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class DesignerController extends Controller
{
    /**
     * Tampilkan antarmuka Visual Canvas Designer untuk e-BIB atau E-Sertifikat.
     */
    public function edit(Event $event, string $type): View
    {
        $type = strtoupper($type);
        if (! in_array($type, ['BIB', 'CERTIFICATE'], true)) {
            abort(404, 'Tipe designer tidak valid.');
        }

        $template = TemplateDesign::firstOrCreate(
            ['event_id' => $event->id, 'type' => $type],
            [
                'background_image_path' => null,
                'canvas_width' => ($type === 'BIB') ? 1200 : 1920,
                'canvas_height' => ($type === 'BIB') ? 800 : 1080,
                'elements_config' => ($type === 'BIB')
                    ? TemplateDesign::defaultBibConfig()
                    : TemplateDesign::defaultCertificateConfig(),
            ]
        );

        $elements = $template->elements_config ?: (($type === 'BIB')
            ? TemplateDesign::defaultBibConfig()
            : TemplateDesign::defaultCertificateConfig());

        return view('admin.designer.edit', compact('event', 'template', 'type', 'elements'));
    }

    /**
     * Simpan pembaruan koordinat dan tipografi elemen.
     */
    public function update(Request $request, Event $event, string $type): RedirectResponse|JsonResponse
    {
        $type = strtoupper($type);
        $template = TemplateDesign::firstOrCreate(
            ['event_id' => $event->id, 'type' => $type],
            [
                'background_image_path' => null,
                'canvas_width' => ($type === 'BIB') ? 1200 : 1920,
                'canvas_height' => ($type === 'BIB') ? 800 : 1080,
                'elements_config' => ($type === 'BIB')
                    ? TemplateDesign::defaultBibConfig()
                    : TemplateDesign::defaultCertificateConfig(),
            ]
        );

        $validated = $request->validate([
            'background_image' => ['nullable', 'image', 'mimes:png,jpg,jpeg', 'max:5120'], // Max 5MB
            'elements' => ['required', 'array'],
        ]);

        if ($request->hasFile('background_image')) {
            $path = $request->file('background_image')->store("templates/{$type}", 'public');
            $template->background_image_path = $path;
        }

        // Format array elements
        $formattedElements = [];
        foreach ($validated['elements'] as $el) {
            $formattedElements[] = [
                'key' => $el['key'],
                'label' => $el['label'] ?? $el['key'],
                'x' => (int) ($el['x'] ?? 0),
                'y' => (int) ($el['y'] ?? 0),
                'font_size' => (int) ($el['font_size'] ?? 32),
                'color' => $el['color'] ?? '#FFFFFF',
                'align' => $el['align'] ?? 'center',
                'visible' => ! empty($el['visible']),
                'size' => isset($el['size']) ? (int) $el['size'] : 100,
            ];
        }

        $template->elements_config = $formattedElements;
        $template->save();

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Konfigurasi tata letak dan koordinat '.$type.' berhasil disimpan!',
                'preview_url' => route('admin.designer.preview', ['event' => $event->id, 'type' => $type]).'?t='.time(),
                'elements' => $formattedElements,
            ]);
        }

        return back()->with('success', 'Konfigurasi tata letak dan koordinat '.$type.' berhasil disimpan!');
    }

    /**
     * Live test render canvas preview dengan data contoh.
     */
    public function preview(Request $request, Event $event, string $type, CanvasRenderService $renderService): Response
    {
        $type = strtoupper($type);
        $template = TemplateDesign::firstOrCreate(
            ['event_id' => $event->id, 'type' => $type],
            [
                'background_image_path' => null,
                'canvas_width' => ($type === 'BIB') ? 1200 : 1920,
                'canvas_height' => ($type === 'BIB') ? 800 : 1080,
                'elements_config' => ($type === 'BIB')
                    ? TemplateDesign::defaultBibConfig()
                    : TemplateDesign::defaultCertificateConfig(),
            ]
        );

        // Jika diminta background bersih tanpa teks (untuk kanvas drag-and-drop interaktif)
        if ($request->boolean('blank')) {
            $png = $renderService->renderBlankBackground($template, $type);

            return response($png, 200, [
                'Content-Type' => 'image/png',
                'Cache-Control' => 'no-cache, no-store, must-revalidate',
            ]);
        }

        // Data dummy untuk live preview lengkap (hasil cetak akhir)
        if ($type === 'BIB') {
            $sampleBib = '1001';
            $dummyData = [
                'bib_number' => $sampleBib,
                'participant_name' => 'BUDI PRATAMA',
                'category_name' => '10K Challenge Run',
                'event_name' => strtoupper($event->title),
                'qr_code' => route('submit.index', ['bib' => $sampleBib]),
            ];

            $png = $renderService->renderBib($template, $dummyData);
        } else {
            $sampleBib = '1001';
            $dummyData = [
                'participant_name' => 'BUDI PRATAMA',
                'category_name' => '10K Challenge Run',
                'bib_number' => $sampleBib,
                'total_distance' => '10.00 KM',
                'total_duration' => '00:52:14',
                'average_pace' => "5'13\" /km",
                'finish_date' => now()->format('d F Y'),
                'qr_code' => route('submit.index', ['bib' => $sampleBib]),
            ];

            $png = $renderService->renderCertificate($template, $dummyData);
        }

        return response($png, 200, [
            'Content-Type' => 'image/png',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
        ]);
    }
}
