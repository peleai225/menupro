<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('restaurant_wallets', function (Blueprint $table) {
            if (!Schema::hasColumn('restaurant_wallets', 'auto_payout_enabled')) {
                $table->boolean('auto_payout_enabled')->default(false)->after('prefix');
            }
            if (!Schema::hasColumn('restaurant_wallets', 'min_payout_amount')) {
                $table->unsignedInteger('min_payout_amount')->default(1000)->after('auto_payout_enabled');
            }
            if (!Schema::hasColumn('restaurant_wallets', 'payout_gateway')) {
                $table->string('payout_gateway', 50)->default('wave')->after('min_payout_amount');
            }
            if (!Schema::hasColumn('restaurant_wallets', 'recipient_name')) {
                $table->string('recipient_name')->nullable()->after('payout_gateway');
            }
            if (!Schema::hasColumn('restaurant_wallets', 'total_collected')) {
                $table->decimal('total_collected', 15, 2)->default(0)->after('balance');
            }
            if (!Schema::hasColumn('restaurant_wallets', 'total_withdrawn')) {
                $table->decimal('total_withdrawn', 15, 2)->default(0)->after('total_collected');
            }
        });
    }

    public function down(): void
    {
        Schema::table('restaurant_wallets', function (Blueprint $table) {
            $table->dropColumn(['auto_payout_enabled', 'min_payout_amount', 'payout_gateway', 'recipient_name', 'total_collected', 'total_withdrawn']);
        });
    }
};
