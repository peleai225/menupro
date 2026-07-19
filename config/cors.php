<?php

return [

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    'allowed_origins' => array_filter(array_merge(
        [
            'https://menupro.ci',
            'https://www.menupro.ci',
            'https://delivery.menupro.ci',
            'https://driver.menupro.ci',
            'https://mpa-five.vercel.app',
            'https://mpa-driver.vercel.app',
            'http://localhost:3000',
            'http://localhost:3001',
            'http://localhost:5173',
            'http://127.0.0.1:3000',
            'http://127.0.0.1:3001',
            'http://127.0.0.1:5173',
        ],
        // Origines supplémentaires via variable d'environnement (virgule-séparées)
        array_map('trim', explode(',', env('CORS_ALLOWED_ORIGINS', '')))
    )),

    'allowed_origins_patterns' => [
        // Tous les déploiements preview Vercel pour ce projet
        '#^https://mpa-[a-z0-9]+-peleai225s-projects\.vercel\.app$#',
    ],

    'allowed_headers' => ['*'],

    'exposed_headers' => ['X-Request-ID'],

    'max_age' => 86400,

    'supports_credentials' => false,

];
