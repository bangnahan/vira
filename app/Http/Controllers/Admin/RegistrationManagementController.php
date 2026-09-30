<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Registration;
use App\Models\RegistrationAddOn;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class RegistrationManagementController extends Controller
{
    /**
     * Tampilkan data pendaftar & monitoring status pembayaran.
     */
    public function index(Request $request): View
    {
        $events = Event::orderBy('title')->get(['id', 'title', 'event_code']);

        $query = Registration::with([
            'participant',
            'event',
            'category',
            'package',
            'payment',
            'shippingAddress',
            'registrationAddOns.addOn',
            'registrationAddOns.variant',
        ]);

        // Filter Event
        if ($request->filled('event_id')) {
            $query->where('event_id', $request->input('event_id'));
        }

        // Filter Status Pembayaran
        if ($request->filled('payment_status') && $request->input('payment_status') !== 'ALL') {
            $query->where('payment_status', $request->input('payment_status'));
        }

        // Filter Status Finisher
        if ($request->filled('finisher_status') && $request->input('finisher_status') !== 'ALL') {
            $query->where('finisher_status', $request->input('finisher_status'));
        }

        // Filter Pencarian Teks (Nama, Email, No HP, BIB, Kode Invoice)
        if ($request->filled('search')) {
            $term = trim($request->input('search'));
            $query->where(function ($q) use ($term) {
                $q->where('bib_number', 'like', "%{$term}%")
                    ->orWhere('access_token', 'like', "%{$term}%")
                    ->orWhereHas('participant', function ($p) use ($term) {
                        $p->where('full_name', 'like', "%{$term}%")
                            ->orWhere('email', 'like', "%{$term}%")
                            ->orWhere('phone_number', 'like', "%{$term}%");
                    })
                    ->orWhereHas('payment', function ($pay) use ($term) {
                        $pay->where('merchant_ref', 'like', "%{$term}%")
                            ->orWhere('tripay_reference', 'like', "%{$term}%");
                    });
            });
        }

        // Hitung Statistik Dashboard
        $baseStatQuery = clone $query;
        $totalRegistrations = $baseStatQuery->count();
        $totalPaid = (clone $baseStatQuery)->where('payment_status', 'PAID')->count();
        $totalUnpaid = (clone $baseStatQuery)->where('payment_status', 'UNPAID')->count();
        $totalFinished = (clone $baseStatQuery)->where('finisher_status', 'FINISHED')->count();

        // Hitung total nominal rupiah yang telah terbayar
        $totalRevenue = (clone $baseStatQuery)
            ->where('payment_status', 'PAID')
            ->join('payments', 'registrations.id', '=', 'payments.registration_id')
            ->sum('payments.total_amount');

        $registrations = $query->latest('id')->paginate(20)->withQueryString();

        return view('admin.registrations.index', compact(
            'registrations',
            'events',
            'totalRegistrations',
            'totalPaid',
            'totalUnpaid',
            'totalFinished',
            'totalRevenue'
        ));
    }

    /**
     * Export data pendaftar & pembayaran ke file CSV / Excel.
     */
    public function export(Request $request): StreamedResponse
    {
        $query = Registration::with([
            'participant',
            'event',
            'category',
            'package',
            'payment',
            'shippingAddress',
            'registrationAddOns.addOn',
            'registrationAddOns.variant',
        ]);

        if ($request->filled('event_id')) {
            $query->where('event_id', $request->input('event_id'));
        }

        if ($request->filled('payment_status') && $request->input('payment_status') !== 'ALL') {
            $query->where('payment_status', $request->input('payment_status'));
        }

        if ($request->filled('finisher_status') && $request->input('finisher_status') !== 'ALL') {
            $query->where('finisher_status', $request->input('finisher_status'));
        }

        if ($request->filled('search')) {
            $term = trim($request->input('search'));
            $query->where(function ($q) use ($term) {
                $q->where('bib_number', 'like', "%{$term}%")
                    ->orWhere('access_token', 'like', "%{$term}%")
                    ->orWhereHas('participant', function ($p) use ($term) {
                        $p->where('full_name', 'like', "%{$term}%")
                            ->orWhere('email', 'like', "%{$term}%")
                            ->orWhere('phone_number', 'like', "%{$term}%");
                    })
                    ->orWhereHas('payment', function ($pay) use ($term) {
                        $pay->where('merchant_ref', 'like', "%{$term}%")
                            ->orWhere('tripay_reference', 'like', "%{$term}%");
                    });
            });
        }

        $preset = $request->input('preset', 'full'); // full, logistics, finance

        $eventSuffix = 'semua-event';
        if ($request->filled('event_id')) {
            $selectedEvent = Event::find($request->input('event_id'));
            if ($selectedEvent) {
                $eventSuffix = $selectedEvent->event_code;
            }
        }

        $presetPrefix = match ($preset) {
            'logistics' => 'logistik-spx-',
            'finance' => 'rekap-keuangan-',
            default => 'data-peserta-master-',
        };

        $filename = 'vira-'.$presetPrefix.$eventSuffix.'-'.date('Ymd-His').'.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        return new StreamedResponse(function () use ($query, $preset) {
            $handle = fopen('php://output', 'w');

            // Tambahkan UTF-8 BOM agar terbaca sempurna di Microsoft Excel
            fwrite($handle, "\xEF\xBB\xBF");

            // Header Kolom CSV berdasarkan preset
            if ($preset === 'logistics') {
                fputcsv($handle, [
                    'No',
                    'ID Registrasi',
                    'Nama Event',
                    'Nomor e-BIB',
                    'Nama Penerima Paket',
                    'No. HP Penerima',
                    'Paket Pendaftaran',
                    'Ukuran Jersey',
                    'Add-ons / Merchandise Tambahan',
                    'Status Pembayaran',
                    'Waktu Bayar Lunas',
                    'Ekspedisi & Layanan',
                    'Provinsi',
                    'Kota / Kabupaten',
                    'Kecamatan',
                    'Kode Pos',
                    'Alamat Pengiriman Lengkap',
                ]);
            } elseif ($preset === 'finance') {
                fputcsv($handle, [
                    'No',
                    'ID Registrasi',
                    'Tanggal Daftar',
                    'Nama Event',
                    'Nomor e-BIB',
                    'Nama Peserta',
                    'No. HP Peserta',
                    'Merchant Ref Invoice',
                    'Metode Pembayaran',
                    'Status Pembayaran',
                    'Biaya Paket (Rp)',
                    'Biaya Ongkir SPX (Rp)',
                    'Biaya Admin Tripay (Rp)',
                    'Total Tagihan (Rp)',
                    'Waktu Bayar Lunas',
                ]);
            } else {
                fputcsv($handle, [
                    'No',
                    'ID Registrasi',
                    'Tanggal Daftar',
                    'Nama Event',
                    'Kode Event',
                    'Kategori Jarak',
                    'Target Jarak (KM)',
                    'Nomor e-BIB',
                    'Nama Lengkap Peserta',
                    'Email',
                    'No. Handphone / WhatsApp',
                    'Jenis Kelamin',
                    'Tanggal Lahir',
                    'Golongan Darah',
                    'Kontak Darurat (Nama)',
                    'Kontak Darurat (No HP)',
                    'Paket Pendaftaran',
                    'Ukuran Jersey',
                    'Add-ons / Merchandise Tambahan',
                    'Merchant Ref Invoice',
                    'Metode Pembayaran',
                    'Status Pembayaran',
                    'Biaya Paket (Rp)',
                    'Biaya Ongkir SPX (Rp)',
                    'Biaya Admin Tripay (Rp)',
                    'Total Tagihan (Rp)',
                    'Waktu Bayar Lunas',
                    'Ekspedisi & Layanan',
                    'Nama Penerima Paket',
                    'No. HP Penerima',
                    'Provinsi',
                    'Kota / Kabupaten',
                    'Kecamatan',
                    'Kode Pos',
                    'Alamat Pengiriman Lengkap',
                    'Total Jarak Tercapai (KM)',
                    'Total Waktu Aktivitas (Menit)',
                    'Status Finisher',
                    'Tanggal & Waktu Finish',
                ]);
            }

            $no = 1;
            $query->chunk(100, function ($registrations) use ($handle, &$no, $preset) {
                foreach ($registrations as $reg) {
                    $p = $reg->participant;
                    $pay = $reg->payment;
                    $ship = $reg->shippingAddress;

                    // Rangkum add-ons
                    $addonsText = '-';
                    if ($reg->registrationAddOns->count() > 0) {
                        $addonsArr = [];
                        foreach ($reg->registrationAddOns as $item) {
                            $addonName = $item->addOn?->name ?? 'Add-on';
                            $varName = $item->variant ? " ({$item->variant->variant_name})" : '';
                            $addonsArr[] = "{$addonName}{$varName} x{$item->quantity}";
                        }
                        $addonsText = implode('; ', $addonsArr);
                    }

                    if ($preset === 'logistics') {
                        fputcsv($handle, [
                            $no++,
                            $reg->id,
                            $reg->event?->title ?? '-',
                            $reg->bib_number ?: 'Belum Terbit (Pending Bayar)',
                            $ship?->recipient_name ?? $p?->full_name ?? '-',
                            $ship?->recipient_phone ?? $p?->phone_number ?? '-',
                            $reg->package?->name ?? '-',
                            $ship?->jersey_size ?? '-',
                            $addonsText,
                            $reg->payment_status,
                            $pay?->paid_at ? $pay->paid_at->format('Y-m-d H:i:s') : '-',
                            $ship?->spx_service ?? ($reg->package?->requires_shipping ? 'SPX_REGULAR' : '-'),
                            $ship?->province ?? '-',
                            $ship?->city ?? $ship?->destination_city ?? '-',
                            $ship?->district ?? $ship?->destination_district ?? '-',
                            $ship?->postal_code ?? '-',
                            $ship?->address_detail ?? '-',
                        ]);
                    } elseif ($preset === 'finance') {
                        fputcsv($handle, [
                            $no++,
                            $reg->id,
                            $reg->created_at ? $reg->created_at->format('Y-m-d H:i:s') : '-',
                            $reg->event?->title ?? '-',
                            $reg->bib_number ?: 'Belum Terbit (Pending Bayar)',
                            $p?->full_name ?? '-',
                            $p?->phone_number ?? '-',
                            $pay?->merchant_ref ?? '-',
                            $pay?->payment_method ?? '-',
                            $reg->payment_status,
                            $pay?->amount ? (float) $pay->amount : 0,
                            $pay?->shipping_cost ? (float) $pay->shipping_cost : 0,
                            $pay?->admin_fee ? (float) $pay->admin_fee : 0,
                            $pay?->total_amount ? (float) $pay->total_amount : 0,
                            $pay?->paid_at ? $pay->paid_at->format('Y-m-d H:i:s') : '-',
                        ]);
                    } else {
                        fputcsv($handle, [
                            $no++,
                            $reg->id,
                            $reg->created_at ? $reg->created_at->format('Y-m-d H:i:s') : '-',
                            $reg->event?->title ?? '-',
                            $reg->event?->event_code ?? '-',
                            $reg->category?->name ?? '-',
                            $reg->category?->target_distance_km ? (float) $reg->category->target_distance_km : 0,
                            $reg->bib_number ?: 'Belum Terbit (Pending Bayar)',
                            $p?->full_name ?? '-',
                            $p?->email ?? '-',
                            $p?->phone_number ?? '-',
                            $p?->gender ?? '-',
                            $p?->date_of_birth ? $p->date_of_birth->format('Y-m-d') : '-',
                            $p?->blood_type ?? '-',
                            $p?->emergency_contact_name ?? '-',
                            $p?->emergency_contact_phone ?? '-',
                            $reg->package?->name ?? '-',
                            $ship?->jersey_size ?? '-',
                            $addonsText,
                            $pay?->merchant_ref ?? '-',
                            $pay?->payment_method ?? '-',
                            $reg->payment_status,
                            $pay?->amount ? (float) $pay->amount : 0,
                            $pay?->shipping_cost ? (float) $pay->shipping_cost : 0,
                            $pay?->admin_fee ? (float) $pay->admin_fee : 0,
                            $pay?->total_amount ? (float) $pay->total_amount : 0,
                            $pay?->paid_at ? $pay->paid_at->format('Y-m-d H:i:s') : '-',
                            $ship?->spx_service ?? ($reg->package?->requires_shipping ? 'SPX_REGULAR' : '-'),
                            $ship?->recipient_name ?? '-',
                            $ship?->recipient_phone ?? '-',
                            $ship?->province ?? '-',
                            $ship?->city ?? $ship?->destination_city ?? '-',
                            $ship?->district ?? $ship?->destination_district ?? '-',
                            $ship?->postal_code ?? '-',
                            $ship?->address_detail ?? '-',
                            (float) $reg->total_distance_km,
                            round(((int) $reg->total_duration_seconds) / 60),
                            $reg->finisher_status,
                            $reg->finished_at ? $reg->finished_at->format('Y-m-d H:i:s') : '-',
                        ]);
                    }
                }
            });

            fclose($handle);
        }, 200, $headers);
    }

    /**
     * Tampilkan rekapitulasi kebutuhan produksi jersey (PAID & UNPAID) per paket & per add-on.
     */
    public function jerseyOrders(Request $request): View
    {
        $events = Event::orderBy('title')->get(['id', 'title', 'event_code']);

        $selectedEventId = $request->input('event_id');
        $paymentStatus = $request->input('payment_status', 'PAID'); // Default: PAID

        // Ukuran jersey standar VIRA
        $standardSizes = ['XS', 'S', 'M', 'L', 'XL', '2XL', '3XL', '4XL', '5XL'];

        // 1. Rekap Jersey dari Paket Pendaftaran
        $packageQuery = Registration::query()
            ->join('packages', 'registrations.package_id', '=', 'packages.id')
            ->leftJoin('shipping_addresses', 'registrations.id', '=', 'shipping_addresses.registration_id')
            ->leftJoin('events', 'registrations.event_id', '=', 'events.id');

        if ($selectedEventId) {
            $packageQuery->where('registrations.event_id', $selectedEventId);
        }

        if ($paymentStatus !== 'ALL') {
            $packageQuery->where('registrations.payment_status', $paymentStatus);
        }

        $packageRaw = $packageQuery
            ->whereNotNull('shipping_addresses.jersey_size')
            ->where('shipping_addresses.jersey_size', '!=', '')
            ->selectRaw('packages.id as package_id, packages.name as package_name, events.title as event_title, shipping_addresses.jersey_size, count(*) as qty')
            ->groupBy('packages.id', 'packages.name', 'events.title', 'shipping_addresses.jersey_size')
            ->get();

        // Matriks Paket: [$packageId => ['name' => ..., 'event' => ..., 'sizes' => ['S' => 10, ...], 'total' => 50]]
        $packageMatrix = [];
        $packageSizeTotals = array_fill_keys($standardSizes, 0);
        $packageSizeTotals['Lainnya'] = 0;
        $totalPackageJerseys = 0;

        foreach ($packageRaw as $row) {
            $pkgId = $row->package_id;
            if (! isset($packageMatrix[$pkgId])) {
                $packageMatrix[$pkgId] = [
                    'package_id' => $pkgId,
                    'package_name' => $row->package_name,
                    'event_title' => $row->event_title,
                    'sizes' => array_fill_keys($standardSizes, 0),
                    'total' => 0,
                ];
                $packageMatrix[$pkgId]['sizes']['Lainnya'] = 0;
            }

            $size = strtoupper(trim($row->jersey_size));
            $qty = (int) $row->qty;

            if (in_array($size, $standardSizes, true)) {
                $packageMatrix[$pkgId]['sizes'][$size] += $qty;
                $packageSizeTotals[$size] += $qty;
            } else {
                $packageMatrix[$pkgId]['sizes']['Lainnya'] += $qty;
                $packageSizeTotals['Lainnya'] += $qty;
            }

            $packageMatrix[$pkgId]['total'] += $qty;
            $totalPackageJerseys += $qty;
        }

        // 2. Rekap Jersey Tambahan dari Item Add-ons
        $addonQuery = RegistrationAddOn::query()
            ->join('registrations', 'registration_add_ons.registration_id', '=', 'registrations.id')
            ->join('add_ons', 'registration_add_ons.add_on_id', '=', 'add_ons.id')
            ->leftJoin('add_on_variants', 'registration_add_ons.add_on_variant_id', '=', 'add_on_variants.id')
            ->leftJoin('events', 'registrations.event_id', '=', 'events.id');

        if ($selectedEventId) {
            $addonQuery->where('registrations.event_id', $selectedEventId);
        }

        if ($paymentStatus !== 'ALL') {
            $addonQuery->where('registrations.payment_status', $paymentStatus);
        }

        $addonRaw = $addonQuery
            ->selectRaw('add_ons.id as add_on_id, add_ons.name as item_name, events.title as event_title, add_on_variants.variant_name, sum(registration_add_ons.quantity) as total_qty')
            ->groupBy('add_ons.id', 'add_ons.name', 'events.title', 'add_on_variants.variant_name')
            ->orderBy('add_ons.name')
            ->get();

        $totalAddonJerseys = (int) $addonRaw->sum('total_qty');

        // 3. Ringkasan Gabungan / Combined Grand Total
        $grandTotalJerseys = $totalPackageJerseys + $totalAddonJerseys;

        // 4. Hitung Estimasi Pending (UNPAID) untuk referensi buffer vendor
        $unpaidPackageQuery = Registration::query()
            ->join('packages', 'registrations.package_id', '=', 'packages.id')
            ->leftJoin('shipping_addresses', 'registrations.id', '=', 'shipping_addresses.registration_id');
        if ($selectedEventId) {
            $unpaidPackageQuery->where('registrations.event_id', $selectedEventId);
        }
        $unpaidPackageCount = $unpaidPackageQuery
            ->where('registrations.payment_status', 'UNPAID')
            ->whereNotNull('shipping_addresses.jersey_size')
            ->where('shipping_addresses.jersey_size', '!=', '')
            ->count();

        $unpaidAddonQuery = RegistrationAddOn::query()
            ->join('registrations', 'registration_add_ons.registration_id', '=', 'registrations.id');
        if ($selectedEventId) {
            $unpaidAddonQuery->where('registrations.event_id', $selectedEventId);
        }
        $unpaidAddonCount = (int) $unpaidAddonQuery
            ->where('registrations.payment_status', 'UNPAID')
            ->sum('registration_add_ons.quantity');

        $totalUnpaidForecast = $unpaidPackageCount + $unpaidAddonCount;

        return view('admin.registrations.jersey-orders', compact(
            'events',
            'selectedEventId',
            'paymentStatus',
            'standardSizes',
            'packageMatrix',
            'packageSizeTotals',
            'totalPackageJerseys',
            'addonRaw',
            'totalAddonJerseys',
            'grandTotalJerseys',
            'totalUnpaidForecast'
        ));
    }

    /**
     * Export rekapitulasi kebutuhan produksi jersey ke file CSV untuk vendor konveksi.
     */
    public function exportJerseyOrders(Request $request): StreamedResponse
    {
        $selectedEventId = $request->input('event_id');
        $paymentStatus = $request->input('payment_status', 'PAID');

        $eventSuffix = 'semua-event';
        $eventTitle = 'Semua Event VIRA';
        if ($selectedEventId) {
            $ev = Event::find($selectedEventId);
            if ($ev) {
                $eventSuffix = $ev->event_code;
                $eventTitle = $ev->title;
            }
        }

        $standardSizes = ['XS', 'S', 'M', 'L', 'XL', '2XL', '3XL', '4XL', '5XL'];

        // Ambil data paket
        $packageQuery = Registration::query()
            ->join('packages', 'registrations.package_id', '=', 'packages.id')
            ->leftJoin('shipping_addresses', 'registrations.id', '=', 'shipping_addresses.registration_id')
            ->leftJoin('events', 'registrations.event_id', '=', 'events.id');

        if ($selectedEventId) {
            $packageQuery->where('registrations.event_id', $selectedEventId);
        }
        if ($paymentStatus !== 'ALL') {
            $packageQuery->where('registrations.payment_status', $paymentStatus);
        }

        $packageRaw = $packageQuery
            ->whereNotNull('shipping_addresses.jersey_size')
            ->where('shipping_addresses.jersey_size', '!=', '')
            ->selectRaw('packages.id as package_id, packages.name as package_name, events.title as event_title, shipping_addresses.jersey_size, count(*) as qty')
            ->groupBy('packages.id', 'packages.name', 'events.title', 'shipping_addresses.jersey_size')
            ->get();

        $packageMatrix = [];
        $packageSizeTotals = array_fill_keys($standardSizes, 0);
        $packageSizeTotals['Lainnya'] = 0;
        $totalPackageJerseys = 0;

        foreach ($packageRaw as $row) {
            $pkgId = $row->package_id;
            if (! isset($packageMatrix[$pkgId])) {
                $packageMatrix[$pkgId] = [
                    'package_name' => $row->package_name,
                    'event_title' => $row->event_title,
                    'sizes' => array_fill_keys($standardSizes, 0),
                    'total' => 0,
                ];
                $packageMatrix[$pkgId]['sizes']['Lainnya'] = 0;
            }

            $size = strtoupper(trim($row->jersey_size));
            $qty = (int) $row->qty;

            if (in_array($size, $standardSizes, true)) {
                $packageMatrix[$pkgId]['sizes'][$size] += $qty;
                $packageSizeTotals[$size] += $qty;
            } else {
                $packageMatrix[$pkgId]['sizes']['Lainnya'] += $qty;
                $packageSizeTotals['Lainnya'] += $qty;
            }

            $packageMatrix[$pkgId]['total'] += $qty;
            $totalPackageJerseys += $qty;
        }

        // Ambil data addons
        $addonQuery = RegistrationAddOn::query()
            ->join('registrations', 'registration_add_ons.registration_id', '=', 'registrations.id')
            ->join('add_ons', 'registration_add_ons.add_on_id', '=', 'add_ons.id')
            ->leftJoin('add_on_variants', 'registration_add_ons.add_on_variant_id', '=', 'add_on_variants.id')
            ->leftJoin('events', 'registrations.event_id', '=', 'events.id');

        if ($selectedEventId) {
            $addonQuery->where('registrations.event_id', $selectedEventId);
        }
        if ($paymentStatus !== 'ALL') {
            $addonQuery->where('registrations.payment_status', $paymentStatus);
        }

        $addonRaw = $addonQuery
            ->selectRaw('add_ons.name as item_name, events.title as event_title, add_on_variants.variant_name, sum(registration_add_ons.quantity) as total_qty')
            ->groupBy('add_ons.name', 'events.title', 'add_on_variants.variant_name')
            ->orderBy('add_ons.name')
            ->get();

        $totalAddonJerseys = (int) $addonRaw->sum('total_qty');
        $grandTotal = $totalPackageJerseys + $totalAddonJerseys;

        $filename = 'spk-rekap-jersey-'.$eventSuffix.'-'.$paymentStatus.'-'.date('Ymd-His').'.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        return new StreamedResponse(function () use ($eventTitle, $paymentStatus, $standardSizes, $packageMatrix, $packageSizeTotals, $totalPackageJerseys, $addonRaw, $totalAddonJerseys, $grandTotal) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");

            // Metadata SPK
            fputcsv($handle, ['SURAT PERINTAH KERJA (SPK) REKAPITULASI PRODUKSI JERSEY']);
            fputcsv($handle, ['Platform', 'VIRA Virtual Sport Platform']);
            fputcsv($handle, ['Event', $eventTitle]);
            fputcsv($handle, ['Filter Status Pembayaran', $paymentStatus === 'PAID' ? 'Hanya Lunas (PAID)' : ($paymentStatus === 'ALL' ? 'Semua Status' : 'Belum Bayar (UNPAID)')]);
            fputcsv($handle, ['Waktu Cetak', date('d F Y, H:i:s')]);
            fputcsv($handle, ['GRAND TOTAL KEBUTUHAN PRODUKSI', $grandTotal.' PCS']);
            fputcsv($handle, []);

            // 1. Bagian Paket Pendaftaran
            fputcsv($handle, ['[BAGIAN 1: REKAPITULASI DARI PAKET PENDAFTARAN EVENT]']);
            $headerRow = array_merge(['No', 'Nama Paket Pendaftaran', 'Event'], $standardSizes, ['Lainnya', 'Total Pcs']);
            fputcsv($handle, $headerRow);

            $no = 1;
            foreach ($packageMatrix as $pkg) {
                $row = [
                    $no++,
                    $pkg['package_name'],
                    $pkg['event_title'],
                ];
                foreach ($standardSizes as $s) {
                    $row[] = $pkg['sizes'][$s];
                }
                $row[] = $pkg['sizes']['Lainnya'];
                $row[] = $pkg['total'];
                fputcsv($handle, $row);
            }

            // Total Baris Paket
            $totalRow = ['TOTAL PAKET', '', ''];
            foreach ($standardSizes as $s) {
                $totalRow[] = $packageSizeTotals[$s];
            }
            $totalRow[] = $packageSizeTotals['Lainnya'];
            $totalRow[] = $totalPackageJerseys;
            fputcsv($handle, $totalRow);

            fputcsv($handle, []);

            // 2. Bagian Add-ons Merchandise
            fputcsv($handle, ['[BAGIAN 2: REKAPITULASI DARI ITEM ADD-ONS / MERCHANDISE TAMBAHAN]']);
            fputcsv($handle, ['No', 'Nama Item Merchandise', 'Event', 'Varian / Ukuran', 'Jumlah (Pcs)']);

            $noAddon = 1;
            foreach ($addonRaw as $item) {
                fputcsv($handle, [
                    $noAddon++,
                    $item->item_name,
                    $item->event_title,
                    $item->variant_name ?: 'Standar (Tanpa Varian)',
                    (int) $item->total_qty,
                ]);
            }
            fputcsv($handle, ['TOTAL MERCHANDISE ADD-ONS', '', '', '', $totalAddonJerseys]);

            fputcsv($handle, []);

            // 3. Ringkasan Total
            fputcsv($handle, ['[BAGIAN 3: RINGKASAN AKHIR SIAP PRODUKSI]']);
            fputcsv($handle, ['Kategori', 'Total Pcs']);
            fputcsv($handle, ['Total Jersey dari Paket Pendaftaran', $totalPackageJerseys.' Pcs']);
            fputcsv($handle, ['Total Jersey / Apparel dari Add-ons', $totalAddonJerseys.' Pcs']);
            fputcsv($handle, ['GRAND TOTAL KESELURUHAN SIAP PRODUKSI', $grandTotal.' PCS']);

            fclose($handle);
        }, 200, $headers);
    }
}
