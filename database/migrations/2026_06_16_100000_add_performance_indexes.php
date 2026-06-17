<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasIndex('orders', 'orders_payment_status_index')) {
                $table->index('payment_status');
            }
            if (!Schema::hasIndex('orders', 'orders_type_index')) {
                $table->index('type');
            }
            if (!Schema::hasIndex('orders', 'orders_tracking_token_index')) {
                $table->index('tracking_token');
            }
        });

        Schema::table('deliveries', function (Blueprint $table) {
            if (!Schema::hasIndex('deliveries', 'deliveries_driver_id_status_index')) {
                $table->index(['driver_id', 'status']);
            }
        });

        Schema::table('delivery_drivers', function (Blueprint $table) {
            if (!Schema::hasIndex('delivery_drivers', 'delivery_drivers_token_index')) {
                $table->index('token');
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['payment_status']);
            $table->dropIndex(['type']);
            $table->dropIndex(['tracking_token']);
        });

        Schema::table('deliveries', function (Blueprint $table) {
            $table->dropIndex(['driver_id', 'status']);
        });

        Schema::table('delivery_drivers', function (Blueprint $table) {
            $table->dropIndex(['token']);
        });
    }
};
