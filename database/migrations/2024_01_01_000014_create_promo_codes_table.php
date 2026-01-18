<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promo_codes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')->constrained()->cascadeOnDelete();
            
            $table->string('code')->index();
            $table->text('description')->nullable();
            
            // Type de réduction
            $table->enum('discount_type', ['percentage', 'fixed'])->default('percentage');
            $table->unsignedInteger('discount_value'); // % ou montant fixe
            
            // Conditions
            $table->unsignedInteger('min_order_amount')->nullable();
            $table->unsignedInteger('max_discount_amount')->nullable(); // Plafond pour %
            
            // Limites d'utilisation
            $table->unsignedInteger('max_uses')->nullable();
            $table->unsignedInteger('max_uses_per_customer')->default(1);
            $table->unsignedInteger('current_uses')->default(0);
            
            // Validité
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->boolean('is_active')->default(true);
            
            $table->timestamps();
            
            $table->unique(['restaurant_id', 'code']);
        });

        // Utilisation des codes promo
        Schema::create('promo_code_uses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('promo_code_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('customer_email');
            $table->unsignedInteger('discount_applied');
            $table->timestamps();
            
            $table->index('customer_email');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promo_code_uses');
        Schema::dropIfExists('promo_codes');
    }
};

