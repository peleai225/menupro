<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('deliveries', function (Blueprint $table) {
            // Ajouter uniquement les nouvelles colonnes (cancelled_at et cancellation_reason existent déjà)
            $table->string('proof_photo_path')->nullable()->after('delivered_at');
            $table->string('cancelled_by')->nullable()->after('cancellation_reason'); // 'driver', 'restaurant', 'admin', 'customer'
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('deliveries', function (Blueprint $table) {
            $table->dropColumn(['proof_photo_path', 'cancelled_by']);
        });
    }
};
