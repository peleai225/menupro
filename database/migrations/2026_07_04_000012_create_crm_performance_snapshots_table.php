<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('crm_performance_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('period_type', 10);
            $table->date('period_start');
            $table->date('period_end');
            $table->unsignedInteger('leads_created')->default(0);
            $table->unsignedInteger('leads_converted')->default(0);
            $table->unsignedBigInteger('revenue_generated_cents')->default(0);
            $table->unsignedBigInteger('commissions_earned_cents')->default(0);
            $table->unsignedInteger('installations_completed')->default(0);
            $table->decimal('conversion_rate', 5, 2)->default(0);
            $table->decimal('avg_cycle_days', 5, 1)->default(0);
            $table->timestamps();

            $table->unique(['user_id', 'period_type', 'period_start'], 'crm_perf_user_period_unique');
            $table->index(['period_type', 'period_start'], 'crm_perf_type_start_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crm_performance_snapshots');
    }
};
