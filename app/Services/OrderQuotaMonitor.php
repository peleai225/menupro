<?php

namespace App\Services;

use App\Enums\UserRole;
use App\Models\Restaurant;
use App\Notifications\OrderQuotaReachedNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Quota mensuel « souple » de commandes.
 *
 * Aucune commande client n'est jamais bloquée. Quand le restaurant franchit un palier
 * (config('plans.order_quota_alert_thresholds') : 80 % puis 100 % de son quota), ses
 * gérants reçoivent UNE notification (cloche + push) l'invitant à passer au plan supérieur.
 *
 * Unicité garantie par la clé unique (restaurant, mois, palier) de order_quota_alerts :
 * même avec des commandes simultanées, un seul envoi par palier et par mois.
 */
class OrderQuotaMonitor
{
    public function __construct(private PlanLimiter $limiter) {}

    /**
     * Vérifie le quota du restaurant et notifie si un nouveau palier est franchi.
     * Retourne le palier notifié (80, 100) ou null.
     */
    public function check(Restaurant $restaurant): ?int
    {
        $status = (clone $this->limiter)->forRestaurant($restaurant)->getOrderQuotaStatus();

        if ($status['threshold'] === null) {
            return null;
        }

        $inserted = DB::table('order_quota_alerts')->insertOrIgnore([
            'restaurant_id' => $restaurant->id,
            'period' => now()->format('Y-m'),
            'threshold' => $status['threshold'],
            'orders_count' => $status['current'],
            'orders_limit' => $status['limit'],
            'created_at' => now(),
        ]);

        if ($inserted === 0) {
            return null; // palier déjà notifié ce mois-ci
        }

        $restaurant->users()
            ->where('role', UserRole::RESTAURANT_ADMIN)
            ->get()
            ->each(fn ($user) => $user->notify(new OrderQuotaReachedNotification(
                $restaurant,
                $status['threshold'],
                $status['current'],
                $status['limit'],
            )));

        Log::info('[Quota] palier de commandes franchi', [
            'restaurant_id' => $restaurant->id,
            'threshold' => $status['threshold'],
            'orders' => $status['current'],
            'limit' => $status['limit'],
        ]);

        return $status['threshold'];
    }

    /**
     * Palier déjà atteint ce mois-ci (pour le bandeau du tableau de bord), sans requête de comptage.
     */
    public function reachedThisMonth(Restaurant $restaurant): ?int
    {
        $threshold = DB::table('order_quota_alerts')
            ->where('restaurant_id', $restaurant->id)
            ->where('period', now()->format('Y-m'))
            ->max('threshold');

        return $threshold !== null ? (int) $threshold : null;
    }
}
