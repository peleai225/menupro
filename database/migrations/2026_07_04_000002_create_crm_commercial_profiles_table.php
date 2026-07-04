<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('crm_commercial_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->uuid('uuid')->unique();
            $table->string('badge_id', 20)->unique()->nullable();
            $table->string('city')->nullable();
            $table->string('statut_metier')->nullable();
            $table->string('id_document_path')->nullable();
            $table->string('verification_status', 30)->default('pending_review');
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('banned_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->foreignId('team_id')->nullable()->constrained('crm_teams')->nullOnDelete();
            $table->timestamps();

            $table->index('verification_status');
            $table->index('team_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crm_commercial_profiles');
    }
};
