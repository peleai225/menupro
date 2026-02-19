<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('commando_agents', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('whatsapp', 20)->unique();
            $table->string('city')->nullable();
            $table->string('statut_metier')->nullable(); // étudiant, auto_entrepreneur, etc.
            $table->string('status_verification')->default('shadow');
            $table->string('id_document_path')->nullable();
            $table->string('photo_path')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('banned_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            $table->index('status_verification');
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commando_agents');
    }
};
