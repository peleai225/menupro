<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('restaurant_wallets', function (Blueprint $table) {
            $table->boolean('auto_payout_enabled')->default(false)->after('prefix');
            $table->string('payout_gateway', 50)->default('wave')->after('auto_payout_enabled');
            $table->unsignedInteger('min_payout_amount')->default(1000)->after('payout_gateway');
        });
    }

    public function down(): void
    {
        Schema::table('restaurant_wallets', function (Blueprint $table) {
            $table->dropColumn(['auto_payout_enabled', 'payout_gateway', 'min_payout_amount']);
        });
    }
};
