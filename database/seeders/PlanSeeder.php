<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        // Plan ESSENTIEL - Pour petits maquis et restaurants de l'interieur
        $essentiel = [
            'name' => 'Essentiel',
            'slug' => 'essentiel',
            'description' => 'Parfait pour les petits maquis et restaurants qui demarrent la digitalisation.',
            'price' => 15000, // 15 000 FCFA/mois
            'duration_days' => 30,
            'max_dishes' => 25,
            'max_categories' => 8,
            'max_employees' => 1,
            'max_orders_per_month' => 200,
            'has_delivery' => false,
            'has_stock_management' => false,
            'has_analytics' => false,
            'has_custom_domain' => false,
            'has_priority_support' => false,
            'is_active' => true,
            'is_featured' => false,
            'sort_order' => 1,
        ];

        // Plan PRO - Le plan principal pour restaurants etablis
        $pro = [
            'name' => 'Pro',
            'slug' => 'pro',
            'description' => 'Le plan complet pour restaurants etablis. Stock, livraison et analytiques inclus.',
            'price' => 25000, // 25 000 FCFA/mois
            'duration_days' => 30,
            'max_dishes' => 80,
            'max_categories' => 20,
            'max_employees' => 3,
            'max_orders_per_month' => 1000,
            'has_delivery' => true,
            'has_stock_management' => true,
            'has_analytics' => true,
            'has_custom_domain' => false,
            'has_priority_support' => false,
            'is_active' => true,
            'is_featured' => true,
            'sort_order' => 2,
        ];

        // Plan BUSINESS - Pour grands restaurants et chaines
        $business = [
            'name' => 'Business',
            'slug' => 'business',
            'description' => 'Pour les grands restaurants et chaines. Tout illimite avec support prioritaire.',
            'price' => 45000, // 45 000 FCFA/mois
            'duration_days' => 30,
            'max_dishes' => 9999,
            'max_categories' => 9999,
            'max_employees' => 10,
            'max_orders_per_month' => 99999,
            'has_delivery' => true,
            'has_stock_management' => true,
            'has_analytics' => true,
            'has_custom_domain' => true,
            'has_priority_support' => true,
            'is_active' => true,
            'is_featured' => false,
            'sort_order' => 3,
        ];

        Plan::updateOrCreate(['slug' => $essentiel['slug']], $essentiel);
        Plan::updateOrCreate(['slug' => $pro['slug']], $pro);
        Plan::updateOrCreate(['slug' => $business['slug']], $business);

        // Desactiver les anciens plans (garder pour historique abonnes existants)
        Plan::whereNotIn('slug', ['essentiel', 'pro', 'business'])
            ->update(['is_active' => false]);

        $this->command->info('Plans Essentiel + Pro + Business crees avec succes.');
    }
}
