<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payout_transactions', function (Blueprint $table) {
            $table->string('gateway', 20)->default('cinetpay')->after('restaurant_wallet_id');
            $table->string('gateway_transaction_id', 100)->nullable()->after('gateway');
        });
    }

    public function down(): void
    {
        Schema::table('payout_transactions', function (Blueprint $table) {
            $table->dropColumn(['gateway', 'gateway_transaction_id']);
        });
    }
};
