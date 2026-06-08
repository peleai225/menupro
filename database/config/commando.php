<?php

return [
    'monthly_target' => env('COMMANDO_MONTHLY_TARGET', 10),

    /*
    | Commission versée à l'agent quand un restaurant parrainé paie son abonnement.
    | only_first_payment: true = une seule commission au premier paiement (inscription).
    */
    'commission_cents_first_payment' => (int) env('COMMANDO_COMMISSION_CENTS_FIRST_PAYMENT', 500000), // 500000 = 5000 FCFA par premier paiement parrainé
    'commission_only_first_payment' => env('COMMANDO_COMMISSION_ONLY_FIRST_PAYMENT', true),
];
