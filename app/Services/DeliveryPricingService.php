<?php

namespace App\Services;

use App\Models\DeliveryCity;
use App\Models\DeliveryZone;
use App\Models\Restaurant;

class DeliveryPricingService
{
    private const DEFAULT_BASE_FEE = 50000;
    private const DEFAULT_FEE_PER_KM = 15000;
    private const DEFAULT_MAX_DISTANCE = 10;

    public function __construct(private GeocodingService $geo) {}

    /**
     * @return array{
     *   fee: int,
     *   distance_km: float,
     *   estimated_minutes: int,
     *   breakdown: array,
     *   is_peak: bool,
     *   within_range: bool,
     *   city_name: ?string,
     *   zone_name: ?string
     * }
     */
    public function calculate(
        Restaurant $restaurant,
        float $customerLat,
        float $customerLng
    ): array {
        $restaurantLat = (float) $restaurant->latitude;
        $restaurantLng = (float) $restaurant->longitude;
        $hasValidCoords = $restaurantLat !== 0.0 || $restaurantLng !== 0.0;

        // Si le restaurant a des coords GPS valides, on détecte sa ville via ses coords.
        // Sinon, on recherche la ville par le nom du champ `city` du restaurant,
        // puis on tombe en fallback sur les coords du client.
        if ($hasValidCoords) {
            $deliveryCity = $this->geo->detectDeliveryCity($restaurantLat, $restaurantLng)
                ?? $this->geo->detectDeliveryCity($customerLat, $customerLng);
        } else {
            $deliveryCity = $this->detectCityByName($restaurant->city)
                ?? $this->geo->detectDeliveryCity($customerLat, $customerLng);
        }

        if (!$deliveryCity) {
            return $this->outOfRange($restaurant, $customerLat, $customerLng);
        }

        // Quand les coords du restaurant sont absentes, utiliser le centre de la ville
        // comme point de référence pour le calcul de distance (plutôt que (0, 0))
        $fromLat = $hasValidCoords ? $restaurantLat : (float) $deliveryCity->center_latitude;
        $fromLng = $hasValidCoords ? $restaurantLng : (float) $deliveryCity->center_longitude;

        $distanceKm = $this->geo->distanceKm(
            $fromLat, $fromLng,
            $customerLat, $customerLng
        );

        if ($distanceKm > $deliveryCity->max_delivery_distance_km) {
            return [
                'fee' => 0,
                'distance_km' => round($distanceKm, 2),
                'estimated_minutes' => 0,
                'breakdown' => [],
                'is_peak' => false,
                'within_range' => false,
                'city_name' => $deliveryCity->name,
                'zone_name' => null,
            ];
        }

        $isPeak = $deliveryCity->isPeakHour();
        $baseFee = $deliveryCity->delivery_base_fee;
        $feePerKm = $deliveryCity->delivery_fee_per_km;
        $distanceFee = (int) round($distanceKm * $feePerKm);
        $rawFee = $baseFee + $distanceFee;

        $fee = $isPeak
            ? (int) round($rawFee * (1 + $deliveryCity->peak_hour_surcharge_percent / 100))
            : $rawFee;

        $prepTime = $restaurant->avg_prep_time_minutes ?? $restaurant->estimated_prep_time ?? 20;
        $transitMinutes = $this->geo->estimatedMinutes($distanceKm);
        $estimatedTotal = $prepTime + $transitMinutes;

        $zoneName = $this->detectZoneName($deliveryCity, $customerLat, $customerLng);

        return [
            'fee' => $fee,
            'distance_km' => round($distanceKm, 2),
            'estimated_minutes' => $estimatedTotal,
            'breakdown' => [
                'base_fee' => $baseFee,
                'distance_fee' => $distanceFee,
                'peak_surcharge' => $isPeak ? ($fee - $rawFee) : 0,
                'prep_minutes' => $prepTime,
                'transit_minutes' => $transitMinutes,
            ],
            'is_peak' => $isPeak,
            'within_range' => true,
            'city_name' => $deliveryCity->name,
            'zone_name' => $zoneName,
        ];
    }

    public function fee(Restaurant $restaurant, float $customerLat, float $customerLng): int
    {
        return $this->calculate($restaurant, $customerLat, $customerLng)['fee'];
    }

    public function isDeliverable(Restaurant $restaurant, float $lat, float $lng): bool
    {
        return $this->calculate($restaurant, $lat, $lng)['within_range'];
    }

    private function detectCityByName(?string $cityName): ?DeliveryCity
    {
        if (!$cityName) {
            return null;
        }

        return DeliveryCity::active()
            ->where(function ($q) use ($cityName) {
                $q->whereRaw('LOWER(name) = ?', [mb_strtolower($cityName)])
                  ->orWhereRaw('LOWER(slug) = ?', [mb_strtolower($cityName)]);
            })
            ->first();
    }

    private function detectZoneName(DeliveryCity $city, float $lat, float $lng): ?string
    {
        $zones = $city->zones()->active()->get();

        foreach ($zones as $zone) {
            if ($zone->containsPoint($lat, $lng)) {
                return $zone->name;
            }
        }

        return null;
    }

    private function outOfRange(Restaurant $restaurant, float $customerLat, float $customerLng): array
    {
        $restaurantLat = (float) $restaurant->latitude;
        $restaurantLng = (float) $restaurant->longitude;
        $hasValidCoords = $restaurantLat !== 0.0 || $restaurantLng !== 0.0;

        $distanceKm = $hasValidCoords
            ? $this->geo->distanceKm($restaurantLat, $restaurantLng, $customerLat, $customerLng)
            : 0.0;

        return [
            'fee' => 0,
            'distance_km' => round($distanceKm, 2),
            'estimated_minutes' => 0,
            'breakdown' => [],
            'is_peak' => false,
            'within_range' => false,
            'city_name' => null,
            'zone_name' => null,
        ];
    }
}
