<?php

namespace App\Enums;

enum PaymentGateway: string
{
    case WAVE = 'wave';
    case JEKO = 'jeko';
    case CINETPAY = 'cinetpay';
    case CASH = 'cash';

    public function label(): string
    {
        return match($this) {
            self::WAVE => 'Wave CI',
            self::JEKO => 'Jeko Mobile Money',
            self::CINETPAY => 'CinetPay',
            self::CASH => 'Espèces',
        };
    }

    public function supportsOnlinePayment(): bool
    {
        return $this !== self::CASH;
    }
}
