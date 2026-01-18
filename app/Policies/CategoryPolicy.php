<?php

namespace App\Policies;

use App\Models\Category;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CategoryPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->canManageRestaurant();
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Category $category): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return $user->belongsToRestaurant($category->restaurant_id);
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
        return $user->restaurant?->canCreate('categories') ?? false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Category $category): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return $user->isRestaurantAdmin() && $user->belongsToRestaurant($category->restaurant_id);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Category $category): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return $user->isRestaurantAdmin() && $user->belongsToRestaurant($category->restaurant_id);
    }

    /**
     * Determine whether the user can reorder categories.
     */
    public function reorder(User $user): bool
    {
        return $user->isSuperAdmin() || $user->isRestaurantAdmin();
    }
}

