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
        $this->plan = $restaurant->currentPlan;

        return $this;
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
            'orders_this_month' => $this->restaurant->orders()
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
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

        return match ($resource) {
            'dishes' => $this->plan->max_dishes,
            'categories' => $this->plan->max_categories,
            'employees' => $this->plan->max_employees,
            'orders_this_month' => $this->plan->max_orders_per_month,
            default => null,
        };
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
        $features = ['delivery', 'stock', 'analytics', 'custom_domain', 'priority_support'];
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

