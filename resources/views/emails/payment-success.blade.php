<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembayaran Berhasil - e-BIB Terbit</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f3f4f6; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color: #1f2937;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f3f4f6; padding: 40px 10px;">
        <tr>
            <td align="center">
                <table width="100%" style="max-width: 600px; background-color: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);" cellpadding="0" cellspacing="0">
                    
                    <!-- Header -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #059669 0%, #064e3b 100%); padding: 32px 30px; text-align: center; color: #ffffff;">
                            <div style="font-size: 26px; font-weight: 900; letter-spacing: 2px;">VIRA</div>
                            <div style="font-size: 13px; color: #a7f3d0; margin-top: 4px; text-transform: uppercase; letter-spacing: 1px;">Virtual Run • Virtual Ride • Virtual Walk</div>
                            <h1 style="font-size: 22px; font-weight: 700; margin: 20px 0 0 0; color: #ffffff;">Pembayaran Berhasil Terkonfirmasi! 🎉</h1>
                        </td>
                    </tr>

                    <!-- Body Content -->
                    <tr>
                        <td style="padding: 32px 30px;">
                            <p style="font-size: 15px; line-height: 1.6; margin-top: 0;">
                                Halo <strong>{{ $participant->full_name }}</strong>,
                            </p>
                            <p style="font-size: 15px; line-height: 1.6; color: #4b5563;">
                                Pembayaran Anda untuk event <strong>{{ $event->title }}</strong> telah berhasil diverifikasi oleh sistem Tripay. Nomor dada elektronik (e-BIB) resmi Anda kini telah aktif!
                            </p>

                            <!-- BIB Highlight Card -->
                            <div style="background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%); border: 2px solid #10b981; border-radius: 16px; padding: 24px; text-align: center; margin: 26px 0;">
                                <div style="font-size: 12px; font-weight: 700; color: #059669; text-transform: uppercase; letter-spacing: 1.5px;">Nomor Dada Elektronik Resmi</div>
                                <div style="font-size: 36px; font-weight: 900; color: #064e3b; letter-spacing: 3px; margin: 10px 0; font-family: 'Courier New', Courier, monospace;">
                                    {{ $registration->bib_number }}
                                </div>
                                <div style="font-size: 14px; color: #475569; font-weight: 600;">
                                    Kategori: {{ $registration->category->name }} (Target: {{ (float)$registration->category->target_distance_km }} KM)
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div style="text-align: center; margin: 30px 0;">
                                <a href="{{ $bibUrl }}" style="display: block; background-color: #059669; color: #ffffff; text-decoration: none; padding: 14px 28px; border-radius: 9999px; font-weight: 700; font-size: 15px; box-shadow: 0 4px 10px rgba(5, 150, 105, 0.3); margin-bottom: 12px;">
                                    📥 Unduh Kartu e-BIB Digital (PNG)
                                </a>
                                <a href="{{ $submitUrl }}" style="display: block; background-color: #4f46e5; color: #ffffff; text-decoration: none; padding: 14px 28px; border-radius: 9999px; font-weight: 700; font-size: 15px; box-shadow: 0 4px 10px rgba(79, 70, 229, 0.3);">
                                    ⚡ Buka Portal Submit Aktivitas Lari
                                </a>
                            </div>

                            <!-- How it Works -->
                            <div style="background-color: #f8fafc; border-radius: 12px; padding: 18px 20px; font-size: 13px; color: #4b5563; line-height: 1.6;">
                                <strong style="color: #1e293b; display: block; margin-bottom: 6px;">Cara Mencatat Hasil Lari:</strong>
                                1. Lari di mana saja dan kapan saja selama periode event berlangsung.<br>
                                2. Rekam menggunakan Strava, Garmin, Polar, atau aplikasi lari favoritmu.<br>
                                3. Buka link portal submit di atas, input nomor e-BIB <strong>{{ $registration->bib_number }}</strong> dan tempel link Strava Anda.<br>
                                4. Jarak akan otomatis terakumulasi hingga target tuntas!
                            </div>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #f8fafc; border-top: 1px solid #e2e8f0; padding: 20px 30px; text-align: center; font-size: 12px; color: #94a3b8;">
                            &copy; {{ date('Y') }} VIRA Platform. Dikirim otomatis via Mailketing.<br>
                            Ada kendala? Hubungi tim support kami di <a href="mailto:hi@jelatix.com" style="color: #059669; text-decoration: none;">hi@jelatix.com</a>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>
