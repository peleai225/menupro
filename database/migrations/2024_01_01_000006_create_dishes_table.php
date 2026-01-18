<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dishes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            
            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->unsignedInteger('price'); // en centimes
            $table->unsignedInteger('compare_price')->nullable(); // prix barré
            
            $table->string('image_path')->nullable();
            $table->json('gallery')->nullable(); // images supplémentaires
            
            // Options
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_new')->default(false);
            $table->boolean('is_spicy')->default(false);
            $table->boolean('is_vegetarian')->default(false);
            $table->boolean('is_vegan')->default(false);
            $table->boolean('is_gluten_free')->default(false);
            
            // Stock
            $table->boolean('track_stock')->default(false);
            $table->unsignedInteger('stock_quantity')->nullable();
            $table->boolean('allow_out_of_stock_orders')->default(false);
            
            // Meta
            $table->unsignedInteger('prep_time')->nullable(); // minutes
            $table->unsignedInteger('calories')->nullable();
            $table->json('allergens')->nullable();
            $table->json('nutritional_info')->nullable();
            
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();
            
            // Index
            $table->unique(['restaurant_id', 'slug']);
            $table->index(['restaurant_id', 'category_id', 'is_active']);
            $table->index(['restaurant_id', 'is_featured']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dishes');
    }
};

