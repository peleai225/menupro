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

// Canal livreur — le livreur lui-même uniquement
Broadcast::channel('driver.{driverId}', function ($user, $driverId) {
    return $user->deliveryDriver?->id === (int) $driverId;
});

// Canal suivi commande client — public (par tracking_token, pas d'auth)
// Utilise Channel (public) dans les Events — pas de règle d'autorisation nécessaire

// Canal super admin
Broadcast::channel('admin.platform', function ($user) {
    return $user->isSuperAdmin();
});
