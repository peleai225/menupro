<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Journal des transactions (registre unique) : une ligne par mouvement d'argent de la
 * plateforme (encaissement, reversement, remboursement, commission livreur, abonnement).
 *
 * Registre en écriture seule : les montants ne sont jamais modifiés après insertion ;
 * seul le statut évolue (réussi → reversé / remboursé). La clé idempotency_key
 * (source + référence) garantit qu'un même mouvement n'est écrit qu'une fois, que la
 * ligne vienne de l'alimentation automatique ou de la commande ledger:reconstruire.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ledger_entries', function (Blueprint $table) {
            $table->id();
            $table->string('idempotency_key', 150)->unique();
            $table->timestamp('occurred_at')->index();

            // Nature du mouvement
            $table->string('type', 40);
            $table->string('direction', 3)->default('in'); // in | out
            $table->string('status', 20);
            $table->string('channel', 30)->nullable();      // client_app, public_menu, pos, back_office, restaurant_space, system
            $table->string('recorded_via', 30)->nullable(); // webhook, back_office, restaurant, driver_app, site, system, reconstruction
            $table->string('method', 20)->nullable();       // jeko, wave, cash, other
            $table->string('method_detail', 40)->nullable(); // valeur brute (cash_on_delivery, orange_money…)

            // Restaurant (instantané au moment du mouvement)
            $table->foreignId('restaurant_id')->nullable()->constrained()->nullOnDelete();
            $table->string('restaurant_name')->nullable();
            $table->string('restaurant_plan', 50)->nullable();
            $table->string('restaurant_plan_label', 80)->nullable();
            $table->boolean('is_demo')->default(false);

            // Liens vers les objets métier
            $table->string('source_type', 40);
            $table->unsignedBigInteger('source_id');
            $table->unsignedBigInteger('order_id')->nullable()->index();
            $table->unsignedBigInteger('subscription_id')->nullable()->index();
            $table->string('order_reference', 60)->nullable()->index();

            // Qui paie / qui reçoit
            $table->string('payer_type', 20)->nullable();
            $table->string('payer_name')->nullable();
            $table->string('payer_phone', 40)->nullable()->index();
            $table->string('beneficiary_type', 20)->nullable();
            $table->string('beneficiary_name')->nullable();

            // Rapprochement passerelle
            $table->string('gateway_reference', 191)->nullable()->index();
            $table->string('gateway_reference_alt', 191)->nullable()->index();

            // Montants FCFA entiers (net_menupro peut être négatif)
            $table->bigInteger('gross_amount')->default(0);
            $table->bigInteger('gateway_fee')->default(0);
            $table->decimal('gateway_fee_rate', 5, 2)->default(0);
            $table->bigInteger('menupro_commission')->default(0);
            $table->bigInteger('restaurant_share')->default(0);
            $table->bigInteger('driver_share')->default(0);
            $table->bigInteger('net_menupro')->default(0);
            $table->string('currency', 3)->default('XOF');

            $table->string('description')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['source_type', 'source_id']);
            $table->index(['is_demo', 'occurred_at']);
            $table->index(['type', 'occurred_at']);
            $table->index(['restaurant_id', 'occurred_at']);
            $table->index(['status', 'occurred_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ledger_entries');
    }
};
