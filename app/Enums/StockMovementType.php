<?php

namespace App\Enums;

enum StockMovementType: string
{
    case ENTRY = 'entry';
    case EXIT_SALE = 'exit_sale';
    case EXIT_MANUAL = 'exit_manual';
    case EXIT_WASTE = 'exit_waste';
    case ADJUSTMENT = 'adjustment';
    case TRANSFER = 'transfer';

    public function label(): string
    {
        return match ($this) {
            self::ENTRY => 'Entrée',
            self::EXIT_SALE => 'Sortie (Vente)',
            self::EXIT_MANUAL => 'Sortie manuelle',
            self::EXIT_WASTE => 'Perte/Gaspillage',
            self::ADJUSTMENT => 'Ajustement inventaire',
            self::TRANSFER => 'Transfert',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::ENTRY => 'success',
            self::EXIT_SALE => 'info',
            self::EXIT_MANUAL => 'warning',
            self::EXIT_WASTE => 'error',
            self::ADJUSTMENT => 'neutral',
            self::TRANSFER => 'primary',
        };
    }

    public function isPositive(): bool
    {
        return $this === self::ENTRY;
    }

    public function isNegative(): bool
    {
        return in_array($this, [self::EXIT_SALE, self::EXIT_MANUAL, self::EXIT_WASTE]);
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}

