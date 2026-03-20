<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();

            // Gestion multi-passerelles (CinetPay, Wave, etc.)
            $table->string('gateway', 50)->default('wave');
            $table->string('gateway_transaction_id')->nullable();
            $table->string('cinetpay_transaction_id')->nullable()->unique();

            // Champs Wave Checkout / Paiement
            $table->string('wave_checkout_id')->nullable();
            $table->string('wave_payment_id')->nullable();

            $table->decimal('amount', 15, 2);
            $table->decimal('commission', 15, 2)->default(0);
            $table->decimal('net_amount', 15, 2)->default(0);
            $table->string('currency', 3)->default('XOF');

            $table->enum('status', ['pending', 'completed', 'failed'])->default('pending');

            $table->string('client_reference')->nullable()->index();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['restaurant_id', 'order_id']);
            $table->index(['order_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_transactions');
    }
};
