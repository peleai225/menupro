<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Table orders — paid_at n'est indexé nulle part dans les migrations existantes,
        // or il est filtré dans Analytics (whereNotNull), SuperAdmin liveStats (whereDate paid_at)
        // et dans les requêtes de revenue partout.
        Schema::table('orders', function (Blueprint $table) {
            // Index simple pour les filtres whereNotNull('paid_at') / whereDate('paid_at', ...)
            if (!Schema::hasIndex('orders', 'orders_paid_at_index')) {
                $table->index('paid_at', 'orders_paid_at_index');
            }
            // Index composite pour les requêtes de revenus par restaurant filtrées sur paid_at
            if (!Schema::hasIndex('orders', 'orders_restaurant_paid_at_index')) {
                $table->index(['restaurant_id', 'paid_at'], 'orders_restaurant_paid_at_index');
            }
        });

        // Table order_items — l'index composite (order_id, dish_id) existe déjà.
        // Un index simple sur dish_id seul accélère les agrégations Analytics
        // (SUM qty GROUP BY dish_id après JOIN orders WHERE restaurant_id + created_at).
        Schema::table('order_items', function (Blueprint $table) {
            if (!Schema::hasIndex('order_items', 'order_items_dish_id_index')) {
                $table->index('dish_id', 'order_items_dish_id_index');
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex('orders_paid_at_index');
            $table->dropIndex('orders_restaurant_paid_at_index');
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->dropIndex('order_items_dish_id_index');
        });
    }
};
