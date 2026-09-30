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
        Schema::create('template_designs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('events')->cascadeOnDelete();
            $table->enum('type', ['BIB', 'CERTIFICATE']);
            $table->string('background_image_path')->nullable();
            $table->unsignedInteger('canvas_width')->default(1200);
            $table->unsignedInteger('canvas_height')->default(800);
            $table->json('elements_config'); // Koordinat X, Y, font_size, color, align per elemen dinamis
            $table->timestamps();

            $table->unique(['event_id', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('template_designs');
    }
};
