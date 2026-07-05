<?php

namespace App\Enums\Crm;

enum SubscriptionPlan: string
{
    case ESSENTIEL = 'essentiel';
    case PRO = 'pro';
    case BUSINESS = 'business';

    public function label(): string
    {
        return match ($this) {
            self::ESSENTIEL => 'Essentiel — 15 000 F/mois',
            self::PRO => 'Pro — 25 000 F/mois',
            self::BUSINESS => 'Business — 45 000 F/mois',
        };
    }

    public function shortLabel(): string
    {
        return match ($this) {
            self::ESSENTIEL => 'Essentiel',
            self::PRO => 'Pro',
            self::BUSINESS => 'Business',
        };
    }

    public function monthlyPriceCents(): int
    {
        return match ($this) {
            self::ESSENTIEL => 1_500_000,
            self::PRO       => 2_500_000,
            self::BUSINESS  => 4_500_000,
        };
    }

    public function signatureCommissionCents(): int
    {
        return match ($this) {
            self::ESSENTIEL => 300_000,   // 3 000 F
            self::PRO       => 500_000,   // 5 000 F
            self::BUSINESS  => 800_000,   // 8 000 F
        };
    }

    public function recurringCommissionCents(): int
    {
        return match ($this) {
            self::ESSENTIEL => 50_000,    // 500 F
            self::PRO       => 100_000,   // 1 000 F
            self::BUSINESS  => 150_000,   // 1 500 F
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::ESSENTIEL => '#64748b', // slate
            self::PRO       => '#f97316', // orange
            self::BUSINESS  => '#a855f7', // purple
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::ESSENTIEL => 'bg-slate-500/10 text-slate-400 border border-slate-500/20',
            self::PRO       => 'bg-orange-500/10 text-orange-400 border border-orange-500/20',
            self::BUSINESS  => 'bg-violet-500/10 text-violet-400 border border-violet-500/20',
        };
    }
}
