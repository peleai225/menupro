<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('commando_verify_scans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('commando_agent_id')->constrained()->cascadeOnDelete();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();

            $table->index(['commando_agent_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commando_verify_scans');
    }
};
