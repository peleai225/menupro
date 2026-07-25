<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Dédupliquer les téléphones avant d'ajouter la contrainte unique.
        // On garde l'enregistrement le plus récent (id max) et on met NULL aux autres.

        if (DB::getDriverName() === 'sqlite') {
            // SQLite doesn't support UPDATE with INNER JOIN, use a different approach
            $duplicatePhones = DB::table('users')
                ->selectRaw('phone, MAX(id) AS keep_id')
                ->whereNotNull('phone')
                ->where('phone', '!=', '')
                ->groupBy('phone')
                ->havingRaw('COUNT(*) > 1')
                ->get();

            foreach ($duplicatePhones as $dup) {
                DB::table('users')
                    ->where('phone', $dup->phone)
                    ->where('id', '!=', $dup->keep_id)
                    ->update(['phone' => null]);
            }
        } else {
            // MySQL version with INNER JOIN
            DB::statement("
                UPDATE users u1
                INNER JOIN (
                    SELECT phone, MAX(id) AS keep_id
                    FROM users
                    WHERE phone IS NOT NULL AND phone != ''
                    GROUP BY phone
                    HAVING COUNT(*) > 1
                ) dup ON u1.phone = dup.phone AND u1.id != dup.keep_id
                SET u1.phone = NULL
            ");
        }

        Schema::table('users', function (Blueprint $table) {
            // Email devient nullable (connexion par téléphone)
            $table->string('email')->nullable()->change();
            // Téléphone unique pour servir d'identifiant de connexion
            $table->string('phone', 20)->unique()->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique('users_phone_unique');
            $table->string('email')->nullable(false)->change();
            $table->string('phone', 20)->nullable()->change();
        });
    }
};
