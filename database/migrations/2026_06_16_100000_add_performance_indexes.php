<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->index('payment_status');
            $table->index('type');
            $table->index('tracking_token');
        });

        Schema::table('deliveries', function (Blueprint $table) {
            $table->index(['driver_id', 'status']);
        });

        Schema::table('delivery_drivers', function (Blueprint $table) {
            $table->index('token');
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
