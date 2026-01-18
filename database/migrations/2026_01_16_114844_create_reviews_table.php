<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            
            // Client
            $table->string('customer_name');
            $table->string('customer_email');
            
            // Avis
            $table->unsignedTinyInteger('rating'); // 1-5
            $table->text('comment')->nullable();
            
            // Modération
            $table->boolean('is_approved')->default(false);
            $table->boolean('is_visible')->default(true);
            $table->text('response')->nullable(); // Réponse du restaurant
            $table->timestamp('responded_at')->nullable();
            
            // Métadonnées
            $table->json('metadata')->nullable(); // Données supplémentaires
            
            $table->timestamps();
            
            // Index
            $table->index(['restaurant_id', 'is_approved', 'is_visible']);
            $table->index(['restaurant_id', 'rating']);
            $table->index('order_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
