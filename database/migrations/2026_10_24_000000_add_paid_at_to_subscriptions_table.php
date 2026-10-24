<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Date d'encaissement réel d'un abonnement (webhook / callback de paiement confirmé).
     * Sert aux indicateurs de revenus du back-office : un abonnement créé un mois et payé
     * le suivant doit compter dans le mois du paiement. Null pour les anciens abonnements
     * et les prolongations gratuites (les rapports se rabattent alors sur created_at).
     */
    public function up(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->timestamp('paid_at')->nullable()->after('payment_metadata');
            $table->index('paid_at');
        });
    }

    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropIndex(['paid_at']);
            $table->dropColumn('paid_at');
        });
    }
};
