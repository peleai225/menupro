<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\PromoBanner;
use App\Models\Restaurant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class PromoBannerController extends Controller
{
    public function index(): View
    {
        $banners = PromoBanner::with(['restaurant', 'creator'])
            ->orderBy('sort_order')
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('pages.super-admin.promo-banners', compact('banners'));
    }

    public function create(): View
    {
        $restaurants = Restaurant::orderBy('name')->get(['id', 'name']);

        return view('pages.super-admin.promo-banner-create', compact('restaurants'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'restaurant_id' => 'nullable|exists:restaurants,id',
            'title'         => 'nullable|string|max:100',
            'subtitle'      => 'nullable|string|max:150',
            'image'         => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
            'link_type'     => 'required|in:none,dish,promo_code,url',
            'link_value'    => 'nullable|string|max:500',
            'cta_label'     => 'nullable|string|max:100',
            'is_active'     => 'boolean',
            'starts_at'     => 'nullable|date',
            'ends_at'       => 'nullable|date|after_or_equal:starts_at',
            'sort_order'    => 'nullable|integer|min:0|max:9999',
        ]);

        $validated['is_active']  = $request->boolean('is_active', true);
        $validated['sort_order'] = $request->input('sort_order', 0);
        $validated['created_by'] = auth()->id();
        $validated['image_path'] = $request->file('image')->store('banners', 'public');

        unset($validated['image']);

        PromoBanner::create($validated);

        return redirect()->route('super-admin.promo-banners.index')
            ->with('success', 'Bannière créée avec succès.');
    }

    public function edit(PromoBanner $promoBanner): View
    {
        $restaurants = Restaurant::orderBy('name')->get(['id', 'name']);

        return view('pages.super-admin.promo-banner-edit', [
            'banner'      => $promoBanner,
            'restaurants' => $restaurants,
        ]);
    }

    public function update(Request $request, PromoBanner $promoBanner): RedirectResponse
    {
        $validated = $request->validate([
            'restaurant_id' => 'nullable|exists:restaurants,id',
            'title'         => 'nullable|string|max:100',
            'subtitle'      => 'nullable|string|max:150',
            'image'         => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'link_type'     => 'required|in:none,dish,promo_code,url',
            'link_value'    => 'nullable|string|max:500',
            'cta_label'     => 'nullable|string|max:100',
            'is_active'     => 'boolean',
            'starts_at'     => 'nullable|date',
            'ends_at'       => 'nullable|date|after_or_equal:starts_at',
            'sort_order'    => 'nullable|integer|min:0|max:9999',
        ]);

        $validated['is_active']  = $request->boolean('is_active', false);
        $validated['sort_order'] = $request->input('sort_order', 0);

        if ($request->hasFile('image')) {
            Storage::disk('public')->delete($promoBanner->image_path);
            $validated['image_path'] = $request->file('image')->store('banners', 'public');
        }

        unset($validated['image']);

        $promoBanner->update($validated);

        return redirect()->route('super-admin.promo-banners.index')
            ->with('success', 'Bannière mise à jour.');
    }

    public function destroy(PromoBanner $promoBanner): RedirectResponse
    {
        Storage::disk('public')->delete($promoBanner->image_path);
        $promoBanner->delete();

        return redirect()->route('super-admin.promo-banners.index')
            ->with('success', 'Bannière supprimée.');
    }

    public function toggleActive(PromoBanner $promoBanner): RedirectResponse
    {
        $promoBanner->update(['is_active' => !$promoBanner->is_active]);

        return back()->with('success', $promoBanner->is_active ? 'Bannière activée.' : 'Bannière désactivée.');
    }
}
