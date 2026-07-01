<?php

namespace App\Services;

use App\Models\Restaurant;

class DeliveryPricingService
{
    // Tarification par défaut plateforme (en centimes XOF)
    private const DEFAULT_BASE_FEE     = 50000;  // 500 FCFA
    private const DEFAULT_FEE_PER_KM   = 15000;  // 150 FCFA/km
    private const DEFAULT_MAX_DISTANCE = 10;     // 10 km max

    // Supplément heure de pointe (+20%)
    private const PEAK_HOURS = [[11, 14], [18, 21]]; // midi et soir
    private const PEAK_MULTIPLIER = 1.20;

    public function __construct(private GeocodingService $geo) {}

    /**
     * Calcule les frais de livraison pour une commande.
     *
     * @return array{
     *   fee: int,
     *   distance_km: float,
     *   estimated_minutes: int,
     *   breakdown: array,
     *   is_peak: bool,
     *   within_range: bool
     * }
     */
    public function calculate(
        Restaurant $restaurant,
        float $customerLat,
        float $customerLng
    ): array {
        $restaurantLat = (float) $restaurant->latitude;
        $restaurantLng = (float) $restaurant->longitude;

        $distanceKm = $this->geo->distanceKm(
            $restaurantLat, $restaurantLng,
            $customerLat,   $customerLng
        );

        $maxDistance = $restaurant->max_delivery_distance_km ?? self::DEFAULT_MAX_DISTANCE;

        if ($distanceKm > $maxDistance) {
            return [
                'fee'               => 0,
                'distance_km'       => round($distanceKm, 2),
                'estimated_minutes' => 0,
                'breakdown'         => [],
                'is_peak'           => false,
                'within_range'      => false,
            ];
        }

        $baseFee   = $restaurant->delivery_base_fee   ?? self::DEFAULT_BASE_FEE;
        $feePerKm  = $restaurant->delivery_fee_per_km ?? self::DEFAULT_FEE_PER_KM;

        $distanceFee = (int) round($distanceKm * $feePerKm);
        $rawFee      = $baseFee + $distanceFee;

        $isPeak = $this->isPeakHour();
        $fee    = $isPeak ? (int) round($rawFee * self::PEAK_MULTIPLIER) : $rawFee;

        $prepTime      = $restaurant->avg_prep_time_minutes ?? 20;
        $transitMinutes = $this->geo->estimatedMinutes($distanceKm);
        $estimatedTotal = $prepTime + $transitMinutes;

        return [
            'fee'               => $fee,
            'distance_km'       => round($distanceKm, 2),
            'estimated_minutes' => $estimatedTotal,
            'breakdown'         => [
                'base_fee'     => $baseFee,
                'distance_fee' => $distanceFee,
                'peak_surcharge' => $isPeak ? ($fee - $rawFee) : 0,
                'prep_minutes'   => $prepTime,
                'transit_minutes' => $transitMinutes,
            ],
            'is_peak'      => $isPeak,
            'within_range' => true,
        ];
    }

    /**
     * Version légère : retourne uniquement les frais (int en centimes).
     */
    public function fee(Restaurant $restaurant, float $customerLat, float $customerLng): int
    {
        return $this->calculate($restaurant, $customerLat, $customerLng)['fee'];
    }

    /**
     * Vérifie si une adresse est livrable par un restaurant.
     */
    public function isDeliverable(Restaurant $restaurant, float $lat, float $lng): bool
    {
        return $this->calculate($restaurant, $lat, $lng)['within_range'];
    }

    private function isPeakHour(): bool
    {
        $hour = (int) now()->format('G');
        foreach (self::PEAK_HOURS as [$start, $end]) {
            if ($hour >= $start && $hour < $end) {
                return true;
            }
        }
        return false;
    }
}
