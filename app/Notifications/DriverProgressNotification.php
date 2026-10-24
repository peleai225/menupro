<?php

namespace App\Notifications;

use App\Models\Delivery;
use App\Notifications\Channels\PushChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

/**
 * Tient le personnel du restaurant au courant du livreur d'une commande (push uniquement,
 * pas de cloche) : course acceptée, commande récupérée. Même tag pour une commande :
 * chaque étape remplace la précédente sur l'appareil.
 */
class DriverProgressNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public const ACCEPTED  = 'accepted';
    public const PICKED_UP = 'picked_up';

    public int $orderId;
    public string $orderReference;
    public string $driverName;

    public function __construct(Delivery $delivery, public string $step)
    {
        $this->orderId        = (int) $delivery->order_id;
        $this->orderReference = (string) ($delivery->order?->reference ?: $delivery->order_id);
        $this->driverName     = (string) ($delivery->driver?->name ?: 'Le livreur');
    }

    /**
     * Prévient tout le personnel du restaurant de la course. Ne bloque jamais le livreur.
     */
    public static function sendFor(Delivery $delivery, string $step): void
    {
        try {
            $restaurant = $delivery->restaurant;
            if (!$restaurant) {
                return;
            }

            \Illuminate\Support\Facades\Notification::send(
                $restaurant->staffToAlert(),
                new self($delivery, $step),
            );
        } catch (\Throwable $e) {
            Log::warning('Push restaurant (livreur) échoué', [
                'delivery_id' => $delivery->id,
                'step'        => $step,
                'error'       => $e->getMessage(),
            ]);
        }
    }

    public function via(object $notifiable): array
    {
        return [PushChannel::class];
    }

    public function toPush(object $notifiable): ?array
    {
        [$title, $body] = match ($this->step) {
            self::ACCEPTED  => [
                "🛵 Livreur en route · #{$this->orderReference}",
                "{$this->driverName} a accepté la course et vient chercher la commande.",
            ],
            self::PICKED_UP => [
                "📦 Commande #{$this->orderReference} récupérée",
                "{$this->driverName} a récupéré la commande et part chez le client.",
            ],
            default => [null, null],
        };

        if (!$title) {
            return null;
        }

        return [
            'title' => $title,
            'body'  => $body,
            'data'  => [
                'type'     => 'driver_' . $this->step,
                'order_id' => (string) $this->orderId,
            ],
            'link' => route('restaurant.deliveries'),
            'tag'  => 'driver-order-' . $this->orderId,
            'ttl'  => 1800,
        ];
    }
}
