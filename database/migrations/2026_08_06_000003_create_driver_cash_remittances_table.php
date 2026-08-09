<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('driver_cash_remittances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('driver_id')->constrained('delivery_drivers')->cascadeOnDelete();
            $table->foreignId('restaurant_id')->constrained('restaurants')->cascadeOnDelete();
            $table->foreignId('debt_id')->constrained('driver_cash_debts')->cascadeOnDelete();
            $table->unsignedInteger('amount_xof');
            $table->enum('method', ['wave', 'orange_money', 'mtn_money', 'moov_money', 'cash']);
            $table->string('wave_reference', 100)->nullable();
            $table->enum('status', ['pending', 'confirmed', 'disputed'])->default('pending');
            $table->foreignId('confirmed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('confirmed_at')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();

            $table->index(['driver_id', 'status']);
            $table->index(['restaurant_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('driver_cash_remittances');
    }
};
