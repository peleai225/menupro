<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Corriger les users livreurs créés avec le rôle par défaut
        // à cause du $guarded sur le champ 'role' du modèle User
        DB::statement("
            UPDATE users
            SET role = 'delivery_driver'
            WHERE id IN (
                SELECT user_id FROM delivery_drivers
            )
            AND role != 'delivery_driver'
        ");
    }

    public function down(): void {}
};
