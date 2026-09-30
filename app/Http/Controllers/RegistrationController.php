<?php

namespace App\Http\Controllers;

use App\Models\AddOn;
use App\Models\AddOnVariant;
use App\Models\Category;
use App\Models\Event;
use App\Models\Package;
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

class RegistrationController extends Controller
{
    public function __construct(
        protected TripayService $tripayService,
        protected MailketingService $mailketingService
    ) {}

    /**
     * Tampilkan form pendaftaran guest untuk event tertentu.
     */
    public function create(string $slug): View
    {
        $event = Event::with(['categories', 'packages', 'addOns.variants'])
            ->where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        // Ambil add-ons spesifik event + add-on global (event_id is null)
        $addOns = AddOn::with('variants')
            ->where(function ($q) use ($event) {
                $q->where('event_id', $event->id)->orWhereNull('event_id');
            })
            ->where('is_active', true)
            ->where('stock', '>', 0)
            ->get();

        // Ambil channel pembayaran Tripay aktif (cache 24 jam agar cepat dan tidak blocking)
        $paymentChannels = Cache::remember('tripay_active_payment_channels', 86400, function () {
            return $this->tripayService->getPaymentChannels();
        });

        $groupedPaymentChannels = collect($paymentChannels)->groupBy('group');

        return view('events.register', compact('event', 'addOns', 'groupedPaymentChannels'));
    }

