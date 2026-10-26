<?php

namespace App\Services;

use App\Models\Plan;
use App\Models\Restaurant;

class PlanLimiter
{
    protected Restaurant $restaurant;
    protected ?Plan $plan = null;

    /**
     * Set the restaurant to check limits for
     */
    public function forRestaurant(Restaurant $restaurant): static
    {
        $this->restaurant = $restaurant;
        // Pendant l'essai : limites du plan Pro (Restaurant::effectivePlan())
        $this->plan = $restaurant->effectivePlan();

        return $this;
    }

    /**
     * État du quota SOUPLE de commandes du mois (n'empêche jamais une commande) :
     * ['current', 'limit' (null = illimité), 'percentage', 'threshold' (palier atteint : 100, 80 ou null)].
     */
    public function getOrderQuotaStatus(): array
    {
        $limit = $this->getLimit('orders_this_month');

        if ($limit === null || $limit <= 0) {
            return ['current' => 0, 'limit' => null, 'percentage' => 0, 'threshold' => null];
        }

        $current = $this->getCurrentCount('orders_this_month');
        $percentage = (int) floor($current * 100 / $limit);

        $threshold = null;
        $thresholds = config('plans.order_quota_alert_thresholds', [80, 100]);
        rsort($thresholds);
        foreach ($thresholds as $t) {
            if ($percentage >= $t) {
                $threshold = (int) $t;
                break;
            }
        }

        return [
            'current' => $current,
            'limit' => $limit,
            'percentage' => $percentage,
            'threshold' => $threshold,
        ];
    }

    /**
     * Check if restaurant can create a new resource
     */
    public function canCreate(string $resource): bool
    {
        if (!$this->plan) {
            return false;
        }

        return !$this->isLimitReached($resource);
    }

    /**
     * Check if restaurant can add a new dish
     */
    public function canAddDish(): bool
    {
        return $this->canCreate('dishes');
    }

    /**
     * Check if restaurant can add a new category
     */
    public function canAddCategory(): bool
    {
        return $this->canCreate('categories');
    }

    /**
     * Check if limit is reached for a resource
     */
    public function isLimitReached(string $resource): bool
    {
        if (!$this->plan) {
            return true;
        }

        $current = $this->getCurrentCount($resource);
        $limit = $this->getLimit($resource);

        // null limit means unlimited
        if ($limit === null) {
            return false;
        }

        return $current >= $limit;
    }

    /**
     * Get remaining quota for a resource
     */
    public function getRemainingQuota(string $resource): int
    {
        if (!$this->plan) {
            return 0;
        }

        $current = $this->getCurrentCount($resource);
        $limit = $this->getLimit($resource);

        if ($limit === null) {
            return PHP_INT_MAX; // Unlimited
        }

        return max(0, $limit - $current);
    }

    /**
     * Get usage percentage for a resource
     */
    public function getUsagePercentage(string $resource): float
    {
        if (!$this->plan) {
            return 100;
        }

        $current = $this->getCurrentCount($resource);
        $limit = $this->getLimit($resource);

        if ($limit === null || $limit === 0) {
            return 0;
        }

        return min(100, ($current / $limit) * 100);
    }

    /**
     * Get current count for a resource
     */
    public function getCurrentCount(string $resource): int
    {
        return match ($resource) {
            'dishes' => $this->restaurant->dishes()->count(),
            'categories' => $this->restaurant->categories()->count(),
            'employees' => $this->restaurant->users()->where('role', 'employee')->count(),
            // Seules les commandes réellement payées/acceptées comptent (hors brouillons,
            // attentes de paiement, annulées et caisse POS) — voir Order::scopeCountingTowardQuota
            'orders_this_month' => \App\Models\Order::withoutGlobalScopes()
                ->where('restaurant_id', $this->restaurant->id)
                ->countingTowardQuota()
                ->count(),
            default => 0,
        };
    }

    /**
     * Get limit for a resource from plan
     */
    public function getLimit(string $resource): ?int
    {
        if (!$this->plan) {
            return 0;
        }

        $limit = match ($resource) {
            'dishes' => $this->plan->max_dishes,
            'categories' => $this->plan->max_categories,
            'employees' => $this->plan->max_employees,
            'orders_this_month' => $this->plan->max_orders_per_month,
            default => null,
        };

        // 9999+ means unlimited
        if ($limit !== null && $limit >= 9999) {
            return null;
        }

        return $limit;
    }

    /**
     * Check if restaurant has a specific feature
     */
    public function hasFeature(string $feature): bool
    {
        if (!$this->plan) {
            return false;
        }

        return $this->plan->hasFeature($feature);
    }

