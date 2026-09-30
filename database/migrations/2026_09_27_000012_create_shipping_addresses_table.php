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
        Schema::create('shipping_addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('registration_id')->constrained('registrations')->cascadeOnDelete();
            $table->string('recipient_name');
            $table->string('recipient_phone', 25);
            $table->string('province');
            $table->string('city');
            $table->string('district');
            $table->string('postal_code', 10);
            $table->text('address_detail');
            $table->string('jersey_size', 10)->nullable(); // S, M, L, XL, XXL, etc.

            // Perhitungan bobot & logistik pengiriman
            $table->unsignedInteger('total_weight_grams')->default(0);
            $table->string('courier_name')->nullable();
            $table->decimal('shipping_cost', 12, 2)->default(0.00);
            $table->string('tracking_number')->nullable(); // Nomor Resi
            $table->enum('shipping_status', ['PENDING', 'SHIPPED', 'DELIVERED'])->default('PENDING');
            $table->dateTime('shipped_at')->nullable();
            $table->timestamps();

            $table->index('registration_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipping_addresses');
    }
};
