<?php

namespace App\Listeners;

use App\Events\NewDeliveryAvailable;
use App\Models\DeliveryDriver;
use App\Services\FcmService;
use Illuminate\Contracts\Queue\ShouldQueue;

class NotifyDriversOnNewDelivery implements ShouldQueue
{
    public string $queue = 'notifications';

    public function __construct(private FcmService $fcm) {}

    public function handle(NewDeliveryAvailable $event): void
    {
        if (! $this->fcm->isConfigured()) {
            return;
        }

        $drivers = DeliveryDriver::available()
            ->where('city', $event->city)
            ->whereNotNull('fcm_token')
            ->get();

        if ($drivers->isEmpty()) {
            return;
        }

        $tokens = $drivers->pluck('fcm_token')->all();

        $this->fcm->sendToMultiple(
            $tokens,
            'Nouvelle course disponible 🛵',
            'Une commande est disponible dans votre zone. Acceptez-la vite !',
            [
                'type'        => 'new_delivery',
                'delivery_id' => (string) $event->delivery->id,
                'city'        => $event->city,
            ]
        );
    }
}
