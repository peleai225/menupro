<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Wave API Configuration
    |--------------------------------------------------------------------------
    |
    | Clés et paramètres de base pour l'intégration Wave (Checkout + Payout).
    | Les valeurs peuvent provenir soit de SystemSetting (backoffice super-admin),
    | soit des variables d'environnement .env en fallback.
    |
    */

    'api_key' => env('WAVE_API_KEY'),

    'signing_secret' => env('WAVE_SIGNING_SECRET'),

    'base_url' => env('WAVE_BASE_URL', 'https://api.wave.com'),

    // Taux de commission MenuPro (par exemple 0.02 = 2 %).
    'commission_rate' => (float) env('WAVE_COMMISSION_RATE', 0.02),

    // URLs de redirection après un paiement Checkout.
    'success_url' => env('WAVE_SUCCESS_URL', 'https://menupro.ci/payment/success'),
    'error_url' => env('WAVE_ERROR_URL', 'https://menupro.ci/payment/error'),
];

