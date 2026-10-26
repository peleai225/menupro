<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Candidatures au programme « Représentant de ville » (page publique /devenir-representant).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('partner_applications')) {
            return;
        }

        Schema::create('partner_applications', function (Blueprint $table) {
            $table->id();
            $table->string('full_name', 120);
            $table->string('phone', 30);
            $table->string('email', 150)->nullable();
            $table->string('city', 80)->index();
            $table->string('legal_status', 40);          // particulier, auto_entrepreneur, entreprise, association
            $table->text('experience');                  // expérience et réseau
            $table->string('availability', 40);          // temps_plein, temps_partiel, week_end
            $table->text('motivation');
            $table->string('attachment_path')->nullable(); // disque local (privé)
            $table->string('attachment_name')->nullable();
            $table->string('status', 20)->default('nouvelle')->index(); // nouvelle, entretien, acceptee, refusee
            $table->text('internal_notes')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete(); // représentant créé
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('partner_applications');
    }
};
