<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\RestaurantStatus;
use App\Enums\SubscriptionStatus;
use App\Models\Order;
use App\Models\Restaurant;
use App\Models\Subscription;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Définitions UNIQUES des indicateurs du back-office super-admin.
 *
 * Toutes les pages (dashboard, statistiques, abonnements, transactions, finances,
 * clients, restaurants, export) passent par ce service pour que les mêmes chiffres
 * aient la même définition partout :
 *
 *  - Périmètre : restaurants réels uniquement (is_demo = false, non supprimés).
 *  - Montants : orders.total et subscriptions.amount_paid sont en FCFA entiers.
 *  - Volume de commandes : commandes passées (hors brouillon et attente de paiement).
 *  - CA / GMV : commandes réellement payées et non annulées/remboursées
 *    (Order::scopeRevenue), datées par paid_at.
 *  - Revenus d'abonnements encaissés : status active|expired, amount_paid > 0,
 *    payment_reference renseignée, datés par paid_at (repli created_at).
 *  - MRR : dernier abonnement payant valide par restaurant, ramené à 30 jours.
 *
 * Toutes les bornes de dates sont calculées en PHP (pas de CURDATE()/NOW() SQL) et les
 * requêtes restent portables MySQL/SQLite (les tests tournent en SQLite).
 */
class PlatformMetrics
{
    /** Statuts exclus du volume de commandes (commande jamais réellement passée). */
    public const NOT_PLACED_STATUSES = [
        OrderStatus::DRAFT,
        OrderStatus::PENDING_PAYMENT,
    ];

    /** Statuts « en cours » (payée → en livraison). */
    public const IN_PROGRESS_STATUSES = [
        OrderStatus::PAID,
        OrderStatus::CONFIRMED,
        OrderStatus::PREPARING,
        OrderStatus::READY,
        OrderStatus::DELIVERING,
    ];

    /** Au-delà, une commande « en cours » est considérée bloquée (oubliée). */
    public const IN_PROGRESS_WINDOW_HOURS = 48;

    /** Un livreur est « en ligne » s'il est disponible ET a envoyé sa position récemment. */
    public const DRIVER_ONLINE_MINUTES = 10;

    /** Durée de référence d'un mois pour le MRR. */
    public const MRR_DAYS = 30;

    /** Périodes (jours) autorisées sur les pages de statistiques. */
    public const ALLOWED_PERIODS = [7, 30, 90, 365];

    // =========================================================================
    // PÉRIMÈTRE
    // =========================================================================

    /**
     * Restreint une requête aux restaurants réels (non démo, non supprimés).
     * Sous-requête portable : pas de liste d'IDs chargée en PHP.
     */
    public function onlyRealRestaurants(Builder|QueryBuilder $query, string $column = 'restaurant_id'): Builder|QueryBuilder
    {
        return $query->whereIn($column, function ($sub) {
            $sub->select('id')
                ->from('restaurants')
                ->where('is_demo', false)
                ->whereNull('deleted_at');
        });
    }

    /** Restaurants réels (SoftDeletes exclut déjà les supprimés). */
    public function restaurants(): Builder
    {
        return Restaurant::query()->where('is_demo', false);
    }

    /** Toutes les commandes des restaurants réels (sans scope restaurant de session). */
    public function orders(): Builder
    {
        return $this->onlyRealRestaurants(
            Order::withoutGlobalScope('restaurant'),
            'orders.restaurant_id'
        );
    }

    /** Commandes réellement passées (hors brouillon / attente de paiement). Les annulées sont incluses. */
    public function placedOrders(): Builder
    {
        return $this->orders()->whereNotIn('orders.status', self::NOT_PLACED_STATUSES);
    }

    /** Commandes génératrices de CA : payées et non annulées/remboursées (Order::scopeRevenue). */
    public function revenueOrders(): Builder
    {
        return $this->orders()->revenue();
    }

    /** Commandes en cours récentes (48 h) — au-delà elles sont comptées comme bloquées. */
    public function inProgressOrders(): Builder
    {
        return $this->orders()
            ->whereIn('orders.status', self::IN_PROGRESS_STATUSES)
            ->where('orders.created_at', '>=', now()->subHours(self::IN_PROGRESS_WINDOW_HOURS));
    }

    /** Commandes restées « en cours » depuis plus de 48 h. */
    public function stuckOrders(): Builder
    {
        return $this->orders()
            ->whereIn('orders.status', self::IN_PROGRESS_STATUSES)
            ->where('orders.created_at', '<', now()->subHours(self::IN_PROGRESS_WINDOW_HOURS));
    }

    /** Abonnements des restaurants réels. */
    public function subscriptions(): Builder
    {
        return $this->onlyRealRestaurants(
            Subscription::withoutGlobalScope('restaurant'),
            'subscriptions.restaurant_id'
        );
    }

