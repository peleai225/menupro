<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Options de variantes (ex: Taille, Cuisson, Accompagnement)
        Schema::create('dish_option_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')->constrained()->cascadeOnDelete();
            
            $table->string('name'); // "Taille", "Cuisson", etc.
            $table->boolean('is_required')->default(false);
            $table->unsignedInteger('min_selections')->default(0);
            $table->unsignedInteger('max_selections')->default(1);
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            
            $table->timestamps();
        });

        // Options individuelles (ex: Petit, Moyen, Grand)
        Schema::create('dish_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('option_group_id')->constrained('dish_option_groups')->cascadeOnDelete();
            
            $table->string('name'); // "Petit", "Moyen", "Grand"
            $table->integer('price_adjustment')->default(0); // +500, -200, etc.
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            
            $table->timestamps();
        });

        // Association plats <-> groupes d'options
        Schema::create('dish_option_group', function (Blueprint $table) {
            $table->foreignId('dish_id')->constrained()->cascadeOnDelete();
            $table->foreignId('option_group_id')->constrained('dish_option_groups')->cascadeOnDelete();
            
            $table->primary(['dish_id', 'option_group_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dish_option_group');
        Schema::dropIfExists('dish_options');
        Schema::dropIfExists('dish_option_groups');
    }
};

