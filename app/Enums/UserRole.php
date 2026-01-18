<?php

namespace App\Enums;

enum UserRole: string
{
    case SUPER_ADMIN = 'super_admin';
    case RESTAURANT_ADMIN = 'restaurant_admin';
    case EMPLOYEE = 'employee';

    public function label(): string
    {
        return match ($this) {
            self::SUPER_ADMIN => 'Super Administrateur',
            self::RESTAURANT_ADMIN => 'Administrateur Restaurant',
            self::EMPLOYEE => 'Employé',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::SUPER_ADMIN => 'purple',
            self::RESTAURANT_ADMIN => 'blue',
            self::EMPLOYEE => 'gray',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}

