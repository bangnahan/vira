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
        Schema::create('activity_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('registration_id')->constrained('registrations')->cascadeOnDelete();
            $table->date('activity_date');
            $table->time('activity_time')->nullable();
            $table->decimal('distance_km', 8, 2);
            $table->unsignedInteger('duration_seconds');
            $table->decimal('calculated_pace', 5, 2)->nullable(); // Menit per KM
            $table->text('proof_url'); // Link Strava, GDrive, Garmin, dll.
            $table->text('notes')->nullable();

            // Status Validasi Admin (khusus lomba / review pemenang)
            $table->boolean('is_potential_winner')->default(false);
            $table->enum('validation_status', ['VALID', 'REVIEW', 'REJECTED'])->default('VALID');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('admin_notes')->nullable();

            $table->timestamps();

            $table->index(['registration_id', 'activity_date']);
            $table->index(['validation_status', 'is_potential_winner']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_submissions');
    }
};
