<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('delivery_zone_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('from_zone_id')
                  ->constrained('delivery_zones')
                  ->cascadeOnDelete();
            $table->foreignId('to_zone_id')
                  ->nullable()  // NULL = fallback hors-zone
                  ->constrained('delivery_zones')
                  ->nullOnDelete();
            $table->unsignedInteger('price_xof');  // FCFA entiers
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Un seul prix par couple de zones
            $table->unique(['from_zone_id', 'to_zone_id'], 'uq_zone_pair');
            $table->index(['from_zone_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_zone_prices');
    }
};
