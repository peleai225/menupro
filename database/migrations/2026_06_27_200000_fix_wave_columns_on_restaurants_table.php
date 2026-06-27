<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('restaurants', function (Blueprint $table) {
            // Ajouter wave_business_phone s'il n'existe pas encore
            if (!Schema::hasColumn('restaurants', 'wave_business_phone')) {
                $table->string('wave_business_phone', 20)->nullable()->after('wave_merchant_id');
            }

            // Supprimer les anciennes colonnes si elles existent (remplacées par wave_business_phone)
            if (Schema::hasColumn('restaurants', 'wave_api_key')) {
                $table->dropColumn('wave_api_key');
            }
            if (Schema::hasColumn('restaurants', 'wave_webhook_secret')) {
                $table->dropColumn('wave_webhook_secret');
            }
        });
    }

    public function down(): void
    {
        Schema::table('restaurants', function (Blueprint $table) {
            if (!Schema::hasColumn('restaurants', 'wave_api_key')) {
                $table->text('wave_api_key')->nullable();
            }
            if (!Schema::hasColumn('restaurants', 'wave_webhook_secret')) {
                $table->text('wave_webhook_secret')->nullable();
            }
            if (Schema::hasColumn('restaurants', 'wave_business_phone')) {
                $table->dropColumn('wave_business_phone');
            }
        });
    }
};
