<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('waiter_daily_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('waiter_id')->constrained()->cascadeOnDelete();
            $table->date('report_date');
            $table->unsignedInteger('orders_count');
            $table->unsignedInteger('revenue');          // FCFA
            $table->unsignedInteger('average_ticket');   // FCFA
            $table->time('first_order_at')->nullable();
            $table->time('last_order_at')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['restaurant_id', 'waiter_id', 'report_date']);
            $table->index(['restaurant_id', 'report_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('waiter_daily_reports');
    }
};
