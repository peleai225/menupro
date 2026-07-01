<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('driver_earnings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('driver_id')->constrained('delivery_drivers')->cascadeOnDelete();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('delivery_id')->constrained('deliveries')->cascadeOnDelete();
            $table->unsignedInteger('gross_amount');     // montant brut en centimes XOF
            $table->unsignedInteger('platform_cut');     // commission plateforme (20%)
            $table->unsignedInteger('net_amount');       // ce que reçoit le livreur
            $table->string('status', 20)->default('pending');
            // pending | available | paid
            $table->timestamp('paid_at')->nullable();
            $table->string('payment_method', 30)->nullable(); // wave | orange_money | mtn_money
            $table->string('payment_reference', 100)->nullable();
            $table->timestamps();

            $table->index(['driver_id', 'status']);
            $table->index(['driver_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('driver_earnings');
    }
};
