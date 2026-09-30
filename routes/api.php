<?php

use App\Http\Controllers\Api\ShippingController;
use App\Http\Controllers\Api\TripayWebhookController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Tripay Payment Callback Webhook
Route::post('/tripay/callback', [TripayWebhookController::class, 'handle'])->name('api.tripay.callback');

// SPX Shipping Endpoints (Asal: Kab. Tangerang, Banten)
Route::prefix('shipping')->group(function () {
    Route::get('/cities', [ShippingController::class, 'cities'])->name('api.shipping.cities');
    Route::get('/districts', [ShippingController::class, 'districts'])->name('api.shipping.districts');
    Route::post('/calculate', [ShippingController::class, 'calculate'])->name('api.shipping.calculate');
    Route::post('/calculate-rate', [ShippingController::class, 'calculate']);
});
