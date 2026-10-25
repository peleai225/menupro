<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

/**
 * Grille d'abonnements MenuPro — SOURCE DE VÉRITÉ (validée le 2026-10-25).
 *
 * Appliquée en production par `php artisan plans:appliquer-grille` (updateOrCreate par slug) :
 * ne touche ni aux abonnements en cours ni à leurs montants.
 *
 * Conventions : limite >= 9999 = illimité ; 0 = zéro. max_orders_per_month est un quota
 * SOUPLE (alerte au restaurant à 80 % / 100 %), aucune commande client n'est jamais bloquée.
 * Livraison MenuPro ouverte à tous les plans (commission 12 %, 10 % Pro et Gold : config/plans.php).
 */
class PlanSeeder extends Seeder
{
    /** Plans publics de la grille */
    public const ACTIVE_SLUGS = ['stand', 'essentiel', 'pro', 'gold'];

    /** Plans conservés en base mais retirés de la vente */
    public const INACTIVE_SLUGS = ['business'];

    public static function definitions(): array
    {
        $unlimited = 9999;

        return [
            // STAND — vendeuses de stand, kiosques, street food
            'stand' => [
                'name'                  => 'Stand',
                'slug'                  => 'stand',
                'description'           => 'Menu QR, commandes et Mobile Money pour les stands, kiosques et street food. Livraison MenuPro incluse.',
                'price'                 => 5000,  // F/mois
                'weekly_price'          => 1500,  // F/semaine (Stand uniquement)
                'duration_days'         => 30,
                'max_dishes'            => 30,
                'max_categories'        => 8,
                'max_employees'         => 0,     // 1 utilisateur : le gérant seul
                'max_orders_per_month'  => 150,   // quota souple
                'has_delivery'          => true,
                'has_stock_management'  => false,
                'has_ingredient_stock'  => false,
                'has_analytics'         => false,
                'has_custom_domain'     => false,
                'has_priority_support'  => false,
                'has_hotel_rooms'       => false,
                'has_multi_spaces'      => false,
                'is_active'             => true,
                'is_featured'           => false,
                'sort_order'            => 0,
            ],

            // RESTAURANT (slug historique « essentiel » conservé pour ne rien casser)
            'essentiel' => [
                'name'                  => 'Restaurant',
                'slug'                  => 'essentiel',
                'description'           => 'Caisse, écran cuisine, stock simple, statistiques et réservations pour les maquis et restaurants.',
                'price'                 => 15000,
                'weekly_price'          => null,
                'duration_days'         => 30,
                'max_dishes'            => 100,
                'max_categories'        => 30,
                'max_employees'         => 3,
                'max_orders_per_month'  => 600,   // quota souple
                'has_delivery'          => true,
                'has_stock_management'  => true,  // stock simple (stock journalier)
                'has_ingredient_stock'  => false,
                'has_analytics'         => true,
                'has_custom_domain'     => false,
                'has_priority_support'  => false,
                'has_hotel_rooms'       => false,
                'has_multi_spaces'      => false,
                'is_active'             => true,
                'is_featured'           => true,
                'sort_order'            => 1,
            ],

            // PRO — commission réduite à 10 %
            'pro' => [
                'name'                  => 'Pro',
                'slug'                  => 'pro',
                'description'           => 'Stock par ingrédient, rentabilité par plat, chambres d\'hôtel, support prioritaire. Commission réduite à 10 %.',
                'price'                 => 30000,
                'weekly_price'          => null,
                'duration_days'         => 30,
                'max_dishes'            => $unlimited,
                'max_categories'        => $unlimited,
                'max_employees'         => 10,
                'max_orders_per_month'  => $unlimited,
                'has_delivery'          => true,
                'has_stock_management'  => true,
                'has_ingredient_stock'  => true,
                'has_analytics'         => true,
                'has_custom_domain'     => false,
                'has_priority_support'  => true,
                'has_hotel_rooms'       => true,
                'has_multi_spaces'      => false,
                'is_active'             => true,
                'is_featured'           => false,
                'sort_order'            => 2,
            ],

            // GOLD — sur devis (prix stocké = prix plancher affiché « à partir de »)
            'gold' => [
                'name'                  => 'Gold',
                'slug'                  => 'gold',
                'description'           => 'Multi-espaces, multi-sites, tout illimité. Sur devis.',
                'price'                 => 75000,
                'weekly_price'          => null,
                'duration_days'         => 30,
                'max_dishes'            => $unlimited,
                'max_categories'        => $unlimited,
                'max_employees'         => $unlimited,
                'max_orders_per_month'  => 999999,
                'has_delivery'          => true,
                'has_stock_management'  => true,
                'has_ingredient_stock'  => true,
                'has_analytics'         => true,
                'has_custom_domain'     => true,
                'has_priority_support'  => true,
                'has_hotel_rooms'       => true,
                'has_multi_spaces'      => true,
                'is_active'             => true,
                'is_featured'           => false,
                'sort_order'            => 3,
            ],

            // BUSINESS — retiré de la vente (0 client), conservé pour l'historique
            'business' => [
                'name'                  => 'Business',
                'slug'                  => 'business',
                'description'           => 'Ancien plan, retiré de la vente.',
                'price'                 => 45000,
                'weekly_price'          => null,
                'duration_days'         => 30,
                'max_dishes'            => $unlimited,
                'max_categories'        => $unlimited,
                'max_employees'         => 10,
                'max_orders_per_month'  => 99999,
                'has_delivery'          => true,
                'has_stock_management'  => true,
                'has_ingredient_stock'  => true,
                'has_analytics'         => true,
                'has_custom_domain'     => true,
                'has_priority_support'  => true,
                'has_hotel_rooms'       => true,
                'has_multi_spaces'      => false,
                'is_active'             => false,
                'is_featured'           => false,
                'sort_order'            => 9,
            ],
        ];
    }

    public function run(): void
    {
        foreach (self::definitions() as $slug => $data) {
            Plan::updateOrCreate(['slug' => $slug], $data);
        }

        // Tout autre plan (starter, menupro, premium…) est retiré de la vente — les restaurants
        // qui y sont encore rattachés gardent leurs fonctions (voir plans:migrer-anciens).
        Plan::whereNotIn('slug', self::ACTIVE_SLUGS)->update(['is_active' => false]);

        $this->command?->info('Grille appliquée : Stand, Restaurant (essentiel), Pro, Gold — Business désactivé.');
    }
}
