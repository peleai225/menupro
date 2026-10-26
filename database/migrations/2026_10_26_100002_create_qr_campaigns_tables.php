<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Campagnes QR (bâches, affiches) : lien court suivi /q/{code}, scans anonymes,
 * attribution des inscriptions restaurant issues d'un QR.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('qr_campaigns')) {
            Schema::create('qr_campaigns', function (Blueprint $table) {
                $table->id();
                $table->string('code', 16)->unique();
                $table->string('name', 120);
                $table->string('target_type', 30);          // client_app, restaurant_signup, representative_page, internal_url
                $table->string('target_path', 255)->nullable(); // URL libre interne (chemin)
                $table->string('city', 80)->nullable()->index();
                $table->string('location', 160)->nullable();
                $table->boolean('is_active')->default(true);
                $table->unsignedInteger('scans_count')->default(0);
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('qr_campaign_scans')) {
            Schema::create('qr_campaign_scans', function (Blueprint $table) {
                $table->id();
                $table->foreignId('qr_campaign_id')->constrained('qr_campaigns')->cascadeOnDelete();
                $table->string('device', 10);   // mobile | desktop — aucune donnée personnelle
                $table->timestamp('scanned_at');

                $table->index(['qr_campaign_id', 'scanned_at']);
            });
        }

        if (Schema::hasTable('restaurants') && !Schema::hasColumn('restaurants', 'qr_campaign_id')) {
            Schema::table('restaurants', function (Blueprint $table) {
                $table->foreignId('qr_campaign_id')->nullable()->constrained('qr_campaigns')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('restaurants', 'qr_campaign_id')) {
            Schema::table('restaurants', function (Blueprint $table) {
                $table->dropForeign(['qr_campaign_id']);
                $table->dropColumn('qr_campaign_id');
            });
        }
        Schema::dropIfExists('qr_campaign_scans');
        Schema::dropIfExists('qr_campaigns');
    }
};
