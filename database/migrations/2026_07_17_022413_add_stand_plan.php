<?php

use App\Models\Plan;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Plan::updateOrCreate(['slug' => 'stand'], [
            'name'                  => 'Stand',
            'slug'                  => 'stand',
            'description'           => 'Pour les vendeuses de stand, kiosques et micro-commerces. Simple, rapide, efficace.',
            'price'                 => 5000,
            'duration_days'         => 30,
            'max_dishes'            => 15,
            'max_categories'        => 5,
            'max_employees'         => 1,
            'max_orders_per_month'  => 100,
            'has_delivery'          => false,
            'has_stock_management'  => false,
            'has_analytics'         => false,
            'has_custom_domain'     => false,
            'has_priority_support'  => false,
            'has_hotel_rooms'       => false,
            'is_active'             => true,
            'is_featured'           => false,
            'sort_order'            => 0,
        ]);
    }

    public function down(): void
    {
        Plan::where('slug', 'stand')->delete();
    }
};
