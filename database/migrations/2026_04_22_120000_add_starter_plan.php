<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Vérifier si le plan Starter n'existe pas déjà
        $exists = DB::table('plans')->where('slug', 'starter')->exists();

        if (!$exists) {
            DB::table('plans')->insert([
                'name' => 'Starter',
                'slug' => 'starter',
                'description' => 'Parfait pour les petits maquis et kiosques qui démarrent la digitalisation.',
                'price' => 9900,
                'duration_days' => 30,
                'max_dishes' => 30,
                'max_categories' => 10,
                'max_employees' => 1,
                'max_orders_per_month' => 300,
                'has_delivery' => false,
                'has_stock_management' => false,
                'has_analytics' => false,
                'has_custom_domain' => false,
                'has_priority_support' => false,
                'is_active' => true,
                'is_featured' => false,
                'sort_order' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // S'assurer que MenuPro a sort_order = 2 pour apparaître après Starter
        DB::table('plans')
            ->where('slug', 'menupro')
            ->update(['sort_order' => 2, 'updated_at' => now()]);
    }

    public function down(): void
    {
        DB::table('plans')->where('slug', 'starter')->delete();
    }
};
