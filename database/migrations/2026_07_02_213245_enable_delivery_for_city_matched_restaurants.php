<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Active delivery_enabled sur les restaurants dont la ville correspond à une
        // DeliveryCity active en base, même s'ils n'ont pas encore de coordonnées GPS.
        $cityNames = DB::table('delivery_cities')
            ->where('is_active', true)
            ->pluck('name')
            ->map(fn ($n) => mb_strtolower($n))
            ->all();

        if (empty($cityNames)) {
            return;
        }

        DB::table('restaurants')
            ->where('delivery_enabled', false)
            ->whereNotNull('city')
            ->where('city', '!=', '')
            ->get(['id', 'city'])
            ->each(function ($restaurant) use ($cityNames) {
                if (in_array(mb_strtolower($restaurant->city), $cityNames, true)) {
                    DB::table('restaurants')
                        ->where('id', $restaurant->id)
                        ->update(['delivery_enabled' => true]);
                }
            });

        try {
            \Illuminate\Support\Facades\Cache::forget('delivery_cities:active');
        } catch (\Throwable) {}
    }

    public function down(): void {}
};
