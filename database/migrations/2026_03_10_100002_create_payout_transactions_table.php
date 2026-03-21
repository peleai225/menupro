<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payout_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('restaurant_wallet_id')->constrained('restaurant_wallets')->cascadeOnDelete();

            // Champs multi-passerelles (CinetPay, Wave, etc.)
            $table->string('gateway', 50)->default('wave');
            $table->string('gateway_transaction_id')->nullable();
            $table->string('cinetpay_transaction_id')->nullable()->unique();

            // Champs Payout Wave
            $table->decimal('amount', 15, 2);
            $table->decimal('fee', 15, 2)->default(0);
            $table->string('currency', 3)->default('XOF');

            $table->string('wave_payout_id')->nullable()->index();
            $table->string('client_reference')->nullable()->index();

            $table->string('mobile');
            $table->string('recipient_name');
            $table->string('payment_reason', 100)->nullable();

            $table->enum('status', ['pending', 'processing', 'succeeded', 'failed', 'reversed'])->default('pending');
            $table->string('idempotency_key')->unique();
            $table->json('payout_error')->nullable();

            $table->timestamps();

            $table->index(['restaurant_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payout_transactions');
    }
};
