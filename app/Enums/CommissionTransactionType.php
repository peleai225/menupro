<?php

namespace App\Enums;

enum CommissionTransactionType: string
{
    case COMMISSION = 'commission';
    case WITHDRAWAL_REQUEST = 'withdrawal_request';
    case WITHDRAWAL_PAID = 'withdrawal_paid';

    public function label(): string
    {
        return match ($this) {
            self::COMMISSION => 'Commission',
            self::WITHDRAWAL_REQUEST => 'Demande de retrait',
            self::WITHDRAWAL_PAID => 'Retrait effectué',
        };
    }
}
