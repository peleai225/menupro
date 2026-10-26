<?php

/*
|--------------------------------------------------------------------------
| Grille d'abonnements MenuPro — paramètres métier
|--------------------------------------------------------------------------
|
| Les plans eux-mêmes (prix, limites, fonctions) vivent en base et sont
| définis par database/seeders/PlanSeeder.php (source de vérité, appliquée
| en production par `php artisan plans:appliquer-grille`).
| Ce fichier regroupe les règles transverses : essai, commission, quota
| souple de commandes et périodes de facturation.
|
*/

return [

    // Essai gratuit : durée (jours) et plan dont les fonctions sont offertes pendant l'essai
    'trial_days' => (int) env('PLAN_TRIAL_DAYS', 30),
    'trial_plan_slug' => 'pro',

    // Commission plateforme sur les commandes livrées via MenuPro (en %)
    'commission' => [
        'default_rate' => 12.0,
        'reduced_rate' => 10.0,
        'reduced_plans' => ['pro', 'gold'],
    ],

    // Quota mensuel « souple » : paliers (en % du quota) déclenchant UNE notification par mois.
    // Aucune commande client n'est jamais bloquée.
    'order_quota_alert_thresholds' => [80, 100],

    // Seuil au-delà duquel une limite est considérée illimitée (convention historique)
    'unlimited_threshold' => 9999,

    // Prix verrouillé des anciens abonnés (restaurants.locked_price) : conservé tant que le
    // restaurant reste abonné, avec une tolérance de N jours après l'échéance pour renouveler
    'locked_price_grace_days' => 7,

    // Plans pouvant être payés à la semaine (prix dans plans.weekly_price)
    'weekly_plans' => ['stand'],

    // Périodes de facturation : durée, nombre de mois facturés et remise (%)
    'billing_periods' => [
        'weekly'     => ['label' => 'Hebdomadaire', 'days' => 7,   'months' => 0,  'discount' => 0],
        'monthly'    => ['label' => 'Mensuel',      'days' => 30,  'months' => 1,  'discount' => 0],
        'quarterly'  => ['label' => 'Trimestriel',  'days' => 90,  'months' => 3,  'discount' => 10],
        'semiannual' => ['label' => 'Semestriel',   'days' => 180, 'months' => 6,  'discount' => 15],
        'annual'     => ['label' => 'Annuel',       'days' => 365, 'months' => 12, 'discount' => 20],
    ],

    /*
    |--------------------------------------------------------------------------
    | « Ce plan donne accès à quoi » — SOURCE UNIQUE
    |--------------------------------------------------------------------------
    |
    | Chaque fonction est portée soit par une colonne booléenne du plan (`column`, valeurs
    | fixées par PlanSeeder), soit par un plan minimum (`min_plan`, rang dans `plan_order`).
    | Lue par Plan::hasFeature(), PlanLimiter, le middleware `feature:` et la navigation du
    | tableau de bord (App\Support\RestaurantNavigation). Pendant l'essai : plan Pro.
    |
    */

    // Rang des plans (du moins cher au plus complet). Un ancien plan absent de la liste
    // garde l'interface complète d'un plan Restaurant (comportement historique).
    'plan_order' => ['stand', 'essentiel', 'pro', 'gold'],
    'legacy_plan_rank_as' => 'essentiel',

    'features' => [
        // Fonctions portées par une colonne du plan
        'delivery'         => ['label' => 'Livraisons',                          'column' => 'has_delivery'],
        'stock'            => ['label' => 'Stock simple (stock journalier)',     'column' => 'has_stock_management'],
        'ingredient_stock' => ['label' => 'Stock par ingrédient et rentabilité', 'column' => 'has_ingredient_stock'],
        'analytics'        => ['label' => 'Statistiques, rapports et codes promo', 'column' => 'has_analytics'],
        'hotel_rooms'      => ['label' => 'Chambres d\'hôtel (QR par chambre)',  'column' => 'has_hotel_rooms'],
        'multi_spaces'     => ['label' => 'Multi-espaces et serveurs',           'column' => 'has_multi_spaces'],
        'custom_domain'    => ['label' => 'Domaine personnalisé',                'column' => 'has_custom_domain'],
        'priority_support' => ['label' => 'Support prioritaire',                 'column' => 'has_priority_support'],

        // Fonctions d'interface : à partir d'un plan minimum
        'pos'              => ['label' => 'Caisse (POS)',            'min_plan' => 'essentiel'],
        'customers'        => ['label' => 'Fichier clients',         'min_plan' => 'essentiel'],
        'reservations'     => ['label' => 'Réservations',            'min_plan' => 'essentiel'],
        'reviews'          => ['label' => 'Avis clients',            'min_plan' => 'essentiel'],
        'team'             => ['label' => 'Équipe (employés)',       'min_plan' => 'essentiel'],
        'taxes'            => ['label' => 'Taxes et frais',          'min_plan' => 'essentiel'],
    ],

    // Abonnement expiré ou en attente de paiement : seules ces pages restent ouvertes
    // (le restaurant bloqué doit toujours pouvoir payer).
    'limited_access_routes' => [
        'restaurant.subscription*',
        'restaurant.settings*',
        'restaurant.profile',
        'restaurant.pending',
    ],

    /*
    |--------------------------------------------------------------------------
    | Packs de supports imprimés (pages d'offres, inscription, abonnement)
    |--------------------------------------------------------------------------
    */
    'welcome_pack' => 'Affiche vitrine « Commandez sur MenuPro » offerte à tous dès l\'inscription',

    'packs' => [
        'stand' => [
            'name'  => 'Pack Stand',
            'items' => ['4 autocollants QR de table', '1 affiche vitrine A4'],
            'note'  => 'offerts dès le 1er paiement',
        ],
        'essentiel' => [
            'name'  => 'Pack Maquis',
            'items' => ['15 autocollants de table', '1 affiche vitrine A4', '1 autocollant de porte A3'],
            'note'  => 'offerts dès le 1er paiement',
        ],
        'pro' => [
            'name'  => 'Pack Pro',
            'items' => ['30 autocollants', '2 chevalets de comptoir', 'Affiche vitrine', 'Autocollant de porte'],
            'note'  => 'inclus',
        ],
        'gold' => [
            'name'  => 'Pack Gold',
            'items' => ['Supports personnalisés'],
            'note'  => 'sur devis',
        ],
    ],
];
