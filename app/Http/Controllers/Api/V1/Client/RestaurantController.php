<?php

namespace App\Http\Controllers\Api\V1\Client;

use App\Http\Controllers\Controller;
use App\Models\Restaurant;
use App\Services\DeliveryPricingService;
use App\Services\GeocodingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RestaurantController extends Controller
{
    public function __construct(
        private GeocodingService $geo,
        private DeliveryPricingService $pricing,
    ) {}

    /**
     * Liste des restaurants sur la plateforme.
     * Filtres : city, category, open_now, lat+lng (tri par distance)
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'city'     => 'nullable|string|max:100',
            'category' => 'nullable|string|max:50',
            'lat'      => 'nullable|numeric|between:-90,90',
            'lng'      => 'nullable|numeric|between:-180,180',
            'open_now' => 'nullable|boolean',
        ]);

        $query = Restaurant::where('status', 'active')
            ->where('is_on_platform', true)
            ->with('activeSubscription');

        if ($request->filled('city')) {
            $query->where('city', $request->city);
        }

        if ($request->filled('category')) {
            $query->where('platform_category', $request->category);
        }

        $restaurants = $query->get()->map(function (Restaurant $r) use ($request) {
            $item = $this->formatRestaurant($r);

            if ($request->filled('lat') && $request->filled('lng')) {
                $item['distance_km'] = round(
                    $this->geo->distanceKm(
                        (float) $request->lat,
                        (float) $request->lng,
                        (float) $r->latitude,
                        (float) $r->longitude
                    ),
                    1
                );
            }

            return $item;
        });

        // Filtre open_now après mapping (isOpenNow() utilise les horaires du restaurant)
        if ($request->boolean('open_now')) {
            $restaurants = $restaurants->filter(fn($r) => $r['is_open']);
        }

        // Trier par distance si coordonnées fournies
        if ($request->filled('lat')) {
            $restaurants = $restaurants->sortBy('distance_km')->values();
        }

        return response()->json(['data' => $restaurants]);
    }

    /**
     * Catégories plateforme actives avec nombre d'établissements.
     */
    public function categories(): JsonResponse
    {
        $labels = [
            'restaurant' => 'Restaurant',
            'ivoirien'   => 'Cuisine ivoirienne',
            'africain'   => 'Cuisine africaine',
            'fastfood'   => 'Fast food',
            'burger'     => 'Burger',
            'pizza'      => 'Pizza',
            'poulet'     => 'Poulet / Grillades',
            'poisson'    => 'Poisson / Fruits de mer',
            'maquis'     => 'Maquis / Bar',
            'traiteur'   => 'Traiteur',
            'cafe'       => 'Café / Snack',
            'patisserie' => 'Pâtisserie / Boulangerie',
            'sain'       => 'Cuisine saine / Végé',
            'asiatique'  => 'Asiatique',
            'stand'      => 'Stand / Kiosque',
        ];

        $rows = Restaurant::where('is_on_platform', true)
            ->where('status', 'active')
            ->whereNotNull('platform_category')
            ->selectRaw('platform_category, COUNT(*) as count')
            ->groupBy('platform_category')
            ->orderByDesc('count')
            ->get();

        $data = $rows->map(fn($row) => [
            'key'   => $row->platform_category,
            'label' => $labels[$row->platform_category] ?? ucfirst($row->platform_category),
            'count' => (int) $row->count,
        ]);

        return response()->json(['data' => $data]);
    }

    /**
     * Détail d'un restaurant.
     */
    public function show(int $id): JsonResponse
    {
        $restaurant = Restaurant::where('is_on_platform', true)
            ->where('status', 'active')
            ->findOrFail($id);

        return response()->json($this->formatRestaurant($restaurant, detailed: true));
    }

    /**
     * Menu complet d'un restaurant (catégories + plats).
     */
    public function menu(int $id): JsonResponse
    {
        $restaurant = Restaurant::where('is_on_platform', true)
            ->where('status', 'active')
            ->findOrFail($id);

        $categories = $restaurant->categories()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->with(['dishes' => function ($q) {
                $q->where('is_active', true)->orderBy('sort_order');
            }])
            ->get()
            ->map(fn($cat) => [
                'id'     => $cat->id,
                'name'   => $cat->name,
                'dishes' => $cat->dishes->map(fn($d) => $this->formatDish($d)),
            ]);

        return response()->json([
            'restaurant_id' => $restaurant->id,
            'currency'      => $restaurant->currency ?? 'XOF',
            'categories'    => $categories,
        ]);
    }

    /**
     * Restaurants proches + estimation frais livraison.
     */
    public function nearby(Request $request): JsonResponse
    {
        $request->validate([
            'lat'       => 'required|numeric|between:-90,90',
            'lng'       => 'required|numeric|between:-180,180',
            'radius_km' => 'nullable|integer|min:1|max:20',
        ]);

        $lat    = (float) $request->lat;
        $lng    = (float) $request->lng;
        $radius = (int) ($request->radius_km ?? 10);

        $restaurants = Restaurant::where('is_on_platform', true)
            ->where('status', 'active')
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get()
            ->map(function (Restaurant $r) use ($lat, $lng) {
                $distKm = $this->geo->distanceKm($lat, $lng, (float) $r->latitude, (float) $r->longitude);
                $pricing = $this->pricing->calculate($r, $lat, $lng);

                return array_merge($this->formatRestaurant($r), [
                    'distance_km'       => round($distKm, 1),
                    'delivery_fee'      => $pricing['fee'],
                    'estimated_minutes' => $pricing['estimated_minutes'],
                    'within_range'      => $pricing['within_range'],
                ]);
            })
            ->filter(fn($r) => $r['distance_km'] <= $radius && $r['within_range'])
            ->sortBy('distance_km')
            ->values();

        return response()->json(['data' => $restaurants]);
    }

    /**
     * Estime les frais de livraison avant de commander.
     */
    public function estimateDelivery(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'lat' => 'required|numeric|between:-90,90',
            'lng' => 'required|numeric|between:-180,180',
        ]);

        $restaurant = Restaurant::where('is_on_platform', true)->findOrFail($id);
        $estimate   = $this->pricing->calculate($restaurant, (float) $request->lat, (float) $request->lng);

        if (!$estimate['within_range']) {
            return response()->json([
                'deliverable'  => false,
                'message'      => 'Ce restaurant ne livre pas à cette adresse.',
                'distance_km'  => $estimate['distance_km'],
                'city_covered' => $estimate['city_name'] !== null,
            ], 422);
        }

        return response()->json([
            'deliverable'       => true,
            'delivery_fee'      => $estimate['fee'],
            'distance_km'       => $estimate['distance_km'],
            'estimated_minutes' => $estimate['estimated_minutes'],
            'is_peak_hour'      => $estimate['is_peak'],
            'breakdown'         => $estimate['breakdown'],
        ]);
    }

    private function formatRestaurant(Restaurant $r, bool $detailed = false): array
    {
        $data = [
            'id'               => $r->id,
            'name'             => $r->name,
            'slug'             => $r->slug,
            'category'         => $r->platform_category,
            'city'             => $r->city,
            'address'          => $r->address,
            'phone'            => $r->phone,
            'logo_url'         => $r->logo_path ? asset('storage/' . $r->logo_path) : null,
            'banner_url'       => $r->banner_path ? asset('storage/' . $r->banner_path) : null,
            'is_open'              => $r->isOpenNow(),
            'min_order_amount'     => $r->min_order_amount ?? 0,
            'avg_prep_time'        => $r->avg_prep_time_minutes ?? 20,
            'cash_on_delivery'     => (bool) $r->cash_on_delivery,
            'payment_methods'      => array_values(array_filter([
                'wave',
                $r->cash_on_delivery ? 'cash_on_delivery' : null,
            ])),
            'latitude'             => $r->latitude,
            'longitude'            => $r->longitude,
        ];

        if ($detailed) {
            $data['description']   = $r->description;
            $data['tagline']       = $r->tagline;
            $data['opening_hours'] = $r->opening_hours;

            $city = ($r->latitude && $r->longitude)
                ? $this->geo->detectDeliveryCity((float) $r->latitude, (float) $r->longitude)
                : null;

            $data['delivery_base_fee']   = $city?->delivery_base_fee ?? 50000;
            $data['delivery_fee_per_km'] = $city?->delivery_fee_per_km ?? 15000;
            $data['max_delivery_km']     = $city?->max_delivery_distance_km ?? 10;
            $data['delivery_city_name']  = $city?->name;
        }

        return $data;
    }

    private function formatDish($dish): array
    {
        return [
            'id'          => $dish->id,
            'name'        => $dish->name,
            'description' => $dish->description,
            'price'       => $dish->price,
            'compare_price' => $dish->compare_price,
            'image_url'   => $dish->image_path ? asset('storage/' . $dish->image_path) : null,
            'is_available' => $dish->hasStock(),
            'is_featured'  => $dish->is_featured,
            'is_spicy'     => $dish->is_spicy,
            'is_vegetarian' => $dish->is_vegetarian,
            'prep_time'    => $dish->prep_time,
            'calories'     => $dish->calories,
        ];
    }
}
