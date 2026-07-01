<?php

namespace App\Http\Controllers\Api\V1\Client;

use App\Http\Controllers\Controller;
use App\Services\GeocodingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GeocodingController extends Controller
{
    public function __construct(private GeocodingService $geo) {}

    /**
     * Géocodage inversé — coordonnées → adresse lisible.
     * Utilisé par l'app quand le client partage sa position GPS.
     */
    public function reverse(Request $request): JsonResponse
    {
        $request->validate([
            'lat' => 'required|numeric|between:-90,90',
            'lng' => 'required|numeric|between:-180,180',
        ]);

        $result = $this->geo->reverseGeocode(
            (float) $request->lat,
            (float) $request->lng
        );

        return response()->json($result);
    }

    /**
     * Recherche d'adresse par texte — autocomplete.
     */
    public function search(Request $request): JsonResponse
    {
        $request->validate([
            'q'    => 'required|string|min:3|max:200',
            'city' => 'nullable|string|max:100',
        ]);

        $results = $this->geo->searchAddress(
            $request->q,
            $request->city ?? 'Abidjan'
        );

        return response()->json(['data' => $results]);
    }
}
