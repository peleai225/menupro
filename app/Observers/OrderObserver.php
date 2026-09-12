<?php

namespace App\Observers;

use App\Models\Order;
use Illuminate\Support\Facades\Schema;

class OrderObserver
{
    /**
     * Handle the Order "created" event.
     * Génère automatiquement un code de vérification à 4 chiffres.
     */
    public function created(Order $order): void
    {
        if (!Schema::hasColumn('orders', 'verification_code')) {
            return;
        }

        if (empty($order->verification_code)) {
            $order->verification_code = str_pad((string) random_int(1000, 9999), 4, '0', STR_PAD_LEFT);
            $order->saveQuietly();
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
