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
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('event_code', 10)->unique(); // Kode event unik (e.g. MVR26, JVR26)
            $table->enum('activity_type', ['RUN', 'RIDE', 'WALK'])->default('RUN');
            $table->enum('submission_mode', ['SINGLE', 'CUMULATIVE'])->default('CUMULATIVE');
            $table->enum('race_type', ['CHALLENGE', 'RACE'])->default('CHALLENGE');
            $table->text('description')->nullable();
            $table->text('rules_and_terms')->nullable();
            $table->dateTime('registration_start');
            $table->dateTime('registration_end');
            $table->dateTime('race_start');
            $table->dateTime('race_end');
            $table->string('banner_image')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['activity_type', 'is_active']);
            $table->index(['registration_start', 'registration_end']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
