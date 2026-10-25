<?php

namespace App\Jobs;

use App\Models\Restaurant;
use App\Services\OrderQuotaMonitor;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;

/**
 * Vérifie le quota souple de commandes d'un restaurant après une commande payée/acceptée.
 * En file d'attente : ne ralentit jamais la prise de commande.
 */
class CheckOrderQuota implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public int $tries = 3;

    public function __construct(public int $restaurantId) {}

    public function handle(OrderQuotaMonitor $monitor): void
    {
        $restaurant = Restaurant::find($this->restaurantId);

        if ($restaurant) {
            $monitor->check($restaurant);
        }
    }
}
