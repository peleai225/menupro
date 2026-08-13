<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('promo_banners', function (Blueprint $table) {
            $table->string('color_start', 20)->default('#F97316')->after('subtitle');
            $table->string('color_end', 20)->default('#EA580C')->after('color_start');
        });
    }

    public function down(): void
    {
        Schema::table('promo_banners', function (Blueprint $table) {
            $table->dropColumn(['color_start', 'color_end']);
        });
    }
};
