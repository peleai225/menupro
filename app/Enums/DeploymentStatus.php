<?php

namespace App\Enums;

enum DeploymentStatus: string
{
    case EN_NEGOCIATION = 'en_negociation';
    case EN_ATTENTE_PAIEMENT = 'en_attente_paiement';
    case ACTIF = 'actif';

    public function label(): string
    {
        return match ($this) {
            self::EN_NEGOCIATION => 'En négociation',
            self::EN_ATTENTE_PAIEMENT => 'En attente de paiement',
            self::ACTIF => 'Actif',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::EN_NEGOCIATION => 'amber',
            self::EN_ATTENTE_PAIEMENT => 'sky',
            self::ACTIF => 'emerald',
        };
    }
}
