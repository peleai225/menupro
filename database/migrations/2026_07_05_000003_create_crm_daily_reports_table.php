<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('crm_daily_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->date('report_date');
            $table->unsignedSmallInteger('visits_count')->default(0);
            $table->unsignedSmallInteger('new_leads_count')->default(0);
            $table->unsignedSmallInteger('demos_count')->default(0);
            $table->unsignedSmallInteger('conversions_count')->default(0);
            $table->string('zone_covered')->nullable();
            $table->text('obstacles')->nullable();
            $table->text('notes')->nullable();
            $table->json('photos')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'report_date']);
            $table->index(['report_date', 'reviewed_by']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crm_daily_reports');
    }
};
