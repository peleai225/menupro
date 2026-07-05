<?php

namespace App\Enums\Crm;

enum AgentStatus: string
{
    case CANDIDAT = 'candidat';
    case EN_FORMATION = 'en_formation';
    case ACTIF = 'actif';
    case SUSPENDU = 'suspendu';
    case INACTIF = 'inactif';
    case BANNI = 'banni';

    public function label(): string
    {
        return match ($this) {
            self::CANDIDAT => 'Candidat',
            self::EN_FORMATION => 'En formation',
            self::ACTIF => 'Actif',
            self::SUSPENDU => 'Suspendu',
            self::INACTIF => 'Inactif',
            self::BANNI => 'Banni',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::CANDIDAT => 'amber',
            self::EN_FORMATION => 'orange',
            self::ACTIF => 'emerald',
            self::SUSPENDU => 'slate',
            self::INACTIF => 'red',
            self::BANNI => 'red',
        };
    }

    public function canWork(): bool
    {
        return $this === self::ACTIF;
    }

    public function canLogin(): bool
    {
        return in_array($this, [self::CANDIDAT, self::EN_FORMATION, self::ACTIF]);
    }
}
