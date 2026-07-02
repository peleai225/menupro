<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Restaurant;
use App\Services\GeocodingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PlatformRestaurantController extends Controller
{
    public function __construct(private GeocodingService $geo) {}

    /**
     * Liste des restaurants MenuPro — activer/désactiver sur la plateforme.
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'on_platform' => 'nullable|boolean',
            'city'        => 'nullable|string|max:100',
        ]);

        $query = Restaurant::withoutGlobalScopes()
            ->where('status', 'active')
            ->with('activeSubscription')
            ->latest();

        if ($request->has('on_platform')) {
            $query->where('is_on_platform', $request->boolean('on_platform'));
        }

        if ($request->filled('city')) {
            $query->where('city', $request->city);
        }

        $restaurants = $query->paginate(30);

        return response()->json([
            'data' => $restaurants->map(fn($r) => $this->formatRestaurant($r)),
            'meta' => [
                'current_page'  => $restaurants->currentPage(),
                'last_page'     => $restaurants->lastPage(),
                'total'         => $restaurants->total(),
                'on_platform'   => Restaurant::where('is_on_platform', true)->count(),
                'off_platform'  => Restaurant::where('is_on_platform', false)->count(),
            ],
        ]);
    }

    /**
     * Activer un restaurant sur la plateforme de livraison.
     */
    public function enable(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'platform_category'        => 'required|string|max:50',
            'platform_commission_rate' => 'nullable|numeric|min:0|max:30',
        ]);

        $restaurant = Restaurant::withoutGlobalScopes()->findOrFail($id);

        if (empty($restaurant->latitude) || empty($restaurant->longitude)) {
            return response()->json([
                'message' => 'Le restaurant n\'a pas de coordonnées GPS. Configurez-les d\'abord.',
            ], 422);
        }

        $restaurant->update([
            'is_on_platform'           => true,
            'platform_category'        => $data['platform_category'],
            'platform_commission_rate' => $data['platform_commission_rate'] ?? 12.00,
        ]);

        return response()->json([
            'message'    => "{$restaurant->name} est maintenant sur la plateforme.",
            'restaurant' => $this->formatRestaurant($restaurant->fresh()),
        ]);
    }

    /**
     * Retirer un restaurant de la plateforme.
     */
    public function disable(int $id): JsonResponse
    {
        $restaurant = Restaurant::withoutGlobalScopes()->findOrFail($id);
        $restaurant->update(['is_on_platform' => false]);

        return response()->json(['message' => "{$restaurant->name} retiré de la plateforme."]);
    }

    /**
     * Mettre à jour le taux de commission d'un restaurant.
     */
    public function updateCommission(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'platform_commission_rate' => 'required|numeric|min:0|max:30',
        ]);

        $restaurant = Restaurant::withoutGlobalScopes()->findOrFail($id);
        $restaurant->update($data);

        return response()->json([
            'message' => "Commission mise à jour : {$data['platform_commission_rate']}%",
        ]);
    }

    private function formatRestaurant(Restaurant $r): array
    {
        $city = ($r->latitude && $r->longitude)
            ? $this->geo->detectDeliveryCity((float) $r->latitude, (float) $r->longitude)
            : null;

        return [
            'id'                       => $r->id,
            'name'                     => $r->name,
            'city'                     => $r->city,
            'status'                   => $r->status,
            'is_on_platform'           => $r->is_on_platform,
            'platform_category'        => $r->platform_category,
            'platform_commission_rate' => $r->platform_commission_rate,
            'delivery_city_name'       => $city?->name,
            'delivery_base_fee'        => $city?->delivery_base_fee,
            'delivery_fee_per_km'      => $city?->delivery_fee_per_km,
            'max_delivery_distance_km' => $city?->max_delivery_distance_km,
            'has_gps'                  => !empty($r->latitude) && !empty($r->longitude),
            'latitude'                 => $r->latitude,
            'longitude'                => $r->longitude,
            'subscription_plan'        => $r->activeSubscription?->plan?->name,
        ];
    }
}
