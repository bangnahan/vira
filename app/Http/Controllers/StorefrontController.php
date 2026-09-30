<?php

namespace App\Http\Controllers;

use App\Models\AddOn;
use App\Models\AddOnVariant;
use App\Models\Event;
use App\Models\Participant;
use App\Models\Payment;
use App\Models\Registration;
use App\Models\RegistrationAddOn;
use App\Models\ShippingAddress;
use App\Models\SpxShippingRate;
use App\Services\MailketingService;
use App\Services\TripayService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\View\View;

class StorefrontController extends Controller
{
    public function __construct(
        protected TripayService $tripayService,
        protected MailketingService $mailketingService
    ) {}

    /**
     * Halaman Official Shop / Etalase Merchandise VIRA.
     */
    public function index(Request $request): View
    {
        $search = trim((string) $request->input('search', ''));
        $eventFilter = $request->input('event_id', 'all');

        $query = AddOn::with(['variants', 'event'])
            ->where('is_active', true);

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($eventFilter === 'global') {
            $query->whereNull('event_id');
        } elseif ($eventFilter !== 'all' && is_numeric($eventFilter)) {
            $query->where('event_id', (int) $eventFilter);
        }

        $addOns = $query->orderBy('price', 'desc')->get();
        $events = Event::select('id', 'title', 'event_code')->orderBy('title')->get();

        // Ambil saluran pembayaran aktif Tripay untuk checkout mandiri (cache 24 jam)
        $paymentChannels = Cache::remember('tripay_active_payment_channels', 86400, function () {
            return $this->tripayService->getPaymentChannels();
        });
        $groupedPaymentChannels = collect($paymentChannels)->groupBy('group');

        return view('etalase.index', compact('addOns', 'events', 'search', 'eventFilter', 'paymentChannels', 'groupedPaymentChannels'));
    }

    /**
     * Halaman Checkout Mandiri Pembelian Merchandise.
     */
    public function checkout(Request $request): RedirectResponse
    {
        return redirect()->route('shop.index');
    }

