<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TemplateDesign extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'type', // BIB, CERTIFICATE
        'background_image_path',
        'canvas_width',
        'canvas_height',
        'elements_config',
    ];

    protected function casts(): array
    {
        return [
            'canvas_width' => 'integer',
            'canvas_height' => 'integer',
            'elements_config' => 'array',
        ];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * Konfigurasi elemen default jika belum diatur.
     */
    public static function defaultBibConfig(): array
    {
        return [
            ['key' => 'bib_number', 'label' => 'Nomor e-BIB', 'x' => 600, 'y' => 450, 'font_size' => 84, 'font_family' => 'BebasNeue', 'color' => '#FF5500', 'align' => 'center', 'visible' => true],
            ['key' => 'participant_name', 'label' => 'Nama Peserta', 'x' => 600, 'y' => 560, 'font_size' => 38, 'font_family' => 'Inter-Bold', 'color' => '#FFFFFF', 'align' => 'center', 'visible' => true],
            ['key' => 'category_name', 'label' => 'Kategori Jarak', 'x' => 600, 'y' => 620, 'font_size' => 26, 'font_family' => 'Inter-Medium', 'color' => '#CBD5E1', 'align' => 'center', 'visible' => true],
            ['key' => 'event_name', 'label' => 'Nama Event', 'x' => 600, 'y' => 200, 'font_size' => 32, 'font_family' => 'Inter-Bold', 'color' => '#FFFFFF', 'align' => 'center', 'visible' => true],
            ['key' => 'qr_code', 'label' => 'QR Code Verifikasi', 'x' => 1040, 'y' => 660, 'size' => 100, 'visible' => true],
        ];
    }

    public static function defaultCertificateConfig(): array
    {
        return [
            ['key' => 'participant_name', 'label' => 'Nama Finisher', 'x' => 960, 'y' => 500, 'font_size' => 52, 'font_family' => 'Inter-Bold', 'color' => '#1E293B', 'align' => 'center', 'visible' => true],
            ['key' => 'category_name', 'label' => 'Kategori Lomba', 'x' => 960, 'y' => 580, 'font_size' => 28, 'font_family' => 'Inter-Medium', 'color' => '#64748B', 'align' => 'center', 'visible' => true],
            ['key' => 'bib_number', 'label' => 'Nomor BIB', 'x' => 960, 'y' => 630, 'font_size' => 24, 'font_family' => 'Inter-Medium', 'color' => '#64748B', 'align' => 'center', 'visible' => true],
            ['key' => 'total_distance', 'label' => 'Total Jarak', 'x' => 640, 'y' => 740, 'font_size' => 36, 'font_family' => 'Inter-Bold', 'color' => '#0F172A', 'align' => 'center', 'visible' => true],
            ['key' => 'total_duration', 'label' => 'Total Waktu', 'x' => 960, 'y' => 740, 'font_size' => 36, 'font_family' => 'Inter-Bold', 'color' => '#0F172A', 'align' => 'center', 'visible' => true],
            ['key' => 'average_pace', 'label' => 'Pace Rata-rata', 'x' => 1280, 'y' => 740, 'font_size' => 36, 'font_family' => 'Inter-Bold', 'color' => '#0F172A', 'align' => 'center', 'visible' => true],
            ['key' => 'finish_date', 'label' => 'Tanggal Finish', 'x' => 960, 'y' => 840, 'font_size' => 22, 'font_family' => 'Inter-Regular', 'color' => '#64748B', 'align' => 'center', 'visible' => true],
            ['key' => 'qr_code', 'label' => 'QR Code Keaslian', 'x' => 1700, 'y' => 880, 'size' => 120, 'visible' => true],
        ];
    }
}