    /**
     * Proses penyimpanan registrasi dan kalkulasi tagihan.
     */
    public function store(Request $request, string $slug): RedirectResponse
    {
        $event = Event::where('slug', $slug)->where('is_active', true)->firstOrFail();

        // Filter add-ons yang benar-benar dipilih peserta (quantity > 0)
        if ($request->has('addons') && is_array($request->input('addons'))) {
            $filteredAddons = [];
            foreach ($request->input('addons') as $item) {
                $qty = isset($item['quantity']) && is_numeric($item['quantity']) ? (int) $item['quantity'] : 0;
                if ($qty > 0 && ! empty($item['id'])) {
                    $filteredAddons[] = [
                        'id' => $item['id'],
                        'variant_id' => ! empty($item['variant_id']) ? $item['variant_id'] : null,
                        'quantity' => $qty,
                    ];
                }
            }
            $request->merge(['addons' => $filteredAddons]);
        }

        $validated = $request->validate([
            // Data Peserta
            'full_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone_number' => ['required', 'string', 'max:25'],
            'gender' => ['nullable', 'in:MALE,FEMALE'],
            'date_of_birth' => ['nullable', 'date'],
            'blood_type' => ['nullable', 'string', 'max:5'],
            'emergency_contact_name' => ['nullable', 'string', 'max:255'],
            'emergency_contact_phone' => ['nullable', 'string', 'max:25'],

            // Pilihan Lomba
            'category_id' => ['required', 'exists:categories,id'],
            'package_id' => ['required', 'exists:packages,id'],

            // Add-ons opsional: hanya divalidasi jika ada item yang dipilih (qty >= 1)
            'addons' => ['nullable', 'array'],
            'addons.*.id' => ['required', 'exists:add_ons,id'],
            'addons.*.variant_id' => ['nullable', 'exists:add_on_variants,id'],
            'addons.*.quantity' => ['required', 'integer', 'min:1', 'max:10'],

            // Pengiriman SPX (jika paket / add-on butuh kirim fisik)
            'recipient_name' => ['nullable', 'string', 'max:255'],
            'recipient_phone' => ['nullable', 'string', 'max:25'],
            'province' => ['nullable', 'string', 'max:100'],
            'destination_city' => ['nullable', 'string', 'max:100'],
            'destination_district' => ['nullable', 'string', 'max:100'],
            'postal_code' => ['nullable', 'string', 'max:10'],
            'address_detail' => ['nullable', 'string'],
            'jersey_size' => ['nullable', 'string', 'max:10'],
            'spx_service' => ['nullable', 'in:SPX_HEMAT,SPX_REGULAR'],

            // Pilihan Metode Pembayaran Tripay
            'payment_channel' => ['nullable', 'string', 'max:50'],
        ]);

        $category = Category::where('id', $validated['category_id'])->where('event_id', $event->id)->firstOrFail();
        $package = Package::where('id', $validated['package_id'])->where('event_id', $event->id)->firstOrFail();

        // Cek kuota kategori jika ada batasan
        if ($category->quota !== null && $category->registered_count >= $category->quota) {
            return back()->withInput()->with('error', 'Mohon maaf, kuota untuk kategori ini telah habis.');
        }

        // Kalkulasi harga tiket dasar
        $basePrice = (float) $package->price;
        $totalWeightGrams = (int) $package->base_weight_grams;
        $requiresShipping = $package->requires_shipping;

        // Proses Add-ons
        $processedAddons = [];
        $addonsSubtotal = 0.00;

        if (! empty($validated['addons'])) {
            foreach ($validated['addons'] as $item) {
                $qty = (int) ($item['quantity'] ?? 0);
                if ($qty <= 0) {
                    continue;
                }

                $addon = AddOn::findOrFail($item['id']);
                if (! $addon->is_active) {
                    continue;
                }
                $variant = ! empty($item['variant_id']) ? AddOnVariant::find($item['variant_id']) : null;

                $unitPrice = (float) $addon->price + ($variant ? (float) $variant->additional_price : 0.00);
                $subtotal = $unitPrice * $qty;

                $addonsSubtotal += $subtotal;
                $totalWeightGrams += ((int) $addon->weight_grams * $qty);
                $requiresShipping = true; // Pembelian add-on fisik otomatis memicu pengiriman

                $processedAddons[] = [
                    'addon' => $addon,
                    'variant' => $variant,
                    'quantity' => $qty,
                    'unit_price' => $unitPrice,
                    'subtotal' => $subtotal,
                ];
            }
        }

        // Hitung Ongkir SPX jika memerlukan pengiriman
        $shippingCost = 0.00;
        $courierName = null;

        if ($requiresShipping) {
            $destCity = $validated['destination_city'] ?? null;
            $destDistrict = $validated['destination_district'] ?? null;
            $spxService = $validated['spx_service'] ?? 'SPX_REGULAR';

            if (! $destCity || ! $destDistrict) {
                return back()->withInput()->with('error', 'Kota dan Kecamatan tujuan wajib dipilih untuk pengiriman paket fisik.');
            }

            $spxData = SpxShippingRate::calculateRate($destCity, $destDistrict, $totalWeightGrams);
            if (! $spxData) {
                return back()->withInput()->with('error', 'Tarif pengiriman SPX untuk tujuan tersebut tidak ditemukan.');
            }

            $selectedService = collect($spxData['services'])->firstWhere('service_code', $spxService);
            if (! $selectedService) {
                $selectedService = $spxData['services'][0];
            }

            $shippingCost = (float) $selectedService['total_cost'];
            $courierName = $selectedService['service_name'];
        }

        $ticketAndAddonsAmount = $basePrice + $addonsSubtotal;
        $grandTotal = $ticketAndAddonsAmount + $shippingCost;

        // Simpan ke database dengan Transaction
        $registration = DB::transaction(function () use (
            $validated,
            $event,
            $category,
            $package,
            $processedAddons,
            $requiresShipping,
            $courierName,
            $shippingCost,
            $totalWeightGrams,
            $ticketAndAddonsAmount,
            $grandTotal
        ) {
            // 1. Data Peserta
            $participant = Participant::updateOrCreate(
                ['email' => strtolower(trim($validated['email']))],
                [
                    'full_name' => trim($validated['full_name']),
                    'phone_number' => trim($validated['phone_number']),
                    'gender' => $validated['gender'] ?? null,
                    'date_of_birth' => $validated['date_of_birth'] ?? null,
                    'blood_type' => $validated['blood_type'] ?? null,
                    'emergency_contact_name' => $validated['emergency_contact_name'] ?? null,
                    'emergency_contact_phone' => $validated['emergency_contact_phone'] ?? null,
                ]
            );

            // 2. Registrasi Event
            $reg = Registration::create([
                'access_token' => (string) Str::uuid(),
                'event_id' => $event->id,
                'category_id' => $category->id,
                'package_id' => $package->id,
                'participant_id' => $participant->id,
                'payment_status' => 'UNPAID',
                'total_distance_km' => 0.00,
                'total_duration_seconds' => 0,
                'last_milestone_notified' => 0,
                'finisher_status' => 'IN_PROGRESS',
            ]);

            $category->increment('registered_count');

            // 3. Simpan Add-ons
            foreach ($processedAddons as $pAddon) {
                RegistrationAddOn::create([
                    'registration_id' => $reg->id,
                    'add_on_id' => $pAddon['addon']->id,
                    'add_on_variant_id' => $pAddon['variant']?->id,
                    'quantity' => $pAddon['quantity'],
                    'unit_price' => $pAddon['unit_price'],
                    'subtotal' => $pAddon['subtotal'],
                ]);

                // Kurangi stok
                if ($pAddon['variant']) {
                    $pAddon['variant']->decrement('stock', $pAddon['quantity']);
                }
                $pAddon['addon']->decrement('stock', $pAddon['quantity']);
            }

            // 4. Simpan Alamat Pengiriman
            if ($requiresShipping) {
                ShippingAddress::create([
                    'registration_id' => $reg->id,
                    'recipient_name' => $validated['recipient_name'] ?: $validated['full_name'],
                    'recipient_phone' => $validated['recipient_phone'] ?: $validated['phone_number'],
                    'province' => $validated['province'] ?? 'Banten',
                    'city' => $validated['destination_city'],
                    'district' => $validated['destination_district'],
                    'postal_code' => $validated['postal_code'] ?? '00000',
                    'address_detail' => $validated['address_detail'] ?? '-',
                    'jersey_size' => $validated['jersey_size'] ?? null,
                    'total_weight_grams' => $totalWeightGrams,
                    'courier_name' => $courierName,
                    'shipping_cost' => $shippingCost,
                    'shipping_status' => 'PENDING',
                ]);
            }

            // 5. Buat Tagihan Pembayaran
            $merchantRef = 'VIRA-'.date('Ymd').'-'.strtoupper(Str::random(6));
            $paymentMethod = $validated['payment_channel'] ?? 'QRIS2';

            Payment::create([
                'registration_id' => $reg->id,
                'merchant_ref' => $merchantRef,
                'payment_method' => $paymentMethod,
                'amount' => $ticketAndAddonsAmount,
                'admin_fee' => 0.00,
                'shipping_cost' => $shippingCost,
                'total_amount' => $grandTotal,
                'status' => 'UNPAID',
                'expired_at' => now()->addHours(24),
            ]);

            return $reg;
        });

        // 6. Buat Closed Transaction di Tripay jika nominal > 0
        $payment = $registration->payment;
        if ($payment && $payment->total_amount > 0) {
            try {
                $channel = $validated['payment_channel'] ?? config('tripay.default_channel', 'QRIS2');
                $this->tripayService->createClosedTransaction($registration, $channel);
                $payment->refresh();
            } catch (\Exception $e) {
                Log::warning('Tripay transaction creation warning: '.$e->getMessage());
            }
        }

        // 7. Kirim Email Tagihan Pendaftaran (Invoice) via Mailketing
        try {
            $this->mailketingService->sendInvoiceEmail($registration);
        } catch (\Exception $e) {
            Log::warning('Mailketing send invoice warning: '.$e->getMessage());
        }

        if (! empty($payment->checkout_url)) {
            return redirect()->away($payment->checkout_url);
        }

        return redirect()->route('payment.show', ['merchant_ref' => $payment->merchant_ref])
            ->with('success', 'Pendaftaran berhasil dibuat! Silakan selesaikan pembayaran tagihan Anda.');
    }
}
