<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Mailketing Transactional Email Configuration
    |--------------------------------------------------------------------------
    |
    | Digunakan untuk mengirim invoice pendaftaran, notifikasi nomor e-BIB,
    | serta 5-Stage Progressive Motivation Email setelah submission lari.
    |
    */

    'api_token' => env('MAILKETING_API_TOKEN', ''),
    'api_url' => env('MAILKETING_API_URL', 'https://api.mailketing.co.id/api/v1/send'),
    'origin_ip' => env('MAILKETING_ORIGIN_IP', '103.153.3.234'),
    'from_email' => env('MAILKETING_SENDER_EMAIL', 'hi@jelatix.com'),
    'from_name' => env('MAILKETING_SENDER_NAME', 'VIRA Virtual Sport'),
];
