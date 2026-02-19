<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('commando_commission_transactions')) {
            Schema::create('commando_commission_transactions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('commando_agent_id')->constrained('commando_agents')->cascadeOnDelete();
                $table->string('type', 30);
                $table->string('status', 30)->default('pending');
                $table->integer('amount_cents');
                $table->string('description')->nullable();
                $table->json('meta')->nullable();
                $table->timestamp('processed_at')->nullable();
                $table->timestamps();

                $table->index(['commando_agent_id', 'created_at'], 'commando_comm_agent_created_idx');
                $table->index('status', 'commando_comm_status_idx');
            });
        } else {
            Schema::table('commando_commission_transactions', function (Blueprint $table) {
                $table->index(['commando_agent_id', 'created_at'], 'commando_comm_agent_created_idx');
                $table->index('status', 'commando_comm_status_idx');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('commando_commission_transactions');
    }
};
