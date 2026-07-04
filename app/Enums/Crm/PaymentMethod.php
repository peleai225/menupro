<?php

namespace App\Enums\Crm;

enum PaymentMethod: string
{
    case WAVE = 'wave';
    case ORANGE_MONEY = 'orange_money';
    case MTN = 'mtn';
    case MOOV = 'moov';
    case BANK_TRANSFER = 'bank_transfer';

    public function label(): string
    {
        return match ($this) {
            self::WAVE => 'Wave',
            self::ORANGE_MONEY => 'Orange Money',
            self::MTN => 'MTN MoMo',
            self::MOOV => 'Moov Money',
            self::BANK_TRANSFER => 'Virement bancaire',
        };
    }
}
