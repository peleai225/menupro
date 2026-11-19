<?php

namespace App\Notifications\Parcels;

use App\Models\Parcel;
use App\Notifications\Channels\PushChannel;
use Illuminate\Notifications\Notification;

/**
 * Alertes super-admin sur un colis : aucun livreur, code de remise bloqué, échec,
 * remboursement à effectuer. Cloche (database) + push.
 */
class ParcelAdminAlertNotification extends Notification
{
    public const NO_DRIVER = 'no_driver';
    public const CODE_LOCKED = 'code_locked';
    public const FAILED = 'failed';
    public const REFUND_REQUIRED = 'refund_required';

    public function __construct(public Parcel $parcel, public string $alert) {}

    public function via(object $notifiable): array
    {
        return ['database', PushChannel::class];
    }

    private function message(): array
    {
        $p = $this->parcel;
        $minutes = $p->driver_search_started_at ? (int) $p->driver_search_started_at->diffInMinutes(now()) : 0;

        return match ($this->alert) {
            self::NO_DRIVER => [
                "📦 Aucun livreur pour le colis {$p->reference}",
                "Aucun livreur n'a accepté depuis {$minutes} min ({$p->city}).",
            ],
            self::CODE_LOCKED => [
                "🔒 Code de remise bloqué · {$p->reference}",
                "Trop d'essais de code par le livreur {$p->driver?->name}. Vérifiez la remise.",
            ],
            self::FAILED => [
                "⚠️ Échec de livraison · {$p->reference}",
                ($p->failure_reason ?: 'Motif non précisé') . ' — le colis doit être rendu à l\'expéditeur.',
            ],
            self::REFUND_REQUIRED => [
                '💸 Remboursement colis à effectuer : ' . number_format($p->price, 0, ',', ' ') . ' F',
                "Colis {$p->reference} payé par Jeko puis annulé ({$p->cancellation_reason}).",
            ],
            default => ["Colis {$p->reference}", ''],
        };
    }

    public function toPush(object $notifiable): array
    {
        [$title, $body] = $this->message();

        return [
            'title' => $title,
            'body' => $body,
            'data' => [
                'type' => 'parcel_' . $this->alert,
                'parcel_reference' => $this->parcel->reference,
            ],
            'link' => route('super-admin.parcels.show', $this->parcel),
            'tag' => 'parcel-admin-' . $this->parcel->id . '-' . $this->alert,
            'require_interaction' => true,
        ];
    }

    public function toArray(object $notifiable): array
    {
        [$title, $body] = $this->message();

        return [
            'type' => 'parcel_' . $this->alert,
            'parcel_id' => $this->parcel->id,
            'parcel_reference' => $this->parcel->reference,
            'message' => $title . ' — ' . $body,
            'url' => route('super-admin.parcels.show', $this->parcel),
        ];
    }
}
