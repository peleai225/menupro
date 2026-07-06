<?php

return [

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    'allowed_origins' => [
        'https://menupro.ci',
        'https://www.menupro.ci',
        'https://delivery.menupro.ci',
        'https://driver.menupro.ci',
        'http://localhost:3000',
        'http://localhost:3001',
        'http://127.0.0.1:3000',
        'http://127.0.0.1:3001',
    ],

    // Les patterns Replit ont été retirés car ils permettaient à n'importe quel projet
    // Replit gratuit d'effectuer des requêtes cross-origin vers l'API MenuPro.
    // Pour le développement local, utiliser localhost:3000/3001 (déjà dans allowed_origins)
    // ou la variable d'environnement CORS_ALLOWED_ORIGINS.
    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => ['X-Request-ID'],

    'max_age' => 86400,

    'supports_credentials' => false,

];
