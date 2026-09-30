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
        Schema::create('packages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('events')->cascadeOnDelete();
            $table->string('name'); // "Digital Finisher Pack", "Race Pack + Jersey & Medal"
            $table->text('description')->nullable();
            $table->decimal('price', 12, 2)->default(0.00);
            $table->unsignedInteger('base_weight_grams')->default(0);
            $table->boolean('includes_jersey')->default(false);
            $table->boolean('includes_medal')->default(false);
            $table->boolean('requires_shipping')->default(false);
            $table->timestamps();

            $table->index('event_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('packages');
    }
};
