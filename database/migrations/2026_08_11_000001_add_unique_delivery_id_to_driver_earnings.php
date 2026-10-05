<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Supprimer les doublons éventuels avant d'ajouter la contrainte.
        // Garde le plus petit id pour chaque delivery_id non nul.
        // Syntaxe portable (MySQL + SQLite) : les null ne sont pas dédupliqués (null != null).
        DB::statement("
            DELETE FROM driver_earnings
            WHERE delivery_id IS NOT NULL
              AND id NOT IN (
                  SELECT keep_id FROM (
                      SELECT MIN(id) AS keep_id
                      FROM driver_earnings
                      WHERE delivery_id IS NOT NULL
                      GROUP BY delivery_id
                  ) t
              )
        ");

        Schema::table('driver_earnings', function (Blueprint $table) {
            $table->unique('delivery_id', 'uq_driver_earnings_delivery');
        });
    }

    public function down(): void
    {
        Schema::table('driver_earnings', function (Blueprint $table) {
            $table->dropUnique('uq_driver_earnings_delivery');
        });
    }
};
