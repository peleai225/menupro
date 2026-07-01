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

    'allowed_origins_patterns' => [
        '#^https://.*\.replit\.dev$#',
        '#^https://.*\.replit\.app$#',
    ],

    'allowed_headers' => ['*'],

    'exposed_headers' => ['X-Request-ID'],

    'max_age' => 86400,

    'supports_credentials' => false,

];
