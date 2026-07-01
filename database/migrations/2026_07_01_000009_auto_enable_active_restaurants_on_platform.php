<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Changer le défaut à true pour les nouveaux restaurants
        Schema::table('restaurants', function (Blueprint $table) {
            $table->boolean('is_on_platform')->default(true)->change();
        });

        // Activer tous les restaurants actifs ayant des coordonnées GPS
        DB::table('restaurants')
            ->where('status', 'active')
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->where('latitude', '!=', '')
            ->where('longitude', '!=', '')
            ->update([
                'is_on_platform'    => true,
                'platform_category' => DB::raw("COALESCE(NULLIF(platform_category, ''), 'restaurant')"),
            ]);
    }

    public function down(): void
    {
        Schema::table('restaurants', function (Blueprint $table) {
            $table->boolean('is_on_platform')->default(false)->change();
        });
    }
};
