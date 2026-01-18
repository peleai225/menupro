<?php

namespace App\Policies;

use App\Models\Ingredient;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class IngredientPolicy
{
    use HandlesAuthorization;

    /**
     * Check if user's restaurant has stock management feature
     */
    protected function hasStockFeature(User $user): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return $user->restaurant?->hasFeature('stock') ?? false;
    }

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $this->hasStockFeature($user);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Ingredient $ingredient): bool
    {
        if (!$this->hasStockFeature($user)) {
            return false;
        }

        if ($user->isSuperAdmin()) {
            return true;
        }

        return $user->belongsToRestaurant($ingredient->restaurant_id);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        if (!$this->hasStockFeature($user)) {
            return false;
        }

        return $user->isSuperAdmin() || $user->isRestaurantAdmin();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Ingredient $ingredient): bool
    {
        if (!$this->hasStockFeature($user)) {
            return false;
        }

        if ($user->isSuperAdmin()) {
            return true;
        }

        return $user->isRestaurantAdmin() && $user->belongsToRestaurant($ingredient->restaurant_id);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Ingredient $ingredient): bool
    {
        if (!$this->hasStockFeature($user)) {
            return false;
        }

        if ($user->isSuperAdmin()) {
            return true;
        }

        return $user->isRestaurantAdmin() && $user->belongsToRestaurant($ingredient->restaurant_id);
    }

    /**
     * Determine whether the user can adjust stock.
     */
    public function adjustStock(User $user, Ingredient $ingredient): bool
    {
        if (!$this->hasStockFeature($user)) {
            return false;
        }

        if ($user->isSuperAdmin()) {
            return true;
        }

        return $user->belongsToRestaurant($ingredient->restaurant_id) && 
               ($user->isRestaurantAdmin() || $user->isEmployee());
    }

    /**
     * Determine whether the user can view stock movements.
     */
    public function viewMovements(User $user, Ingredient $ingredient): bool
    {
        if (!$this->hasStockFeature($user)) {
            return false;
        }

        if ($user->isSuperAdmin()) {
            return true;
        }

        return $user->belongsToRestaurant($ingredient->restaurant_id);
    }
}

