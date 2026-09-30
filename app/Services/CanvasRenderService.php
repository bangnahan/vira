<?php

namespace App\Services;

use App\Models\TemplateDesign;
use chillerlan\QRCode\Common\EccLevel;
use chillerlan\QRCode\Output\QRGdImagePNG;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use Illuminate\Support\Facades\Storage;

class CanvasRenderService
{
    protected string $boldFont;

    protected string $regularFont;

    public function __construct()
    {
        $this->boldFont = resource_path('fonts/athletic-bold.ttf');
        $this->regularFont = resource_path('fonts/sans-regular.ttf');
    }

    /**
     * Render kartu e-BIB dinamis berdasarkan konfigurasi koordinat TemplateDesign.
     *
     * @param  array<string, mixed>  $data
     * @return string Binary PNG content
     */
    public function renderBib(TemplateDesign $template, array $data): string
    {
        $width = $template->canvas_width ?: 1200;
        $height = $template->canvas_height ?: 800;

        $im = $this->loadOrCreateBackground($template, $width, $height, 'BIB');

        $elements = $template->elements_config ?: TemplateDesign::defaultBibConfig();

        foreach ($elements as $el) {
            if (empty($el['visible'])) {
                continue;
            }

            $key = $el['key'] ?? '';
            $text = (string) ($data[$key] ?? '');

            if ($key === 'qr_code') {
                $qrContent = (string) ($data['qr_code'] ?? ($data['bib_number'] ?? ''));
                $this->drawQrCode(
                    $im,
                    (int) ($el['x'] ?? 1040),
                    (int) ($el['y'] ?? 660),
                    (int) ($el['size'] ?? 100),
                    $qrContent
                );

                continue;
            }

            if ($text === '') {
                continue;
            }

            $x = (int) ($el['x'] ?? ($width / 2));
            $y = (int) ($el['y'] ?? ($height / 2));
            $fontSize = (int) ($el['font_size'] ?? 36);
            $colorHex = $el['color'] ?? '#FFFFFF';
            $align = strtolower($el['align'] ?? 'center');

            $fontFile = in_array($key, ['bib_number', 'event_name'], true)
                ? $this->boldFont
                : $this->regularFont;

            $this->drawAlignedText($im, $fontSize, 0, $x, $y, $colorHex, $fontFile, $text, $align);
        }

        ob_start();
        imagepng($im);
        $pngData = ob_get_clean();

        return $pngData;
    }

    /**
     * Render E-Certificate Finisher dinamis berdasarkan konfigurasi koordinat TemplateDesign.
     *
     * @param  array<string, mixed>  $data
     * @return string Binary PNG content
     */
    public function renderCertificate(TemplateDesign $template, array $data): string
    {
        $width = $template->canvas_width ?: 1920;
        $height = $template->canvas_height ?: 1080;

        $im = $this->loadOrCreateBackground($template, $width, $height, 'CERTIFICATE');

        $elements = $template->elements_config ?: TemplateDesign::defaultCertificateConfig();

        foreach ($elements as $el) {
            if (empty($el['visible'])) {
                continue;
            }

            $key = $el['key'] ?? '';
            $text = (string) ($data[$key] ?? '');

            if ($key === 'qr_code') {
                $qrContent = (string) ($data['qr_code'] ?? ($data['bib_number'] ?? ''));
                $this->drawQrCode(
                    $im,
                    (int) ($el['x'] ?? 1700),
                    (int) ($el['y'] ?? 880),
                    (int) ($el['size'] ?? 120),
                    $qrContent
                );

                continue;
            }

            if ($text === '') {
                continue;
            }

            $x = (int) ($el['x'] ?? ($width / 2));
            $y = (int) ($el['y'] ?? ($height / 2));
            $fontSize = (int) ($el['font_size'] ?? 32);
            $colorHex = $el['color'] ?? '#1E293B';
            $align = strtolower($el['align'] ?? 'center');

            $fontFile = in_array($key, ['participant_name', 'total_distance', 'total_duration', 'average_pace'], true)
                ? $this->boldFont
                : $this->regularFont;

            $this->drawAlignedText($im, $fontSize, 0, $x, $y, $colorHex, $fontFile, $text, $align);
        }

        ob_start();
        imagepng($im);
        $pngData = ob_get_clean();

        return $pngData;
    }

