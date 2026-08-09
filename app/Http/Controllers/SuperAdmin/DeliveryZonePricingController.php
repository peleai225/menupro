<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\DeliveryCity;
use App\Models\DeliveryZone;
use App\Models\DeliveryZonePrice;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DeliveryZonePricingController extends Controller
{
    public function index(DeliveryCity $city): View
    {
        $zones = DeliveryZone::where('delivery_city_id', $city->id)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        // Charge tous les prix existants pour cette ville, indexés par from_zone_id → to_zone_id (ou 'fallback')
        $fromIds = $zones->pluck('id')->all();
        $existingPrices = DeliveryZonePrice::whereIn('from_zone_id', $fromIds)
            ->get()
            ->groupBy(fn($p) => $p->from_zone_id . '_' . ($p->to_zone_id ?? 'fallback'));

        return view('pages.super-admin.delivery-zone-pricing', compact('city', 'zones', 'existingPrices'));
    }

    public function store(Request $request, DeliveryCity $city): RedirectResponse
    {
        $prices = $request->input('prices', []);

        // Load valid zones for ownership validation
        $zones = DeliveryZone::where('delivery_city_id', $city->id)
            ->where('is_active', true)
            ->pluck('id');

        foreach ($prices as $fromId => $toMap) {
            // Validate zone ownership
            if (!$zones->contains((int) $fromId)) {
                continue;
            }

            foreach ($toMap as $toId => $price) {
                $toZoneId = ($toId === 'fallback') ? null : (int) $toId;

                // Validate to_zone ownership
                if ($toZoneId !== null && !$zones->contains($toZoneId)) {
                    continue;
                }

                $priceInt = (int) $price;

                if ($priceInt <= 0) {
                    // Supprimer l'entrée existante si elle existe (permet de revenir au calcul km)
                    DeliveryZonePrice::where('from_zone_id', (int) $fromId)
                        ->where(function ($q) use ($toZoneId) {
                            $toZoneId === null
                                ? $q->whereNull('to_zone_id')
                                : $q->where('to_zone_id', $toZoneId);
                        })
                        ->delete();
                    continue;
                }

                DeliveryZonePrice::updateOrCreate(
                    [
                        'from_zone_id' => (int) $fromId,
                        'to_zone_id'   => $toZoneId,
                    ],
                    [
                        'price_xof' => $priceInt,
                        'is_active' => true,
                    ]
                );
            }
        }

        return redirect()
            ->route('super-admin.delivery.zone-pricing.index', $city)
            ->with('success', 'Tarifs par quartiers mis à jour.');
    }
}
