<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')->constrained()->cascadeOnDelete();
            
            $table->string('name');
            $table->string('contact_name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone', 20)->nullable();
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            
            // Conditions
            $table->unsignedInteger('min_order_amount')->nullable();
            $table->unsignedInteger('delivery_days')->nullable(); // Délai livraison
            $table->string('payment_terms')->nullable(); // "30 jours", "À réception", etc.
            
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            
            $table->timestamps();
            
            $table->index('restaurant_id');
        });

        // Pivot : fournisseur <-> ingrédient avec prix
        Schema::create('ingredient_supplier', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ingredient_id')->constrained()->cascadeOnDelete();
            $table->foreignId('supplier_id')->constrained()->cascadeOnDelete();
            
            $table->unsignedInteger('unit_price'); // Prix du fournisseur
            $table->string('supplier_sku')->nullable(); // Référence fournisseur
            $table->boolean('is_preferred')->default(false);
            
            $table->timestamps();
            
            $table->unique(['ingredient_id', 'supplier_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ingredient_supplier');
        Schema::dropIfExists('suppliers');
    }
};

