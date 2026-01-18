<?php

namespace App\Enums;

enum Unit: string
{
    case KILOGRAM = 'kg';
    case GRAM = 'g';
    case LITER = 'L';
    case MILLILITER = 'mL';
    case PIECE = 'piece';
    case PACK = 'pack';
    case DOZEN = 'dozen';
    case BOTTLE = 'bottle';

    public function label(): string
    {
        return match ($this) {
            self::KILOGRAM => 'Kilogramme (kg)',
            self::GRAM => 'Gramme (g)',
            self::LITER => 'Litre (L)',
            self::MILLILITER => 'Millilitre (mL)',
            self::PIECE => 'Pièce',
            self::PACK => 'Paquet',
            self::DOZEN => 'Douzaine',
            self::BOTTLE => 'Bouteille',
        };
    }

    public function shortLabel(): string
    {
        return match ($this) {
            self::KILOGRAM => 'kg',
            self::GRAM => 'g',
            self::LITER => 'L',
            self::MILLILITER => 'mL',
            self::PIECE => 'pce',
            self::PACK => 'pqt',
            self::DOZEN => 'dz',
            self::BOTTLE => 'btl',
        };
    }

    /**
     * Convert to base unit (gram for weight, mL for volume)
     */
    public function toBaseMultiplier(): float
    {
        return match ($this) {
            self::KILOGRAM => 1000,
            self::GRAM => 1,
            self::LITER => 1000,
            self::MILLILITER => 1,
            self::PIECE, self::PACK, self::DOZEN, self::BOTTLE => 1,
        };
    }

    public function isWeight(): bool
    {
        return in_array($this, [self::KILOGRAM, self::GRAM]);
    }

    public function isVolume(): bool
    {
        return in_array($this, [self::LITER, self::MILLILITER]);
    }

    public function isCountable(): bool
    {
        return in_array($this, [self::PIECE, self::PACK, self::DOZEN, self::BOTTLE]);
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}

