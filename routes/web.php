<?php

use App\Http\Controllers\Admin\AddOnController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DesignerController;
use App\Http\Controllers\Admin\RegistrationManagementController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ParticipantDownloadController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\RegistrationController;
use App\Http\Controllers\StorefrontController;
use App\Http\Controllers\UniversalSubmissionController;
use Illuminate\Support\Facades\Route;

// Beranda & List Event
Route::get('/', [HomeController::class, 'index'])->name('home');

// Detail Event & Pendaftaran
Route::get('/event/{slug}', [EventController::class, 'show'])->name('events.show');
Route::get('/event/{slug}/register', [RegistrationController::class, 'create'])->name('events.register');
Route::post('/event/{slug}/register', [RegistrationController::class, 'store'])->name('events.register.store');

// Official Store & Merchandise Shop (Bisa dibeli mandiri tanpa lewat event)
Route::get('/etalase', [StorefrontController::class, 'index'])->name('etalase.index');
Route::get('/shop', [StorefrontController::class, 'index'])->name('shop.index');
Route::get('/shop/checkout', [StorefrontController::class, 'checkout'])->name('shop.checkout');
Route::post('/shop/checkout', [StorefrontController::class, 'storeOrder'])->name('shop.order.store');

// Tagihan Pembayaran & Simulasi
Route::get('/payment/{merchant_ref}', [PaymentController::class, 'show'])->name('payment.show');
Route::match(['GET', 'POST'], '/payment/{merchant_ref}/simulate-pay', [PaymentController::class, 'simulateSuccess'])->name('payment.simulate');

// Universal Submission Portal (Satu Link untuk Semua Event!)
Route::get('/submit', [UniversalSubmissionController::class, 'index'])->name('submit.index');
Route::post('/submit/lookup', [UniversalSubmissionController::class, 'lookup'])->name('submit.lookup');
Route::post('/submit/record', [UniversalSubmissionController::class, 'record'])->name('submit.record');

// Unduh Kartu e-BIB & E-Sertifikat Digital (Format PNG Resolusi Tinggi)
Route::get('/p/{identifier}/download-bib', [ParticipantDownloadController::class, 'downloadBib'])->name('participant.download.bib');
Route::get('/p/{identifier}/download-certificate', [ParticipantDownloadController::class, 'downloadCertificate'])->name('participant.download.certificate');

// Admin Authentication (Public Login & Rate Limiting)
Route::prefix('admin')->group(function () {
    Route::get('/login', [AuthController::class, 'create'])->name('admin.login');
    Route::post('/login', [AuthController::class, 'store'])->name('admin.login.store')->middleware('throttle:10,1');
});

// Admin Backoffice: Event Management & Meta Conversions API (CAPI)
Route::redirect('/admin', '/admin/events');
Route::prefix('admin')->middleware(['auth', 'admin'])->group(function () {
    Route::post('/logout', [AuthController::class, 'destroy'])->name('admin.logout');

    Route::get('/events', [App\Http\Controllers\Admin\EventController::class, 'index'])->name('admin.events.index');
    Route::get('/events/create', [App\Http\Controllers\Admin\EventController::class, 'create'])->name('admin.events.create');
    Route::post('/events', [App\Http\Controllers\Admin\EventController::class, 'store'])->name('admin.events.store');
    Route::get('/events/{event}/edit', [App\Http\Controllers\Admin\EventController::class, 'edit'])->name('admin.events.edit');
    Route::put('/events/{event}', [App\Http\Controllers\Admin\EventController::class, 'update'])->name('admin.events.update');
    Route::delete('/events/{event}', [App\Http\Controllers\Admin\EventController::class, 'destroy'])->name('admin.events.destroy');
    Route::post('/events/upload-description-image', [App\Http\Controllers\Admin\EventController::class, 'uploadDescriptionImage'])->name('admin.events.upload-description-image');
    Route::post('/events/{event}/test-capi', [App\Http\Controllers\Admin\EventController::class, 'testCapi'])->name('admin.events.test-capi');

    // Visual Canvas Designer (Koordinat e-BIB & E-Sertifikat)
    Route::get('/events/{event}/designer/{type}', [DesignerController::class, 'edit'])->name('admin.designer.edit');
    Route::post('/events/{event}/designer/{type}', [DesignerController::class, 'update'])->name('admin.designer.update');
    Route::get('/events/{event}/designer/{type}/preview', [DesignerController::class, 'preview'])->name('admin.designer.preview');

    // Data Peserta, Monitoring Pembayaran & Export CSV
    Route::get('/registrations', [RegistrationManagementController::class, 'index'])->name('admin.registrations.index');
    Route::post('/registrations/{registration}/mark-as-paid', [RegistrationManagementController::class, 'markAsPaid'])->name('admin.registrations.mark-as-paid');
    Route::get('/registrations/export', [RegistrationManagementController::class, 'export'])->name('admin.registrations.export');
    Route::get('/registrations/jersey-orders', [RegistrationManagementController::class, 'jerseyOrders'])->name('admin.registrations.jersey-orders');
    Route::get('/registrations/jersey-orders/export', [RegistrationManagementController::class, 'exportJerseyOrders'])->name('admin.registrations.jersey-orders.export');

    // Pengaturan Item Add-ons & Merchandise
    Route::patch('/addons/{addon}/toggle-status', [AddOnController::class, 'toggleStatus'])->name('admin.addons.toggle-status');
    Route::resource('addons', AddOnController::class)->names('admin.addons');
});
