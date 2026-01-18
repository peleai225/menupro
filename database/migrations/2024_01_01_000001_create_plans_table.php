<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->unsignedInteger('price'); // Prix en centimes (XOF)
            $table->unsignedInteger('duration_days')->default(30);
            
            // Limites du plan
            $table->unsignedInteger('max_dishes')->default(20);
            $table->unsignedInteger('max_categories')->default(5);
            $table->unsignedInteger('max_employees')->default(1);
            $table->unsignedInteger('max_orders_per_month')->nullable(); // null = illimité
            
            // Fonctionnalités
            $table->boolean('has_delivery')->default(false);
            $table->boolean('has_stock_management')->default(false);
            $table->boolean('has_analytics')->default(false);
            $table->boolean('has_custom_domain')->default(false);
            $table->boolean('has_priority_support')->default(false);
            
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};

