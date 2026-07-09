<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
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
            $table->string('email')->nullable(false)->change();
            $table->string('phone', 20)->nullable()->change();
        });
    }
};
