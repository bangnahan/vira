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
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('events')->cascadeOnDelete();
            $table->string('name'); // "5K", "10K", "21K Half Marathon"
            $table->decimal('target_distance_km', 8, 2);
            $table->string('bib_prefix', 10)->default('BIB'); // e.g. 05K, 10K
            $table->unsignedInteger('last_bib_sequence')->default(0);
            $table->unsignedInteger('quota')->nullable();
            $table->unsignedInteger('registered_count')->default(0);
            $table->timestamps();

            $table->index('event_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
