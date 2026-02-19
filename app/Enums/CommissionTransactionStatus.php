<?php

namespace App\Enums;

enum CommissionTransactionStatus: string
{
    case PENDING = 'pending';
    case VALIDATED = 'validated';
    case WITHDRAWN = 'withdrawn';
    case REJECTED = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'En attente',
            self::VALIDATED => 'Validée',
            self::WITHDRAWN => 'Retirée',
            self::REJECTED => 'Rejetée',
        };
    }
}
