<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('crm_commissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wallet_id')->constrained('crm_wallets')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type', 30);
            $table->string('status', 30)->default('pending');
            $table->bigInteger('amount_cents');
            $table->nullableMorphs('source');
            $table->string('description')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('validated_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
            $table->index('status');
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crm_commissions');
    }
};
