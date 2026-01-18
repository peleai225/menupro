<?php

namespace App\Policies;

use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class RestaurantPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin();
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Restaurant $restaurant): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return $user->belongsToRestaurant($restaurant->id);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Anyone can create a restaurant during registration
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Restaurant $restaurant): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return $user->isRestaurantAdmin() && $user->belongsToRestaurant($restaurant->id);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Restaurant $restaurant): bool
    {
        return $user->isSuperAdmin();
    }

    /**
     * Determine whether the user can validate the restaurant.
     */
    public function validate(User $user, Restaurant $restaurant): bool
    {
        return $user->isSuperAdmin();
    }

    /**
     * Determine whether the user can suspend the restaurant.
     */
    public function suspend(User $user, Restaurant $restaurant): bool
    {
        return $user->isSuperAdmin();
    }

    /**
     * Determine whether the user can manage settings.
     */
    public function manageSettings(User $user, Restaurant $restaurant): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return $user->isRestaurantAdmin() && $user->belongsToRestaurant($restaurant->id);
    }

    /**
     * Determine whether the user can manage payment settings.
     */
    public function managePayments(User $user, Restaurant $restaurant): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return $user->isRestaurantAdmin() && $user->belongsToRestaurant($restaurant->id);
    }
}

