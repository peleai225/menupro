<?php

use App\Enums\UserRole;
use App\Models\Order;
use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
| Canaux publics (Channel) : pas d'autorisation requise
| Canaux privés (PrivateChannel) : authentification Sanctum requise
*/

// Canal restaurant — admin et employees du restaurant
Broadcast::channel('restaurant.{restaurantId}.orders', function ($user, $restaurantId) {
    return $user->isSuperAdmin() || (int) $user->restaurant_id === (int) $restaurantId;
});

Broadcast::channel('restaurant.{restaurantId}.deliveries', function ($user, $restaurantId) {
    return $user->isSuperAdmin() || (int) $user->restaurant_id === (int) $restaurantId;
});

// Canal livreur — le livreur lui-même ou super admin
Broadcast::channel('driver.{driverId}', function ($user, $driverId) {
    return $user->isSuperAdmin() || $user->deliveryDriver?->id === (int) $driverId;
});

// Canal delivery — restaurant propriétaire ou super admin
Broadcast::channel('delivery.{deliveryId}', function ($user, $deliveryId) {
    $delivery = \App\Models\Delivery::find($deliveryId);
    return $delivery && ($user->isSuperAdmin() || (int) $user->restaurant_id === (int) $delivery->restaurant_id);
});

// Canal suivi commande client — public (par tracking_token, pas d'auth)
// Utilise Channel (public) dans les Events — pas de règle d'autorisation nécessaire

// Canal super admin
Broadcast::channel('admin.platform', function ($user) {
    return $user->isSuperAdmin();
});

// Canaux CRM
Broadcast::channel('crm.user.{userId}', function ($user, $userId) {
    return (int) $user->id === (int) $userId;
});

Broadcast::channel('crm.admin', function ($user) {
    return $user->isSuperAdmin() || $user->isCrmUser();
});

Broadcast::channel('crm.team.{teamId}', function ($user, $teamId) {
    return $user->isSuperAdmin() || $user->isCrmUser();
});
