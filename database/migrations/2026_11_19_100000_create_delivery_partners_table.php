<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Partenaires externes (ex. plateforme de vendeuses en ligne) qui créent des colis
 * par API. Clé d'API stockée hachée (SHA-256), affichée une seule fois.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('delivery_partners', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->boolean('is_active')->default(true);
            // Noms de villes autorisées (delivery_cities.name) ; null = toutes
            $table->json('cities')->nullable();
            $table->string('contact_name', 120)->nullable();
            $table->string('contact_phone', 30)->nullable();
            $table->string('contact_email', 150)->nullable();

            $table->string('api_key_hash', 64)->nullable()->unique();
            $table->string('api_key_prefix', 16)->nullable();
            $table->timestamp('api_key_generated_at')->nullable();
            $table->timestamp('last_used_at')->nullable();

            $table->string('webhook_url', 500)->nullable();
            $table->text('webhook_secret')->nullable(); // chiffré (cast encrypted)

            // Tarif propre facultatif : remise % sur le tarif ville, ou grille (mêmes clés que config parcels.pricing)
            $table->decimal('discount_percent', 5, 2)->nullable();
            $table->json('pricing')->nullable();

            // Limite de requêtes API par minute
            $table->unsignedInteger('rate_limit_per_minute')->default(60);

            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_partners');
    }
};
