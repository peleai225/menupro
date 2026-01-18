<?php

namespace App\Policies;

use App\Models\Dish;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class DishPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->canManageRestaurant() || $user->isEmployee();
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Dish $dish): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return $user->belongsToRestaurant($dish->restaurant_id);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if (!$user->isRestaurantAdmin()) {
            return false;
        }

        // Check quota
        return $user->restaurant?->canCreate('dishes') ?? false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Dish $dish): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if (!$user->belongsToRestaurant($dish->restaurant_id)) {
            return false;
        }

        // Restaurant admin can update everything
        if ($user->isRestaurantAdmin()) {
            return true;
        }

        // Employees can only update availability
        return $user->isEmployee();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Dish $dish): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return $user->isRestaurantAdmin() && $user->belongsToRestaurant($dish->restaurant_id);
    }

    /**
     * Determine whether the user can toggle dish availability.
     */
    public function toggleAvailability(User $user, Dish $dish): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return $user->belongsToRestaurant($dish->restaurant_id) && 
               ($user->isRestaurantAdmin() || $user->isEmployee());
    }

    /**
     * Determine whether the user can manage dish ingredients.
     */
    public function manageIngredients(User $user, Dish $dish): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return $user->isRestaurantAdmin() && $user->belongsToRestaurant($dish->restaurant_id);
    }
}

