<?php

return [
    'api_url' => env('FUSIONPAY_API_URL'),
    'private_key' => env('FUSIONPAY_PRIVATE_KEY'),
    'app_url' => env('APP_URL', 'https://menupro.ci'),

    'payin_verify_url' => 'https://www.pay.moneyfusion.net/paiementNotif',
    'payin_page_url' => 'https://www.pay.moneyfusion.net/pay',
    'payout_url' => 'https://pay.moneyfusion.net/api/v1/withdraw',
];
