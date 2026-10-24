<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Acceptation obligatoire des commandes par le restaurant :
     * - accepted_at / prep_minutes : acceptation et temps de préparation annoncé ;
     * - rejected_at / rejection_reason : refus motivé (rupture, trop de commandes, fermé, autre) ;
     * - refund_status : « pending » quand une commande payée en ligne est refusée (remboursement manuel) ;
     * - acceptance_reminder_level : dernière relance envoyée (1 = push, 2 = SMS, 3 = alerte admin) ;
     * - driver_search_at : heure à laquelle les livreurs sont appelés (vers la fin de la préparation).
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->timestamp('accepted_at')->nullable()->after('confirmed_at');
            $table->unsignedSmallInteger('prep_minutes')->nullable()->after('accepted_at');
            $table->timestamp('rejected_at')->nullable()->after('cancellation_reason');
            $table->string('rejection_reason', 30)->nullable()->after('rejected_at');
            $table->string('refund_status', 20)->nullable()->after('rejection_reason');
            $table->unsignedTinyInteger('acceptance_reminder_level')->default(0)->after('refund_status');
            $table->timestamp('driver_search_at')->nullable()->after('driver_assigned_at');

            $table->index('refund_status');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['refund_status']);
            $table->dropColumn([
                'accepted_at',
                'prep_minutes',
                'rejected_at',
                'rejection_reason',
                'refund_status',
                'acceptance_reminder_level',
                'driver_search_at',
            ]);
        });
    }
};
