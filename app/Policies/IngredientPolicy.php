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

        return $this->canAccessIngredient($user, $ingredient);
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

        return $user->isRestaurantAdmin() && $this->canAccessIngredient($user, $ingredient);
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

        return $user->isRestaurantAdmin() && $this->canAccessIngredient($user, $ingredient);
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

        return $this->canAccessIngredient($user, $ingredient) &&
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

        return $this->canAccessIngredient($user, $ingredient);
    }

    /**
     * Check if user can access ingredient (handles orphaned ingredients with null restaurant_id).
     * Uses session context when available (for consistency with the list scope).
     */
    protected function canAccessIngredient(User $user, Ingredient $ingredient): bool
    {
        if ($ingredient->restaurant_id === null) {
            return $user->restaurant_id !== null || session()->has('current_restaurant_id');
        }

        $effectiveRestaurantId = session('current_restaurant_id') ?? $user->restaurant_id;

        return $effectiveRestaurantId !== null
            && (int) $ingredient->restaurant_id === (int) $effectiveRestaurantId;
    }
}

