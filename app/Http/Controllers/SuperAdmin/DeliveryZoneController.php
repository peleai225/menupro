<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\DeliveryZone;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class DeliveryZoneController extends Controller
{
    public function index(): View
    {
        $zones = DeliveryZone::orderBy('city')->orderBy('sort_order')->get();
        return view('pages.super-admin.delivery-zones.index', compact('zones'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'             => 'required|string|max:100',
            'city'             => 'required|string|max:100',
            'radius_km'        => 'required|integer|min:1|max:50',
            'center_latitude'  => 'nullable|numeric|between:-90,90',
            'center_longitude' => 'nullable|numeric|between:-180,180',
            'is_active'        => 'boolean',
        ]);
        $data['is_active'] = $request->boolean('is_active', true);

        DeliveryZone::create($data);
        return back()->with('success', "Zone \"{$data['name']}\" créée.");
    }

    public function update(Request $request, DeliveryZone $zone): RedirectResponse
    {
        $data = $request->validate([
            'name'             => 'required|string|max:100',
            'city'             => 'required|string|max:100',
            'radius_km'        => 'required|integer|min:1|max:50',
            'center_latitude'  => 'nullable|numeric|between:-90,90',
            'center_longitude' => 'nullable|numeric|between:-180,180',
            'is_active'        => 'boolean',
        ]);
        $data['is_active'] = $request->boolean('is_active', true);

        $zone->update($data);
        return back()->with('success', "Zone \"{$zone->name}\" mise à jour.");
    }

    public function destroy(DeliveryZone $zone): RedirectResponse
    {
        $name = $zone->name;
        $zone->delete();
        return back()->with('success', "Zone \"{$name}\" supprimée.");
    }

    public function toggle(DeliveryZone $zone): RedirectResponse
    {
        $zone->update(['is_active' => !$zone->is_active]);
        return back()->with('success', $zone->is_active ? "Zone activée." : "Zone désactivée.");
    }
}
