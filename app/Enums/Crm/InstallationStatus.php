<?php

namespace App\Enums\Crm;

enum InstallationStatus: string
{
    case PLANIFIEE = 'planifiee';
    case EN_COURS = 'en_cours';
    case TERMINEE = 'terminee';
    case PROBLEME = 'probleme';
    case ANNULEE = 'annulee';

    public function label(): string
    {
        return match ($this) {
            self::PLANIFIEE => 'Planifiée',
            self::EN_COURS => 'En cours',
            self::TERMINEE => 'Terminée',
            self::PROBLEME => 'Problème',
            self::ANNULEE => 'Annulée',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PLANIFIEE => 'blue',
            self::EN_COURS => 'amber',
            self::TERMINEE => 'green',
            self::PROBLEME => 'red',
            self::ANNULEE => 'gray',
        };
    }
}
