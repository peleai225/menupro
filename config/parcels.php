<?php

/*
|--------------------------------------------------------------------------
| Livraison de colis
|--------------------------------------------------------------------------
| Valeurs par défaut. Chaque ville (delivery_cities.parcel_pricing) peut
| surcharger les clés de « pricing » depuis l'admin (Colis → Tarifs).
| Montants en FCFA entiers.
*/

return [

    'pricing' => [
        // Service activé dans la ville
        'enabled' => true,

        // Prix de base de la course
        'base_fee' => (int) env('PARCEL_BASE_FEE', 500),

        // Prix par kilomètre (distance « route » estimée)
        'per_km_fee' => (int) env('PARCEL_PER_KM_FEE', 100),

        // Supplément selon la taille du colis
        'size_supplements' => [
            'small'  => 0,
            'medium' => 200,
            'large'  => 500,
        ],

        // Distance « route » maximale acceptée (km)
        'max_distance_km' => (float) env('PARCEL_MAX_DISTANCE_KM', 15),

        // Part du livreur (%) : même règle que les livraisons de repas
        // (DriverAssignmentService::PLATFORM_CUT_RATE = 20 % pour MenuPro ⇒ 80 % livreur)
        'driver_share_percent' => (float) env('PARCEL_DRIVER_SHARE_PERCENT', 80),
    ],

    // Distance à vol d'oiseau × ce facteur ≈ distance par la route
    'road_factor' => 1.3,

    // Le prix (base + km) est arrondi au multiple supérieur de ce pas
    'rounding_step' => 50,

    'sizes' => [
        'small'  => 'Petit (enveloppe, sachet)',
        'medium' => 'Moyen (carton à chaussures)',
        'large'  => 'Grand (carton, sac de voyage)',
    ],

    'dispatch' => [
        // Rayons de recherche successifs autour du point de ramassage (km)
        'search_radii_km' => [3, 6, 10],
        // Relance des livreurs toutes les N minutes tant que personne n'accepte
        'relaunch_every_minutes' => (int) env('PARCEL_RELAUNCH_MINUTES', 2),
        // Alerte admin si aucun livreur n'a accepté après N minutes
        'no_driver_alert_minutes' => (int) env('PARCEL_NO_DRIVER_ALERT_MINUTES', 10),
        // Nombre maximum de relances push (au-delà, la course reste visible dans l'app)
        'max_relaunches' => 10,
    ],

    // Code de remise : nombre d'essais avant blocage
    'handover_max_attempts' => 5,

    // Photo de prise en charge (Ko)
    'pickup_photo_max_kb' => 6144,

    // Paiement Jeko non finalisé : colis annulé automatiquement après N minutes
    'unpaid_expiry_minutes' => 60,
];
