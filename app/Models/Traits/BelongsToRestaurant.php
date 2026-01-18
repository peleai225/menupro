<?php

namespace App\Models\Traits;

use App\Models\Restaurant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToRestaurant
{
    /**
     * Boot the trait - add global scope
     */
    protected static function bootBelongsToRestaurant(): void
    {
        // Automatically scope queries to the current restaurant
        static::addGlobalScope('restaurant', function (Builder $builder) {
            if ($restaurantId = static::getCurrentRestaurantId()) {
                $builder->where($builder->getModel()->getTable() . '.restaurant_id', $restaurantId);
            }
        });

        // Automatically set restaurant_id when creating
        static::creating(function ($model) {
            if (!$model->restaurant_id && $restaurantId = static::getCurrentRestaurantId()) {
                $model->restaurant_id = $restaurantId;
            }
        });
    }

    /**
     * Get the current restaurant ID from context
     */
    protected static function getCurrentRestaurantId(): ?int
    {
        // Priority: session > auth user > null
        if (session()->has('current_restaurant_id')) {
            return session('current_restaurant_id');
        }

        if (auth()->check() && auth()->user()->restaurant_id) {
            return auth()->user()->restaurant_id;
        }

        return null;
    }

    /**
     * Relationship to restaurant
     */
    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }

    /**
     * Scope to specific restaurant (bypasses global scope)
     */
    public function scopeForRestaurant(Builder $query, int $restaurantId): Builder
    {
        return $query->withoutGlobalScope('restaurant')
            ->where($this->getTable() . '.restaurant_id', $restaurantId);
    }

    /**
     * Scope without restaurant filter
     */
    public function scopeWithoutRestaurantScope(Builder $query): Builder
    {
        return $query->withoutGlobalScope('restaurant');
    }
}

