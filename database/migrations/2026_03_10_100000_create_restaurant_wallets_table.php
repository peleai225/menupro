<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('restaurant_wallets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')->constrained()->cascadeOnDelete();
            $table->decimal('balance', 15, 2)->default(0);
            $table->decimal('total_collected', 15, 2)->default(0);
            $table->decimal('total_withdrawn', 15, 2)->default(0);
            // Champs existants pour compatibilité avec d'autres passerelles
            $table->string('phone')->nullable();
            $table->string('prefix', 10)->default('225');
            $table->timestamps();

            $table->unique('restaurant_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('restaurant_wallets');
    }
};
