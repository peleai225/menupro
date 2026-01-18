<?php

namespace App\Enums;

enum SubscriptionStatus: string
{
    case ACTIVE = 'active';
    case EXPIRED = 'expired';
    case CANCELLED = 'cancelled';
    case PENDING = 'pending';

    public function label(): string
    {
        return match ($this) {
            self::ACTIVE => 'Actif',
            self::EXPIRED => 'Expiré',
            self::CANCELLED => 'Annulé',
            self::PENDING => 'En attente',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::ACTIVE => 'success',
            self::EXPIRED => 'error',
            self::CANCELLED => 'neutral',
            self::PENDING => 'warning',
        };
    }

    public function isValid(): bool
    {
        return $this === self::ACTIVE;
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}

