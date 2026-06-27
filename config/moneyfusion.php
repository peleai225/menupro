<?php

return [

    'base_url' => env('MONEYFUSION_API_URL', 'https://api.moneyfusion.net'),

    'api_key' => env('MONEYFUSION_API_KEY', ''),

    'secret_key' => env('MONEYFUSION_SECRET_KEY', ''),

    'currency' => env('MONEYFUSION_CURRENCY', 'XOF'),

];
