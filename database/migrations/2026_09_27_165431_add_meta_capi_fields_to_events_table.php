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
        Schema::table('events', function (Blueprint $table) {
            $table->string('meta_pixel_id')->nullable()->after('banner_image');
            $table->text('meta_capi_token')->nullable()->after('meta_pixel_id');
            $table->string('meta_test_code', 50)->nullable()->after('meta_capi_token');
            $table->boolean('is_meta_capi_enabled')->default(false)->after('meta_test_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn([
                'meta_pixel_id',
                'meta_capi_token',
                'meta_test_code',
                'is_meta_capi_enabled',
            ]);
        });
    }
};
