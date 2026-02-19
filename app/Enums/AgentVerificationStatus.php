<?php

namespace App\Enums;

enum AgentVerificationStatus: string
{
    case SHADOW = 'shadow';
    case PENDING_REVIEW = 'pending_review';
    case VALIDE = 'valide';
    case REJETE = 'rejete';
    case BANNI = 'banni';

    public function label(): string
    {
        return match ($this) {
            self::SHADOW => 'Shadow (inscription en cours)',
            self::PENDING_REVIEW => 'En attente de vérification',
            self::VALIDE => 'Valide',
            self::REJETE => 'Rejeté',
            self::BANNI => 'Banni',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::SHADOW => 'neutral',
            self::PENDING_REVIEW => 'amber',
            self::VALIDE => 'green',
            self::REJETE => 'red',
            self::BANNI => 'red',
        };
    }

    public function canAccessParrainage(): bool
    {
        return $this === self::VALIDE;
    }

    public function canGenerateCard(): bool
    {
        return $this === self::VALIDE;
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
