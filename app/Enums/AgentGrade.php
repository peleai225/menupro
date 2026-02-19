<?php

namespace App\Enums;

enum AgentGrade: string
{
    case ROOKIE = 'rookie';
    case COMMANDO = 'commando';
    case ELITE = 'elite';

    public function label(): string
    {
        return match ($this) {
            self::ROOKIE => 'Rookie',
            self::COMMANDO => 'Commando',
            self::ELITE => 'Élite',
        };
    }

    /** Restaurants parrainés (actifs) requis pour ce grade */
    public static function fromReferredCount(int $count): self
    {
        if ($count >= 21) {
            return self::ELITE;
        }
        if ($count >= 6) {
            return self::COMMANDO;
        }
        return self::ROOKIE;
    }

    /** Couleur bordure / glow pour la carte */
    public function glowColor(): string
    {
        return match ($this) {
            self::ROOKIE => '#94a3b8',   // slate
            self::COMMANDO => '#f97316', // orange
            self::ELITE => '#eab308',    // amber/gold
        };
    }

    /** Titre style "Colonel Digital" pour la carte */
    public function rankTitle(): string
    {
        return match ($this) {
            self::ROOKIE => 'Agent Rookie',
            self::COMMANDO => 'Commando',
            self::ELITE => 'Colonel Digital',
        };
    }

    /** Niveau d'accès 1-5 pour la carte */
    public function accessLevel(): int
    {
        return match ($this) {
            self::ROOKIE => 1,
            self::COMMANDO => 3,
            self::ELITE => 5,
        };
    }

    /** Rang lettre (A+, A, B) pour les métriques */
    public function rankLetter(): string
    {
        return match ($this) {
            self::ROOKIE => 'B',
            self::COMMANDO => 'A',
            self::ELITE => 'A+',
        };
    }
}
