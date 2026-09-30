<?php

$apiKey = env('TRIPAY_API_KEY', '');
$isDevKey = str_starts_with($apiKey, 'DEV-');
$isSandbox = $isDevKey || (bool) env('TRIPAY_SANDBOX', true);

return [
    /*
    |--------------------------------------------------------------------------
    | Tripay Payment Gateway Configuration
    |--------------------------------------------------------------------------
    |
    | Digunakan untuk membuat closed transaction, mengambil daftar payment
    | channel, dan memvalidasi webhook notifikasi callback signature.
    |
    */

    'api_key' => $apiKey,
    'private_key' => env('TRIPAY_PRIVATE_KEY', ''),
    'merchant_code' => env('TRIPAY_MERCHANT_CODE', ''),
    'sandbox' => $isSandbox,
    'api_url' => $isSandbox
        ? 'https://tripay.co.id/api-sandbox/'
        : 'https://tripay.co.id/api/',
    'default_channel' => env('TRIPAY_DEFAULT_CHANNEL', 'QRIS2'),
];
