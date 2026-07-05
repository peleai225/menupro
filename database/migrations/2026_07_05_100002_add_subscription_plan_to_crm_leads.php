<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('crm_leads', function (Blueprint $table) {
            // Plan souscrit — connu au moment de la signature, figé pour les commissions
            $table->string('subscription_plan', 20)->nullable()->after('source');
            // Mois de début de la récurrente (YYYY-MM) — dès le 2ème mois d'abonnement
            $table->string('recurring_starts_month', 7)->nullable()->after('subscription_plan');
        });
    }

    public function down(): void
    {
        Schema::table('crm_leads', function (Blueprint $table) {
            $table->dropColumn(['subscription_plan', 'recurring_starts_month']);
        });
    }
};
