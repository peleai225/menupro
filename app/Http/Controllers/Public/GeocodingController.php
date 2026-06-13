<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeocodingController extends Controller
{
    /**
     * Search addresses using Geoapify (primary) with fallbacks
     */
    public function search(Request $request): JsonResponse
    {
        $query = $request->input('q', '');
        $userLat = $request->input('lat');
        $userLon = $request->input('lon');

        if (strlen($query) < 3) {
            return response()->json([]);
        }

        // Try Geoapify first (best quality, free plan: 3000 requests/day)
        $geoapifyKey = \App\Models\SystemSetting::get('geoapify_api_key', env('GEOAPIFY_API_KEY', ''));

        if (!empty($geoapifyKey)) {
            try {
                $url = 'https://api.geoapify.com/v1/geocode/autocomplete';
                $params = [
                    'text' => $query,
                    'apiKey' => $geoapifyKey,
                    'format' => 'json',
                    'lang' => 'fr',
                    'filter' => 'countrycode:ci'
                ];

                if ($userLat && $userLon) {
                    $params['bias'] = "proximity:{$userLon},{$userLat}";
                }
                
                $response = Http::timeout(10)->get($url, $params);
                
                if ($response->successful()) {
                    $data = $response->json();

                    if (isset($data['features']) && is_array($data['features']) && count($data['features']) > 0) {
                        $results = [];
                        
                        foreach ($data['features'] as $feature) {
                            // Documentation: Each feature has 'properties' and 'geometry'
                            $props = $feature['properties'] ?? [];
                            $geometry = $feature['geometry'] ?? [];
                            $coords = $geometry['coordinates'] ?? []; // [lon, lat] according to GeoJSON spec
                            
                            // Documentation fields: formatted, name, address_line1, address_line2, 
                            // city, street, housenumber, country, country_code, etc.
                            $results[] = [
                                'display_name' => $props['formatted'] ?? $props['name'] ?? $props['address_line1'] ?? '',
                                'lat' => isset($coords[1]) ? (float)$coords[1] : 0,  // lat is second coordinate
                                'lon' => isset($coords[0]) ? (float)$coords[0] : 0,  // lon is first coordinate
                                'address' => [
                                    'road' => $props['street'] ?? $props['address_line1'] ?? '',
                                    'house_number' => $props['housenumber'] ?? '',
                                    'city' => $props['city'] ?? $props['district'] ?? $props['state'] ?? '',
                                    'town' => $props['town'] ?? $props['city'] ?? '',
                                    'village' => $props['village'] ?? '',
                                    'municipality' => $props['municipality'] ?? '',
                                    'state' => $props['state'] ?? '',
                                    'country' => $props['country'] ?? 'Côte d\'Ivoire',
                                    'country_code' => $props['country_code'] ?? 'ci',
                                    'postcode' => $props['postcode'] ?? ''
                                ]
                            ];
                        }
                        
                        return response()->json($results);
                    }
                }
            } catch (\Exception $e) {
                Log::warning('[Geocoding] Geoapify failed: ' . $e->getMessage());
            }
        }
        
        // Fallback to Photon (free, no API key needed)
        try {
            $url = 'https://photon.komoot.io/api/';
            $params = [
                'q' => $query,
                'limit' => 8,
                'lang' => 'fr'
            ];

            if ($userLat && $userLon) {
                $params['lat'] = $userLat;
                $params['lon'] = $userLon;
            }

            $response = Http::timeout(10)->get($url, $params);

            if ($response->successful()) {
                $data = $response->json();

                if (isset($data['features']) && count($data['features']) > 0) {
                    $results = array_map(function ($feature) {
                        $props = $feature['properties'];
                        $coords = $feature['geometry']['coordinates'];
                        
                        return [
                            'display_name' => $this->formatDisplayName($props),
                            'lat' => $coords[1],
                            'lon' => $coords[0],
                            'address' => [
                                'road' => $props['street'] ?? $props['name'] ?? '',
                                'house_number' => $props['housenumber'] ?? '',
                                'city' => $props['city'] ?? $props['district'] ?? $props['state'] ?? '',
                                'town' => $props['town'] ?? $props['city'] ?? '',
                                'village' => $props['village'] ?? '',
                                'municipality' => $props['municipality'] ?? '',
                                'state' => $props['state'] ?? '',
                                'country' => $props['country'] ?? 'Côte d\'Ivoire'
                            ]
                        ];
                    }, $data['features']);
                    
                    return response()->json($results);
                }
            }
        } catch (\Exception $e) {
            Log::warning('[Geocoding] Photon failed: ' . $e->getMessage());
        }
        
        // Final fallback to Nominatim
        try {
            $url = 'https://nominatim.openstreetmap.org/search';
            $params = [
                'format' => 'json',
                'q' => $query,
                'countrycodes' => 'ci',
                'limit' => 8,
                'addressdetails' => 1
            ];

            if ($userLat && $userLon) {
                $lat = (float) $userLat;
                $lon = (float) $userLon;
                $params['viewbox'] = ($lon - 0.1) . ',' . ($lat + 0.1) . ',' . ($lon + 0.1) . ',' . ($lat - 0.1);
                $params['bounded'] = 0;
            }

            $response = Http::timeout(10)->withHeaders([
                'User-Agent' => 'MenuPro/1.0 (Food Delivery App)'
            ])->get($url, $params);

            if ($response->successful()) {
                $data = $response->json();
                if (is_array($data) && count($data) > 0) {
                    return response()->json($data);
                }
            }
        } catch (\Exception $e) {
            Log::warning('[Geocoding] Nominatim failed: ' . $e->getMessage());
        }

        return response()->json([]);
    }
    
    /**
     * Reverse geocode (coordinates to address)
     */
    public function reverse(Request $request): JsonResponse
    {
        $lat = $request->input('lat');
        $lng = $request->input('lon') ?? $request->input('lng');

        if (!$lat || !$lng) {
            return response()->json(['error' => 'Missing coordinates'], 400);
        }
        
        // Try Geoapify first
        $geoapifyKey = \App\Models\SystemSetting::get('geoapify_api_key', env('GEOAPIFY_API_KEY', ''));
        
        if (!empty($geoapifyKey)) {
            try {
                $response = Http::timeout(5)->get('https://api.geoapify.com/v1/geocode/reverse', [
                    'lat' => $lat,
                    'lon' => $lng,
                    'apiKey' => $geoapifyKey,
                    'lang' => 'fr'
                ]);
                
                if ($response->successful()) {
                    $data = $response->json();
                    
                    if (isset($data['features']) && count($data['features']) > 0) {
                        $props = $data['features'][0]['properties'];
                        $street = $props['street'] ?? $props['name'] ?? '';
                        $houseNumber = $props['housenumber'] ?? '';
                        $city = $props['city'] ?? $props['district'] ?? $props['state'] ?? 'Côte d\'Ivoire';
                        
                        return response()->json([
                            'address' => $houseNumber ? trim("$houseNumber $street") : $street,
                            'city' => $city
                        ]);
                    }
                }
            } catch (\Exception $e) {
                Log::warning('Geoapify reverse geocoding failed: ' . $e->getMessage());
            }
        }
        
        // Fallback to Photon
        try {
            $response = Http::timeout(5)->get('https://photon.komoot.io/reverse', [
                'lat' => $lat,
                'lon' => $lng,
                'lang' => 'fr'
            ]);
            
            if ($response->successful()) {
                $data = $response->json();
                
                if (isset($data['features']) && count($data['features']) > 0) {
                    $props = $data['features'][0]['properties'];
                    $street = $props['street'] ?? $props['name'] ?? '';
                    $houseNumber = $props['housenumber'] ?? '';
                    $city = $props['city'] ?? $props['district'] ?? $props['state'] ?? 'Côte d\'Ivoire';
                    
                    return response()->json([
                        'address' => $houseNumber ? trim("$houseNumber $street") : $street,
                        'city' => $city
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::warning('Photon reverse geocoding failed: ' . $e->getMessage());
        }
        
        // Final fallback to Nominatim
        try {
            $response = Http::timeout(5)->withHeaders([
                'User-Agent' => 'MenuPro/1.0 (Food Delivery App)'
            ])->get('https://nominatim.openstreetmap.org/reverse', [
                'format' => 'json',
                'lat' => $lat,
                'lon' => $lng,
                'zoom' => 18,
                'addressdetails' => 1
            ]);
            
            if ($response->successful()) {
                $data = $response->json();
                
                if (isset($data['address'])) {
                    $address = $data['address'];
                    $street = $address['road'] ?? $address['pedestrian'] ?? '';
                    $houseNumber = $address['house_number'] ?? '';
                    $city = $address['city'] ?? $address['town'] ?? $address['village'] ?? $address['municipality'] ?? $address['state'] ?? 'Côte d\'Ivoire';
                    
                    return response()->json([
                        'address' => $houseNumber ? trim("$houseNumber $street") : ($street ?: ($data['display_name'] ?? '')),
                        'city' => $city
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::warning('Nominatim reverse geocoding failed: ' . $e->getMessage());
        }
        
        return response()->json(['error' => 'Unable to geocode'], 500);
    }
    
    /**
     * Format display name from Photon properties
     */
    private function formatDisplayName(array $props): string
    {
        $parts = [];
        
        if (!empty($props['name'])) {
            $parts[] = $props['name'];
        }
        if (!empty($props['street']) && $props['street'] !== $props['name']) {
            $parts[] = $props['street'];
        }
        if (!empty($props['city'])) {
            $parts[] = $props['city'];
        }
        if (!empty($props['country'])) {
            $parts[] = $props['country'];
        }
        
        return implode(', ', $parts) ?: 'Adresse';
    }
}
