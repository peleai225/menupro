<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Jeko API Configuration
    |--------------------------------------------------------------------------
    |
    | Base URL et timeouts pour l'API Jeko.
    | Les credentials (API keys) sont gérés dans SystemPaymentSetting.
    |
    */

    'base_url' => env('JEKO_API_URL', 'https://api.jeko.africa/partner_api'),

    'timeout' => env('JEKO_TIMEOUT', 30), // Timeout HTTP en secondes

    'payout_timeout' => env('JEKO_PAYOUT_TIMEOUT', 60), // Timeout pour payouts (plus long)

    'currency' => env('JEKO_CURRENCY', 'XOF'),

    /*
    |--------------------------------------------------------------------------
    | Marketplace Mode
    |--------------------------------------------------------------------------
    |
    | Configuration spécifique au mode Marketplace (sous-marchands).
    |
    */

    'marketplace' => [
        'enabled' => env('JEKO_MARKETPLACE_ENABLED', true),
    ],
];
