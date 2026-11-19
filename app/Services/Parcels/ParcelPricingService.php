<?php

namespace App\Services\Parcels;

use App\Models\DeliveryCity;
use App\Models\DeliveryPartner;
use App\Services\GeocodingService;

/**
 * Devis d'une course colis.
 *
 *   distance route = Haversine(ramassage, remise) × road_factor (1,3)
 *   course         = base + prix_km × distance route, arrondie au 50 F supérieur
 *   prix           = course + supplément taille (petit 0, moyen +200, grand +500)
 *   partenaire     = remise % éventuelle (arrondie au 50 F le plus proche)
 *   part livreur   = prix × driver_share_percent (80 % par défaut, même règle que les repas)
 *   commission     = prix − part livreur
 *
 * Tarifs : config/parcels.php ← delivery_cities.parcel_pricing (ville) ← delivery_partners.pricing.
 */
class ParcelPricingService
{
    public function __construct(private GeocodingService $geo) {}

    /**
     * @return array{city: DeliveryCity, distance_km: float, straight_km: float, price: int, driver_share: int,
     *               platform_commission: int, breakdown: array<string, mixed>}
     *
     * @throws ParcelException
     */
    public function quote(
        float $pickupLat,
        float $pickupLng,
        float $dropoffLat,
        float $dropoffLng,
        string $size,
        ?DeliveryPartner $partner = null,
    ): array {
        $city = $this->geo->detectDeliveryCity($pickupLat, $pickupLng);

        if (!$city) {
            throw new ParcelException(
                'La livraison de colis n\'est pas encore disponible à cette adresse de ramassage.',
                'city_not_served',
            );
        }

        if ($partner && !$partner->servesCity($city->name)) {
            throw new ParcelException(
                "Votre compte partenaire n'est pas ouvert dans la ville de {$city->name}.",
                'city_not_allowed_for_partner',
            );
        }

        $tariff = $this->tariffFor($city, $partner);

        if (!$tariff['enabled']) {
            throw new ParcelException(
                "La livraison de colis est momentanément suspendue à {$city->name}.",
                'city_disabled',
            );
        }

        if (!array_key_exists($size, $tariff['size_supplements'])) {
            throw new ParcelException('Taille de colis inconnue.', 'invalid_size');
        }

        $straight = DeliveryCity::haversineKm($pickupLat, $pickupLng, $dropoffLat, $dropoffLng);
        $road = round($straight * (float) config('parcels.road_factor', 1.3), 2);
        $max = (float) $tariff['max_distance_km'];

        if ($road > $max) {
            throw new ParcelException(
                'Distance trop longue : ' . self::km($road) . ' km par la route, le maximum à '
                    . $city->name . ' est de ' . self::km($max) . ' km.',
                'distance_too_long',
                422,
                ['distance_km' => $road, 'max_distance_km' => $max],
            );
        }

        $step = max(1, (int) config('parcels.rounding_step', 50));
        $distanceFee = (int) round($tariff['per_km_fee'] * $road);
        $course = (int) (ceil(($tariff['base_fee'] + $distanceFee) / $step) * $step);
        $supplement = (int) $tariff['size_supplements'][$size];
        $price = $course + $supplement;

        $discount = 0;
        if ($partner && (float) $partner->discount_percent > 0) {
            $discounted = (int) (round($price * (1 - min(100, (float) $partner->discount_percent) / 100) / $step) * $step);
            $discount = $price - $discounted;
            $price = $discounted;
        }

        $driverShare = (int) round($price * (float) $tariff['driver_share_percent'] / 100);
        $driverShare = max(0, min($price, $driverShare));

        return [
            'city' => $city,
            'distance_km' => $road,
            'straight_km' => round($straight, 2),
            'price' => $price,
            'driver_share' => $driverShare,
            'platform_commission' => $price - $driverShare,
            'breakdown' => [
                'base_fee' => (int) $tariff['base_fee'],
                'per_km_fee' => (int) $tariff['per_km_fee'],
                'distance_fee' => $distanceFee,
                'course_rounded' => $course,
                'size' => $size,
                'size_supplement' => $supplement,
                'partner_discount' => $discount,
                'road_factor' => (float) config('parcels.road_factor', 1.3),
                'max_distance_km' => $max,
                'driver_share_percent' => (float) $tariff['driver_share_percent'],
            ],
        ];
    }

    /**
     * Tarif effectif d'une ville (et d'un partenaire éventuel).
     *
     * @return array{enabled: bool, base_fee: int, per_km_fee: int, size_supplements: array<string,int>,
     *               max_distance_km: float, driver_share_percent: float}
     */
    public function tariffFor(?DeliveryCity $city, ?DeliveryPartner $partner = null): array
    {
        $tariff = config('parcels.pricing');

        foreach ([$city?->parcel_pricing, $partner?->pricing] as $override) {
            $tariff = self::merge($tariff, (array) $override);
        }

        return [
            'enabled' => (bool) $tariff['enabled'],
            'base_fee' => max(0, (int) $tariff['base_fee']),
            'per_km_fee' => max(0, (int) $tariff['per_km_fee']),
            'size_supplements' => array_map(fn ($v) => max(0, (int) $v), (array) $tariff['size_supplements']),
            'max_distance_km' => max(0.5, (float) $tariff['max_distance_km']),
            'driver_share_percent' => min(100, max(0, (float) $tariff['driver_share_percent'])),
        ];
    }

    /** Surcharge clé par clé : une valeur vide (null / '') garde la valeur par défaut. */
    private static function merge(array $base, array $override): array
    {
        foreach ($override as $key => $value) {
            if (!array_key_exists($key, $base) || $value === null || $value === '') {
                continue;
            }

            $base[$key] = is_array($value) && is_array($base[$key])
                ? self::merge($base[$key], $value)
                : $value;
        }

        return $base;
    }

    private static function km(float $km): string
    {
        return rtrim(rtrim(number_format($km, 1, ',', ' '), '0'), ',');
    }
}
