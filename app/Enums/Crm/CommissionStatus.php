<?php

namespace App\Enums\Crm;

enum CommissionStatus: string
{
    case PENDING = 'pending';
    case VALIDATED = 'validated';
    case PAID = 'paid';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'En attente',
            self::VALIDATED => 'Validée',
            self::PAID => 'Payée',
            self::CANCELLED => 'Annulée',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PENDING => 'amber',
            self::VALIDATED => 'blue',
            self::PAID => 'green',
            self::CANCELLED => 'red',
        };
    }
}
