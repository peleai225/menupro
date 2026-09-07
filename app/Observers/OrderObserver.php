<?php

namespace App\Observers;

use App\Models\Order;

class OrderObserver
{
    /**
     * Handle the Order "created" event.
     * Génère automatiquement un code de vérification à 4 chiffres.
     */
    public function created(Order $order): void
    {
        // Générer un code aléatoire à 4 chiffres si pas encore défini
        if (empty($order->verification_code)) {
            $order->verification_code = str_pad((string) random_int(1000, 9999), 4, '0', STR_PAD_LEFT);
            $order->saveQuietly(); // saveQuietly pour éviter de retrigger l'observer
        }
    }

    /**
     * Handle the Order "updated" event.
     */
    public function updated(Order $order): void
    {
        //
    }

    /**
     * Handle the Order "deleted" event.
     */
    public function deleted(Order $order): void
    {
        //
    }

    /**
     * Handle the Order "restored" event.
     */
    public function restored(Order $order): void
    {
        //
    }

    /**
     * Handle the Order "force deleted" event.
     */
    public function forceDeleted(Order $order): void
    {
        //
    }
}
