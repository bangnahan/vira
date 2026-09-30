<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Services\MailketingService;
use App\Services\MetaCapiService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function __construct(
        protected MailketingService $mailketingService,
        protected MetaCapiService $metaCapiService
    ) {}

    /**
     * Tampilkan rincian tagihan pembayaran.
     */
    public function show(string $merchant_ref): View
    {
        $payment = Payment::with([
            'registration.event',
            'registration.category',
            'registration.package',
            'registration.participant',
            'registration.registrationAddOns.addOn',
            'registration.registrationAddOns.variant',
            'registration.shippingAddress',
        ])
            ->where('merchant_ref', $merchant_ref)
            ->firstOrFail();

        return view('payments.show', compact('payment'));
    }

    /**
     * Simulasi Pembayaran Sukses (Mode Pengujian Lokal & Sandbox).
     */
    public function simulateSuccess(Request $request, string $merchant_ref): RedirectResponse
    {
        if (! config('tripay.sandbox', false) && ! (auth()->check() && auth()->user()->isAdmin())) {
            abort(403, 'Simulasi pembayaran dinonaktifkan pada mode produksi.');
        }

        $payment = Payment::with(['registration.category', 'registration.event'])
            ->where('merchant_ref', $merchant_ref)
            ->firstOrFail();

        if ($payment->isPaid()) {
            return redirect()->route('payment.show', ['merchant_ref' => $merchant_ref])
                ->with('info', 'Tagihan ini sudah lunas sebelumnya.');
        }

        $registration = $payment->registration;

        DB::transaction(function () use ($payment, $registration) {
            // 1. Update status payment
            $payment->update([
                'status' => 'PAID',
                'paid_at' => now(),
            ]);

            // 2. Jika pendaftaran event, terbitkan nomor e-BIB
            if ($registration->event_id && $registration->category) {
                if (empty($registration->bib_number)) {
                    $newBib = $registration->category->generateNextBibNumber();
                    $registration->update([
                        'payment_status' => 'PAID',
                        'bib_number' => $newBib,
                    ]);
                } else {
                    $registration->update(['payment_status' => 'PAID']);
                }
            } else {
                $registration->update(['payment_status' => 'PAID']);
            }
        });

        // 3. Kirim notifikasi jika pendaftaran event
        if ($registration->event_id && $registration->category) {
            // Kirim email notifikasi e-BIB via Mailketing
            try {
                $this->mailketingService->sendPaymentSuccessEmail($registration->fresh());
            } catch (\Exception $e) {
                Log::warning('Mailketing send payment success error in simulate: '.$e->getMessage());
            }

            // Kirim server-side Meta CAPI Purchase event
            try {
                $this->metaCapiService->sendPurchaseEvent($payment->fresh(['registration.event', 'registration.participant', 'registration.category']));
            } catch (\Exception $e) {
                Log::warning('Meta CAPI purchase send error in simulate: '.$e->getMessage());
            }

            return redirect()->route('payment.show', ['merchant_ref' => $merchant_ref])
                ->with('success', 'Pembayaran berhasil dikonfirmasi! Nomor e-BIB Anda: '.$registration->bib_number);
        }

        // 3. Jika pembelian merchandise mandiri (Official Store)
        $registration->update(['payment_status' => 'PAID']);

        return redirect()->route('payment.show', ['merchant_ref' => $merchant_ref])
            ->with('success', 'Pembayaran pesanan merchandise berhasil dikonfirmasi! Paket Anda sedang diproses.');
    }
}
