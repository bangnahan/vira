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
        Schema::create('registrations', function (Blueprint $table) {
            $table->id();
            $table->uuid('access_token')->unique(); // Token unik untuk direct URL akses
            $table->foreignId('event_id')->constrained('events')->cascadeOnDelete();
            $table->foreignId('category_id')->constrained('categories')->cascadeOnDelete();
            $table->foreignId('package_id')->constrained('packages')->cascadeOnDelete();
            $table->foreignId('participant_id')->constrained('participants')->cascadeOnDelete();

            $table->string('bib_number', 30)->nullable()->unique(); // Global unique across entire app
            $table->string('bib_image_path')->nullable();
            $table->string('certificate_path')->nullable();

            $table->enum('payment_status', ['UNPAID', 'PAID', 'EXPIRED', 'CANCELLED'])->default('UNPAID');

            // Progres Lari & Milestone Motivasi
            $table->decimal('total_distance_km', 8, 2)->default(0.00);
            $table->unsignedInteger('total_duration_seconds')->default(0);
            $table->unsignedTinyInteger('last_milestone_notified')->default(0); // 0, 20, 40, 60, 80, 100
            $table->enum('finisher_status', ['IN_PROGRESS', 'FINISHED'])->default('IN_PROGRESS');
            $table->dateTime('finished_at')->nullable();

            $table->timestamps();

            $table->index(['event_id', 'bib_number']);
            $table->index(['event_id', 'finisher_status']);
            $table->index('payment_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('registrations');
    }
};
