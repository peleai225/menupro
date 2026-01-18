<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin() || $user->isRestaurantAdmin();
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, User $model): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        // Can view self
        if ($user->id === $model->id) {
            return true;
        }

        // Restaurant admin can view users of their restaurant
        return $user->isRestaurantAdmin() && 
               $model->restaurant_id === $user->restaurant_id;
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

        // Check quota for employees
        return $user->restaurant?->canCreate('employees') ?? false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, User $model): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        // Can update self
        if ($user->id === $model->id) {
            return true;
        }

        // Restaurant admin can update employees of their restaurant
        if ($user->isRestaurantAdmin() && $model->isEmployee()) {
            return $model->restaurant_id === $user->restaurant_id;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, User $model): bool
    {
        // Can't delete self
        if ($user->id === $model->id) {
            return false;
        }

        if ($user->isSuperAdmin()) {
            return true;
        }

        // Restaurant admin can delete employees of their restaurant
        if ($user->isRestaurantAdmin() && $model->isEmployee()) {
            return $model->restaurant_id === $user->restaurant_id;
        }

        return false;
    }

    /**
     * Determine whether the user can change user role.
     */
    public function changeRole(User $user, User $model): bool
    {
        // Only super admin can change roles
        return $user->isSuperAdmin();
    }

    /**
     * Determine whether the user can activate/deactivate user.
     */
    public function toggleActive(User $user, User $model): bool
    {
        // Can't deactivate self
        if ($user->id === $model->id) {
            return false;
        }

        if ($user->isSuperAdmin()) {
            return true;
        }

        // Restaurant admin can toggle employees
        if ($user->isRestaurantAdmin() && $model->isEmployee()) {
            return $model->restaurant_id === $user->restaurant_id;
        }

        return false;
    }
}

