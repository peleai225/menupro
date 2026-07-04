<?php

namespace App\Enums\Crm;

enum WithdrawalStatus: string
{
    case PENDING = 'pending';
    case APPROVED = 'approved';
    case PAID = 'paid';
    case REJECTED = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'En attente',
            self::APPROVED => 'Approuvé',
            self::PAID => 'Payé',
            self::REJECTED => 'Rejeté',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PENDING => 'amber',
            self::APPROVED => 'blue',
            self::PAID => 'green',
            self::REJECTED => 'red',
        };
    }
}
