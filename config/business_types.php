<?php

/*
|--------------------------------------------------------------------------
| Types de commerce (colonne restaurants.type)
|--------------------------------------------------------------------------
|
| Source unique des types acceptés à l'inscription et exposés par l'API client.
| Chaque type appartient à une famille (« kind ») :
|  - food : restauration (restaurant, maquis, stand…) — vocabulaire « Plats / Menu » ;
|  - shop : boutique (superette, accessoires, boissons…) — vocabulaire « Articles / Catalogue ».
|
| Le vocabulaire du tableau de bord et du menu public vient de `vocabulary.{kind}`
| (helper business_label()). Les onglets listés dans `hidden_nav.{kind}` sont masqués
| (fonctions sans objet pour ce type de commerce — les plans restent identiques).
|
| group : regroupement d'affichage du formulaire d'inscription (restaurant | stand | shop).
*/

return [

    'default' => 'restaurant',

    'types' => [
        // Restauration — plans Essentiel / Pro
        'restaurant'   => ['label' => 'Restaurant',          'icon' => '🍽️', 'kind' => 'food', 'group' => 'restaurant'],
        'bar'          => ['label' => 'Bar',                 'icon' => '🍹', 'kind' => 'food', 'group' => 'restaurant'],
        'brasserie'    => ['label' => 'Brasserie',           'icon' => '🍺', 'kind' => 'food', 'group' => 'restaurant'],
        'maquis'       => ['label' => 'Maquis',              'icon' => '🍗', 'kind' => 'food', 'group' => 'restaurant'],
        'traiteur'     => ['label' => 'Traiteur',            'icon' => '🥘', 'kind' => 'food', 'group' => 'restaurant'],
        'cafe'         => ['label' => 'Café',                'icon' => '☕', 'kind' => 'food', 'group' => 'restaurant'],
        'food_truck'   => ['label' => 'Food Truck',          'icon' => '🚚', 'kind' => 'food', 'group' => 'restaurant'],
        'brunch'       => ['label' => 'Brunch',              'icon' => '🥞', 'kind' => 'food', 'group' => 'restaurant'],
        'evenementiel' => ['label' => 'Événementiel',        'icon' => '🎉', 'kind' => 'food', 'group' => 'restaurant'],

        // Petite restauration — plan Stand
        'stand'        => ['label' => 'Stand de rue',            'icon' => '🌭', 'kind' => 'food', 'group' => 'stand'],
        'kiosque'      => ['label' => 'Kiosque',                 'icon' => '🏪', 'kind' => 'food', 'group' => 'stand'],
        'cantine'      => ['label' => 'Cantine / Cafétéria',     'icon' => '🍲', 'kind' => 'food', 'group' => 'stand'],
        'patisserie'   => ['label' => 'Pâtisserie / Boulangerie', 'icon' => '🥐', 'kind' => 'food', 'group' => 'stand'],
        'epicerie'     => ['label' => 'Épicerie',                'icon' => '🧺', 'kind' => 'food', 'group' => 'stand'],
        'jus'          => ['label' => 'Jus & Boissons',          'icon' => '🧃', 'kind' => 'food', 'group' => 'stand'],
        'snack'        => ['label' => 'Snack rapide',            'icon' => '🍟', 'kind' => 'food', 'group' => 'stand'],

        // Commerce de détail — n'importe quel plan (Stand / Essentiel / Pro)
        'boutique'     => ['label' => 'Boutique',            'icon' => '🛍️', 'kind' => 'shop', 'group' => 'shop'],
    ],

    'vocabulary' => [
        'food' => [
            'business'         => 'Restaurant',
            'business_lc'      => 'restaurant',
            'your_business'    => 'votre restaurant',
            'the_business'     => 'le restaurant',
            'business_closed'  => 'Restaurant fermé',
            'dish'             => 'Plat',
            'dish_lc'          => 'plat',
            'dishes'           => 'Plats',
            'dishes_lc'        => 'plats',
            'new_dish'         => 'Nouveau plat',
            'a_new_dish'       => 'un nouveau plat',
            'add_dish'         => 'Ajouter un plat',
            'the_dish'         => 'le plat',
            'this_dish'        => 'ce plat',
            'first_dish'       => 'premier plat',
            'no_dish'          => 'Aucun plat',
            'dish_name'        => 'Nom du plat',
            'search_dish'      => 'Rechercher un plat...',
            'popular_dishes'   => 'Plats populaires',
            'menu'             => 'Menu',
            'menu_lc'          => 'menu',
            'categories'       => 'Catégories',
            'categories_hint'  => 'Ex: Entrées, Plats principaux...',
            'kitchen'          => 'Cuisine',
            'kitchen_screen'   => 'Écran cuisine',
            'stock_daily'      => 'Stock journalier',
            'add_to_cart'      => 'Ajouter au panier',
            'back_to_menu'     => 'Retour au menu',
            'dine_in'          => 'Sur place',
        ],
        'shop' => [
            'business'         => 'Boutique',
            'business_lc'      => 'boutique',
            'your_business'    => 'votre boutique',
            'the_business'     => 'la boutique',
            'business_closed'  => 'Boutique fermée',
            'dish'             => 'Article',
            'dish_lc'          => 'article',
            'dishes'           => 'Articles',
            'dishes_lc'        => 'articles',
            'new_dish'         => 'Nouvel article',
            'a_new_dish'       => 'un nouvel article',
            'add_dish'         => 'Ajouter un article',
            'the_dish'         => "l'article",
            'this_dish'        => 'cet article',
            'first_dish'       => 'premier article',
            'no_dish'          => 'Aucun article',
            'dish_name'        => "Nom de l'article",
            'search_dish'      => 'Rechercher un article...',
            'popular_dishes'   => 'Articles populaires',
            'menu'             => 'Catalogue',
            'menu_lc'          => 'catalogue',
            'categories'       => 'Catégories',
            'categories_hint'  => 'Ex: Boissons, Épicerie, Accessoires...',
            'kitchen'          => 'Préparation',
            'kitchen_screen'   => 'Écran préparation',
            'stock_daily'      => 'Stock articles',
            'add_to_cart'      => 'Ajouter au panier',
            'back_to_menu'     => 'Retour au catalogue',
            'dine_in'          => 'En boutique',
        ],
    ],

    // Onglets du tableau de bord (clés de App\Support\RestaurantNavigation) sans objet pour ce type
    'hidden_nav' => [
        'food' => [],
        'shop' => ['rooms', 'spaces', 'waiters', 'reservations', 'ingredients', 'suppliers', 'stock-alerts'],
    ],
];
