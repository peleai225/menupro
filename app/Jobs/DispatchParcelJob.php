<?php

namespace App\Jobs;

use App\Enums\ParcelStatus;
use App\Models\Parcel;
use App\Notifications\Parcels\ParcelDriverNotification;
use App\Services\DriverAssignmentService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

/**
 * Une vague d'appel des livreurs pour un colis (même principe que AssignDriverJob pour les
 * repas) : rayon élargi à chaque vague (3, 6, 10 km), le premier qui accepte prend la course.
 * Les relances et l'alerte admin sont pilotées par la commande planifiée parcels:surveiller.
 */
class DispatchParcelJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public int $tries = 2;

    public function __construct(public int $parcelId) {}

    public function handle(DriverAssignmentService $assignment): void
    {
        $parcel = Parcel::find($this->parcelId);

        if (!$parcel || $parcel->status !== ParcelStatus::SEARCHING_DRIVER) {
            return;
        }

        $radii = (array) config('parcels.dispatch.search_radii_km', [3, 6, 10]);
        $attempt = $parcel->dispatch_attempts + 1;
        $radius = (float) $radii[min($attempt - 1, count($radii) - 1)];

        $drivers = $assignment->availableDriversAround($parcel->pickup_latitude, $parcel->pickup_longitude, $radius);

        $parcel->forceFill([
            'dispatch_attempts' => $attempt,
            'last_dispatch_at' => now(),
        ])->save();

        foreach ($drivers as $driver) {
            try {
                $driver->notify(new ParcelDriverNotification($parcel, ParcelDriverNotification::NEW));
            } catch (\Throwable $e) {
                Log::warning('Colis : push livreur impossible', ['driver_id' => $driver->id, 'error' => $e->getMessage()]);
            }
        }

        Log::info('Colis : livreurs appelés', [
            'parcel' => $parcel->reference,
            'attempt' => $attempt,
            'radius_km' => $radius,
            'drivers' => $drivers->count(),
        ]);
    }
}
