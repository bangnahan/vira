<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menunggu Pembayaran - VIRA Virtual Sport</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f3f4f6; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color: #1f2937;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f3f4f6; padding: 40px 10px;">
        <tr>
            <td align="center">
                <table width="100%" style="max-width: 600px; background-color: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);" cellpadding="0" cellspacing="0">
                    
                    <!-- Header -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #4f46e5 0%, #312e81 100%); padding: 32px 30px; text-align: center; color: #ffffff;">
                            <div style="font-size: 26px; font-weight: 900; letter-spacing: 2px;">VIRA</div>
                            <div style="font-size: 13px; color: #c7d2fe; margin-top: 4px; text-transform: uppercase; letter-spacing: 1px;">Virtual Run • Virtual Ride • Virtual Walk</div>
                            <h1 style="font-size: 20px; font-weight: 700; margin: 20px 0 0 0; color: #ffffff;">Tagihan Pendaftaran Siap Dibayar</h1>
                        </td>
                    </tr>

                    <!-- Body Content -->
                    <tr>
                        <td style="padding: 32px 30px;">
                            <p style="font-size: 15px; line-height: 1.6; margin-top: 0;">
                                Halo <strong>{{ $participant->full_name }}</strong>,
                            </p>
                            <p style="font-size: 15px; line-height: 1.6; color: #4b5563;">
                                @if($event)
                                    Terima kasih telah mendaftar di event <strong>{{ $event->title }}</strong>! Untuk mengaktifkan pendaftaran dan menerbitkan nomor e-BIB resmi Anda, silakan selesaikan pembayaran tagihan berikut:
                                @else
                                    Terima kasih telah berbelanja di <strong>VIRA Official Store</strong>! Silakan selesaikan pembayaran berikut agar pesanan Anda dapat segera kami proses dan kirimkan via SPX Express:
                                @endif
                            </p>

                            <!-- Invoice Card -->
                            <div style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px; margin: 24px 0;">
                                <table width="100%" cellpadding="6" cellspacing="0" style="font-size: 14px;">
                                    <tr>
                                        <td style="color: #64748b;">No. Pesanan / Registrasi</td>
                                        <td align="right" style="font-weight: 700; font-family: monospace;">{{ $registration->registration_number }}</td>
                                    </tr>
                                    @if($registration->category)
                                        <tr>
                                            <td style="color: #64748b;">Kategori Event</td>
                                            <td align="right" style="font-weight: 600;">{{ $registration->category->name }} ({{ (float)$registration->category->target_distance_km }}K)</td>
                                        </tr>
                                    @endif
                                    @if($registration->package)
                                        <tr>
                                            <td style="color: #64748b;">Paket Pendaftaran</td>
                                            <td align="right" style="font-weight: 600;">{{ $registration->package->name }}</td>
                                        </tr>
                                    @endif
                                    @if($registration->registrationAddOns->count() > 0)
                                        <tr>
                                            <td style="color: #64748b;">Add-on / Merchandise</td>
                                            <td align="right" style="font-weight: 600;">
                                                @foreach($registration->registrationAddOns as $item)
                                                    {{ $item->addOn->name }} (x{{ $item->quantity }})<br>
                                                @endforeach
                                            </td>
                                        </tr>
                                    @endif
                                    @if($payment->shipping_cost > 0)
                                        <tr>
                                            <td style="color: #64748b;">Ongkos Kirim SPX</td>
                                            <td align="right" style="font-weight: 600;">Rp {{ number_format($payment->shipping_cost, 0, ',', '.') }}</td>
                                        </tr>
                                    @endif
                                    <tr style="border-top: 2px dashed #cbd5e1;">
                                        <td style="padding-top: 12px; font-weight: 700; font-size: 16px; color: #1e293b;">Total Tagihan</td>
                                        <td align="right" style="padding-top: 12px; font-weight: 800; font-size: 18px; color: #4f46e5;">
                                            Rp {{ number_format($payment->total_amount, 0, ',', '.') }}
                                        </td>
                                    </tr>
                                </table>
                            </div>

                            <!-- CTA Button -->
                            <div style="text-align: center; margin: 30px 0;">
                                <a href="{{ $paymentUrl }}" style="display: inline-block; background-color: #4f46e5; color: #ffffff; text-decoration: none; padding: 14px 32px; border-radius: 9999px; font-weight: 700; font-size: 16px; box-shadow: 0 4px 10px rgba(79, 70, 229, 0.4);">
                                    Bayar Sekarang via Tripay
                                </a>
                                <p style="font-size: 12px; color: #94a3b8; margin-top: 10px;">
                                    Tersedia QRIS (Gopay, OVO, ShopeePay, DANA) &amp; Virtual Account Bank.
                                </p>
                            </div>

                            <p style="font-size: 13px; line-height: 1.5; color: #64748b; margin-bottom: 0;">
                                <em>Catatan: Nomor e-BIB elektronik resmi Anda akan otomatis terbit dan dikirimkan segera setelah sistem mendeteksi pembayaran berhasil.</em>
                            </p>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #f8fafc; border-top: 1px solid #e2e8f0; padding: 20px 30px; text-align: center; font-size: 12px; color: #94a3b8;">
                            &copy; {{ date('Y') }} VIRA Platform. Dikirim melalui Mailketing Transaksional.<br>
                            Jika ada pertanyaan, hubungi tim kami di <a href="mailto:hi@jelatix.com" style="color: #4f46e5; text-decoration: none;">hi@jelatix.com</a>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>
