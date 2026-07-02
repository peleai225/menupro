<?php

namespace App\Services;

use App\Models\DeliveryCity;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class GeocodingService
{
    private const EARTH_RADIUS_KM = 6371;
    private const NOMINATIM_URL   = 'https://nominatim.openstreetmap.org';

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
     * Géocodage inversé — convertit des coordonnées GPS en adresse lisible.
     * Utilise Nominatim (OpenStreetMap) — gratuit, sans clé API.
     * Résultat mis en cache 24h pour éviter les requêtes répétées.
     *
     * @return array{ address: string, road: string, neighbourhood: string, city: string, country: string }
     */
    public function reverseGeocode(float $lat, float $lng): array
    {
        $cacheKey = 'geocode:' . round($lat, 4) . ':' . round($lng, 4);

        return Cache::remember($cacheKey, 86400, function () use ($lat, $lng) {
            try {
                $response = Http::withHeaders([
                    'User-Agent' => 'MenuPro/1.0 (menupro.ci)',
                    'Accept-Language' => 'fr',
                ])->timeout(5)->get(self::NOMINATIM_URL . '/reverse', [
                    'lat'    => $lat,
                    'lon'    => $lng,
                    'format' => 'json',
                    'addressdetails' => 1,
                    'zoom'   => 18,
                ]);

                if (!$response->ok()) {
                    return $this->fallbackAddress($lat, $lng);
                }

                $data    = $response->json();
                $address = $data['address'] ?? [];

                $road         = $address['road'] ?? $address['pedestrian'] ?? $address['path'] ?? '';
                $neighbourhood = $address['neighbourhood'] ?? $address['suburb'] ?? $address['quarter'] ?? '';
                $city         = $address['city'] ?? $address['town'] ?? $address['village'] ?? $address['county'] ?? 'Abidjan';
                $country      = $address['country'] ?? 'Côte d\'Ivoire';

                // Construire une adresse lisible
                $parts = array_filter([$road, $neighbourhood, $city]);
                $readable = implode(', ', $parts) ?: $data['display_name'] ?? "Lat: $lat, Lng: $lng";

                return [
                    'address'       => $readable,
                    'road'          => $road,
                    'neighbourhood' => $neighbourhood,
                    'city'          => $city,
                    'country'       => $country,
                    'display_name'  => $data['display_name'] ?? $readable,
                ];
            } catch (\Throwable) {
                return $this->fallbackAddress($lat, $lng);
            }
        });
    }

    /**
     * Recherche d'adresse par texte (autocomplete).
     * Limité à la Côte d'Ivoire.
     */
    public function searchAddress(string $query, string $city = 'Abidjan'): array
    {
        if (strlen(trim($query)) < 3) {
            return [];
        }

        $cacheKey = 'geocode:search:' . md5($query . $city);

        return Cache::remember($cacheKey, 3600, function () use ($query, $city) {
            try {
                $response = Http::withHeaders([
                    'User-Agent'      => 'MenuPro/1.0 (menupro.ci)',
                    'Accept-Language' => 'fr',
                ])->timeout(5)->get(self::NOMINATIM_URL . '/search', [
                    'q'              => $query . ', ' . $city . ', Côte d\'Ivoire',
                    'format'         => 'json',
                    'addressdetails' => 1,
                    'limit'          => 5,
                    'countrycodes'   => 'ci',
                ]);

                if (!$response->ok()) {
                    return [];
                }

                return collect($response->json())->map(fn($r) => [
                    'display_name' => $r['display_name'],
                    'lat'          => (float) $r['lat'],
                    'lng'          => (float) $r['lon'],
                    'address'      => ($r['address']['road'] ?? '') . ', ' . ($r['address']['suburb'] ?? $r['address']['city'] ?? ''),
                    'city'         => $r['address']['city'] ?? $r['address']['town'] ?? $city,
                ])->values()->all();
            } catch (\Throwable) {
                return [];
            }
        });
    }

    /**
     * Détecte la ville de livraison active la plus proche des coordonnées.
     */
    public function detectDeliveryCity(float $lat, float $lng): ?DeliveryCity
    {
        $cities = Cache::remember('delivery_cities:active', 3600, function () {
            return DeliveryCity::active()->get();
        });

        $closest = null;
        $closestDistance = PHP_FLOAT_MAX;

        foreach ($cities as $city) {
            $distance = $this->distanceKm(
                (float) $city->center_latitude,
                (float) $city->center_longitude,
                $lat,
                $lng
            );

            if ($distance <= $city->coverage_radius_km && $distance < $closestDistance) {
                $closest = $city;
                $closestDistance = $distance;
            }
        }

        return $closest;
    }

    private function fallbackAddress(float $lat, float $lng): array
    {
        return [
            'address'       => "Position GPS ($lat, $lng)",
            'road'          => '',
            'neighbourhood' => '',
            'city'          => 'Abidjan',
            'country'       => 'Côte d\'Ivoire',
            'display_name'  => "Position GPS ($lat, $lng)",
        ];
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
