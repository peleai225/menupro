<?php

namespace App\Support;

use App\Models\Order;
use Illuminate\Support\Facades\URL;

/**
 * Lien signé « Accepter / Refuser » d'une commande, envoyé dans la cloche et les push du restaurant.
 * Il ouvre une page légère utilisable sans connexion (voir OrderActionController), valable
 * config('orders.acceptance.action_link_minutes') minutes.
 */
class OrderActionLink
{
    public static function for(Order|int $order): string
    {
        $minutes = (int) config('orders.acceptance.action_link_minutes', 30);

        return URL::temporarySignedRoute(
            'order-action.show',
            now()->addMinutes($minutes),
            ['order' => $order instanceof Order ? $order->id : $order],
        );
    }
}
