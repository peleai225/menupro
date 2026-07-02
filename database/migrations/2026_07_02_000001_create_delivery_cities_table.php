<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('delivery_cities', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('slug', 100)->unique();
            $table->string('country', 5)->default('CI');
            $table->decimal('center_latitude', 10, 7);
            $table->decimal('center_longitude', 10, 7);
            $table->unsignedInteger('coverage_radius_km')->default(15);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('delivery_base_fee')->default(50000);
            $table->unsignedInteger('delivery_fee_per_km')->default(15000);
            $table->unsignedInteger('peak_hour_surcharge_percent')->default(20);
            $table->unsignedInteger('max_delivery_distance_km')->default(10);
            $table->unsignedInteger('min_order_amount')->default(0);
            $table->string('currency', 5)->default('XOF');
            $table->timestamps();

            $table->index('is_active');
            $table->index('country');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_cities');
    }
};
