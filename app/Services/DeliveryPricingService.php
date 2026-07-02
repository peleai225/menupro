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
        $deliveryCity = $this->geo->detectDeliveryCity($customerLat, $customerLng);

        if (!$deliveryCity) {
            return $this->outOfRange($restaurant, $customerLat, $customerLng);
        }

        $restaurantLat = (float) $restaurant->latitude;
        $restaurantLng = (float) $restaurant->longitude;

        $distanceKm = $this->geo->distanceKm(
            $restaurantLat, $restaurantLng,
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

        $distanceKm = $this->geo->distanceKm(
            $restaurantLat, $restaurantLng,
            $customerLat, $customerLng
        );

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
