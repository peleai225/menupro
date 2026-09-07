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
        Schema::create('delivery_issues', function (Blueprint $table) {
            $table->id();
            $table->foreignId('delivery_id')->constrained('deliveries')->onDelete('cascade');
            $table->foreignId('driver_id')->constrained('delivery_drivers')->onDelete('cascade');
            $table->enum('issue_type', [
                'client_absent',
                'address_not_found',
                'order_refused',
                'order_damaged',
                'accident',
                'restaurant_closed',
                'order_not_ready',
                'items_missing',
                'order_cancelled_by_restaurant',
            ]);
            $table->text('issue_details')->nullable();
            $table->enum('resolution_status', ['pending', 'resolved', 'escalated'])->default('pending');
            $table->text('resolution_notes')->nullable();
            $table->timestamp('reported_at');
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            $table->index(['delivery_id', 'reported_at']);
            $table->index('driver_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delivery_issues');
    }
};
