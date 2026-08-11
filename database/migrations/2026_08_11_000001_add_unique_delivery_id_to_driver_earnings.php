<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Supprimer les doublons éventuels avant d'ajouter la contrainte
        // Garde le premier earning créé pour chaque delivery_id
        DB::statement("
            DELETE e1 FROM driver_earnings e1
            INNER JOIN driver_earnings e2
            WHERE e1.id > e2.id AND e1.delivery_id = e2.delivery_id
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
