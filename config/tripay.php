<?php

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

    'api_key' => env('TRIPAY_API_KEY', ''),
    'private_key' => env('TRIPAY_PRIVATE_KEY', ''),
    'merchant_code' => env('TRIPAY_MERCHANT_CODE', ''),
    'sandbox' => env('TRIPAY_SANDBOX', true),
    'api_url' => env('TRIPAY_SANDBOX', true)
        ? 'https://tripay.co.id/api-sandbox/'
        : 'https://tripay.co.id/api/',
    'default_channel' => env('TRIPAY_DEFAULT_CHANNEL', 'QRIS2'),
];
