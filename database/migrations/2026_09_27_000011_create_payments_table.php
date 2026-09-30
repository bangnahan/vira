<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('registration_id')->constrained('registrations')->cascadeOnDelete();
            $table->string('tripay_reference')->unique()->nullable();
            $table->string('merchant_ref')->unique();
            $table->string('payment_method'); // QRIS, BRIVA, MANDIRIVA, BCAVA, OVO, dll.
            $table->decimal('amount', 12, 2);
            $table->decimal('admin_fee', 12, 2)->default(0.00);
            $table->decimal('shipping_cost', 12, 2)->default(0.00);
            $table->decimal('total_amount', 12, 2);
            $table->string('checkout_url')->nullable();
            $table->text('qr_code_url')->nullable();
            $table->string('pay_code')->nullable(); // Kode bayar VA / Retail
            $table->enum('status', ['UNPAID', 'PAID', 'EXPIRED', 'FAILED'])->default('UNPAID');
            $table->dateTime('paid_at')->nullable();
            $table->dateTime('expired_at')->nullable();
            $table->json('raw_callback')->nullable();
            $table->timestamps();

            $table->index(['merchant_ref', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
