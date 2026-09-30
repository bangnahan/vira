<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Progres Lari - VIRA</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f3f4f6; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color: #1f2937;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f3f4f6; padding: 40px 10px;">
        <tr>
            <td align="center">
                <table width="100%" style="max-width: 600px; background-color: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);" cellpadding="0" cellspacing="0">
                    
                    <!-- Header -->
                    <tr>
                        <td style="background: @if($milestone >= 100) linear-gradient(135deg, #f59e0b 0%, #b45309 100%) @else linear-gradient(135deg, #4f46e5 0%, #312e81 100%) @endif; padding: 32px 30px; text-align: center; color: #ffffff;">
                            <div style="font-size: 26px; font-weight: 900; letter-spacing: 2px;">VIRA</div>
                            <div style="font-size: 13px; color: rgba(255,255,255,0.8); margin-top: 4px; text-transform: uppercase; letter-spacing: 1px;">Milestone Tracker • {{ $event->title }}</div>
                            <h1 style="font-size: 22px; font-weight: 800; margin: 20px 0 0 0; color: #ffffff;">
                                @if($milestone >= 100)
                                    🏆 SELAMAT, KAMU FINISHER RESMI!
                                @else
                                    🔥 Progres {{ $milestone }}% Tercapai!
                                @endif
                            </h1>
                        </td>
                    </tr>

                    <!-- Body Content -->
                    <tr>
                        <td style="padding: 32px 30px;">
                            <p style="font-size: 15px; line-height: 1.6; margin-top: 0;">
                                Semangat luar biasa, <strong>{{ $participant->full_name }}</strong>!
                            </p>

                            <!-- Motivational Quote Callout -->
                            <div style="background-color: #eff6ff; border-left: 4px solid #3b82f6; border-radius: 0 12px 12px 0; padding: 18px 20px; margin: 20px 0; font-size: 15px; font-style: italic; color: #1e3a8a; line-height: 1.6;">
                                &ldquo;{{ $quote }}&rdquo;
                            </div>

                            <!-- Progress Bar Container -->
                            <div style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px; margin: 24px 0;">
                                <div style="display: flex; justify-content: space-between; font-size: 14px; font-weight: 700; margin-bottom: 8px; color: #334155;">
                                    <span>Pencapaian Jarak</span>
                                    <span style="color: #4f46e5;">{{ (float)$registration->total_distance_km }} km / {{ (float)$registration->category->target_distance_km }} km</span>
                                </div>
                                
                                <!-- Progress Track -->
                                <div style="background-color: #e2e8f0; border-radius: 9999px; height: 16px; width: 100%; overflow: hidden;">
                                    <div style="background: linear-gradient(90deg, #4f46e5, #10b981); height: 100%; width: {{ min(100, $milestone) }}%; border-radius: 9999px;"></div>
                                </div>

                                <div style="text-align: right; font-size: 12px; color: #64748b; margin-top: 6px; font-weight: 600;">
                                    {{ $milestone }}% Selesai
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div style="text-align: center; margin: 30px 0;">
                                @if($milestone >= 100 && $certificateUrl)
                                    <a href="{{ $certificateUrl }}" style="display: block; background-color: #f59e0b; color: #ffffff; text-decoration: none; padding: 14px 28px; border-radius: 9999px; font-weight: 700; font-size: 16px; box-shadow: 0 4px 10px rgba(245, 158, 11, 0.4); margin-bottom: 12px;">
                                        🎓 Unduh E-Sertifikat Finisher (PNG)
                                    </a>
                                @endif

                                <a href="{{ $submitUrl }}" style="display: block; background-color: #4f46e5; color: #ffffff; text-decoration: none; padding: 12px 28px; border-radius: 9999px; font-weight: 700; font-size: 14px; box-shadow: 0 4px 10px rgba(79, 70, 229, 0.3);">
                                    @if($milestone >= 100)
                                        Lihat Riwayat &amp; Rincian Selesai
                                    @else
                                        Catat Lari Berikutnya (Nomor e-BIB: {{ $registration->bib_number }})
                                    @endif
                                </a>
                            </div>

                            <p style="font-size: 13px; line-height: 1.5; color: #64748b; margin-bottom: 0;">
                                Tetap semangat, jaga kesehatan, dan selalu utamakan keselamatan saat berolahraga!
                            </p>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #f8fafc; border-top: 1px solid #e2e8f0; padding: 20px 30px; text-align: center; font-size: 12px; color: #94a3b8;">
                            &copy; {{ date('Y') }} VIRA Platform. Dikirim melalui Mailketing Transaksional.<br>
                            Virtual Run • Virtual Ride • Virtual Walk
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>
