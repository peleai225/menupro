<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Référence article (SKU / code-barres) facultative — utile aux boutiques
 * (restaurants.type = 'boutique') pour retrouver un article à la caisse ou dans le catalogue.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('dishes', 'sku')) {
            return;
        }

        Schema::table('dishes', function (Blueprint $table) {
            $table->string('sku', 64)->nullable()->after('name');
            $table->index(['restaurant_id', 'sku']);
        });
    }

    public function down(): void
    {
        if (!Schema::hasColumn('dishes', 'sku')) {
            return;
        }

        Schema::table('dishes', function (Blueprint $table) {
            $table->dropIndex(['restaurant_id', 'sku']);
            $table->dropColumn('sku');
        });
    }
};
