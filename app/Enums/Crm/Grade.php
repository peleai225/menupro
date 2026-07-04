<?php

namespace App\Enums\Crm;

enum Grade: string
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

    public function title(): string
    {
        return match ($this) {
            self::ROOKIE => 'Agent Terrain',
            self::COMMANDO => 'Agent Commando',
            self::ELITE => 'Colonel Digital',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::ROOKIE => 'slate',
            self::COMMANDO => 'orange',
            self::ELITE => 'amber',
        };
    }

    public function glowClass(): string
    {
        return match ($this) {
            self::ROOKIE => 'shadow-slate-400/20',
            self::COMMANDO => 'shadow-orange-500/40',
            self::ELITE => 'shadow-amber-400/50',
        };
    }

    public function minConversions(): int
    {
        return match ($this) {
            self::ROOKIE => 0,
            self::COMMANDO => 6,
            self::ELITE => 21,
        };
    }

    public static function fromConversions(int $count): self
    {
        return match (true) {
            $count >= 21 => self::ELITE,
            $count >= 6 => self::COMMANDO,
            default => self::ROOKIE,
        };
    }
}
