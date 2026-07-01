<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('restaurants', function (Blueprint $table) {
            // Visibilité sur la plateforme de livraison
            $table->boolean('is_on_platform')->default(false)->after('status');
            $table->string('platform_category')->nullable()->after('is_on_platform');
            // fast_food | restaurant | patisserie | pizza | poulet | etc.

            // Livraison plateforme — tarification
            $table->unsignedInteger('delivery_base_fee')->default(50000)->after('delivery_fee');
            // frais fixe de base en centimes XOF (500 FCFA)
            $table->unsignedInteger('delivery_fee_per_km')->default(15000)->after('delivery_base_fee');
            // tarif par km en centimes XOF (150 FCFA)
            $table->unsignedInteger('max_delivery_distance_km')->default(10)->after('delivery_fee_per_km');

            // Temps de préparation moyen (en minutes)
            $table->unsignedInteger('avg_prep_time_minutes')->default(20)->after('max_delivery_distance_km');

            // Commission plateforme sur les commandes (en %)
            $table->decimal('platform_commission_rate', 4, 2)->default(12.00)->after('avg_prep_time_minutes');

            $table->index('is_on_platform');
            $table->index('platform_category');
        });
    }

    public function down(): void
    {
        Schema::table('restaurants', function (Blueprint $table) {
            $table->dropIndex(['is_on_platform']);
            $table->dropIndex(['platform_category']);
            $table->dropColumn([
                'is_on_platform', 'platform_category',
                'delivery_base_fee', 'delivery_fee_per_km', 'max_delivery_distance_km',
                'avg_prep_time_minutes', 'platform_commission_rate',
            ]);
        });
    }
};
