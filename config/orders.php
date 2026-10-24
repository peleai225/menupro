<?php

/*
|--------------------------------------------------------------------------
| Commandes : acceptation par le restaurant, relances, appel du livreur
|--------------------------------------------------------------------------
|
| Une commande payée (ou « espèces » de l'app client) arrive au statut « paid » = à accepter.
| Le restaurant l'accepte avec un temps de préparation ou la refuse avec un motif.
| Tant qu'elle attend, il est relancé automatiquement (délais en minutes depuis la réception).
|
*/

return [

    'acceptance' => [
        // Temps de préparation proposés au restaurant (minutes) et choix par défaut
        'prep_choices'         => [10, 15, 20, 30, 45],
        'default_prep_minutes' => (int) env('ORDER_DEFAULT_PREP_MINUTES', 15),

        // Durée de validité des liens « Accepter / Refuser » envoyés dans les notifications
        'action_link_minutes'  => (int) env('ORDER_ACTION_LINK_MINUTES', 30),

        // Relances (minutes après réception de la commande)
        'reminders' => [
            'push'  => (int) env('ORDER_REMINDER_PUSH_MINUTES', 2),   // push + WhatsApp au personnel
            'sms'   => (int) env('ORDER_REMINDER_SMS_MINUTES', 3),    // SMS au téléphone du restaurant
            'admin' => (int) env('ORDER_REMINDER_ADMIN_MINUTES', 5),  // alerte super-admins + client rassuré
        ],
    ],

    // Les livreurs sont appelés N minutes avant la fin de la préparation annoncée (au plus tôt 1 min
    // après l'acceptation) : ils arrivent quand la commande est prête.
    'driver_lead_minutes' => (int) env('ORDER_DRIVER_LEAD_MINUTES', 10),

];
