<?php

return [

    'base_url' => env('WAVE_API_URL', 'https://api.wave.com'),

    'api_key' => env('WAVE_API_KEY', ''),

    'webhook_secret' => env('WAVE_WEBHOOK_SECRET', ''),

    'currency' => env('WAVE_CURRENCY', 'XOF'),

    // Taux de commission plateforme (0.05 = 5%)
    'commission_rate' => env('WAVE_COMMISSION_RATE', 0.05),

    // Délai avant payout automatique en secondes (0 = immédiat)
    'payout_delay' => env('WAVE_PAYOUT_DELAY', 0),

    // Montant minimum pour un payout (en FCFA)
    'min_payout_amount' => env('WAVE_MIN_PAYOUT_AMOUNT', 500),

];
