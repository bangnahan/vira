<?php

namespace App\Console\Commands;

use App\Models\SpxShippingRate;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use XMLReader;
use ZipArchive;

class ImportSpxRatesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'spx:import-rates {--file= : Path ke file XLSX rate card SPX}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import data tarif ongkir SPX dari file Excel XLSX ke tabel database spx_shipping_rates';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $defaultPath = base_path('Data dari SPX/[CONFIDENTIAL] SPXID RATE CARD - For PT MANA PERSADA KOMPUTER (Pinusrun) - KAB TANGERANG.xlsx');
        $filePath = $this->option('file') ?: $defaultPath;

        if (! file_exists($filePath)) {
            $this->error("Berkas tidak ditemukan di: {$filePath}");

            return self::FAILURE;
        }

        $this->info('Membuka berkas Excel SPX: '.basename($filePath));

        $zip = new ZipArchive;
        if ($zip->open($filePath) !== true) {
            $this->error('Gagal membuka arsip XLSX.');

            return self::FAILURE;
        }

        // 1. Baca shared strings
        $this->info('Membaca data string teks...');
        $sharedStrings = [];
        $sharedStringsXml = $zip->getFromName('xl/sharedStrings.xml');

        if ($sharedStringsXml) {
            $reader = new XMLReader;
            $reader->XML($sharedStringsXml);
            $currentString = '';

            while ($reader->read()) {
                if ($reader->nodeType === XMLReader::ELEMENT && $reader->name === 'si') {
                    $currentString = '';
                } elseif ($reader->nodeType === XMLReader::ELEMENT && $reader->name === 't') {
                    $currentString .= $reader->readString();
                } elseif ($reader->nodeType === XMLReader::END_ELEMENT && $reader->name === 'si') {
                    $sharedStrings[] = $currentString;
                }
            }
            $reader->close();
        }

        $this->info('Total shared strings terbaca: '.count($sharedStrings));

        // 2. Baca sheet1 data baris demi baris secara streaming
        $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
        $zip->close();

        if (! $sheetXml) {
            $this->error('Sheet1 tidak ditemukan di dalam XLSX.');

            return self::FAILURE;
        }

        $this->info('Mengimpor data tarif ke database...');
        $reader = new XMLReader;
        $reader->XML($sheetXml);

        $now = now();
        $batch = [];
        $totalImported = 0;
        $rowNumber = 0;

        DB::beginTransaction();
        try {
            // Bersihkan data lama jika ada
            SpxShippingRate::truncate();

            while ($reader->read()) {
                if ($reader->nodeType === XMLReader::ELEMENT && $reader->name === 'row') {
                    $rowNumber = (int) $reader->getAttribute('r');
                    if ($rowNumber <= 3) {
                        // Lewati header (baris 1, 2, 3)
                        continue;
                    }

                    // Baca sel dalam baris ini
                    $cells = [];
                    $rowSubtree = $reader->readOuterXml();
                    $cellReader = new XMLReader;
                    $cellReader->XML($rowSubtree);

                    while ($cellReader->read()) {
                        if ($cellReader->nodeType === XMLReader::ELEMENT && $cellReader->name === 'c') {
                            $cellRef = $cellReader->getAttribute('r');
                            $col = preg_replace('/[0-9]/', '', $cellRef);
                            $type = $cellReader->getAttribute('t');

                            // Baca nilai
                            $valReader = new XMLReader;
                            $valReader->XML($cellReader->readOuterXml());
                            $val = null;
                            while ($valReader->read()) {
                                if ($valReader->nodeType === XMLReader::ELEMENT && $valReader->name === 'v') {
                                    $rawVal = $valReader->readString();
                                    $val = ($type === 's') ? ($sharedStrings[(int) $rawVal] ?? '') : $rawVal;
                                    break;
                                }
                            }
                            $valReader->close();
                            $cells[$col] = $val;
                        }
                    }
                    $cellReader->close();

                    $destCity = trim($cells['B'] ?? '');
                    $destDistrict = trim($cells['C'] ?? '');
                    $rateHemat = (float) ($cells['D'] ?? 0);
                    $slaHemat = (int) ($cells['E'] ?? 7);
                    $rateRegular = (float) ($cells['F'] ?? 0);
                    $slaRegular = (int) ($cells['G'] ?? 3);

                    if ($destCity !== '' && $destDistrict !== '') {
                        $batch[] = [
                            'origin_city' => 'KAB. TANGERANG',
                            'destination_city' => $destCity,
                            'destination_district' => $destDistrict,
                            'rate_hemat' => $rateHemat,
                            'sla_hemat_days' => $slaHemat,
                            'rate_regular' => $rateRegular,
                            'sla_regular_days' => $slaRegular,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ];

                        if (count($batch) >= 500) {
                            SpxShippingRate::insert($batch);
                            $totalImported += count($batch);
                            $batch = [];
                        }
                    }
                }
            }

            if (! empty($batch)) {
                SpxShippingRate::insert($batch);
                $totalImported += count($batch);
            }

            DB::commit();
            $reader->close();

            $totalCities = SpxShippingRate::distinct('destination_city')->count();
            $this->info("✓ Berhasil mengimpor {$totalImported} data tarif SPX dari KAB. TANGERANG ke {$totalCities} Kota/Kabupaten seluruh Indonesia.");

            return self::SUCCESS;
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->error('Terjadi kesalahan saat import: '.$e->getMessage());

            return self::FAILURE;
        }
    }
}
