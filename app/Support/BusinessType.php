<?php

namespace App\Support;

use App\Models\Restaurant;

/**
 * Types de commerce (restaurants.type) et vocabulaire associé.
 *
 * Source : config/business_types.php. Deux familles :
 *  - KIND_FOOD : restauration (Plats / Menu / Cuisine) ;
 *  - KIND_SHOP : boutique (Articles / Catalogue / Préparation).
 */
class BusinessType
{
    public const KIND_FOOD = 'food';
    public const KIND_SHOP = 'shop';

    /** @return array<string, array{label: string, icon: string, kind: string, group: string}> */
    public static function all(): array
    {
        return config('business_types.types', []);
    }

    /** Valeurs acceptées pour restaurants.type (validation « in: »). */
    public static function values(): array
    {
        return array_keys(static::all());
    }

    /** Types d'une famille (food | shop). */
    public static function valuesOfKind(string $kind): array
    {
        return array_keys(array_filter(static::all(), fn ($t) => ($t['kind'] ?? self::KIND_FOOD) === $kind));
    }

    /** Types d'un groupe d'inscription (restaurant | stand | shop) : [valeur => libellé]. */
    public static function optionsForGroup(string $group): array
    {
        return array_map(
            fn ($t) => $t['label'],
            array_filter(static::all(), fn ($t) => ($t['group'] ?? null) === $group)
        );
    }

    public static function normalize(?string $type): string
    {
        return isset(static::all()[$type]) ? $type : config('business_types.default', 'restaurant');
    }

    public static function kindOf(?string $type): string
    {
        return static::all()[static::normalize($type)]['kind'] ?? self::KIND_FOOD;
    }

    public static function labelOf(?string $type): string
    {
        return static::all()[static::normalize($type)]['label'] ?? 'Restaurant';
    }

    public static function iconOf(?string $type): string
    {
        return static::all()[static::normalize($type)]['icon'] ?? '🍽️';
    }

    public static function isShop(?string $type): bool
    {
        return static::kindOf($type) === self::KIND_SHOP;
    }

    /**
     * Libellé selon le type de commerce (ex. 'dishes' → « Plats » ou « Articles »).
     * Sans commerce fourni : celui de l'utilisateur connecté (tableau de bord), sinon restauration.
     */
    public static function label(string $key, Restaurant|string|null $business = null): string
    {
        $kind = static::kindFor($business);
        $vocabulary = config('business_types.vocabulary', []);

        return $vocabulary[$kind][$key]
            ?? $vocabulary[self::KIND_FOOD][$key]
            ?? $key;
    }

    /** Onglets du tableau de bord sans objet pour ce commerce. */
    public static function hiddenNavKeys(Restaurant|string|null $business): array
    {
        return config('business_types.hidden_nav.' . static::kindFor($business), []);
    }

    protected static function kindFor(Restaurant|string|null $business): string
    {
        if ($business === null) {
            $business = auth()->user()?->restaurant;
        }

        if ($business instanceof Restaurant) {
            return static::kindOf($business->type);
        }

        return static::kindOf($business);
    }
}
