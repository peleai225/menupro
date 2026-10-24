<?php

namespace App\Services;

use App\Models\Order;
use App\Support\PushBadge;

class CustomerPushService
{
    public function __construct(private FcmService $fcm) {}

    public function notifyOrderStatusChanged(Order $order): void
    {
        if (! $this->fcm->isConfigured()) return;

        $customer = $order->customer;
        if (! $customer) return;

        // App client (token sur le profil client) + site web (appareils du compte utilisateur)
        $tokens = array_filter([
            $customer->fcm_token,
            ...($customer->user?->pushTokens()->pluck('token')->all() ?? []),
        ]);
        if (! $tokens) return;

        [$title, $body] = $this->messageFor($order->status->value, $order);

        if (! $title) return;

        $this->fcm->sendToMultiple($tokens, $title, $body, [
            'type'     => 'order_status',
            'order_id' => (string) $order->id,
            'status'   => $order->status->value,
            'badge'    => PushBadge::forCustomer($customer),
        ], [
            // L'app client ouvre « Mes commandes » avec ce paramètre (src/lib/nav.tsx)
            'link' => rtrim(config('apps.client_url'), '/') . '/?open=orders',
            'tag'  => 'order-' . $order->id,
            'icon' => '/icon-192.png',
        ]);
    }

    private function messageFor(string $status, Order $order): array
    {
        $restaurant = $order->restaurant?->name ?? 'le restaurant';
        $id = '#' . ($order->reference ?: $order->id);

        // Valeurs de App\Enums\OrderStatus
        return match ($status) {
            'confirmed'   => ["Commande confirmée ✅", "Votre commande {$id} est acceptée par {$restaurant}."],
            'preparing'   => ["En préparation 👨‍🍳", "{$restaurant} prépare votre commande {$id}."],
            'ready'       => ["Commande prête 📦", "Votre commande {$id} est prête, un livreur va la récupérer."],
            'delivering'  => ["Livreur en route 🛵", "Votre commande {$id} est en chemin !"],
            'completed'   => ["Livraison effectuée 🎉", "Votre commande {$id} a été livrée. Bon appétit !"],
            'cancelled'   => ["Commande annulée ❌", "Votre commande {$id} a été annulée."],
            default       => [null, null],
        };
    }
}
