<?php

namespace App\Enums;

enum UserRole: string
{
    case SUPER_ADMIN = 'super_admin';
    case RESTAURANT_ADMIN = 'restaurant_admin';
    case EMPLOYEE = 'employee';
    case COMMANDO_AGENT = 'commando_agent';
    case COMMERCIAL = 'commercial';
    case TECHNICIAN = 'technician';
    case TEAM_LEADER = 'team_leader';
    case CUSTOMER = 'customer';
    case DELIVERY_DRIVER = 'delivery_driver';

    public function label(): string
    {
        return match ($this) {
            self::SUPER_ADMIN => 'Super Administrateur',
            self::RESTAURANT_ADMIN => 'Administrateur Restaurant',
            self::EMPLOYEE => 'Employé',
            self::COMMANDO_AGENT => 'Agent Commando',
            self::COMMERCIAL => 'Commercial',
            self::TECHNICIAN => 'Technicien',
            self::TEAM_LEADER => 'Team Leader',
            self::CUSTOMER => 'Client',
            self::DELIVERY_DRIVER => 'Livreur',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::SUPER_ADMIN => 'purple',
            self::RESTAURANT_ADMIN => 'blue',
            self::EMPLOYEE => 'gray',
            self::COMMANDO_AGENT => 'orange',
            self::COMMERCIAL => 'orange',
            self::TECHNICIAN => 'cyan',
            self::TEAM_LEADER => 'emerald',
            self::CUSTOMER => 'green',
            self::DELIVERY_DRIVER => 'yellow',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}

