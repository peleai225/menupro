<?php

namespace App\Support;

use App\Enums\DeliveryStatus;
use App\Enums\OrderStatus;
use App\Models\Customer;
use App\Models\Delivery;
use App\Models\DeliveryDriver;
use App\Models\Order;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Support\Facades\Log;

/**
 * Compteur affiché sur l'icône de l'application (comme WhatsApp), envoyé dans data.badge
 * de chaque push. Il représente ce qui attend le destinataire, pas le nombre de pushs reçus :
 *
 * - personnel d'un restaurant : commandes payées (à accepter) ou confirmées (acceptées mais
 *   pas encore en préparation) + appels du personnel (service requests) en attente ;
 * - livreur : 1 s'il a une course en cours, sinon le nombre de courses disponibles dans sa
 *   ville (0 s'il est hors ligne), comme la liste de l'application livreur ;
 * - client : ses commandes en cours (payées → en livraison) ;
 * - autres comptes (admins, CRM, commando…) : notifications non lues.
 *
 * Uniquement des count() sur colonnes indexées : appelé à chaque envoi de push.
 */
class PushBadge
{
    public const RESTAURANT_ORDER_STATUSES = [OrderStatus::PAID, OrderStatus::CONFIRMED];

    public const CUSTOMER_ORDER_STATUSES = [
        OrderStatus::PAID,
        OrderStatus::CONFIRMED,
        OrderStatus::PREPARING,
        OrderStatus::READY,
        OrderStatus::DELIVERING,
    ];

    public static function for(object $notifiable): int
    {
        try {
            return match (true) {
                $notifiable instanceof DeliveryDriver => self::forDriver($notifiable),
                $notifiable instanceof Customer       => self::forCustomer($notifiable),
                $notifiable instanceof User           => self::forUser($notifiable),
                default                               => 0,
            };
        } catch (\Throwable $e) {
            // Le compteur est un bonus : il ne doit jamais empêcher l'envoi du push
            Log::warning('[PushBadge] calcul impossible', ['error' => $e->getMessage()]);
            return 0;
        }
    }

    public static function forUser(User $user): int
    {
        if (($user->isRestaurantAdmin() || $user->isEmployee()) && $user->restaurant_id) {
            return self::forRestaurant($user->restaurant_id);
        }

        if ($user->isDeliveryDriver() && $user->deliveryDriver) {
            return self::forDriver($user->deliveryDriver);
        }

        if ($user->isCustomer() && $user->customer) {
            return self::forCustomer($user->customer);
        }

        return $user->unreadNotifications()->count();
    }

    public static function forRestaurant(int $restaurantId): int
    {
        $orders = Order::withoutGlobalScope('restaurant')
            ->where('restaurant_id', $restaurantId)
            ->whereIn('status', array_map(fn ($s) => $s->value, self::RESTAURANT_ORDER_STATUSES))
            ->count();

        $serviceRequests = ServiceRequest::where('restaurant_id', $restaurantId)
            ->where('status', 'pending')
            ->count();

        return $orders + $serviceRequests;
    }

    public static function forDriver(DeliveryDriver $driver): int
    {
        $hasActiveDelivery = Delivery::withoutGlobalScope('restaurant')
            ->where('driver_id', $driver->id)
            ->whereIn('status', [
                DeliveryStatus::ASSIGNED->value,
                DeliveryStatus::HEADING_TO_RESTAURANT->value,
                DeliveryStatus::PICKED_UP->value,
                DeliveryStatus::DELIVERING->value,
            ])
            ->exists();

        if ($hasActiveDelivery) {
            return 1;
        }

        if (!$driver->is_available || !$driver->city) {
            return 0;
        }

        // Même définition que Api\V1\Driver\DeliveryController::pendingOrders()
        return Delivery::withoutGlobalScope('restaurant')
            ->where('status', DeliveryStatus::PENDING->value)
            ->whereHas('order', fn ($q) => $q->withoutGlobalScope('restaurant')->whereIn('status', [
                OrderStatus::PAID->value,
                OrderStatus::CONFIRMED->value,
            ]))
            ->whereHas('restaurant', fn ($q) => $q->where('city', $driver->city))
            ->count();
    }

    public static function forCustomer(Customer $customer): int
    {
        return Order::withoutGlobalScope('restaurant')
            ->where('customer_id', $customer->id)
            ->whereIn('status', array_map(fn ($s) => $s->value, self::CUSTOMER_ORDER_STATUSES))
            ->count();
    }
}
