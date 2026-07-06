<?php

namespace App\Services;

use App\Models\Order;

class CustomerPushService
{
    public function __construct(private FcmService $fcm) {}

    public function notifyOrderStatusChanged(Order $order): void
    {
        if (! $this->fcm->isConfigured()) return;

        $token = $order->customer?->fcm_token;
        if (! $token) return;

        [$title, $body] = $this->messageFor($order->status->value, $order);

        if (! $title) return;

        $this->fcm->sendToToken($token, $title, $body, [
            'type'     => 'order_status',
            'order_id' => (string) $order->id,
            'status'   => $order->status->value,
        ]);
    }

    private function messageFor(string $status, Order $order): array
    {
        $restaurant = $order->restaurant?->name ?? 'le restaurant';
        $id = '#' . $order->id;

        return match ($status) {
            'confirmed'   => ["Commande confirmée ✅", "Votre commande {$id} est en préparation chez {$restaurant}."],
            'preparing'   => ["En préparation 👨‍🍳", "{$restaurant} prépare votre commande {$id}."],
            'ready'       => ["Commande prête 📦", "Votre commande {$id} est prête, un livreur va la récupérer."],
            'picked_up'   => ["Livreur en route 🛵", "Votre commande {$id} est en chemin !"],
            'delivered'   => ["Livraison effectuée 🎉", "Votre commande {$id} a été livrée. Bon appétit !"],
            'cancelled'   => ["Commande annulée ❌", "Votre commande {$id} a été annulée."],
            default       => [null, null],
        };
    }
}
