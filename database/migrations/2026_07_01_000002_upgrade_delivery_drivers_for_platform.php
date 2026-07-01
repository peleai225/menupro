<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('delivery_drivers', function (Blueprint $table) {
            // Lier le livreur à un compte user (pour l'auth API)
            $table->foreignId('user_id')->nullable()->after('id')->constrained()->nullOnDelete();

            // Le livreur n'appartient plus à UN restaurant — il est sur la plateforme
            $table->dropForeign(['restaurant_id']);
            $table->dropColumn('restaurant_id');

            // Informations personnelles
            $table->string('email')->nullable()->after('phone');
            $table->string('city')->nullable()->after('email');
            $table->string('zone')->nullable()->after('city'); // commune/quartier de travail

            // Documents d'identité
            $table->string('cni_number', 30)->nullable()->after('zone');
            $table->string('cni_photo_path')->nullable()->after('cni_number');
            $table->string('license_photo_path')->nullable()->after('cni_photo_path');
            $table->string('vehicle_photo_path')->nullable()->after('license_photo_path');

            // Statut de validation
            $table->string('verification_status', 20)->default('pending')->after('vehicle_photo_path');
            // pending | approved | rejected | suspended

            // Performance
            $table->decimal('rating', 3, 2)->default(5.00)->after('total_deliveries');
            $table->unsignedInteger('total_ratings')->default(0)->after('rating');
            $table->unsignedInteger('total_cancelled')->default(0)->after('total_ratings');

            // Gains
            $table->unsignedBigInteger('total_earnings_xof')->default(0)->after('total_cancelled');

            // Device pour push notifications
            $table->string('fcm_token')->nullable()->after('total_earnings_xof');

            $table->index('verification_status');
            $table->index(['city', 'is_available', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::table('delivery_drivers', function (Blueprint $table) {
            $table->dropIndex(['city', 'is_available', 'is_active']);
            $table->dropIndex(['verification_status']);
            $table->dropForeign(['user_id']);
            $table->dropColumn([
                'user_id', 'email', 'city', 'zone',
                'cni_number', 'cni_photo_path', 'license_photo_path', 'vehicle_photo_path',
                'verification_status', 'rating', 'total_ratings', 'total_cancelled',
                'total_earnings_xof', 'fcm_token',
            ]);
            $table->foreignId('restaurant_id')->constrained()->cascadeOnDelete();
        });
    }
};
