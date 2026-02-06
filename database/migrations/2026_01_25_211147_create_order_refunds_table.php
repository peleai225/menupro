<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('order_refunds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->integer('amount'); // Amount in FCFA
            $table->text('reason')->nullable();
            $table->string('payment_reference')->nullable(); // Original payment reference
            $table->string('refund_reference')->nullable(); // Refund reference from payment gateway
            $table->string('status')->default('pending'); // pending, completed, failed
            $table->json('metadata')->nullable(); // Additional data from payment gateway
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
            
            $table->index(['order_id', 'status']);
            $table->index('refund_reference');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_refunds');
    }
};
