<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Services\MailketingService;
use App\Services\MetaCapiService;
use App\Services\TripayService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TripayWebhookController extends Controller
{
    public function __construct(
        protected TripayService $tripayService,
        protected MailketingService $mailketingService,
        protected MetaCapiService $metaCapiService
    ) {}

    /**
     * Handle webhook notifikasi callback status pembayaran dari Tripay.
     */
    public function handle(Request $request): JsonResponse
    {
        $callbackSignature = $request->header('X-Callback-Signature');
        $callbackEvent = $request->header('X-Callback-Event');
        $rawContent = $request->getContent();

        // 1. Verifikasi Signature Tripay
        if (! $this->tripayService->verifyWebhookSignature($rawContent, $callbackSignature)) {
            Log::warning('Tripay webhook callback invalid signature', [
                'received_signature' => $callbackSignature,
                'content' => $rawContent,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Invalid signature',
            ], 403);
        }

        // Pastikan event adalah payment_status
        if ($callbackEvent !== 'payment_status') {
            return response()->json([
                'success' => true,
                'message' => 'Unrecognized event ignored',
            ]);
        }

        $data = $request->json()->all();
        $merchantRef = $data['merchant_ref'] ?? null;
        $status = strtoupper($data['status'] ?? '');

        if (! $merchantRef) {
            return response()->json([
                'success' => false,
                'message' => 'Missing merchant_ref',
            ], 400);
        }

        $payment = Payment::with(['registration.category', 'registration.event', 'registration.participant'])
            ->where('merchant_ref', $merchantRef)
            ->first();

        if (! $payment) {
            Log::error("Tripay webhook: Payment dengan merchant_ref '{$merchantRef}' tidak ditemukan.");

            return response()->json([
                'success' => false,
                'message' => 'Payment reference not found',
            ], 404);
        }

        $registration = $payment->registration;

        if ($status === 'PAID') {
            if ($payment->isPaid()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Payment already processed',
                ]);
            }

            DB::transaction(function () use ($payment, $registration, $data) {
                // Update payment status
                $payment->update([
                    'status' => 'PAID',
                    'tripay_reference' => $data['reference'] ?? $payment->tripay_reference,
                    'paid_at' => now(),
                    'raw_callback' => $data,
                ]);

                // Generate e-BIB jika pendaftaran event
                if ($registration->event_id && $registration->category && empty($registration->bib_number)) {
                    $registration->bib_number = $registration->category->generateNextBibNumber();
                }

                $registration->payment_status = 'PAID';
                $registration->save();
            });

            // Kirim notifikasi email e-BIB ke peserta via Mailketing jika pendaftaran event
            if ($registration->event_id) {
                try {
                    $this->mailketingService->sendPaymentSuccessEmail($registration->fresh());
                } catch (\Exception $e) {
                    Log::error('Gagal mengirim email konfirmasi pembayaran Mailketing: '.$e->getMessage());
                }

                // Kirim Server-side Purchase Event ke Meta CAPI jika dikonfigurasi
                try {
                    $this->metaCapiService->sendPurchaseEvent($payment->fresh(['registration.event', 'registration.participant', 'registration.category']));
                } catch (\Exception $e) {
                    Log::error('Gagal mengirim Meta CAPI Purchase event: '.$e->getMessage());
                }
            }

            Log::info("Tripay webhook: Payment {$merchantRef} berhasil di-set PAID. ".($registration->bib_number ? "Nomor e-BIB: {$registration->bib_number}" : 'Order Merchandise Standalone.'));

            return response()->json([
                'success' => true,
                'message' => 'Payment processed successfully',
            ]);
        }

        if (in_array($status, ['EXPIRED', 'FAILED'])) {
            $payment->update([
                'status' => $status,
                'raw_callback' => $data,
            ]);

            if ($registration->payment_status === 'UNPAID') {
                $registration->update(['payment_status' => $status]);
            }

            return response()->json([
                'success' => true,
                'message' => "Payment marked as {$status}",
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Status acknowledged',
        ]);
    }
}
