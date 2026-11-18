<?php

/*
|--------------------------------------------------------------------------
| Site vitrine — contenus éditoriaux
|--------------------------------------------------------------------------
|
| Témoignages : n'ajouter QUE de vrais témoignages, avec l'accord écrit du commerçant.
| Tant que la liste est vide, la section « Ils utilisent MenuPro » est masquée.
|
| Format d'un témoignage :
|   [
|       'name'  => 'Prénom Nom',              // obligatoire
|       'role'  => 'Gérante, Maquis …',       // obligatoire
|       'city'  => 'Daloa',                   // facultatif
|       'text'  => 'Ce que la personne dit…', // obligatoire, sans chiffre invérifiable
|       'photo' => 'images/temoignages/x.webp', // facultatif, chemin dans public/ (WebP ≤ 40 Ko)
|   ],
|
*/

return [

    /*
    | Les trois offres de commerce mises en avant (accueil, inscription).
    | `register_type` = valeur de ?type= sur /inscription ; `restaurant_type` = valeur envoyée
    | par défaut dans le champ restaurant_type ; `from_plan` = plan dont le prix sert de « dès … ».
    */
    'offers' => [
        'restaurant' => [
            'label'           => 'Restaurant / Maquis',
            'register_type'   => null,
            'restaurant_type' => '',
            'from_plan'       => 'essentiel',
            'from_weekly'     => false,
        ],
        'stand' => [
            'label'           => 'Stand de rue',
            'register_type'   => 'stand',
            'restaurant_type' => 'stand',
            'from_plan'       => 'stand',
            'from_weekly'     => true,
        ],
        'boutique' => [
            'label'           => 'Boutique',
            'register_type'   => 'boutique',
            'restaurant_type' => 'boutique',
            'from_plan'       => 'stand',
            'from_weekly'     => false,
        ],
    ],

    'testimonials' => [
        // Aucun témoignage vérifié pour l'instant.
    ],

];
