<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Abidjan couvre une métropole de ~50km de rayon, pas 20km
        DB::table('delivery_cities')->where('name', 'Abidjan')->update([
            'coverage_radius_km'       => 50,
            'max_delivery_distance_km' => 25,
        ]);

        // Active la livraison sur tous les restaurants qui ont des coordonnées GPS valides
        DB::table('restaurants')
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->where('latitude', '!=', 0)
            ->where('longitude', '!=', 0)
            ->update(['delivery_enabled' => true]);

        // Vide le cache des villes de livraison
        try {
            \Illuminate\Support\Facades\Cache::forget('delivery_cities:active');
        } catch (\Throwable) {
            // Redis absent en local — pas bloquant
        }
    }

    public function down(): void
    {
        DB::table('delivery_cities')->where('name', 'Abidjan')->update([
            'coverage_radius_km'       => 20,
            'max_delivery_distance_km' => 12,
        ]);
    }
};
