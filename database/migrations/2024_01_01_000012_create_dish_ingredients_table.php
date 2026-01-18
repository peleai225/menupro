<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Recettes : ingrédients par plat
        Schema::create('dish_ingredients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dish_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ingredient_id')->constrained()->cascadeOnDelete();
            
            $table->decimal('quantity', 10, 3); // Quantité nécessaire
            $table->string('unit')->nullable(); // Peut être différent de l'unité de stock
            
            $table->timestamps();
            
            // Un ingrédient ne peut être qu'une fois par plat
            $table->unique(['dish_id', 'ingredient_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dish_ingredients');
    }
};

