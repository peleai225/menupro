<?php

use App\Enums\StockMovementType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ingredient_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            
            $table->string('type')->default(StockMovementType::ENTRY->value);
            $table->decimal('quantity', 10, 3); // Peut être négatif
            $table->decimal('quantity_before', 10, 3);
            $table->decimal('quantity_after', 10, 3);
            
            // Prix unitaire (pour entrées)
            $table->unsignedInteger('unit_cost')->nullable();
            
            // Référence (commande, fournisseur, etc.)
            $table->string('reference_type')->nullable(); // 'order', 'supplier', etc.
            $table->unsignedBigInteger('reference_id')->nullable();
            
            // Péremption (pour les entrées)
            $table->date('expiry_date')->nullable();
            $table->string('batch_number')->nullable();
            
            // Notes
            $table->text('reason')->nullable();
            
            $table->timestamps();
            
            // Index
            $table->index(['restaurant_id', 'ingredient_id', 'created_at']);
            $table->index(['restaurant_id', 'type']);
            $table->index(['reference_type', 'reference_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};

