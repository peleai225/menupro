<?php

namespace App\Enums;

/**
 * Motifs de refus d'une commande par le restaurant (stockés dans orders.rejection_reason).
 */
enum OrderRejectionReason: string
{
    case OUT_OF_STOCK = 'out_of_stock';
    case TOO_BUSY = 'too_busy';
    case CLOSED = 'closed';
    case OTHER = 'other';

    public function label(): string
    {
        return match ($this) {
            self::OUT_OF_STOCK => 'Rupture de stock',
            self::TOO_BUSY => 'Trop de commandes en cours',
            self::CLOSED => 'Restaurant fermé',
            self::OTHER => 'Autre motif',
        };
    }

    /**
     * Phrase destinée au client (« … ne peut pas honorer votre commande : … »).
     */
    public function customerLabel(): string
    {
        return match ($this) {
            self::OUT_OF_STOCK => 'un ou plusieurs plats ne sont plus disponibles',
            self::TOO_BUSY => 'le restaurant a trop de commandes en ce moment',
            self::CLOSED => 'le restaurant est fermé',
            self::OTHER => 'le restaurant ne peut pas la préparer',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