    /**
     * Render background bersih (polos tanpa teks elemen) untuk kanvas desainer drag-and-drop.
     */
    public function renderBlankBackground(TemplateDesign $template, string $type): string
    {
        $type = strtoupper($type);
        $width = $template->canvas_width ?: ($type === 'BIB' ? 1200 : 1920);
        $height = $template->canvas_height ?: ($type === 'BIB' ? 800 : 1080);

        $im = $this->loadOrCreateBackground($template, $width, $height, $type);

        ob_start();
        imagepng($im);
        $pngData = ob_get_clean();

        return $pngData;
    }

    /**
     * Muat background dari storage atau buat canvas atletis modern default.
     */
    protected function loadOrCreateBackground(TemplateDesign $template, int $width, int $height, string $type)
    {
        $bgPath = $template->background_image_path;

        if ($bgPath && Storage::disk('public')->exists($bgPath)) {
            $fullPath = Storage::disk('public')->path($bgPath);
            $info = getimagesize($fullPath);
            if ($info) {
                $rawImg = null;
                switch ($info[2]) {
                    case IMAGETYPE_JPEG:
                        $rawImg = imagecreatefromjpeg($fullPath);
                        break;
                    case IMAGETYPE_PNG:
                        $rawImg = imagecreatefrompng($fullPath);
                        break;
                }

                if ($rawImg) {
                    $srcW = (int) $info[0];
                    $srcH = (int) $info[1];

                    if ($srcW === $width && $srcH === $height) {
                        return $rawImg;
                    }

                    // Resample background agar presisi sama dengan canvas ($width x $height)
                    $resampled = imagecreatetruecolor($width, $height);
                    imagealphablending($resampled, true);
                    imagesavealpha($resampled, true);
                    imagecopyresampled($resampled, $rawImg, 0, 0, 0, 0, $width, $height, $srcW, $srcH);

                    return $resampled;
                }
            }
        }

        // Jika tidak ada background kustom, generate canvas default berkualitas tinggi
        $im = imagecreatetruecolor($width, $height);

        if ($type === 'BIB') {
            // Background Gelap Atletis untuk BIB
            $darkBg = imagecolorallocate($im, 11, 15, 25);
            $cardBg = imagecolorallocate($im, 19, 27, 46);
            $accentOrange = imagecolorallocate($im, 255, 85, 0);
            $accentCyan = imagecolorallocate($im, 0, 229, 255);
            $borderGrey = imagecolorallocate($im, 40, 50, 75);

            imagefill($im, 0, 0, $darkBg);

            // Kotak BIB dalam
            imagefilledrectangle($im, 40, 40, $width - 40, $height - 40, $cardBg);
            imagerectangle($im, 40, 40, $width - 40, $height - 40, $borderGrey);

            // Garis aksen atas & bawah
            imagefilledrectangle($im, 40, 40, $width - 40, 70, $accentOrange);
            imagefilledrectangle($im, 40, $height - 70, $width - 40, $height - 40, $accentCyan);

            // Mockup Lubang Peniti BIB di 4 sudut
            $holeColor = imagecolorallocate($im, 8, 11, 18);
            imagefilledellipse($im, 80, 80, 24, 24, $holeColor);
            imagefilledellipse($im, $width - 80, 80, 24, 24, $holeColor);
            imagefilledellipse($im, 80, $height - 80, 24, 24, $holeColor);
            imagefilledellipse($im, $width - 80, $height - 80, 24, 24, $holeColor);
        } else {
            // Background Sertifikat Elegan (Cream White dengan Gold & Navy Border)
            $ivoryBg = imagecolorallocate($im, 252, 250, 245);
            $gold = imagecolorallocate($im, 212, 175, 55);
            $navy = imagecolorallocate($im, 15, 23, 42);

            imagefill($im, 0, 0, $ivoryBg);

            // Border ganda elegan
            imagefilledrectangle($im, 30, 30, $width - 30, $height - 30, $ivoryBg);
            imagesetthickness($im, 6);
            imagerectangle($im, 40, 40, $width - 40, $height - 40, $gold);
            imagesetthickness($im, 2);
            imagerectangle($im, 55, 55, $width - 55, $height - 55, $navy);
            imagesetthickness($im, 1);
        }

        return $im;
    }