    /**
     * Abonnements réellement encaissés : status active|expired, montant > 0 et
     * référence de paiement. Exclut les PENDING abandonnés, les essais et les
     * prolongations gratuites.
     */
    public function collectedSubscriptions(): Builder
    {
        return $this->subscriptions()
            ->whereIn('subscriptions.status', [SubscriptionStatus::ACTIVE->value, SubscriptionStatus::EXPIRED->value])
            ->where('subscriptions.amount_paid', '>', 0)
            ->whereNotNull('subscriptions.payment_reference');
    }

    /** Expression SQL de la date d'encaissement d'un abonnement (paid_at, repli created_at). */
    public function subscriptionPaidAtColumn(): string
    {
        return 'COALESCE(subscriptions.paid_at, subscriptions.created_at)';
    }

    /** Abonnements encaissés sur une période (bornes incluses). */
    public function collectedSubscriptionsBetween(CarbonInterface $from, CarbonInterface $to): Builder
    {
        return $this->collectedSubscriptions()
            ->whereRaw($this->subscriptionPaidAtColumn() . ' >= ?', [$from])
            ->whereRaw($this->subscriptionPaidAtColumn() . ' <= ?', [$to]);
    }

    // =========================================================================
    // MONTANTS
    // =========================================================================

    /** GMV / CA commandes (FCFA) payé entre deux dates (sur paid_at). */
    public function gmv(?CarbonInterface $from = null, ?CarbonInterface $to = null): int
    {
        return (int) $this->revenueOrders()
            ->when($from, fn ($q) => $q->where('orders.paid_at', '>=', $from))
            ->when($to, fn ($q) => $q->where('orders.paid_at', '<=', $to))
            ->sum('orders.total');
    }

    /**
     * Commissions plateforme (FCFA) : orders.platform_commission des commandes génératrices
     * de CA (payées, non annulées/remboursées), datées par paid_at. Remplace CommissionLog,
     * qui n'est pas alimenté pour toutes les commandes.
     */
    public function commissions(?CarbonInterface $from = null, ?CarbonInterface $to = null): int
    {
        return (int) $this->revenueOrders()
            ->when($from, fn ($q) => $q->where('orders.paid_at', '>=', $from))
            ->when($to, fn ($q) => $q->where('orders.paid_at', '<=', $to))
            ->sum('orders.platform_commission');
    }

    /** Statuts de reversement : fait (Jeko ou manuel) / à faire (en attente, échoué, cash à remettre). */
    public const PAYOUT_DONE_STATUSES = ['completed', 'manual'];
    public const PAYOUT_TODO_STATUSES = ['pending', 'failed', 'cash_pending'];

    /** Expression SQL du montant net reversé au restaurant (cf. ProcessJekoPayoutJob). */
    public function payoutAmountExpression(): string
    {
        return 'orders.total - COALESCE(orders.platform_commission, 0) - COALESCE(orders.delivery_fee, 0)';
    }

    /** Reversements aux restaurants calculés depuis orders.payout_status : ['count' => , 'amount' => ]. */
    public function payouts(array $statuses): array
    {
        $row = $this->revenueOrders()
            ->whereIn('orders.payout_status', $statuses)
            ->selectRaw('COUNT(*) as cnt, SUM(' . $this->payoutAmountExpression() . ') as amount')
            ->toBase()
            ->first();

        return ['count' => (int) ($row->cnt ?? 0), 'amount' => (int) ($row->amount ?? 0)];
    }

    /** Revenus d'abonnements encaissés (FCFA) entre deux dates. */
    public function subscriptionRevenue(?CarbonInterface $from = null, ?CarbonInterface $to = null): int
    {
        $query = $from || $to
            ? $this->collectedSubscriptionsBetween($from ?? now()->subYears(100), $to ?? now())
            : $this->collectedSubscriptions();

        return (int) $query->sum('subscriptions.amount_paid');
    }

    /**
     * MRR (FCFA) : pour chaque restaurant réel, son DERNIER abonnement payant valide
     * (status active, hors essai, amount_paid > 0, ends_at > maintenant), ramené à
     * 30 jours : amount_paid × 30 / durée de la période en jours (min 1).
     * Calculé en PHP (pas de fonctions de fenêtrage SQL) : volume = nb de restaurants.
     */
    public function mrr(): int
    {
        return (int) round(
            $this->latestPaidValidSubscriptions()->sum(fn (Subscription $s) => $this->monthlyAmount($s))
        );
    }

    /** Montant d'un abonnement ramené à 30 jours. */
    public function monthlyAmount(Subscription $subscription): float
    {
        $start = $subscription->starts_at ?? $subscription->created_at;
        $days = $start && $subscription->ends_at
            ? (int) round($start->diffInDays($subscription->ends_at, true))
            : self::MRR_DAYS;

        return $subscription->amount_paid * self::MRR_DAYS / max($days, 1);
    }

