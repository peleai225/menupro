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
        
        Log::info('[Geocoding] Search request received', ['query' => $query]);
        
        if (strlen($query) < 3) {
            Log::info('[Geocoding] Query too short', ['length' => strlen($query)]);
            return response()->json([]);
        }
        
        // Try Geoapify first (best quality, free plan: 3000 requests/day)
        $geoapifyKey = \App\Models\SystemSetting::get('geoapify_api_key', env('GEOAPIFY_API_KEY', ''));
        
        Log::info('[Geocoding] Geoapify key status', ['has_key' => !empty($geoapifyKey), 'key_length' => strlen($geoapifyKey ?? '')]);
        
        if (!empty($geoapifyKey)) {
            try {
                // According to official documentation: https://apidocs.geoapify.com/docs/geocoding/address-autocomplete/
                // URL: https://api.geoapify.com/v1/geocode/autocomplete?REQUEST_PARAMS
                // Required: apiKey, text
                // Optional: type, lang, filter, bias, format (json/xml/geojson - default is geojson)
                $url = 'https://api.geoapify.com/v1/geocode/autocomplete';
                $params = [
                    'text' => $query,
                    'apiKey' => $geoapifyKey,
                    'format' => 'json',  // Explicit JSON format (default is GeoJSON)
                    'lang' => 'fr',        // Optional: Result language
                    'filter' => 'countrycode:ci'  // Optional: Filter to Côte d'Ivoire
                ];
                
                // Build URL exactly like documentation example
                $fullUrl = $url . '?' . http_build_query($params);
                Log::info('[Geocoding] Calling Geoapify (official documentation format)', [
                    'url' => $fullUrl,
                    'text' => $query,
                    'params' => array_merge($params, ['apiKey' => '***']) // Hide API key in logs
                ]);
                
                $response = Http::timeout(10)->get($url, $params);
                
                Log::info('[Geocoding] Geoapify response', [
                    'status' => $response->status(),
                    'successful' => $response->successful()
                ]);
                
                if ($response->successful()) {
                    $data = $response->json();
                    
                    Log::info('[Geocoding] Geoapify response parsed', [
                        'type' => $data['type'] ?? 'unknown',
                        'has_features' => isset($data['features']),
                        'features_count' => isset($data['features']) ? count($data['features']) : 0,
                        'raw_data_preview' => json_encode(array_slice($data, 0, 1))
                    ]);
                    
                    // According to documentation: Response is FeatureCollection (GeoJSON format)
                    // Even with format=json, structure is still GeoJSON FeatureCollection
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
                        
                        Log::info('[Geocoding] Returning Geoapify results', ['count' => count($results)]);
                        return response()->json($results);
                    } else {
                        Log::warning('[Geocoding] Geoapify returned no features', [
                            'data_keys' => array_keys($data ?? []),
                            'data_type' => gettype($data ?? null),
                            'full_response' => json_encode($data)
                        ]);
                    }
                } else {
                    Log::error('[Geocoding] Geoapify request failed', [
                        'status' => $response->status(),
                        'body' => substr($response->body(), 0, 500)
                    ]);
                }
            } catch (\Exception $e) {
                Log::error('[Geocoding] Geoapify exception', [
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ]);
            }
        } else {
            Log::info('[Geocoding] No Geoapify key, skipping to fallback');
        }
        
        // Fallback to Photon (free, no API key needed)
        Log::info('[Geocoding] Trying Photon fallback');
        try {
            $url = 'https://photon.komoot.io/api/';
            $params = [
                'q' => $query,
                'limit' => 8,
                'lang' => 'fr'
            ];
            
            Log::info('[Geocoding] Calling Photon', ['url' => $url, 'params' => $params]);
            
            $response = Http::timeout(10)->get($url, $params);
            
            Log::info('[Geocoding] Photon response', [
                'status' => $response->status(),
                'successful' => $response->successful()
            ]);
            
            if ($response->successful()) {
                $data = $response->json();
                
                Log::info('[Geocoding] Photon JSON parsed', [
                    'has_features' => isset($data['features']),
                    'features_count' => isset($data['features']) ? count($data['features']) : 0
                ]);
                
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
                    
                    Log::info('[Geocoding] Returning Photon results', ['count' => count($results)]);
                    return response()->json($results);
                } else {
                    Log::warning('[Geocoding] Photon returned no features');
                }
            } else {
                Log::error('[Geocoding] Photon request failed', [
                    'status' => $response->status(),
                    'body' => substr($response->body(), 0, 200)
                ]);
            }
        } catch (\Exception $e) {
            Log::error('[Geocoding] Photon exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
        
        // Final fallback to Nominatim
        Log::info('[Geocoding] Trying Nominatim fallback');
        try {
            $url = 'https://nominatim.openstreetmap.org/search';
            $params = [
                'format' => 'json',
                'q' => $query,
                'countrycodes' => 'ci',
                'limit' => 8,
                'addressdetails' => 1
            ];
            
            Log::info('[Geocoding] Calling Nominatim', ['url' => $url, 'params' => $params]);
            
            $response = Http::timeout(10)->withHeaders([
                'User-Agent' => 'MenuPro/1.0 (Food Delivery App)'
            ])->get($url, $params);
            
            Log::info('[Geocoding] Nominatim response', [
                'status' => $response->status(),
                'successful' => $response->successful()
            ]);
            
            if ($response->successful()) {
                $data = $response->json();
                if (is_array($data) && count($data) > 0) {
                    Log::info('[Geocoding] Returning Nominatim results', ['count' => count($data)]);
                    return response()->json($data);
                } else {
                    Log::warning('[Geocoding] Nominatim returned empty array');
                }
            } else {
                Log::error('[Geocoding] Nominatim request failed', [
                    'status' => $response->status(),
                    'body' => substr($response->body(), 0, 200)
                ]);
            }
        } catch (\Exception $e) {
            Log::error('[Geocoding] Nominatim exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
        
        Log::warning('[Geocoding] All geocoding services failed, returning empty array');
        return response()->json([]);
    }
    
    /**
     * Reverse geocode (coordinates to address)
     */
    public function reverse(Request $request): JsonResponse
    {
        $lat = $request->input('lat');
        $lng = $request->input('lng');
        
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