    // =========================================================================
    // ÉTAT D'ACCÈS (essai / actif / limité) — utilisé par la navigation et le middleware
    // =========================================================================

    public const ACCESS_TRIAL = 'trial';
    public const ACCESS_ACTIVE = 'active';
    public const ACCESS_LIMITED = 'limited';

    /**
     * Le restaurant a-t-il un abonnement (payé ou essai) encore valide ?
     */
    public function hasValidSubscription(): bool
    {
        $subscription = $this->restaurant->activeSubscription;

        return $subscription !== null
            && ($subscription->ends_at === null || $subscription->ends_at->isFuture());
    }

    /**
     * Abonnement expiré (ou essai terminé, éventuellement avec un paiement en attente) :
     * le tableau de bord est limité à l'abonnement et aux paramètres.
     */
    public function isAccessLimited(): bool
    {
        if ($this->restaurant->status === \App\Enums\RestaurantStatus::EXPIRED) {
            return true;
        }

        return $this->restaurant->is_subscription_expired && !$this->hasValidSubscription();
    }

    public function accessState(): string
    {
        if ($this->isAccessLimited()) {
            return self::ACCESS_LIMITED;
        }

        return $this->restaurant->isOnTrial() ? self::ACCESS_TRIAL : self::ACCESS_ACTIVE;
    }

    /** Paiement d'abonnement en attente (lien Jeko créé, webhook pas encore reçu). */
    public function pendingSubscription(): ?\App\Models\Subscription
    {
        return $this->restaurant->subscriptions()
            ->where('status', \App\Enums\SubscriptionStatus::PENDING)
            ->latest()
            ->first();
    }

    /** Jours d'essai restants (null hors essai). */
    public function trialDaysLeft(): ?int
    {
        if (!$this->restaurant->isOnTrial()) {
            return null;
        }

        $endsAt = $this->restaurant->activeSubscription?->ends_at;

        return $endsAt ? max(0, (int) ceil(now()->diffInHours($endsAt, false) / 24)) : null;
    }

    /**
     * Plan le moins cher de la grille qui inclut cette fonction (pour « Passer au plan X »).
     */
    public static function requiredPlanFor(string $feature): ?Plan
    {
        // Cache « array » : durée de la requête (la navigation l'appelle pour chaque onglet verrouillé)
        $plans = cache()->store('array')->rememberForever('plan-limiter.active-plans', fn () => Plan::active()->ordered()->get());

        return $plans->first(fn (Plan $plan) => !$plan->is_on_quote && $plan->hasFeature($feature))
            ?? $plans->first(fn (Plan $plan) => $plan->hasFeature($feature));
    }

    public static function featureLabel(string $feature): string
    {
        return config("plans.features.{$feature}.label") ?? ucfirst($feature);
    }

    /**
     * Route accessible quand l'accès est limité (abonnement expiré / en attente de paiement) ?
     */
    public static function isRouteAllowedWhenLimited(\Illuminate\Http\Request $request): bool
    {
        foreach (config('plans.limited_access_routes', []) as $pattern) {
            if ($request->routeIs($pattern)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get all quotas summary
     */
    public function getQuotasSummary(): array
    {
        $resources = ['dishes', 'categories', 'employees', 'orders_this_month'];
        $summary = [];

        foreach ($resources as $resource) {
            $current = $this->getCurrentCount($resource);
            $limit = $this->getLimit($resource);

            $summary[$resource] = [
                'current' => $current,
                'limit' => $limit,
                'remaining' => $limit !== null ? max(0, $limit - $current) : null,
                'percentage' => $limit !== null && $limit > 0 
                    ? min(100, round(($current / $limit) * 100)) 
                    : 0,
                'unlimited' => $limit === null,
            ];
        }

        return $summary;
    }

    /**
     * Get features summary
     */
    public function getFeaturesSummary(): array
    {
        $features = array_keys(config('plans.features', []));
        $summary = [];

        foreach ($features as $feature) {
            $summary[$feature] = $this->hasFeature($feature);
        }

        return $summary;
    }

    /**
     * Validate action and throw exception if not allowed
     */
    public function validateOrFail(string $resource): void
    {
        if (!$this->canCreate($resource)) {
            $limit = $this->getLimit($resource);
            $resourceName = match ($resource) {
                'dishes' => 'plats',
                'categories' => 'catégories',
                'employees' => 'employés',
                'orders_this_month' => 'commandes ce mois-ci',
                default => $resource,
            };

            throw new \App\Exceptions\QuotaExceededException(
                "Vous avez atteint la limite de {$limit} {$resourceName} pour votre plan actuel."
            );
        }
    }
}

