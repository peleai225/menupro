<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\DeliveryZone;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class DeliveryZoneController extends Controller
{
    public function index(): View
    {
        $zones = DeliveryZone::orderBy('city')->orderBy('sort_order')->get();
        return view('pages.super-admin.delivery-zones.index', compact('zones'));
    }

    public function store(Request $request): JsonResponse|RedirectResponse
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

        $zone = DeliveryZone::create($data);

        if ($request->wantsJson()) {
            return response()->json(['message' => "Zone \"{$data['name']}\" créée.", 'zone' => $zone]);
        }
        return back()->with('success', "Zone \"{$data['name']}\" créée.");
    }

    public function update(Request $request, DeliveryZone $zone): JsonResponse|RedirectResponse
    {
        $data = $request->validate([
            'name'             => 'required|string|max:100',
            'city'             => 'required|string|max:100',
            'radius_km'        => 'required|integer|min:1|max:50',
            'center_latitude'  => 'nullable|numeric|between:-90,90',
            'center_longitude' => 'nullable|numeric|between:-180,180',
            'is_active'        => 'boolean',
        ]);
        $data['is_active'] = $request->boolean('is_active', $zone->is_active);

        $zone->update($data);

        if ($request->wantsJson()) {
            return response()->json(['message' => "Zone \"{$zone->name}\" mise à jour.", 'zone' => $zone->fresh()]);
        }
        return back()->with('success', "Zone \"{$zone->name}\" mise à jour.");
    }

    public function destroy(DeliveryZone $zone): JsonResponse|RedirectResponse
    {
        $name = $zone->name;
        $zone->delete();

        if (request()->wantsJson()) {
            return response()->json(['message' => "Zone \"{$name}\" supprimée."]);
        }
        return back()->with('success', "Zone \"{$name}\" supprimée.");
    }

    public function toggle(DeliveryZone $zone): JsonResponse|RedirectResponse
    {
        $zone->update(['is_active' => !$zone->is_active]);
        $msg = $zone->is_active ? 'Zone activée.' : 'Zone désactivée.';

        if (request()->wantsJson()) {
            return response()->json(['message' => $msg, 'is_active' => $zone->is_active]);
        }
        return back()->with('success', $msg);
    }
}
