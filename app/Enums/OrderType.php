<?php

namespace App\Enums;

enum OrderType: string
{
    case DINE_IN = 'dine_in';
    case TAKEAWAY = 'takeaway';
    case DELIVERY = 'delivery';

    public function label(): string
    {
        return match ($this) {
            self::DINE_IN => 'Sur place',
            self::TAKEAWAY => 'À emporter',
            self::DELIVERY => 'Livraison',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::DINE_IN => 'building-storefront',
            self::TAKEAWAY => 'shopping-bag',
            self::DELIVERY => 'truck',
        };
    }

    public function requiresAddress(): bool
    {
        return $this === self::DELIVERY;
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}

