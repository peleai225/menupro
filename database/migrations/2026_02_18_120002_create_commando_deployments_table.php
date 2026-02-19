<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('commando_deployments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('commando_agent_id')->constrained('commando_agents')->cascadeOnDelete();
            $table->string('restaurant_name');
            $table->string('manager_name')->nullable();
            $table->string('phone', 30)->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('status', 30)->default('en_negociation'); // en_negociation, en_attente_paiement, actif
            $table->foreignId('restaurant_id')->nullable()->constrained('restaurants')->nullOnDelete();
            $table->timestamps();

            $table->index(['commando_agent_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commando_deployments');
    }
};
