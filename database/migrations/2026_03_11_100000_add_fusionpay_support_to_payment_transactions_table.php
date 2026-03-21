<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payment_transactions', function (Blueprint $table) {
            $table->string('gateway', 20)->default('cinetpay')->after('restaurant_id');
            $table->string('gateway_transaction_id', 100)->nullable()->after('gateway');
        });

        \Illuminate\Support\Facades\DB::statement('UPDATE payment_transactions SET gateway_transaction_id = cinetpay_transaction_id WHERE cinetpay_transaction_id IS NOT NULL');

        Schema::table('payment_transactions', function (Blueprint $table) {
            $table->string('cinetpay_transaction_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('payment_transactions', function (Blueprint $table) {
            $table->dropColumn(['gateway', 'gateway_transaction_id']);
        });
    }
};
