<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('dish_id')->nullable()->constrained()->nullOnDelete();
            
            // Snapshot du plat au moment de la commande
            $table->string('dish_name');
            $table->unsignedInteger('unit_price');
            $table->unsignedInteger('quantity');
            $table->unsignedInteger('total_price');
            
            // Options sélectionnées
            $table->json('selected_options')->nullable();
            $table->unsignedInteger('options_price')->default(0);
            
            // Notes spécifiques à l'item
            $table->text('special_instructions')->nullable();
            
            $table->timestamps();
            
            // Index
            $table->index(['order_id', 'dish_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};

