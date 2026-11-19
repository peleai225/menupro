<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Colis : une course livreur indépendante d'une commande de repas.
 * Voir App\Models\Parcel et App\Services\Parcels\ParcelService.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('parcels', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 20)->unique();      // COL-XXXXXX
            $table->string('tracking_token', 64)->unique(); // suivi public destinataire

            // Expéditeur : client app, commerçant inscrit ou partenaire API
            $table->string('sender_type', 20);               // customer | restaurant | partner
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('restaurant_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('delivery_partner_id')->nullable()->constrained('delivery_partners')->nullOnDelete();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('external_reference', 100)->nullable(); // référence commande du partenaire
            $table->json('pickup_contact')->nullable();             // partenaire : {name, phone} (la vendeuse)

            $table->foreignId('delivery_city_id')->nullable()->constrained('delivery_cities')->nullOnDelete();
            $table->string('city', 100);

            $table->string('pickup_address', 300);
            $table->decimal('pickup_latitude', 10, 7);
            $table->decimal('pickup_longitude', 10, 7);
            $table->string('dropoff_address', 300);
            $table->decimal('dropoff_latitude', 10, 7);
            $table->decimal('dropoff_longitude', 10, 7);

            $table->string('sender_name', 120);
            $table->string('sender_phone', 30);
            $table->string('recipient_name', 120);
            $table->string('recipient_phone', 30);

            $table->string('size', 10);                 // small | medium | large
            $table->string('description', 160);
            $table->string('note', 300)->nullable();

            // Prix (FCFA)
            $table->decimal('distance_km', 6, 2);
            $table->unsignedInteger('price');
            $table->unsignedInteger('driver_share');
            $table->unsignedInteger('platform_commission');
            $table->json('pricing_breakdown')->nullable();
            // Phase 2, NON UTILISÉE : montant de l'article à encaisser à la livraison (contre-remboursement)
            $table->unsignedInteger('cod_amount')->nullable();

            // Paiement de la course
            $table->string('payment_method', 20);       // jeko | cash | partner_billing
            $table->string('payment_status', 20)->default('pending'); // pending | paid | failed | refunded | billed_to_partner
            $table->string('jeko_operator', 10)->nullable();
            $table->string('payment_reference', 191)->nullable();
            $table->string('payment_link_id', 191)->nullable()->index();
            $table->timestamp('paid_at')->nullable();
            $table->boolean('cash_collected')->default(false);
            $table->timestamp('cash_collected_at')->nullable();
            $table->string('refund_status', 20)->nullable(); // pending | refunded
            $table->timestamp('refunded_at')->nullable();

            // Course
            $table->string('status', 20)->default('pending_payment');
            $table->foreignId('driver_id')->nullable()->constrained('delivery_drivers')->nullOnDelete();
            $table->string('pickup_photo_path')->nullable();

            // Code de remise (chiffré), essais et blocage
            $table->text('handover_code')->nullable();
            $table->unsignedTinyInteger('handover_attempts')->default(0);
            $table->timestamp('handover_locked_at')->nullable();

            // Dispatch
            $table->timestamp('driver_search_started_at')->nullable();
            $table->timestamp('last_dispatch_at')->nullable();
            $table->unsignedSmallInteger('dispatch_attempts')->default(0);
            $table->timestamp('no_driver_alerted_at')->nullable();

            // Étapes
            $table->timestamp('assigned_at')->nullable();
            $table->timestamp('picked_up_at')->nullable();
            $table->timestamp('delivering_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->string('cancelled_by', 20)->nullable(); // sender | admin | system
            $table->string('cancellation_reason', 500)->nullable();
            $table->string('failure_reason', 500)->nullable();

            $table->timestamps();

            $table->index(['status', 'city']);
            $table->index(['customer_id', 'created_at']);
            $table->index(['restaurant_id', 'created_at']);
            $table->index(['driver_id', 'status']);
            $table->index(['delivery_partner_id', 'created_at']);
            $table->unique(['delivery_partner_id', 'external_reference'], 'parcels_partner_external_unique');
            $table->index(['payment_status', 'created_at']);
            $table->index('created_at');
        });

        Schema::create('delivery_partner_webhook_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('delivery_partner_id')->constrained('delivery_partners')->cascadeOnDelete();
            $table->foreignId('parcel_id')->nullable()->constrained('parcels')->nullOnDelete();
            $table->string('event', 50);
            $table->string('url', 500);
            $table->json('payload');
            $table->unsignedSmallInteger('attempt')->default(1);
            $table->boolean('success')->default(false);
            $table->unsignedSmallInteger('status_code')->nullable();
            $table->string('response_excerpt', 500)->nullable();
            $table->string('error', 500)->nullable();
            $table->timestamps();

            $table->index(['delivery_partner_id', 'created_at']);
        });

        // Tarifs colis par ville (surcharge de config/parcels.php)
        Schema::table('delivery_cities', function (Blueprint $table) {
            $table->json('parcel_pricing')->nullable();
        });

        // Gains livreur : un gain peut venir d'un colis (pas de commande ni de course repas)
        Schema::table('driver_earnings', function (Blueprint $table) {
            $table->foreignId('order_id')->nullable()->change();
            $table->foreignId('delivery_id')->nullable()->change();
            $table->foreignId('parcel_id')->nullable()->after('delivery_id')->constrained('parcels')->nullOnDelete();
            $table->unique('parcel_id', 'uq_driver_earnings_parcel');
        });

        // Argent liquide détenu par le livreur : une dette peut être due à MenuPro pour un colis
        Schema::table('driver_cash_debts', function (Blueprint $table) {
            $table->foreignId('restaurant_id')->nullable()->change();
            $table->foreignId('order_id')->nullable()->change();
            $table->foreignId('delivery_id')->nullable()->change();
            $table->foreignId('parcel_id')->nullable()->after('delivery_id')->constrained('parcels')->nullOnDelete();
            $table->unique('parcel_id', 'uq_driver_cash_debts_parcel');
        });

        // Reversement d'une dette colis : destinataire MenuPro (pas de restaurant), confirmé par l'admin
        Schema::table('driver_cash_remittances', function (Blueprint $table) {
            $table->foreignId('restaurant_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('driver_cash_debts', function (Blueprint $table) {
            $table->dropUnique('uq_driver_cash_debts_parcel');
            $table->dropConstrainedForeignId('parcel_id');
        });
        Schema::table('driver_earnings', function (Blueprint $table) {
            $table->dropUnique('uq_driver_earnings_parcel');
            $table->dropConstrainedForeignId('parcel_id');
        });
        Schema::table('delivery_cities', function (Blueprint $table) {
            $table->dropColumn('parcel_pricing');
        });
        Schema::dropIfExists('delivery_partner_webhook_logs');
        Schema::dropIfExists('parcels');
    }
};
