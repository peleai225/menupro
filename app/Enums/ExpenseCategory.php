<?php

namespace App\Enums;

enum ExpenseCategory: string
{
    case FOOD_SUPPLIES = 'food_supplies';
    case BEVERAGES = 'beverages';
    case STAFF_SALARY = 'staff_salary';
    case RENT = 'rent';
    case UTILITIES = 'utilities';
    case EQUIPMENT = 'equipment';
    case MARKETING = 'marketing';
    case DELIVERY = 'delivery';
    case MAINTENANCE = 'maintenance';
    case OTHER = 'other';

    public function label(): string
    {
        return match ($this) {
            self::FOOD_SUPPLIES => 'Matières premières',
            self::BEVERAGES => 'Boissons',
            self::STAFF_SALARY => 'Salaires',
            self::RENT => 'Loyer',
            self::UTILITIES => 'Charges (eau, électricité)',
            self::EQUIPMENT => 'Équipement',
            self::MARKETING => 'Marketing',
            self::DELIVERY => 'Livraison',
            self::MAINTENANCE => 'Entretien',
            self::OTHER => 'Autre',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::FOOD_SUPPLIES => 'M3 3h18v18H3V3z',
            self::BEVERAGES => 'M12 19l9 2-9-18-9 18 9-2zm0 0v-8',
            self::STAFF_SALARY => 'M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2',
            self::RENT => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3',
            self::UTILITIES => 'M13 10V3L4 14h7v7l9-11h-7z',
            self::EQUIPMENT => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z',
            self::MARKETING => 'M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z',
            self::DELIVERY => 'M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0',
            self::MAINTENANCE => 'M14.7 6.3a1 1 0 000 1.4l1.6 1.6a1 1 0 001.4 0l3.77-3.77a6 6 0 01-7.94 7.94l-6.91 6.91a2.12 2.12 0 01-3-3l6.91-6.91a6 6 0 017.94-7.94l-3.76 3.76z',
            self::OTHER => 'M5 12h.01M12 12h.01M19 12h.01M6 12a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0z',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::FOOD_SUPPLIES => '#ef4444',
            self::BEVERAGES => '#f97316',
            self::STAFF_SALARY => '#8b5cf6',
            self::RENT => '#6366f1',
            self::UTILITIES => '#eab308',
            self::EQUIPMENT => '#64748b',
            self::MARKETING => '#ec4899',
            self::DELIVERY => '#14b8a6',
            self::MAINTENANCE => '#78716c',
            self::OTHER => '#9ca3af',
        };
    }
}
