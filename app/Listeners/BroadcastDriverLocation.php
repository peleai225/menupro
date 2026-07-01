<?php

namespace App\Listeners;

use App\Events\DriverLocationUpdated;

class BroadcastDriverLocation
{
    public function handle(DriverLocationUpdated $event): void
    {
        // L'event implémente déjà ShouldBroadcast — Laravel le diffuse automatiquement.
        // Ce listener sert à brancher des effets secondaires futurs (ex: log, alertes).
    }
}
