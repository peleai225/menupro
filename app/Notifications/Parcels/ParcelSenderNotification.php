<?php

namespace App\Notifications\Parcels;

use App\Models\Parcel;
use App\Models\User;
use App\Notifications\Channels\PushChannel;
use Illuminate\Notifications\Notification;

/**
 * Tient l'expéditeur d'un colis au courant (push) : livreur trouvé, ramassé, en route,
 * livré, annulé, échec. Même tag pour un colis : chaque étape remplace la précédente.
 * Destinataires : le Customer (app client) ou le personnel du commerce (User).
 */
class ParcelSenderNotification extends Notification
{
    public const DRIVER_FOUND = 'driver_found';
    public const PICKED_UP = 'picked_up';
    public const DELIVERING = 'delivering';
    public const DELIVERED = 'delivered';
    public const CANCELLED = 'cancelled';
    public const FAILED = 'failed';

    public function __construct(public Parcel $parcel, public string $event) {}

    public function via(object $notifiable): array
    {
        return [PushChannel::class];
    }

    public function toPush(object $notifiable): ?array
    {
        $ref = $this->parcel->reference;
        $driver = Parcel::firstName($this->parcel->driver?->name) ?? 'Le livreur';

        [$title, $body] = match ($this->event) {
            self::DRIVER_FOUND => ["🛵 Livreur trouvé · {$ref}", "{$driver} vient chercher votre colis."],
            self::PICKED_UP => ["📦 Colis {$ref} ramassé", "{$driver} a récupéré le colis."],
            self::DELIVERING => ["🛵 Colis {$ref} en route", "{$driver} part chez {$this->parcel->recipient_name}."],
            self::DELIVERED => ["✅ Colis {$ref} livré", "Le colis a été remis à {$this->parcel->recipient_name}."],
            self::CANCELLED => ["Colis {$ref} annulé", $this->parcel->cancellation_reason ?: 'La course a été annulée.'],
            self::FAILED => ["⚠️ Livraison du colis {$ref} échouée", $this->parcel->failure_reason ?: 'Le livreur n\'a pas pu remettre le colis. MenuPro vous contacte.'],
            default => [null, null],
        };

        if (!$title) {
            return null;
        }

        $link = $notifiable instanceof User
            ? route('restaurant.parcels')
            : rtrim(config('apps.client_url'), '/') . '/?open=parcels&ref=' . $ref;

        return [
            'title' => $title,
            'body' => $body,
            'data' => [
                'type' => 'parcel_' . $this->event,
                'parcel_reference' => $ref,
                'status' => $this->parcel->status->value,
            ],
            'link' => $link,
            'tag' => 'parcel-' . $this->parcel->id,
            'ttl' => 3600,
        ];
    }
}
