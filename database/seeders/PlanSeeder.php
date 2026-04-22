<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        // Plan STARTER - Pour petits maquis et kiosques
        $starter = [
            'name' => 'Starter',
            'slug' => 'starter',
            'description' => 'Parfait pour les petits maquis et kiosques qui démarrent la digitalisation.',
            'price' => 9900, // 9 900 FCFA/mois
            'duration_days' => 30,

            // Limites adaptées aux petits commerces
            'max_dishes' => 30,
            'max_categories' => 10,
            'max_employees' => 1,
            'max_orders_per_month' => 300,

            // Fonctionnalités essentielles
            'has_delivery' => false,
            'has_stock_management' => false,
            'has_analytics' => false,
            'has_custom_domain' => false,
            'has_priority_support' => false,
            'is_active' => true,
            'is_featured' => false,
            'sort_order' => 1,
        ];

        // Plan MENUPRO - Le plan principal (featured)
        $menupro = [
            'name' => 'MenuPro',
            'slug' => 'menupro',
            'description' => 'Le plan complet pour restaurants. Toutes les fonctionnalités incluses.',
            'price' => 25000, // 25 000 FCFA/mois
            'duration_days' => 30,

            // Limites généreuses
            'max_dishes' => 100,
            'max_categories' => 30,
            'max_employees' => 5,
            'max_orders_per_month' => 2000,

            // Toutes fonctionnalités
            'has_delivery' => true,
            'has_stock_management' => true,
            'has_analytics' => true,
            'has_custom_domain' => false, // Add-on
            'has_priority_support' => false, // Add-on
            'is_active' => true,
            'is_featured' => true,
            'sort_order' => 2,
        ];

        Plan::updateOrCreate(['slug' => $starter['slug']], $starter);
        Plan::updateOrCreate(['slug' => $menupro['slug']], $menupro);

        // Désactiver les anciens plans (garder pour historique)
        Plan::whereNotIn('slug', ['starter', 'menupro'])
            ->update(['is_active' => false]);

        $this->command->info('Plans Starter + MenuPro créés avec succès.');
    }
}
