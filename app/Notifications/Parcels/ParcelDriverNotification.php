<?php

namespace App\Notifications\Parcels;

use App\Models\Parcel;
use App\Notifications\Channels\PushChannel;
use Illuminate\Notifications\Notification;

/**
 * Push livreur : nouvelle course colis disponible, ou course colis annulée.
 */
class ParcelDriverNotification extends Notification
{
    public const NEW = 'new';
    public const CANCELLED = 'cancelled';

    public function __construct(public Parcel $parcel, public string $event) {}

    public function via(object $notifiable): array
    {
        return [PushChannel::class];
    }

    public function toPush(object $notifiable): ?array
    {
        $p = $this->parcel;
        $base = rtrim(config('apps.driver_url'), '/');

        if ($this->event === self::NEW) {
            return [
                'title' => '📦 Nouvelle course colis',
                'body' => 'Colis ' . $p->sizeLabel() . ' · ' . number_format($p->distance_km, 1, ',', ' ')
                    . ' km · gain ' . number_format($p->driver_share, 0, ',', ' ') . ' F. Ouvrez l\'app pour accepter.',
                'data' => [
                    'type' => 'new_parcel',
                    'kind' => 'parcel',
                    'parcel_reference' => $p->reference,
                ],
                'link' => $base . '/?open=parcels',
                'tag' => 'parcel-' . $p->id,
                'require_interaction' => true,
                'ttl' => 600,
            ];
        }

        if ($this->event === self::CANCELLED) {
            return [
                'title' => "Course colis {$p->reference} annulée",
                'body' => $p->cancellation_reason ?: 'L\'expéditeur a annulé la course. Vous êtes de nouveau disponible.',
                'data' => [
                    'type' => 'parcel_cancelled',
                    'kind' => 'parcel',
                    'parcel_reference' => $p->reference,
                ],
                'link' => $base . '/?open=deliveries',
                'tag' => 'parcel-' . $p->id,
            ];
        }

        return null;
    }
}
