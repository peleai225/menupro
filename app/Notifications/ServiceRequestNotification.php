<?php

namespace App\Notifications;

use App\Models\ServiceRequest;
use App\Notifications\Channels\PushChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

/**
 * Appel du personnel depuis une table ou une chambre (QR code) : cloche + push sur tous les
 * appareils du personnel, même tableau de bord fermé.
 *
 * La cloche Livewire sonne déjà pour les appels via son propre suivi des service requests :
 * elle ignore ce type de notification pour ne pas sonner deux fois (voir Livewire\Restaurant\Notifications).
 */
class ServiceRequestNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public int $serviceRequestId;
    public string $place;
    public string $type;
    public string $typeLabel;
    public string $icon;
    public ?string $notes;

    public function __construct(ServiceRequest $serviceRequest)
    {
        $this->serviceRequestId = $serviceRequest->id;
        $this->place            = $serviceRequest->placeLabel();
        $this->type             = (string) $serviceRequest->type;
        $this->typeLabel        = $serviceRequest->typeLabel();
        $this->icon             = $serviceRequest->typeIcon();
        $this->notes            = $serviceRequest->notes;
    }

    public function via(object $notifiable): array
    {
        return ['database', PushChannel::class];
    }

    public function toPush(object $notifiable): array
    {
        return [
            'title' => "{$this->icon} {$this->place} : {$this->shortLabel()}",
            'body'  => $this->notes ?: "Demande : {$this->typeLabel}. Merci d'y aller rapidement.",
            'data'  => [
                'type'               => 'service_request',
                'service_request_id' => (string) $this->serviceRequestId,
            ],
            // Les appels en attente s'affichent dans la cloche du tableau de bord
            'link'                => route('restaurant.dashboard'),
            'tag'                 => 'service-request-' . $this->serviceRequestId,
            'require_interaction' => true,
            'ttl'                 => 900,
        ];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'               => 'service_request',
            'service_request_id' => $this->serviceRequestId,
            'table_number'       => $this->place,
            'request_type'       => $this->type,
            'message'            => "{$this->place} : {$this->typeLabel}" . ($this->notes ? " — {$this->notes}" : ''),
        ];
    }

    private function shortLabel(): string
    {
        return match ($this->type) {
            'cleaning'   => 'Ménage',
            'assistance' => 'Appel du personnel',
            'checkout'   => 'Addition',
            'other'      => 'Demande',
            default      => $this->typeLabel,
        };
    }
}
