<?php

namespace App\Http\Controllers\Restaurant;

use App\Http\Controllers\Controller;
use App\Models\IngredientCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class IngredientCategoryController extends Controller
{
    /**
     * Display a listing of ingredient categories.
     */
    public function index(Request $request): View
    {
        $restaurant = $request->user()->restaurant;

        if (!$restaurant->hasFeature('stock')) {
            return view('pages.restaurant.stock-upgrade');
        }

        $categories = IngredientCategory::where('restaurant_id', $restaurant->id)
            ->withCount('ingredients')
            ->orderBy('sort_order')
            ->get();

        return view('pages.restaurant.ingredient-categories', compact('categories'));
    }

    /**
     * Store a newly created category.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'color' => ['nullable', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
        ]);

        $restaurant = $request->user()->restaurant;
        
        IngredientCategory::create([
            'restaurant_id' => $restaurant->id,
            'name' => $request->name,
            'color' => $request->color ?? '#6b7280',
            'sort_order' => IngredientCategory::where('restaurant_id', $restaurant->id)->max('sort_order') + 1,
        ]);

        return back()->with('success', 'Catégorie d\'ingrédients créée.');
    }

    /**
     * Update the specified category.
     */
    public function update(Request $request, IngredientCategory $category): RedirectResponse
    {
        if ($category->restaurant_id !== $request->user()->restaurant_id) {
            abort(403, 'Vous n\'avez pas accès à cette catégorie.');
        }

        $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'color' => ['nullable', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
        ]);

        $category->update($request->only('name', 'color'));

        return back()->with('success', 'Catégorie mise à jour.');
    }

    /**
     * Remove the specified category.
     */
    public function destroy(Request $request, IngredientCategory $category): RedirectResponse
    {
        if ($category->restaurant_id !== $request->user()->restaurant_id) {
            abort(403, 'Vous n\'avez pas accès à cette catégorie.');
        }

        // Move ingredients to uncategorized
        $category->ingredients()->update(['ingredient_category_id' => null]);
        
        $category->delete();

        return back()->with('success', 'Catégorie supprimée.');
    }
}

