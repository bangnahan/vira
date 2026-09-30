<?php

namespace App\Http\Controllers;

use App\Models\Registration;
use App\Models\TemplateDesign;
use App\Services\CanvasRenderService;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ParticipantDownloadController extends Controller
{
    /**
     * Download kartu e-BIB peserta beresolusi tinggi (PNG).
     */
    public function downloadBib(string $identifier, CanvasRenderService $renderService): StreamedResponse
    {
        $registration = Registration::with(['event', 'category', 'participant'])
            ->where('access_token', $identifier)
            ->orWhere('bib_number', $identifier)
            ->firstOrFail();

        if (! $registration->isPaid() || empty($registration->bib_number)) {
            abort(403, 'Nomor e-BIB belum aktif atau pembayaran belum selesai.');
        }

        $template = TemplateDesign::firstOrCreate(
            ['event_id' => $registration->event_id, 'type' => 'BIB'],
            [
                'background_image_path' => null,
                'canvas_width' => 1200,
                'canvas_height' => 800,
                'elements_config' => TemplateDesign::defaultBibConfig(),
            ]
        );

        $data = [
            'bib_number' => $registration->bib_number,
            'participant_name' => strtoupper($registration->participant->full_name),
            'category_name' => $registration->category->name,
            'event_name' => strtoupper($registration->event->title),
            'qr_code' => route('submit.index', ['bib' => $registration->bib_number]),
        ];

        $pngBinary = $renderService->renderBib($template, $data);
        $fileName = "ebib-{$registration->bib_number}.png";

        return response()->streamDownload(function () use ($pngBinary) {
            echo $pngBinary;
        }, $fileName, [
            'Content-Type' => 'image/png',
            'Content-Length' => strlen($pngBinary),
        ]);
    }

    /**
     * Download E-Certificate resmi finisher (PNG).
     */
    public function downloadCertificate(string $identifier, CanvasRenderService $renderService): StreamedResponse
    {
        $registration = Registration::with(['event', 'category', 'participant'])
            ->where('access_token', $identifier)
            ->orWhere('bib_number', $identifier)
            ->firstOrFail();

        if (! $registration->isFinisher()) {
            abort(403, 'Sertifikat hanya dapat diunduh setelah peserta berhasil menuntaskan target jarak (Finisher).');
        }

        $template = TemplateDesign::firstOrCreate(
            ['event_id' => $registration->event_id, 'type' => 'CERTIFICATE'],
            [
                'background_image_path' => null,
                'canvas_width' => 1920,
                'canvas_height' => 1080,
                'elements_config' => TemplateDesign::defaultCertificateConfig(),
            ]
        );

        $data = [
            'participant_name' => strtoupper($registration->participant->full_name),
            'category_name' => $registration->category->name,
            'bib_number' => $registration->bib_number,
            'total_distance' => number_format($registration->total_distance_km, 2).' KM',
            'total_duration' => $registration->formattedTotalDuration(),
            'average_pace' => $registration->averagePace(),
            'finish_date' => $registration->finished_at ? $registration->finished_at->format('d F Y') : now()->format('d F Y'),
            'qr_code' => route('submit.index', ['bib' => $registration->bib_number]),
        ];

        $pngBinary = $renderService->renderCertificate($template, $data);
        $fileName = "certificate-{$registration->bib_number}.png";

        return response()->streamDownload(function () use ($pngBinary) {
            echo $pngBinary;
        }, $fileName, [
            'Content-Type' => 'image/png',
            'Content-Length' => strlen($pngBinary),
        ]);
    }
}
