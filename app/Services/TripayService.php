<?php

namespace App\Services;

use App\Models\Registration;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TripayService
{
    protected string $apiKey;

    protected string $privateKey;

    protected string $merchantCode;

    protected string $apiUrl;

    public function __construct()
    {
        $this->apiKey = (string) config('tripay.api_key');
        $this->privateKey = (string) config('tripay.private_key');
        $this->merchantCode = (string) config('tripay.merchant_code');

        $isSandbox = (bool) config('tripay.sandbox', true);
        if (! empty($this->apiKey) && str_starts_with($this->apiKey, 'DEV-')) {
            $isSandbox = true;
        }

        $this->apiUrl = $isSandbox
            ? 'https://tripay.co.id/api-sandbox/'
            : 'https://tripay.co.id/api/';
    }

    /**
     * Ambil daftar channel pembayaran aktif dari Tripay.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getPaymentChannels(): array
    {
        try {
            if (! empty($this->apiKey)) {
                $response = Http::withToken($this->apiKey)
                    ->connectTimeout(5)
                    ->timeout(10)
                    ->retry(2, 200)
                    ->get($this->apiUrl.'merchant/payment-channel');

                if ($response->successful()) {
                    $data = $response->json('data') ?? [];
                    $active = collect($data)
                        ->filter(fn ($channel) => (bool) ($channel['active'] ?? false))
                        ->values()
                        ->toArray();

                    if (! empty($active)) {
                        return $active;
                    }
                } else {
                    Log::warning('Tripay merchant/payment-channel unsuccessful: '.$response->body());
                }
            }
        } catch (\Throwable $e) {
            Log::warning('Tripay getPaymentChannels fallback used: '.$e->getMessage());
        }

        return self::allDefaultChannels();
    }

    /**
     * Seluruh metode pembayaran Tripay lengkap sebagai referensi & fallback cepat.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function allDefaultChannels(): array
    {
        return [
            // E-Wallet & QRIS
            [
                'code' => 'QRIS2',
                'name' => 'QRIS (Semua Bank & E-Wallet)',
                'group' => 'E-Wallet',
                'icon_url' => 'https://assets.tripay.co.id/upload/payment-icon/Z14i8x1Dqm1583408985.png',
                'total_fee' => ['flat' => 750, 'percent' => '0.70'],
                'active' => true,
            ],
            [
                'code' => 'OVO',
                'name' => 'OVO',
                'group' => 'E-Wallet',
                'icon_url' => 'https://assets.tripay.co.id/upload/payment-icon/T3f0Vn389L1583408998.png',
                'total_fee' => ['flat' => 1000, 'percent' => '1.50'],
                'active' => true,
            ],
            [
                'code' => 'SHOPEEPAY',
                'name' => 'ShopeePay',
                'group' => 'E-Wallet',
                'icon_url' => 'https://assets.tripay.co.id/upload/payment-icon/shoopepay.png',
                'total_fee' => ['flat' => 750, 'percent' => '1.50'],
                'active' => true,
            ],
            [
                'code' => 'DANA',
                'name' => 'DANA',
                'group' => 'E-Wallet',
                'icon_url' => 'https://assets.tripay.co.id/upload/payment-icon/DANA.png',
                'total_fee' => ['flat' => 750, 'percent' => '1.50'],
                'active' => true,
            ],

            // Virtual Account
            [
                'code' => 'BCAVA',
                'name' => 'BCA Virtual Account',
                'group' => 'Virtual Account',
                'icon_url' => 'https://assets.tripay.co.id/upload/payment-icon/ytBKvaleGy1605260384.png',
                'total_fee' => ['flat' => 5500, 'percent' => '0.00'],
                'active' => true,
            ],
            [
                'code' => 'BRIVA',
                'name' => 'BRI Virtual Account',
                'group' => 'Virtual Account',
                'icon_url' => 'https://assets.tripay.co.id/upload/payment-icon/y922u927J31583408752.png',
                'total_fee' => ['flat' => 4250, 'percent' => '0.00'],
                'active' => true,
            ],
            [
                'code' => 'BNIVA',
                'name' => 'BNI Virtual Account',
                'group' => 'Virtual Account',
                'icon_url' => 'https://assets.tripay.co.id/upload/payment-icon/11P4v9d1501583408765.png',
                'total_fee' => ['flat' => 4250, 'percent' => '0.00'],
                'active' => true,
            ],
            [
                'code' => 'MANDIRIVA',
                'name' => 'Mandiri Virtual Account',
                'group' => 'Virtual Account',
                'icon_url' => 'https://assets.tripay.co.id/upload/payment-icon/m7sU6Z81x01583408778.png',
                'total_fee' => ['flat' => 4250, 'percent' => '0.00'],
                'active' => true,
            ],
            [
                'code' => 'PERMATAVA',
                'name' => 'Permata Virtual Account',
                'group' => 'Virtual Account',
                'icon_url' => 'https://assets.tripay.co.id/upload/payment-icon/9v14b301701583408791.png',
                'total_fee' => ['flat' => 4250, 'percent' => '0.00'],
                'active' => true,
            ],
            [
                'code' => 'CIMBVA',
                'name' => 'CIMB Niaga Virtual Account',
                'group' => 'Virtual Account',
                'icon_url' => 'https://assets.tripay.co.id/upload/payment-icon/1n132170801583408803.png',
                'total_fee' => ['flat' => 4250, 'percent' => '0.00'],
                'active' => true,
            ],
            [
                'code' => 'BSIVA',
                'name' => 'BSI (Bank Syariah Indonesia) Virtual Account',
                'group' => 'Virtual Account',
                'icon_url' => 'https://assets.tripay.co.id/upload/payment-icon/BSIVA.png',
                'total_fee' => ['flat' => 4250, 'percent' => '0.00'],
                'active' => true,
            ],
            [
                'code' => 'DANAMONVA',
                'name' => 'Danamon Virtual Account',
                'group' => 'Virtual Account',
                'icon_url' => 'https://assets.tripay.co.id/upload/payment-icon/DANAMONVA.png',
                'total_fee' => ['flat' => 4250, 'percent' => '0.00'],
                'active' => true,
            ],
            [
                'code' => 'MUAMALATVA',
                'name' => 'Muamalat Virtual Account',
                'group' => 'Virtual Account',
                'icon_url' => 'https://assets.tripay.co.id/upload/payment-icon/MUAMALATVA.png',
                'total_fee' => ['flat' => 4250, 'percent' => '0.00'],
                'active' => true,
            ],
            [
                'code' => 'OCBCVA',
                'name' => 'OCBC NISP Virtual Account',
                'group' => 'Virtual Account',
                'icon_url' => 'https://assets.tripay.co.id/upload/payment-icon/ysiSToLvKl1644244798.png',
                'total_fee' => ['flat' => 4250, 'percent' => '0.00'],
                'active' => true,
            ],
            [
                'code' => 'OTHERBANKVA',
                'name' => 'Other Bank Virtual Account',
                'group' => 'Virtual Account',
                'icon_url' => 'https://assets.tripay.co.id/upload/payment-icon/qQYo61sIDa1702995837.png',
                'total_fee' => ['flat' => 4250, 'percent' => '0.00'],
                'active' => true,
            ],

            // Gerai Retail / Minimarket
            [
                'code' => 'ALFAMART',
                'name' => 'Alfamart',
                'group' => 'Gerai Retail / Minimarket',
                'icon_url' => 'https://assets.tripay.co.id/upload/payment-icon/alfa.png',
                'total_fee' => ['flat' => 5000, 'percent' => '0.00'],
                'active' => true,
            ],
            [
                'code' => 'INDOMARET',
                'name' => 'Indomaret',
                'group' => 'Gerai Retail / Minimarket',
                'icon_url' => 'https://assets.tripay.co.id/upload/payment-icon/indomaret.png',
                'total_fee' => ['flat' => 5000, 'percent' => '0.00'],
                'active' => true,
            ],
            [
                'code' => 'ALFAMIDI',
                'name' => 'Alfamidi',
                'group' => 'Gerai Retail / Minimarket',
                'icon_url' => 'https://assets.tripay.co.id/upload/payment-icon/alfamidi.png',
                'total_fee' => ['flat' => 5000, 'percent' => '0.00'],
                'active' => true,
            ],
        ];
    }

    /**
     * Buat Closed Transaction di Tripay untuk pendaftaran peserta.
     *
     * @return array<string, mixed>
     */
    public function createClosedTransaction(Registration $registration, string $paymentMethod = 'QRIS2'): array
    {
        $payment = $registration->payment;
        if (! $payment) {
            throw new Exception("Registration #{$registration->id} tidak memiliki relasi Payment.");
        }

        // Normalisasi alias payment method
        $channelAliases = [
            'QRIS' => 'QRIS2',
            'BCA' => 'BCAVA',
            'BNI' => 'BNIVA',
            'BRI' => 'BRIVA',
            'MANDIRI' => 'MANDIRIVA',
            'PERMATA' => 'PERMATAVA',
            'CIMB' => 'CIMBVA',
            'BSI' => 'BSIVA',
            'DANAMON' => 'DANAMONVA',
            'MUAMALAT' => 'MUAMALATVA',
            'OCBC' => 'OCBCVA',
            'OTHERBANK' => 'OTHERBANKVA',
        ];
        $paymentMethod = $channelAliases[strtoupper(trim($paymentMethod))] ?? strtoupper(trim($paymentMethod));

        $merchantRef = $payment->merchant_ref;
        $orderItems = [];

        // 1. Item Tiket Pendaftaran (jika pembelian tiket event)
        $package = $registration->package;
        $category = $registration->category;
        $event = $registration->event;

        if ($package && $category && $event) {
            $ticketPrice = (int) round($package->price);
            $sku = ! empty($category->bib_prefix) ? 'TKT-'.$category->bib_prefix : 'TKT-'.$category->id;
            $orderItems[] = [
                'sku' => $sku,
                'name' => 'Tiket '.$event->title.' ('.$category->name.')',
                'price' => $ticketPrice,
                'quantity' => 1,
                'subtotal' => $ticketPrice,
            ];
        }

        // 2. Item Add-on / Merchandise jika ada
        foreach ($registration->registrationAddOns as $regAddon) {
            $addonName = $regAddon->addOn->name;
            if ($regAddon->variant) {
                $addonName .= ' - '.$regAddon->variant->variant_name;
            }

            $addonPrice = (int) round($regAddon->unit_price);
            $qty = (int) $regAddon->quantity;

            $orderItems[] = [
                'sku' => 'ADD-'.$regAddon->add_on_id,
                'name' => $addonName,
                'price' => $addonPrice,
                'quantity' => $qty,
                'subtotal' => $addonPrice * $qty,
            ];
        }

        // 3. Item Biaya Pengiriman SPX jika ada
        $shippingCost = (int) round($payment->shipping_cost);
        if ($shippingCost > 0) {
            $courier = $registration->shippingAddress?->courier_name ?? 'SPX Express';
            $orderItems[] = [
                'sku' => 'SHIP-SPX',
                'name' => 'Ongkos Kirim '.$courier,
                'price' => $shippingCost,
                'quantity' => 1,
                'subtotal' => $shippingCost,
            ];
        }

        // Hitung total amount berdasarkan akumulasi order items
        $amount = (int) collect($orderItems)->sum('subtotal');

        if ($amount <= 0) {
            throw new Exception('Total tagihan adalah Rp 0, tidak memerlukan transaksi Tripay.');
        }

        // Generate Signature HMAC-SHA256: merchant_code + merchant_ref + amount
        $signature = hash_hmac('sha256', $this->merchantCode.$merchantRef.$amount, $this->privateKey);

        $participant = $registration->participant;
        $customerName = $participant->full_name ?? 'Peserta VIRA';
        $customerEmail = $participant->email ?? 'peserta@vira.id';
        $customerPhone = $participant->phone_number ?? '081234567890';

        $payload = [
            'method' => $paymentMethod,
            'merchant_ref' => $merchantRef,
            'amount' => $amount,
            'customer_name' => $customerName,
            'customer_email' => $customerEmail,
            'customer_phone' => $customerPhone,
            'order_items' => $orderItems,
            'return_url' => route('payment.show', ['merchant_ref' => $merchantRef]),
            'expired_time' => now()->addHours(24)->timestamp,
            'signature' => $signature,
        ];

        $response = Http::withToken($this->apiKey)
            ->connectTimeout(10)
            ->timeout(15)
            ->retry(2, 500)
            ->post($this->apiUrl.'transaction/create', $payload);

        $result = $response->json();

        if (! $response->successful() || ! ($result['success'] ?? false)) {
            $msg = $result['message'] ?? 'Gagal membuat tagihan pembayaran Tripay.';
            Log::error('Tripay create transaction error: '.$response->body(), ['payload' => $payload]);
            throw new Exception($msg);
        }

        $data = $result['data'];

        // Simpan info referensi Tripay beserta respon raw (termasuk instructions & payment_name) ke database
        $payment->update([
            'tripay_reference' => $data['reference'] ?? null,
            'payment_method' => $paymentMethod,
            'checkout_url' => $data['checkout_url'] ?? null,
            'qr_code_url' => $data['qr_url'] ?? null,
            'pay_code' => $data['pay_code'] ?? null,
            'admin_fee' => (float) ($data['total_fee'] ?? 0),
            'total_amount' => (float) ($data['amount'] ?? $amount),
            'expired_at' => isset($data['expired_time']) ? date('Y-m-d H:i:s', $data['expired_time']) : now()->addHours(24),
            'raw_callback' => $data,
        ]);

        return $data;
    }

    /**
     * Verifikasi callback signature dari webhook notifikasi Tripay.
     */
    public function verifyWebhookSignature(string $rawContent, ?string $signature): bool
    {
        if (empty($signature) || empty($this->privateKey)) {
            return false;
        }

        $expected = hash_hmac('sha256', $rawContent, $this->privateKey);

        return hash_equals($expected, $signature);
    }

    /**
     * Ambil rincian transaksi dari Tripay berdasarkan nomor referensi.
     *
     * @return array<string, mixed>|null
     */
    public function getTransactionDetail(string $reference): ?array
    {
        try {
            $response = Http::withToken($this->apiKey)
                ->get($this->apiUrl.'transaction/detail', ['reference' => $reference]);

            if ($response->successful() && ($response->json('success') ?? false)) {
                return $response->json('data');
            }

            return null;
        } catch (Exception $e) {
            Log::error('Tripay getTransactionDetail error: '.$e->getMessage());

            return null;
        }
    }
}
