<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('subscription_addons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subscription_id')->constrained()->cascadeOnDelete();
            $table->string('addon_type'); // 'priority_support', 'custom_domain', 'extra_employees', 'extra_dishes'
            $table->string('name'); // Nom lisible de l'add-on
            $table->unsignedInteger('price'); // Prix en centimes (XOF)
            $table->json('metadata')->nullable(); // Données supplémentaires (ex: nombre d'employés supplémentaires)
            $table->timestamps();
            
            $table->index(['subscription_id', 'addon_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscription_addons');
    }
};
