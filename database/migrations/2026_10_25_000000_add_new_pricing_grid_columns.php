<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Nouvelle grille d'abonnements (2026-10) :
 *  - plans.weekly_price : prix à la semaine (Stand uniquement) ;
 *  - plans.has_ingredient_stock : stock par ingrédient + rentabilité par plat (Pro et +),
 *    distinct du stock simple (has_stock_management). Initialisé à has_stock_management pour
 *    que personne ne perde l'accès avant l'application de la grille ;
 *  - restaurants.locked_price : prix mensuel garanti (anciens abonnés), prioritaire au renouvellement ;
 *  - order_quota_alerts : une ligne par restaurant / mois / palier notifié (clé unique =
 *    une seule notification même avec des commandes simultanées).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            if (!Schema::hasColumn('plans', 'weekly_price')) {
                $table->unsignedInteger('weekly_price')->nullable()->after('price');
            }
            if (!Schema::hasColumn('plans', 'has_ingredient_stock')) {
                $table->boolean('has_ingredient_stock')->default(false)->after('has_stock_management');
            }
        });

        DB::table('plans')->update(['has_ingredient_stock' => DB::raw('has_stock_management')]);

        Schema::table('restaurants', function (Blueprint $table) {
            if (!Schema::hasColumn('restaurants', 'locked_price')) {
                $table->unsignedInteger('locked_price')->nullable()->after('subscription_ends_at');
            }
        });

        if (!Schema::hasTable('order_quota_alerts')) {
            Schema::create('order_quota_alerts', function (Blueprint $table) {
                $table->id();
                $table->foreignId('restaurant_id')->constrained()->cascadeOnDelete();
                $table->string('period', 7); // AAAA-MM
                $table->unsignedSmallInteger('threshold'); // 80, 100
                $table->unsignedInteger('orders_count');
                $table->unsignedInteger('orders_limit');
                $table->timestamp('created_at')->nullable();

                $table->unique(['restaurant_id', 'period', 'threshold']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('order_quota_alerts');

        Schema::table('restaurants', function (Blueprint $table) {
            if (Schema::hasColumn('restaurants', 'locked_price')) {
                $table->dropColumn('locked_price');
            }
        });

        Schema::table('plans', function (Blueprint $table) {
            if (Schema::hasColumn('plans', 'has_ingredient_stock')) {
                $table->dropColumn('has_ingredient_stock');
            }
            if (Schema::hasColumn('plans', 'weekly_price')) {
                $table->dropColumn('weekly_price');
            }
        });
    }
};
