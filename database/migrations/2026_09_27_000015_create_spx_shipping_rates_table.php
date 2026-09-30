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
        Schema::create('spx_shipping_rates', function (Blueprint $table) {
            $table->id();
            $table->string('origin_city')->default('KAB. TANGERANG');
            $table->string('destination_city')->index();
            $table->string('destination_district')->index();
            $table->decimal('rate_hemat', 12, 2)->default(0.00);
            $table->unsignedSmallInteger('sla_hemat_days')->default(7);
            $table->decimal('rate_regular', 12, 2)->default(0.00);
            $table->unsignedSmallInteger('sla_regular_days')->default(3);
            $table->timestamps();

            $table->index(['destination_city', 'destination_district']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('spx_shipping_rates');
    }
};
