<?php

namespace App\Enums;

enum DeliveryStatus: string
{
    case PENDING = 'pending';
    case ASSIGNED = 'assigned';
    case HEADING_TO_RESTAURANT = 'heading_to_restaurant';
    case PICKED_UP = 'picked_up';
    case DELIVERING = 'delivering';
    case DELIVERED = 'delivered';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'En attente de livreur',
            self::ASSIGNED => 'Livreur assigné',
            self::HEADING_TO_RESTAURANT => 'En route vers le resto',
            self::PICKED_UP => 'Commande récupérée',
            self::DELIVERING => 'En livraison',
            self::DELIVERED => 'Livrée',
            self::CANCELLED => 'Annulée',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PENDING => 'gray',
            self::ASSIGNED => 'blue',
            self::HEADING_TO_RESTAURANT => 'indigo',
            self::PICKED_UP => 'orange',
            self::DELIVERING => 'purple',
            self::DELIVERED => 'green',
            self::CANCELLED => 'red',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::PENDING => 'clock',
            self::ASSIGNED => 'user-check',
            self::HEADING_TO_RESTAURANT => 'navigation',
            self::PICKED_UP => 'package',
            self::DELIVERING => 'truck',
            self::DELIVERED => 'check-circle',
            self::CANCELLED => 'x-circle',
        };
    }

    public function isActive(): bool
    {
        return in_array($this, [
            self::ASSIGNED,
            self::HEADING_TO_RESTAURANT,
            self::PICKED_UP,
            self::DELIVERING,
        ]);
    }
}
