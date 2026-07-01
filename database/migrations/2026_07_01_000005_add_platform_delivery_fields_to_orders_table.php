<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Lien vers le client plateforme
            $table->foreignId('customer_id')->nullable()->after('restaurant_id')
                ->constrained('customers')->nullOnDelete();

            // Source de la commande
            $table->string('source', 20)->default('pos')->after('type');
            // pos | platform_web | platform_app

            // Commission plateforme
            $table->unsignedInteger('platform_commission')->default(0)->after('service_fee');
            // en centimes XOF

            // Tracking token pour le suivi public (déjà dans tracking_token)
            $table->timestamp('driver_assigned_at')->nullable()->after('ready_at');
            $table->timestamp('picked_up_at')->nullable()->after('driver_assigned_at');

            $table->index(['customer_id', 'created_at']);
            $table->index('source');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['customer_id']);
            $table->dropIndex(['customer_id', 'created_at']);
            $table->dropIndex(['source']);
            $table->dropColumn([
                'customer_id', 'source', 'platform_commission',
                'driver_assigned_at', 'picked_up_at',
            ]);
        });
    }
};
