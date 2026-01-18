<?php

use App\Enums\Unit;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Catégories d'ingrédients
        Schema::create('ingredient_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')->constrained()->cascadeOnDelete();
            
            $table->string('name');
            $table->string('color', 7)->default('#6b7280');
            $table->unsignedInteger('sort_order')->default(0);
            
            $table->timestamps();
            
            $table->index('restaurant_id');
        });

        // Ingrédients
        Schema::create('ingredients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ingredient_category_id')->nullable()->constrained()->nullOnDelete();
            
            $table->string('name');
            $table->string('sku')->nullable(); // Code article
            $table->string('unit')->default(Unit::PIECE->value);
            
            // Stock
            $table->decimal('current_quantity', 10, 3)->default(0);
            $table->decimal('min_quantity', 10, 3)->default(0); // Seuil alerte
            $table->decimal('max_quantity', 10, 3)->nullable(); // Capacité max stockage
            
            // Prix
            $table->unsignedInteger('unit_cost')->default(0); // Prix d'achat moyen (centimes)
            $table->unsignedInteger('last_purchase_cost')->nullable();
            
            // Péremption
            $table->boolean('track_expiry')->default(false);
            $table->unsignedInteger('default_expiry_days')->nullable();
            
            // Meta
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            
            $table->timestamps();
            
            // Index
            $table->unique(['restaurant_id', 'sku']);
            $table->index(['restaurant_id', 'is_active']);
            $table->index(['restaurant_id', 'current_quantity']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ingredients');
        Schema::dropIfExists('ingredient_categories');
    }
};

