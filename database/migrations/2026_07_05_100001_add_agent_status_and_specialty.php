<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Ajouter agent_status sur users
        Schema::table('users', function (Blueprint $table) {
            $table->string('agent_status', 20)->default('actif')->after('is_active');
        });

        // Synchroniser : is_active = false → agent_status = 'inactif'
        DB::table('users')->where('is_active', false)->update(['agent_status' => 'inactif']);

        // 2. Ajouter specialty sur commercial_profiles
        Schema::table('crm_commercial_profiles', function (Blueprint $table) {
            $table->string('specialty', 30)->nullable()->after('city');
        });

        // 3. Ajouter specialty sur technician_profiles
        Schema::table('crm_technician_profiles', function (Blueprint $table) {
            $table->string('specialty', 30)->nullable()->after('speciality');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('agent_status');
        });

        Schema::table('crm_commercial_profiles', function (Blueprint $table) {
            $table->dropColumn('specialty');
        });

        Schema::table('crm_technician_profiles', function (Blueprint $table) {
            $table->dropColumn('specialty');
        });
    }
};