    /**
     * Gambar teks dengan perataan (left, center, right) berdasarkan koordinat X dan Y.
     * Menggunakan perhitungan bounding box TrueType agar posisi teks 100% presisi
     * sama persis dengan visual preview di CSS (transform: translate(..., -50%)).
     */
    protected function drawAlignedText($im, int $size, int $angle, int $x, int $y, string $hexColor, string $fontFile, string $text, string $align): void
    {
        $rgb = $this->hexToRgb($hexColor);
        $color = imagecolorallocate($im, $rgb['r'], $rgb['g'], $rgb['b']);

        $box = imagettfbbox($size, $angle, $fontFile, $text);
        $minX = min($box[0], $box[6]);
        $maxX = max($box[2], $box[4]);
        $textWidth = $maxX - $minX;

        $minY = min($box[1], $box[3], $box[5], $box[7]);
        $maxY = max($box[1], $box[3], $box[5], $box[7]);
        $textHeight = $maxY - $minY;

        // Hitung koordinat horizontal X
        $finalX = $x;
        if ($align === 'center') {
            $finalX = (int) round($x - ($textWidth / 2) - $minX);
        } elseif ($align === 'right') {
            $finalX = (int) round($x - $textWidth - $minX);
        } else {
            $finalX = (int) round($x - $minX);
        }

        // Hitung koordinat vertikal Y (baseline) agar teks terpusat secara vertikal tepat di $y
        // Meniru perilaku CSS `top: Y%; transform: translateY(-50%);`
        $finalY = (int) round($y + ($textHeight / 2) - $maxY);

        imagettftext($im, $size, $angle, $finalX, $finalY, $color, $fontFile, $text);
    }

    /**
     * Gambar QR code verifikasi 2D asli yang dapat di-scan oleh smartphone.
     * Koordinat X dan Y merujuk ke titik tengah QR Code, sama persis dengan CSS transform: translate(-50%, -50%).
     */
    protected function drawQrCode($im, int $x, int $y, int $size, string $code): void
    {
        $code = trim($code);
        if ($code === '') {
            $code = 'VERIFIED';
        }

        // Jika hanya nomor BIB atau identifier tanpa http, arahkan ke route submit verifikasi
        if (! str_starts_with($code, 'http://') && ! str_starts_with($code, 'https://')) {
            $code = route('submit.index', ['bib' => $code]);
        }

        $drawX = (int) round($x - ($size / 2));
        $drawY = (int) round($y - ($size / 2));

        try {
            $options = new QROptions([
                'outputInterface' => QRGdImagePNG::class,
                'returnResource' => true,
                'eccLevel' => EccLevel::M,
                'addQuietzone' => true,
                'quietzoneSize' => 2,
            ]);

            $qrCode = new QRCode($options);
            /** @var \GdImage $qrImage */
            $qrImage = $qrCode->render($code);

            $srcW = imagesx($qrImage);
            $srcH = imagesy($qrImage);

            // Background putih solid di balik QR code agar kontras di latar belakang gelap/motif
            $white = imagecolorallocate($im, 255, 255, 255);
            imagefilledrectangle($im, $drawX, $drawY, $drawX + $size, $drawY + $size, $white);

            // Copy resampled QR code langsung ke kanvas dengan resolusi tajam
            imagecopyresampled($im, $qrImage, $drawX, $drawY, 0, 0, $size, $size, $srcW, $srcH);
        } catch (\Throwable $e) {
            // Fallback cadangan jika terjadi error render QR
            $white = imagecolorallocate($im, 255, 255, 255);
            $black = imagecolorallocate($im, 10, 10, 10);
            imagefilledrectangle($im, $drawX, $drawY, $drawX + $size, $drawY + $size, $white);
            imagerectangle($im, $drawX, $drawY, $drawX + $size, $drawY + $size, $black);
            $fontSize = max(8, (int) ($size * 0.08));
            $this->drawAlignedText($im, $fontSize, 0, $x, $y, '#000000', $this->boldFont, substr($code, 0, 10), 'center');
        }
    }

    protected function hexToRgb(string $hex): array
    {
        $hex = ltrim($hex, '#');
        if (strlen($hex) === 3) {
            $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
        }

        return [
            'r' => hexdec(substr($hex, 0, 2)),
            'g' => hexdec(substr($hex, 2, 2)),
            'b' => hexdec(substr($hex, 4, 2)),
        ];
    }
}
