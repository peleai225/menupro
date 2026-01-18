<?php

namespace App\Policies;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class OrderPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->canManageOrders();
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Order $order): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return $user->belongsToRestaurant($order->restaurant_id);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Orders are created by customers, not by admin users
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Order $order): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return $user->belongsToRestaurant($order->restaurant_id) && $user->canManageOrders();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Order $order): bool
    {
        // Orders should never be deleted, only cancelled
        return false;
    }

    /**
     * Determine whether the user can update order status.
     */
    public function updateStatus(User $user, Order $order): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if (!$user->belongsToRestaurant($order->restaurant_id)) {
            return false;
        }

        return $user->canManageOrders();
    }

    /**
     * Determine whether the user can cancel the order.
     */
    public function cancel(User $user, Order $order): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if (!$user->belongsToRestaurant($order->restaurant_id)) {
            return false;
        }

        // Only restaurant admin can cancel, and order must be cancellable
        return $user->isRestaurantAdmin() && $order->can_be_cancelled;
    }

    /**
     * Determine whether the user can refund the order.
     */
    public function refund(User $user, Order $order): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if (!$user->belongsToRestaurant($order->restaurant_id)) {
            return false;
        }

        // Only restaurant admin can refund
        return $user->isRestaurantAdmin() && $order->is_paid;
    }

    /**
     * Determine whether the user can print the order.
     */
    public function print(User $user, Order $order): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return $user->belongsToRestaurant($order->restaurant_id) && $user->canManageOrders();
    }

    /**
     * Determine whether the user can view order analytics.
     */
    public function viewAnalytics(User $user): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        // Check if restaurant plan has analytics
        return $user->isRestaurantAdmin() && 
               $user->restaurant?->hasFeature('analytics');
    }
}