    /**
     * Proses Pemesanan Mandiri Merchandise Shop (Tanpa Pendaftaran Event).
     */
    public function storeOrder(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            // Pemesan
            'full_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone_number' => ['required', 'string', 'max:25'],

            // Pengiriman SPX
            'destination_city' => ['required', 'string', 'max:150'],
            'destination_district' => ['required', 'string', 'max:150'],
            'address_detail' => ['required', 'string', 'max:500'],
            'postal_code' => ['nullable', 'string', 'max:10'],
            'spx_service' => ['nullable', 'string', 'max:50'],

            // Saluran Pembayaran Tripay
            'payment_channel' => ['nullable', 'string', 'max:25'],

            // Daftar Barang yang Dibeli
            'items' => ['required', 'array', 'min:1'],
            'items.*.id' => ['required', 'exists:add_ons,id'],
            'items.*.variant_id' => ['nullable', 'exists:add_on_variants,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ]);

        // Verifikasi item & hitung subtotal serta berat total
        $processedItems = [];
        $itemsSubtotal = 0.00;
        $totalWeightGrams = 0;

        foreach ($validated['items'] as $itemData) {
            $qty = (int) $itemData['quantity'];
            if ($qty <= 0) {
                continue;
            }

            $addon = AddOn::with('variants')->findOrFail($itemData['id']);

            if (! $addon->is_active) {
                return back()->withInput()->with('error', "Item '{$addon->name}' saat ini tidak aktif.");
            }

            $variant = null;
            if (! empty($itemData['variant_id'])) {
                $variant = AddOnVariant::where('add_on_id', $addon->id)
                    ->where('id', $itemData['variant_id'])
                    ->first();
            }

            // Validasi stok
            $availableStock = $variant ? $variant->stock : $addon->stock;
            if ($availableStock < $qty) {
                $varLabel = $variant ? " ({$variant->variant_name})" : '';

                return back()->withInput()->with('error', "Stok untuk '{$addon->name}{$varLabel}' tidak mencukupi (Tersisa: {$availableStock}).");
            }

            $unitPrice = (float) $addon->price + ($variant ? (float) $variant->additional_price : 0.00);
            $subtotal = $unitPrice * $qty;

            $itemsSubtotal += $subtotal;
            $totalWeightGrams += ((int) $addon->weight_grams * $qty);

            $processedItems[] = [
                'addon' => $addon,
                'variant' => $variant,
                'quantity' => $qty,
                'unit_price' => $unitPrice,
                'subtotal' => $subtotal,
            ];
        }

        if (empty($processedItems)) {
            return back()->withInput()->with('error', 'Keranjang belanja kosong. Silakan pilih minimal 1 produk.');
        }

        // Hitung Ongkir SPX
        $destCity = $validated['destination_city'];
        $destDistrict = $validated['destination_district'];
        $spxService = $validated['spx_service'] ?? 'SPX_REGULAR';

        $spxData = SpxShippingRate::calculateRate($destCity, $destDistrict, $totalWeightGrams);
        if (! $spxData) {
            return back()->withInput()->with('error', 'Tarif pengiriman SPX untuk kota dan kecamatan tersebut tidak ditemukan.');
        }

        $selectedService = collect($spxData['services'])->firstWhere('service_code', $spxService)
            ?? $spxData['services'][0];

        $shippingCost = (float) $selectedService['total_cost'];
        $courierName = $selectedService['service_name'];

        $grandTotal = $itemsSubtotal + $shippingCost;

        // Simpan data order mandiri ke database
        $registration = DB::transaction(function () use (
            $validated,
            $processedItems,
            $itemsSubtotal,
            $totalWeightGrams,
            $destCity,
            $destDistrict,
            $shippingCost,
            $courierName,
            $grandTotal
        ) {
            // 1. Data Pembeli
            $participant = Participant::updateOrCreate(
                ['email' => strtolower(trim($validated['email']))],
                [
                    'full_name' => trim($validated['full_name']),
                    'phone_number' => trim($validated['phone_number']),
                ]
            );

            // 2. Order Standalone (event_id, category_id, package_id bernilai null)
            $order = Registration::create([
                'access_token' => (string) Str::uuid(),
                'event_id' => null,
                'category_id' => null,
                'package_id' => null,
                'participant_id' => $participant->id,
                'bib_number' => null,
                'payment_status' => 'UNPAID',
                'total_distance_km' => 0.00,
                'total_duration_seconds' => 0,
                'last_milestone_notified' => 0,
                'finisher_status' => 'IN_PROGRESS',
            ]);

            // 3. Simpan Barang yang Dipesan & Kurangi Stok
            foreach ($processedItems as $pItem) {
                RegistrationAddOn::create([
                    'registration_id' => $order->id,
                    'add_on_id' => $pItem['addon']->id,
                    'add_on_variant_id' => $pItem['variant']?->id,
                    'quantity' => $pItem['quantity'],
                    'unit_price' => $pItem['unit_price'],
                    'subtotal' => $pItem['subtotal'],
                ]);

                if ($pItem['variant']) {
                    $pItem['variant']->decrement('stock', $pItem['quantity']);
                }
                $pItem['addon']->decrement('stock', $pItem['quantity']);
            }

            // 4. Simpan Alamat Pengiriman SPX
            ShippingAddress::create([
                'registration_id' => $order->id,
                'recipient_name' => $validated['full_name'],
                'recipient_phone' => $validated['phone_number'],
                'province' => $validated['province'] ?? 'Banten',
                'city' => $destCity,
                'district' => $destDistrict,
                'postal_code' => $validated['postal_code'] ?? '00000',
                'address_detail' => $validated['address_detail'],
                'total_weight_grams' => $totalWeightGrams,
                'courier_name' => $courierName,
                'shipping_cost' => $shippingCost,
                'shipping_status' => 'PENDING',
            ]);

            // 5. Buat Tagihan Pembayaran
            $merchantRef = 'VIRA-SHOP-'.date('Ymd').'-'.strtoupper(Str::random(6));
            $paymentMethod = $validated['payment_channel'] ?? 'QRIS2';

            Payment::create([
                'registration_id' => $order->id,
                'merchant_ref' => $merchantRef,
                'payment_method' => $paymentMethod,
                'amount' => $itemsSubtotal,
                'admin_fee' => 0.00,
                'shipping_cost' => $shippingCost,
                'total_amount' => $grandTotal,
                'status' => 'UNPAID',
                'expired_at' => now()->addHours(24),
            ]);

            return $order;
        });

        // 6. Buat Closed Transaction di Tripay
        $payment = $registration->payment;
        if ($payment && $payment->total_amount > 0) {
            try {
                $channel = $validated['payment_channel'] ?? config('tripay.default_channel', 'QRIS2');
                $this->tripayService->createClosedTransaction($registration, $channel);
                $payment->refresh();
            } catch (\Exception $e) {
                Log::warning('Tripay merchandise order transaction warning: '.$e->getMessage());
            }
        }

        // 7. Kirim Email Tagihan
        try {
            $this->mailketingService->sendInvoiceEmail($registration);
        } catch (\Exception $e) {
            Log::warning('Mailketing send merchandise invoice warning: '.$e->getMessage());
        }

        return redirect()->route('payment.show', ['merchant_ref' => $payment->merchant_ref])
            ->with('success', 'Pesanan merchandise Anda berhasil dibuat! Silakan selesaikan pembayaran.');
    }
}