    // =========================================================================
    // ABONNEMENTS VALIDES (dernier par restaurant)
    // =========================================================================

    /**
     * Abonnements valides (active|trial, ends_at > maintenant) des restaurants réels,
     * dédupliqués : un seul par restaurant (celui qui finit le plus tard).
     */
    public function latestValidSubscriptions(): Collection
    {
        return $this->subscriptions()
            ->whereIn('subscriptions.status', [SubscriptionStatus::ACTIVE->value, SubscriptionStatus::TRIAL->value])
            ->where('subscriptions.ends_at', '>', now())
            ->get()
            ->groupBy('restaurant_id')
            ->map(fn (Collection $subs) => $subs->sort(fn ($a, $b) => [$b->ends_at->getTimestamp(), $b->id] <=> [$a->ends_at->getTimestamp(), $a->id])->first())
            ->values();
    }

    /** Dernier abonnement PAYANT valide par restaurant (base du MRR). */
    public function latestPaidValidSubscriptions(): Collection
    {
        return $this->subscriptions()
            ->where('subscriptions.status', SubscriptionStatus::ACTIVE->value)
            ->where('subscriptions.is_trial', false)
            ->where('subscriptions.amount_paid', '>', 0)
            ->where('subscriptions.ends_at', '>', now())
            ->get()
            ->groupBy('restaurant_id')
            ->map(fn (Collection $subs) => $subs->sort(fn ($a, $b) => [($b->starts_at ?? $b->created_at)->getTimestamp(), $b->id] <=> [($a->starts_at ?? $a->created_at)->getTimestamp(), $a->id])->first())
            ->values();
    }

    /**
     * Abonnements qui expirent dans $days jours : uniquement le dernier abonnement valide
     * de chaque restaurant (un restaurant qui a déjà renouvelé n'apparaît pas), essais inclus.
     */
    public function expiringSubscriptions(int $days = 7, ?int $limit = null): Collection
    {
        $limitDate = now()->addDays($days);

        $expiring = $this->latestValidSubscriptions()
            ->filter(fn (Subscription $s) => $s->ends_at->lte($limitDate))
            ->sortBy('ends_at')
            ->values();

        if ($limit !== null) {
            $expiring = $expiring->take($limit)->values();
        }

        return $expiring->load('restaurant', 'plan');
    }

    /** Un abonnement valide est-il un essai ? */
    public function isTrialSubscription(Subscription $subscription): bool
    {
        return $subscription->is_trial || $subscription->status === SubscriptionStatus::TRIAL;
    }

    /**
     * Répartition des restaurants réels selon leurs abonnements :
     *  - paying  : abonnement actif non-essai en cours de validité (payant ou prolongé par l'admin) ;
     *  - trial   : essai en cours (et pas d'abonnement payant valide) ;
     *  - pending : restaurant en attente de validation, sans abonnement valide ;
     *  - expired : tout le reste (abonnement/essai terminé, jamais abonné, suspendu sans abonnement).
     */
    public function restaurantSubscriptionCounts(): array
    {
        $valid = $this->subscriptions()
            ->whereIn('subscriptions.status', [SubscriptionStatus::ACTIVE->value, SubscriptionStatus::TRIAL->value])
            ->where('subscriptions.ends_at', '>', now())
            ->get(['subscriptions.restaurant_id', 'subscriptions.status', 'subscriptions.is_trial']);

        $paying = $valid->reject(fn ($s) => $this->isTrialSubscription($s))->pluck('restaurant_id')->unique();
        $trial = $valid->filter(fn ($s) => $this->isTrialSubscription($s))->pluck('restaurant_id')->unique()->diff($paying);
        $covered = $paying->merge($trial)->unique();

        $total = $this->restaurants()->count();
        $pending = $this->restaurants()
            ->where('status', RestaurantStatus::PENDING)
            ->whereNotIn('id', $covered->all())
            ->count();

        return [
            'total' => $total,
            'paying' => $paying->count(),
            'trial' => $trial->count(),
            'pending' => $pending,
            'expired' => max(0, $total - $paying->count() - $trial->count() - $pending),
        ];
    }

    /**
     * Restaurants actifs : non démo, non supprimés, avec un abonnement payant OU un essai
     * encore valide (ends_at > maintenant). Définition unique pour tout le back-office.
     */
    public function activeRestaurantsCount(): int
    {
        $counts = $this->restaurantSubscriptionCounts();

        return $counts['paying'] + $counts['trial'];
    }

