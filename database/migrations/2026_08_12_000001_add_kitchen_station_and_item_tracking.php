<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Ajouter le poste de préparation sur les catégories
        Schema::table('categories', function (Blueprint $table) {
            $table->string('preparation_station', 20)->default('cuisine')->after('is_active');
        });

        // Suivi item par item pour les bons de cuisine
        Schema::table('order_items', function (Blueprint $table) {
            $table->timestamp('prepared_at')->nullable()->after('special_instructions');
            $table->unsignedBigInteger('prepared_by')->nullable()->after('prepared_at');
        });
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn('preparation_station');
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn(['prepared_at', 'prepared_by']);
        });
    }
};
