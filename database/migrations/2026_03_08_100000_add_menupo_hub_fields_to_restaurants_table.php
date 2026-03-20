<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('restaurants', function (Blueprint $table) {
            $table->boolean('menupo_hub_enabled')->default(false)->after('geniuspay_webhook_secret');
            $table->string('wave_merchant_id', 100)->nullable()->after('menupo_hub_enabled');
            $table->string('orange_money_number', 20)->nullable()->after('wave_merchant_id');
            $table->string('mtn_money_number', 20)->nullable()->after('orange_money_number');
            $table->decimal('commission_wallet_balance', 12, 2)->default(0)->after('mtn_money_number');
        });
    }

    public function down(): void
    {
        Schema::table('restaurants', function (Blueprint $table) {
            $table->dropColumn([
                'menupo_hub_enabled',
                'wave_merchant_id',
                'orange_money_number',
                'mtn_money_number',
                'commission_wallet_balance',
            ]);
        });
    }
};
