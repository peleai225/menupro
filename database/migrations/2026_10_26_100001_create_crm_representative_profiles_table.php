<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Profils « représentant de ville » (module CRM) et relevés mensuels de rémunération.
 * Le portefeuille (crm_wallets), les commissions (crm_commissions) et les retraits
 * (crm_withdrawals) du CRM existant sont réutilisés pour le versement.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('crm_representative_profiles')) {
            Schema::create('crm_representative_profiles', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
                $table->foreignId('partner_application_id')->nullable()->constrained('partner_applications')->nullOnDelete();
                $table->uuid('uuid')->unique();            // lien de parrainage /inscription?ref={uuid}
                $table->string('city', 80)->index();       // territoire (une seule ville)
                $table->boolean('exclusivity_active')->default(false);
                $table->timestamp('exclusivity_granted_at')->nullable();
                $table->decimal('subscription_rate', 5, 2)->default(20);
                $table->decimal('delivery_rate', 5, 2)->default(20);
                $table->date('trial_ends_at')->nullable();
                $table->unsignedInteger('quarterly_target')->default(10); // restaurants payants visés / trimestre
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('crm_representative_statements')) {
            Schema::create('crm_representative_statements', function (Blueprint $table) {
                $table->id();
                $table->foreignId('representative_profile_id')->constrained('crm_representative_profiles')->cascadeOnDelete();
                $table->char('month', 7);                                  // AAAA-MM
                $table->unsignedInteger('restaurants_attributed')->default(0);
                $table->unsignedInteger('restaurants_active')->default(0);
                $table->unsignedInteger('restaurants_paying')->default(0);
                $table->bigInteger('subscription_collected')->default(0); // FCFA
                $table->bigInteger('delivery_commission')->default(0);    // FCFA
                $table->decimal('subscription_rate', 5, 2)->default(0);
                $table->decimal('delivery_rate', 5, 2)->default(0);
                $table->bigInteger('subscription_share')->default(0);     // FCFA
                $table->bigInteger('delivery_share')->default(0);         // FCFA
                $table->bigInteger('total_share')->default(0);            // FCFA
                $table->bigInteger('credited_cents')->default(0);         // déjà versé au wallet CRM
                $table->json('details')->nullable();                      // détail par restaurant
                $table->timestamp('computed_at')->nullable();
                $table->timestamps();

                $table->unique(['representative_profile_id', 'month'], 'rep_statements_profile_month_unique');
                $table->index('month');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('crm_representative_statements');
        Schema::dropIfExists('crm_representative_profiles');
    }
};
