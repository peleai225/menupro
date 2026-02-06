<?php

namespace App\Enums;

enum SubscriptionStatus: string
{
    case ACTIVE = 'active';
    case TRIAL = 'trial';
    case EXPIRED = 'expired';
    case CANCELLED = 'cancelled';
    case PENDING = 'pending';

    public function label(): string
    {
        return match ($this) {
            self::ACTIVE => 'Actif',
            self::TRIAL => 'Essai gratuit',
            self::EXPIRED => 'Expiré',
            self::CANCELLED => 'Annulé',
            self::PENDING => 'En attente',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::ACTIVE => 'success',
            self::TRIAL => 'info',
            self::EXPIRED => 'error',
            self::CANCELLED => 'neutral',
            self::PENDING => 'warning',
        };
    }

    public function isValid(): bool
    {
        return in_array($this, [self::ACTIVE, self::TRIAL]);
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}

