<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('driver_cash_debts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('driver_id')->constrained('delivery_drivers')->cascadeOnDelete();
            $table->foreignId('restaurant_id')->constrained('restaurants')->cascadeOnDelete();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('delivery_id')->constrained('deliveries')->cascadeOnDelete();
            $table->unsignedInteger('amount_xof');
            $table->enum('status', ['pending', 'settled'])->default('pending');
            $table->timestamp('settled_at')->nullable();
            $table->timestamps();

            $table->index(['driver_id', 'status']);
            $table->index(['restaurant_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('driver_cash_debts');
    }
};
