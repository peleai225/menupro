<?php

namespace App\Listeners;

use App\Events\NewDeliveryAvailable;
use App\Models\DeliveryDriver;
use Illuminate\Contracts\Queue\ShouldQueue;

class NotifyDriversOnNewDelivery implements ShouldQueue
{
    public string $queue = 'notifications';

    public function handle(NewDeliveryAvailable $event): void
    {
        // Trouver les livreurs en ligne dans la ville et leur envoyer une notification push
        $drivers = DeliveryDriver::available()
            ->where('city', $event->city)
            ->whereNotNull('fcm_token')
            ->get();

        foreach ($drivers as $driver) {
            // TODO: envoyer push FCM quand Firebase est configuré
            // FcmService::send($driver->fcm_token, 'Nouvelle course disponible', ...)
        }
    }
}
