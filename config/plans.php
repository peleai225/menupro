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
];
