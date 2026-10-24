<?php

namespace App\Notifications;

use App\Models\Delivery;
use App\Models\DeliveryDriver;
use App\Notifications\Channels\PushChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

/**
 * Prévient le livreur (app livreur) que sa course est annulée : il ne doit plus se déplacer
 * ni livrer. Envoyée par toute annulation qui ne vient pas de lui (client, restaurant, admin).
 */
class DeliveryCancelledNotification extends Notification implements ShouldQueue
{
    use Queueable;

    // Données copiées (pas le modèle) : la course a pu être réattribuée entre-temps
    public int $deliveryId;
    public string $orderReference;
    public string $restaurantName;

    public function __construct(Delivery $delivery, public ?string $reason = null)
    {
        $this->deliveryId     = $delivery->id;
        $this->orderReference = (string) ($delivery->order?->reference ?: $delivery->order_id);
        $this->restaurantName = (string) ($delivery->restaurant?->name ?? 'le restaurant');
    }

    /**
     * Prévient le livreur assigné, sauf s'il a annulé lui-même. Ne bloque jamais l'annulation.
     */
    public static function sendTo(?int $driverId, Delivery $delivery, ?string $reason = null): void
    {
        if (!$driverId) {
            return;
        }

        try {
            DeliveryDriver::find($driverId)?->notify(new self($delivery, $reason));
        } catch (\Throwable $e) {
            Log::warning('Push annulation livreur échoué', [
                'delivery_id' => $delivery->id,
                'error'       => $e->getMessage(),
            ]);
        }
    }

    public function via(object $notifiable): array
    {
        return [PushChannel::class];
    }

    public function toPush(object $notifiable): array
    {
        $body = "La course #{$this->orderReference} ({$this->restaurantName}) a été annulée. Ne la livrez pas.";
        if ($this->reason) {
            $body .= " Motif : {$this->reason}";
        }

        return [
            'title' => '❌ Course annulée',
            'body'  => $body,
            'data'  => [
                'type'        => 'delivery_cancelled',
                'delivery_id' => (string) $this->deliveryId,
            ],
            'link'                => rtrim(config('apps.driver_url'), '/') . '/?open=dashboard',
            'tag'                 => 'delivery-' . $this->deliveryId,
            'require_interaction' => true,
            'ttl'                 => 1800,
        ];
    }
}
