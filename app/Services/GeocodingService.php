<?php

namespace App\Services;

class GeocodingService
{
    private const EARTH_RADIUS_KM = 6371;

    /**
     * Calcule la distance en km entre deux points GPS (formule Haversine).
     */
    public function distanceKm(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;

        return self::EARTH_RADIUS_KM * 2 * asin(sqrt($a));
    }

    /**
     * Retourne la distance en mètres.
     */
    public function distanceMeters(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        return $this->distanceKm($lat1, $lng1, $lat2, $lng2) * 1000;
    }

    /**
     * Estime le temps de trajet en minutes (vitesse moto en ville : ~25 km/h).
     */
    public function estimatedMinutes(float $distanceKm, float $speedKmh = 25.0): int
    {
        return (int) ceil(($distanceKm / $speedKmh) * 60);
    }

    /**
     * Vérifie si un point est dans un rayon donné depuis un centre.
     */
    public function isWithinRadius(
        float $centerLat,
        float $centerLng,
        float $pointLat,
        float $pointLng,
        float $radiusKm
    ): bool {
        return $this->distanceKm($centerLat, $centerLng, $pointLat, $pointLng) <= $radiusKm;
    }

    /**
     * Trie un tableau de points par distance croissante depuis un point de référence.
     * Chaque élément doit avoir 'latitude' et 'longitude'.
     */
    public function sortByDistance(array $points, float $refLat, float $refLng): array
    {
        usort($points, function ($a, $b) use ($refLat, $refLng) {
            $distA = $this->distanceKm($refLat, $refLng, (float) $a['latitude'], (float) $a['longitude']);
            $distB = $this->distanceKm($refLat, $refLng, (float) $b['latitude'], (float) $b['longitude']);
            return $distA <=> $distB;
        });

        return $points;
    }
}
