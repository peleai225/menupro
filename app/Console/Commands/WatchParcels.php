<?php

namespace App\Console\Commands;

use App\Enums\ParcelStatus;
use App\Jobs\DispatchParcelJob;
use App\Models\Parcel;
use App\Notifications\Parcels\ParcelAdminAlertNotification;
use App\Services\Parcels\ParcelService;
use Illuminate\Console\Command;

/**
 * Planifiée chaque minute (routes/console.php) :
 *  - relance les livreurs pour les colis sans livreur (toutes les N minutes, rayon élargi) ;
 *  - alerte les admins si personne n'a accepté après N minutes (une seule fois) ;
 *  - annule les colis Jeko jamais payés après le délai de paiement.
 */
class WatchParcels extends Command
{
    protected $signature = 'parcels:surveiller';

    protected $description = 'Relance les livreurs, alerte les admins et expire les colis non payés';

    public function handle(ParcelService $service): int
    {
        $relaunch = (int) config('parcels.dispatch.relaunch_every_minutes', 2);
        $alertAfter = (int) config('parcels.dispatch.no_driver_alert_minutes', 10);
        $maxRelaunches = (int) config('parcels.dispatch.max_relaunches', 10);

        $relaunched = 0;
        Parcel::where('status', ParcelStatus::SEARCHING_DRIVER->value)
            ->where('dispatch_attempts', '<', $maxRelaunches)
            ->where(fn ($q) => $q->whereNull('last_dispatch_at')->orWhere('last_dispatch_at', '<=', now()->subMinutes($relaunch)))
            ->select('id')
            ->chunkById(200, function ($parcels) use (&$relaunched) {
                foreach ($parcels as $p) {
                    DispatchParcelJob::dispatch($p->id);
                    $relaunched++;
                }
            });

        $alerted = 0;
        Parcel::where('status', ParcelStatus::SEARCHING_DRIVER->value)
            ->whereNull('no_driver_alerted_at')
            ->where('driver_search_started_at', '<=', now()->subMinutes($alertAfter))
            ->chunkById(100, function ($parcels) use ($service, &$alerted) {
                foreach ($parcels as $p) {
                    // Marque d'abord (pas de double alerte si deux passages se chevauchent)
                    $claimed = Parcel::whereKey($p->id)->whereNull('no_driver_alerted_at')->update(['no_driver_alerted_at' => now()]);
                    if ($claimed) {
                        $service->alertAdmins($p, ParcelAdminAlertNotification::NO_DRIVER);
                        $alerted++;
                    }
                }
            });

        $expired = 0;
        Parcel::where('status', ParcelStatus::PENDING_PAYMENT->value)
            ->where('created_at', '<=', now()->subMinutes((int) config('parcels.unpaid_expiry_minutes', 60)))
            ->chunkById(100, function ($parcels) use ($service, &$expired) {
                foreach ($parcels as $p) {
                    try {
                        $service->cancel($p, 'system', 'Paiement non finalisé dans le délai.');
                        $expired++;
                    } catch (\Throwable) {
                        // déjà payé ou modifié entre-temps
                    }
                }
            });

        $this->info("Colis : {$relaunched} relance(s), {$alerted} alerte(s) admin, {$expired} expiré(s).");

        return self::SUCCESS;
    }
}