    /**
     * Répartition par plan sur le dernier abonnement valide de chaque restaurant
     * (essais inclus). Retourne une collection d'objets {name, count}.
     */
    public function planDistribution(): Collection
    {
        $latest = $this->latestValidSubscriptions()->load('plan');

        return $latest
            ->groupBy(fn (Subscription $s) => $s->plan?->name ?? 'Sans plan')
            ->map(fn (Collection $subs, string $name) => (object) ['name' => $name, 'count' => $subs->count()])
            ->sortByDesc('count')
            ->values();
    }

    /**
     * Taux de renouvellement et de désabonnement, en RESTAURANTS, sur $days jours.
     *
     *  - Renouvellement : parmi les restaurants dont un abonnement payant a pris fin
     *    pendant la fenêtre, part de ceux qui ont aujourd'hui un abonnement payant valide.
     *  - Désabonnement : parmi les restaurants qui avaient un abonnement payant valide au
     *    début de la fenêtre, part de ceux qui n'en ont plus aujourd'hui.
     *
     * « Payant » = hors essai, amount_paid > 0, status active|expired.
     */
    public function retentionRates(int $days = 30): array
    {
        $windowStart = now()->subDays($days);

        $paid = $this->subscriptions()
            ->whereIn('subscriptions.status', [SubscriptionStatus::ACTIVE->value, SubscriptionStatus::EXPIRED->value])
            ->where('subscriptions.is_trial', false)
            ->where('subscriptions.amount_paid', '>', 0)
            ->whereNotNull('subscriptions.ends_at')
            ->where('subscriptions.ends_at', '>', $windowStart)
            ->get(['subscriptions.restaurant_id', 'subscriptions.status', 'subscriptions.starts_at', 'subscriptions.ends_at', 'subscriptions.created_at']);

        $now = now();
        $payingNow = $paid
            ->filter(fn ($s) => $s->status === SubscriptionStatus::ACTIVE && $s->ends_at->gt($now))
            ->pluck('restaurant_id')->unique();

        $endedInWindow = $paid
            ->filter(fn ($s) => $s->ends_at->lte($now))
            ->pluck('restaurant_id')->unique();

        $payingAtStart = $paid
            ->filter(fn ($s) => ($s->starts_at ?? $s->created_at)->lte($windowStart))
            ->pluck('restaurant_id')->unique();

        $renewed = $endedInWindow->intersect($payingNow)->count();
        $churned = $payingAtStart->diff($payingNow)->count();

        return [
            'renewal_rate' => $endedInWindow->isEmpty() ? null : round($renewed / $endedInWindow->count() * 100, 1),
            'renewal_base' => $endedInWindow->count(),
            'churn_rate' => $payingAtStart->isEmpty() ? null : round($churned / $payingAtStart->count() * 100, 1),
            'churn_base' => $payingAtStart->count(),
            'window_days' => $days,
        ];
    }

    // =========================================================================
    // OUTILS
    // =========================================================================

    /** Période validée (jours) : retombe sur $default si la valeur n'est pas autorisée. */
    public function period(mixed $value, int $default = 30, array $allowed = self::ALLOWED_PERIODS): int
    {
        $value = (int) $value;

        return in_array($value, $allowed, true) ? $value : $default;
    }

    /**
     * Évolution en % entre deux valeurs ; null quand la période précédente vaut 0
     * (la vue affiche alors « nouveau » ou « — » au lieu d'un +100 % trompeur).
     */
    public function growth(int|float $current, int|float $previous): ?float
    {
        if ($previous == 0) {
            return null;
        }

        return round(($current - $previous) / $previous * 100, 1);
    }

    /** Expression SQL portable « heure (0-23) » d'une colonne datetime. */
    public function hourExpression(string $column): string
    {
        return DB::connection()->getDriverName() === 'sqlite'
            ? "CAST(strftime('%H', {$column}) AS INTEGER)"
            : "HOUR({$column})";
    }

    /** Expression SQL portable « année-mois » (YYYY-MM) d'une colonne datetime. */
    public function yearMonthExpression(string $column): string
    {
        return DB::connection()->getDriverName() === 'sqlite'
            ? "strftime('%Y-%m', {$column})"
            : "DATE_FORMAT({$column}, '%Y-%m')";
    }

    /**
     * Regroupe des lignes journalières (clé « date » au format Y-m-d) par semaine ISO
     * (lundi). Évite YEARWEEK(), propre à MySQL. Retourne [lundi Y-m-d => [champ => somme]].
     */
    public function groupDailyRowsByWeek(iterable $rows, array $fields): array
    {
        $weeks = [];

        foreach ($rows as $row) {
            $row = (array) $row;
            $monday = \Carbon\Carbon::parse($row['date'])->startOfWeek(CarbonInterface::MONDAY)->toDateString();

            foreach ($fields as $field) {
                $weeks[$monday][$field] = ($weeks[$monday][$field] ?? 0) + (float) ($row[$field] ?? 0);
            }
        }

        ksort($weeks);

        return $weeks;
    }
}
