<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\DeliveryCity;
use App\Models\DeliveryZone;
use App\Models\Restaurant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class DeliveryCityController extends Controller
{
    public function index(): View
    {
        $cities = DeliveryCity::withCount(['zones' => fn($q) => $q->where('is_active', true)])
            ->orderBy('name')
            ->get();

        $restaurantCounts = Restaurant::where('delivery_enabled', true)
            ->whereNotNull('city')
            ->selectRaw('city, COUNT(*) as count')
            ->groupBy('city')
            ->pluck('count', 'city');

        return view('pages.super-admin.delivery-cities.index', compact('cities', 'restaurantCounts'));
    }

    public function show(DeliveryCity $city): View
    {
        $zones = DeliveryZone::where('delivery_city_id', $city->id)
            ->orderBy('sort_order')
            ->get();

        $restaurantCount = Restaurant::where('delivery_enabled', true)
            ->where('city', $city->name)
            ->count();

        return view('pages.super-admin.delivery-cities.show', compact('city', 'zones', 'restaurantCount'));
    }

    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'center_latitude' => 'required|numeric|between:-90,90',
            'center_longitude' => 'required|numeric|between:-180,180',
            'coverage_radius_km' => 'required|integer|min:1|max:100',
            'delivery_base_fee' => 'required|integer|min:0',
            'delivery_fee_per_km' => 'required|integer|min:0',
            'max_delivery_distance_km' => 'required|integer|min:1|max:50',
            'peak_hour_surcharge_percent' => 'nullable|integer|min:0|max:100',
            'min_order_amount' => 'nullable|integer|min:0',
        ]);

        $data['slug'] = Str::slug($data['name']);
        $data['country'] = 'CI';
        $data['is_active'] = $request->boolean('is_active', true);
        $data['peak_hour_surcharge_percent'] = $data['peak_hour_surcharge_percent'] ?? 20;
        $data['min_order_amount'] = $data['min_order_amount'] ?? 0;

        $city = DeliveryCity::create($data);

        if ($request->wantsJson()) {
            return response()->json(['message' => "Ville \"{$city->name}\" créée.", 'city' => $city]);
        }
        return back()->with('success', "Ville \"{$city->name}\" créée.");
    }

    public function update(Request $request, DeliveryCity $city): JsonResponse|RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'center_latitude' => 'required|numeric|between:-90,90',
            'center_longitude' => 'required|numeric|between:-180,180',
            'coverage_radius_km' => 'required|integer|min:1|max:100',
            'delivery_base_fee' => 'required|integer|min:0',
            'delivery_fee_per_km' => 'required|integer|min:0',
            'max_delivery_distance_km' => 'required|integer|min:1|max:50',
            'peak_hour_surcharge_percent' => 'nullable|integer|min:0|max:100',
            'min_order_amount' => 'nullable|integer|min:0',
        ]);

        $data['slug'] = Str::slug($data['name']);
        $data['is_active'] = $request->boolean('is_active', $city->is_active);

        $city->update($data);

        if ($request->wantsJson()) {
            return response()->json(['message' => "Ville \"{$city->name}\" mise à jour.", 'city' => $city->fresh()]);
        }
        return back()->with('success', "Ville \"{$city->name}\" mise à jour.");
    }

    public function toggle(DeliveryCity $city): JsonResponse|RedirectResponse
    {
        $city->update(['is_active' => !$city->is_active]);
        $msg = $city->is_active ? "Ville \"{$city->name}\" activée." : "Ville \"{$city->name}\" désactivée.";

        cache()->forget('delivery_cities:active');

        if (request()->wantsJson()) {
            return response()->json(['message' => $msg, 'is_active' => $city->is_active]);
        }
        return back()->with('success', $msg);
    }

    public function destroy(DeliveryCity $city): JsonResponse|RedirectResponse
    {
        $name = $city->name;
        $city->zones()->update(['delivery_city_id' => null]);
        $city->delete();

        cache()->forget('delivery_cities:active');

        if (request()->wantsJson()) {
            return response()->json(['message' => "Ville \"{$name}\" supprimée."]);
        }
        return back()->with('success', "Ville \"{$name}\" supprimée.");
    }

    public function storeZone(Request $request, DeliveryCity $city): JsonResponse|RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'center_latitude' => 'nullable|numeric|between:-90,90',
            'center_longitude' => 'nullable|numeric|between:-180,180',
            'radius_km' => 'required|integer|min:1|max:50',
        ]);

        $data['delivery_city_id'] = $city->id;
        $data['city'] = $city->name;
        $data['country'] = $city->country;
        $data['is_active'] = $request->boolean('is_active', true);

        $zone = DeliveryZone::create($data);

        if ($request->wantsJson()) {
            return response()->json(['message' => "Zone \"{$zone->name}\" ajoutée.", 'zone' => $zone]);
        }
        return back()->with('success', "Zone \"{$zone->name}\" ajoutée.");
    }

    public function toggleZone(DeliveryZone $zone): JsonResponse|RedirectResponse
    {
        $zone->update(['is_active' => !$zone->is_active]);
        $msg = $zone->is_active ? 'Zone activée.' : 'Zone désactivée.';

        if (request()->wantsJson()) {
            return response()->json(['message' => $msg, 'is_active' => $zone->is_active]);
        }
        return back()->with('success', $msg);
    }
}
