<?php

namespace App\Enums\Crm;

enum CommissionType: string
{
    case SIGNATURE = 'signature';
    case INSTALLATION = 'installation';
    case RECURRING = 'recurring';
    case BONUS_PERFORMANCE = 'bonus_performance';
    case BONUS_GRADE = 'bonus_grade';
    case REFERRAL_BONUS = 'referral_bonus';
    case LEADER_OVERRIDE = 'leader_override';

    public function label(): string
    {
        return match ($this) {
            self::SIGNATURE => 'Commission signature',
            self::INSTALLATION => 'Commission installation',
            self::RECURRING => 'Commission récurrente',
            self::BONUS_PERFORMANCE => 'Bonus performance',
            self::BONUS_GRADE => 'Bonus grade',
            self::REFERRAL_BONUS => 'Bonus parrainage',
            self::LEADER_OVERRIDE => 'Override team leader',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::SIGNATURE => 'document-check',
            self::INSTALLATION => 'wrench-screwdriver',
            self::RECURRING => 'arrow-path',
            self::BONUS_PERFORMANCE => 'trophy',
            self::BONUS_GRADE => 'star',
            self::REFERRAL_BONUS => 'gift',
            self::LEADER_OVERRIDE => 'user-group',
        };
    }
}
