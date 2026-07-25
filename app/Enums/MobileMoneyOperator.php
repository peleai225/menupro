<?php

namespace App\Enums;

enum MobileMoneyOperator: string
{
    case ORANGE = 'orange';
    case MTN = 'mtn';
    case MOOV = 'moov';
    case WAVE = 'wave';

    public function label(): string
    {
        return match($this) {
            self::ORANGE => 'Orange Money',
            self::MTN => 'MTN Mobile Money',
            self::MOOV => 'Moov Money',
            self::WAVE => 'Wave',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::ORANGE => '#FF6600',
            self::MTN => '#FFCC00',
            self::MOOV => '#009DDB',
            self::WAVE => '#1B4D89',
        };
    }
}
