<?php

namespace App\Enums\Crm;

enum LeadStatus: string
{
    case NOUVEAU = 'nouveau';
    case CONTACTE = 'contacte';
    case DEMONSTRATION = 'demonstration';
    case RELANCE = 'relance';
    case SIGNATURE = 'signature';
    case INSTALLATION = 'installation';
    case ACTIF = 'actif';
    case PERDU = 'perdu';

    public function label(): string
    {
        return match ($this) {
            self::NOUVEAU => 'Nouveau',
            self::CONTACTE => 'Contacté',
            self::DEMONSTRATION => 'Démonstration',
            self::RELANCE => 'Relance',
            self::SIGNATURE => 'Signature',
            self::INSTALLATION => 'Installation',
            self::ACTIF => 'Actif',
            self::PERDU => 'Perdu',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::NOUVEAU => 'sky',
            self::CONTACTE => 'indigo',
            self::DEMONSTRATION => 'violet',
            self::RELANCE => 'amber',
            self::SIGNATURE => 'emerald',
            self::INSTALLATION => 'blue',
            self::ACTIF => 'green',
            self::PERDU => 'red',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::NOUVEAU => 'sparkles',
            self::CONTACTE => 'phone',
            self::DEMONSTRATION => 'presentation-chart-bar',
            self::RELANCE => 'arrow-path',
            self::SIGNATURE => 'document-check',
            self::INSTALLATION => 'wrench-screwdriver',
            self::ACTIF => 'check-badge',
            self::PERDU => 'x-circle',
        };
    }

    public function order(): int
    {
        return match ($this) {
            self::NOUVEAU => 1,
            self::CONTACTE => 2,
            self::DEMONSTRATION => 3,
            self::RELANCE => 4,
            self::SIGNATURE => 5,
            self::INSTALLATION => 6,
            self::ACTIF => 7,
            self::PERDU => 8,
        };
    }

    public function isActive(): bool
    {
        return !in_array($this, [self::PERDU, self::ACTIF]);
    }

    public function canTransitionTo(self $target): bool
    {
        $allowed = match ($this) {
            self::NOUVEAU => [self::CONTACTE, self::PERDU],
            self::CONTACTE => [self::DEMONSTRATION, self::RELANCE, self::PERDU],
            self::DEMONSTRATION => [self::RELANCE, self::SIGNATURE, self::PERDU],
            self::RELANCE => [self::DEMONSTRATION, self::SIGNATURE, self::PERDU],
            self::SIGNATURE => [self::INSTALLATION, self::PERDU],
            self::INSTALLATION => [self::ACTIF, self::PERDU],
            self::ACTIF => [],
            self::PERDU => [self::NOUVEAU],
        };

        return in_array($target, $allowed);
    }

    public static function pipelineStatuses(): array
    {
        return [
            self::NOUVEAU,
            self::CONTACTE,
            self::DEMONSTRATION,
            self::RELANCE,
            self::SIGNATURE,
            self::INSTALLATION,
            self::ACTIF,
        ];
    }
}
