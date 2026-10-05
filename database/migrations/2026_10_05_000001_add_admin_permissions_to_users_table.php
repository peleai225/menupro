<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * admin_permissions : null = admin complet (accès total) ;
     * tableau de clés de sections = employé back-office restreint.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->json('admin_permissions')->nullable()->after('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('admin_permissions');
        });
    }
};
