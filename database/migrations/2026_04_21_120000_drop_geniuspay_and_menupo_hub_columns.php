<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('restaurants', function (Blueprint $table) {
            $columns = [
                'geniuspay_enabled',
                'geniuspay_api_key',
                'geniuspay_api_secret',
                'geniuspay_webhook_secret',
                'menupo_hub_enabled',
                'orange_money_number',
                'mtn_money_number',
                'moov_money_number',
                'commission_wallet_balance',
            ];
            foreach ($columns as $column) {
                if (Schema::hasColumn('restaurants', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'payment_screenshot_url')) {
                $table->dropColumn('payment_screenshot_url');
            }
        });
    }

    public function down(): void
    {
        Schema::table('restaurants', function (Blueprint $table) {
            $table->boolean('geniuspay_enabled')->default(false);
            $table->text('geniuspay_api_key')->nullable();
            $table->text('geniuspay_api_secret')->nullable();
            $table->string('geniuspay_webhook_secret', 255)->nullable();
            $table->boolean('menupo_hub_enabled')->default(false);
            $table->string('orange_money_number', 20)->nullable();
            $table->string('mtn_money_number', 20)->nullable();
            $table->string('moov_money_number', 20)->nullable();
            $table->decimal('commission_wallet_balance', 12, 2)->default(0);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->string('payment_screenshot_url')->nullable();
        });
    }
};
