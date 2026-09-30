<?php

namespace App\Services;

use App\Models\Registration;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MailketingService
{
    protected string $apiToken;

    protected string $apiUrl;

    protected string $fromEmail;

    protected string $fromName;

    protected ?string $originIp;

    public function __construct()
    {
        $this->apiToken = config('mailketing.api_token');
        $this->apiUrl = config('mailketing.api_url');
        $this->originIp = config('mailketing.origin_ip');
        $this->fromEmail = config('mailketing.from_email');
        $this->fromName = config('mailketing.from_name');
    }

    /**
     * Kirim email generik via Mailketing API.
     *
     * @return array<string, mixed>
     */
    public function send(string $recipient, string $subject, string $htmlContent): array
    {
        if (empty($this->apiToken)) {
            Log::warning("Mailketing API token kosong, email ke {$recipient} di-skip.");

            return ['status' => 'skipped', 'message' => 'API token missing'];
        }

        try {
            $options = [];
            if (! empty($this->originIp)) {
                $host = parse_url($this->apiUrl, PHP_URL_HOST) ?: 'api.mailketing.co.id';
                $port = (int) (parse_url($this->apiUrl, PHP_URL_PORT) ?: 443);
                $options['curl'] = [
                    CURLOPT_RESOLVE => ["{$host}:{$port}:{$this->originIp}"],
                ];
            }

            $request = Http::asForm()->timeout(10);
            if (! empty($options)) {
                $request = $request->withOptions($options);
            }

            $response = $request->post($this->apiUrl, [
                'api_token' => $this->apiToken,
                'from_email' => $this->fromEmail,
                'from_name' => $this->fromName,
                'recipient' => $recipient,
                'subject' => $subject,
                'content' => $htmlContent,
            ]);

            $result = $response->json();

            if (! $response->successful() || ($result['status'] ?? '') !== 'success') {
                Log::error("Gagal mengirim email via Mailketing ke {$recipient}: ".$response->body());

                return ['status' => 'failed', 'response' => $response->body()];
            }

            Log::info("Email Mailketing berhasil terkirim ke {$recipient}: [{$subject}]");

            return ['status' => 'success', 'response' => $result];
        } catch (Exception $e) {
            Log::error("Mailketing exception ke {$recipient}: ".$e->getMessage());

            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    /**
     * Kirim email tagihan pendaftaran baru (Invoice Pending).
     */
    public function sendInvoiceEmail(Registration $registration): array
    {
        $registration->loadMissing(['participant', 'event', 'category', 'package', 'payment', 'registrationAddOns.addOn', 'shippingAddress']);

        $participant = $registration->participant;
        $event = $registration->event;
        $payment = $registration->payment;

        if (! $participant || ! $participant->email) {
            return ['status' => 'skipped', 'message' => 'No participant email'];
        }

        $paymentUrl = $payment->checkout_url ?: route('payment.show', ['merchant_ref' => $payment->merchant_ref]);
        $subject = $event
            ? "Menunggu Pembayaran: Pendaftaran {$event->title} (#{$registration->registration_number})"
            : "Menunggu Pembayaran: Pesanan Merchandise VIRA (#{$registration->registration_number})";

        $html = view('emails.invoice', [
            'registration' => $registration,
            'participant' => $participant,
            'event' => $event,
            'payment' => $payment,
            'paymentUrl' => $paymentUrl,
        ])->render();

        return $this->send($participant->email, $subject, $html);
    }

    /**
     * Kirim email konfirmasi pembayaran sukses lengkap dengan nomor e-BIB dan link unduh kartu.
     */
    public function sendPaymentSuccessEmail(Registration $registration): array
    {
        $registration->loadMissing(['participant', 'event', 'category', 'payment']);

        $participant = $registration->participant;
        $event = $registration->event;

        if (! $participant || ! $participant->email) {
            return ['status' => 'skipped', 'message' => 'No participant email'];
        }

        if (! $event) {
            // Email konfirmasi pembayaran pesanan merchandise mandiri
            $subject = "Pembayaran Terkonfirmasi! Pesanan Merchandise VIRA (#{$registration->registration_number})";
            $html = "<div style='font-family:sans-serif;padding:20px;color:#333;'><h2>Pembayaran Berhasil!</h2><p>Halo {$participant->full_name}, pembayaran pesanan merchandise Anda telah berhasil diverifikasi. Paket Anda akan segera kami kemas dan kirimkan via SPX Express.</p></div>";

            return $this->send($participant->email, $subject, $html);
        }

        $bibUrl = route('participant.download.bib', ['identifier' => $registration->bib_number]);
        $submitUrl = route('submit.index', ['bib' => $registration->bib_number]);
        $subject = "Pembayaran Terkonfirmasi! e-BIB Resmi Kamu: {$registration->bib_number} - {$event->title}";

        $html = view('emails.payment-success', [
            'registration' => $registration,
            'participant' => $participant,
            'event' => $event,
            'bibUrl' => $bibUrl,
            'submitUrl' => $submitUrl,
        ])->render();

        return $this->send($participant->email, $subject, $html);
    }

    /**
     * Kirim 5-Stage Progressive Motivation Email (Milestone 20%, 40%, 60%, 80%, 100%).
     */
    public function sendMilestoneEmail(Registration $registration, int $milestone, string $quote): array
    {
        $registration->loadMissing(['participant', 'event', 'category']);

        $participant = $registration->participant;
        $event = $registration->event;

        if (! $participant || ! $participant->email) {
            return ['status' => 'skipped', 'message' => 'No participant email'];
        }

        $certificateUrl = ($milestone >= 100)
            ? route('participant.download.certificate', ['identifier' => $registration->bib_number])
            : null;

        $submitUrl = route('submit.index', ['bib' => $registration->bib_number]);

        $subject = ($milestone >= 100)
            ? "🏆 SELAMAT FINISHER! Kamu telah menuntaskan 100% {$event->title}"
            : "🔥 Hebat! Kamu sudah mencapai progres {$milestone}% di {$event->title}";

        $html = view('emails.milestone-progress', [
            'registration' => $registration,
            'participant' => $participant,
            'event' => $event,
            'milestone' => $milestone,
            'quote' => $quote,
            'certificateUrl' => $certificateUrl,
            'submitUrl' => $submitUrl,
        ])->render();

        return $this->send($participant->email, $subject, $html);
    }
}
