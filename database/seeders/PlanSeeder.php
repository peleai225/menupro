<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        // Plan unique MenuPro avec toutes les fonctionnalités
        $plan = [
            'name' => 'MenuPro',
            'slug' => 'menupro',
            'description' => 'Un seul plan, toutes les fonctionnalités. Parfait pour tous les restaurants.',
            'price' => 25000, // 25,000 FCFA/mois
            'duration_days' => 30,
            
            // Limites généreuses mais raisonnables
            'max_dishes' => 100,        // Suffisant pour 95% des restaurants
            'max_categories' => 30,     // Large marge
            'max_employees' => 5,       // Équipe standard
            'max_orders_per_month' => 2000, // Très généreux
            
            // Toutes les fonctionnalités de base incluses
            'has_delivery' => true,
            'has_stock_management' => true,
            'has_analytics' => true,
            'has_custom_domain' => false,     // Add-on disponible
            'has_priority_support' => false,   // Add-on disponible
            'is_active' => true,
            'is_featured' => true,
            'sort_order' => 1,
        ];

        Plan::updateOrCreate(
            ['slug' => $plan['slug']],
            $plan
        );

        // Désactiver les anciens plans (garder pour historique)
        Plan::whereNotIn('slug', ['menupro'])
            ->update(['is_active' => false]);

        $this->command->info('Plan MenuPro créé avec succès.');
    }
}

