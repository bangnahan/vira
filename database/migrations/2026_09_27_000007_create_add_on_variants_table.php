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
        Schema::create('add_on_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('add_on_id')->constrained('add_ons')->cascadeOnDelete();
            $table->string('variant_name'); // e.g. "Ukuran S", "Ukuran M", "Warna Hitam"
            $table->decimal('additional_price', 12, 2)->default(0.00);
            $table->unsignedInteger('stock')->default(50);
            $table->timestamps();

            $table->index('add_on_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('add_on_variants');
    }
};
