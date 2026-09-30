<?php

namespace App\Services;

use App\Models\Event;
use App\Models\Payment;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MetaCapiService
{
    /**
     * Kirim server-side Purchase event ke Meta Conversions API saat status pembayaran PAID.
     *
     * @return array<string, mixed>
     */
    public function sendPurchaseEvent(Payment $payment): array
    {
        $registration = $payment->registration;
        if (! $registration) {
            return ['status' => 'skipped', 'message' => 'No registration linked to payment'];
        }

        $event = $registration->event;
        $participant = $registration->participant;

        // Ambil konfigurasi Pixel & Token dari Event
        $pixelId = trim($event->meta_pixel_id ?? '');
        $capiToken = trim($event->meta_capi_token ?? '');
        $testEventCode = trim($event->meta_test_code ?? '');
        $isEnabled = (bool) $event->is_meta_capi_enabled;

        if (! $isEnabled || empty($pixelId) || empty($capiToken)) {
            Log::info("Meta CAPI dilewati untuk Event #{$event->id}: CAPI nonaktif atau kredensial kosong.");

            return ['status' => 'skipped', 'message' => 'Meta CAPI not enabled or credentials empty'];
        }

        // Hashing data peserta sesuai standar SHA-256 Meta
        $cleanEmail = strtolower(trim($participant->email ?? ''));
        $cleanPhone = preg_replace('/[^0-9]/', '', (string) ($participant->phone_number ?? ''));
        if (! str_starts_with($cleanPhone, '62') && str_starts_with($cleanPhone, '0')) {
            $cleanPhone = '62'.substr($cleanPhone, 1);
        }

        $nameParts = explode(' ', trim($participant->full_name ?? ''));
        $firstName = strtolower(trim($nameParts[0] ?? ''));

        $userData = [
            'em' => $cleanEmail ? [hash('sha256', $cleanEmail)] : [],
            'ph' => $cleanPhone ? [hash('sha256', $cleanPhone)] : [],
            'fn' => $firstName ? [hash('sha256', $firstName)] : [],
            'client_ip_address' => request()->ip() ?: '127.0.0.1',
            'client_user_agent' => request()->userAgent() ?: 'Mozilla/5.0 (VIRA Server)',
        ];

        $eventTime = $payment->paid_at ? $payment->paid_at->timestamp : now()->timestamp;
        $paymentUrl = route('payment.show', ['merchant_ref' => $payment->merchant_ref]);

        $eventData = [
            'event_name' => 'Purchase',
            'event_time' => $eventTime,
            'event_id' => $payment->merchant_ref, // Deduplikasi dengan Pixel browser
            'event_source_url' => $paymentUrl,
            'action_source' => 'website',
            'user_data' => $userData,
            'custom_data' => [
                'currency' => 'IDR',
                'value' => (float) $payment->total_amount,
                'content_name' => "{$event->title} - {$registration->category->name}",
                'content_type' => 'product',
                'order_id' => $payment->merchant_ref,
                'num_items' => 1,
            ],
        ];

        $payload = [
            'data' => [$eventData],
        ];

        // Jika terdapat test_event_code (dari tab Events Manager Test Events)
        if (! empty($testEventCode)) {
            $payload['test_event_code'] = $testEventCode;
        }

        try {
            $url = "https://graph.facebook.com/v20.0/{$pixelId}/events";

            $response = Http::withToken($capiToken)
                ->acceptJson()
                ->post($url, $payload);

            $result = $response->json();

            if (! $response->successful()) {
                Log::error("Meta CAPI Error for Payment #{$payment->merchant_ref}: ".$response->body(), ['payload' => $payload]);

                return ['status' => 'failed', 'error' => $response->body()];
            }

            Log::info("Meta CAPI Purchase berhasil dikirim untuk Payment #{$payment->merchant_ref}. Events Received: ".($result['events_received'] ?? 0));

            return ['status' => 'success', 'response' => $result];
        } catch (Exception $e) {
            Log::error("Meta CAPI Exception for Payment #{$payment->merchant_ref}: ".$e->getMessage());

            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    /**
     * Kirim uji coba CAPI langsung dari Panel Admin.
     *
     * @return array<string, mixed>
     */
    public function sendTestEvent(Event $event, ?string $overrideTestCode = null): array
    {
        $pixelId = trim($event->meta_pixel_id ?? '');
        $capiToken = trim($event->meta_capi_token ?? '');
        $testCode = trim($overrideTestCode ?: ($event->meta_test_code ?? ''));

        if (empty($pixelId) || empty($capiToken)) {
            return ['status' => 'failed', 'message' => 'Pixel ID atau Access Token belum diisi.'];
        }

        $eventData = [
            'event_name' => 'Purchase',
            'event_time' => now()->timestamp,
            'event_id' => 'TEST-CAPI-'.strtoupper(uniqid()),
            'event_source_url' => route('events.show', ['slug' => $event->slug]),
            'action_source' => 'website',
            'user_data' => [
                'em' => [hash('sha256', 'test_runner@vira.id')],
                'ph' => [hash('sha256', '6281234567890')],
                'fn' => [hash('sha256', 'test')],
                'client_ip_address' => request()->ip() ?: '127.0.0.1',
                'client_user_agent' => request()->userAgent() ?: 'Mozilla/5.0 (Admin Test)',
            ],
            'custom_data' => [
                'currency' => 'IDR',
                'value' => 150000.00,
                'content_name' => "{$event->title} (Test Purchase)",
                'content_type' => 'product',
            ],
        ];

        $payload = [
            'data' => [$eventData],
        ];

        if (! empty($testCode)) {
            $payload['test_event_code'] = $testCode;
        }

        try {
            $url = "https://graph.facebook.com/v20.0/{$pixelId}/events";
            $response = Http::withToken($capiToken)->post($url, $payload);
            $result = $response->json();

            if (! $response->successful()) {
                return ['status' => 'failed', 'message' => $result['error']['message'] ?? $response->body()];
            }

            return ['status' => 'success', 'response' => $result];
        } catch (Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }
}
