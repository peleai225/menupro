<?php

namespace App\Http\Controllers\Restaurant;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SupplierController extends Controller
{
    /**
     * Display a listing of suppliers.
     */
    public function index(Request $request): View
    {
        $restaurant = $request->user()->restaurant;

        if (!$restaurant->hasFeature('stock')) {
            return view('pages.restaurant.stock-upgrade');
        }

        $suppliers = Supplier::where('restaurant_id', $restaurant->id)
            ->withCount('ingredients')
            ->orderBy('name')
            ->paginate(20);

        return view('pages.restaurant.suppliers', compact('suppliers'));
    }

    /**
     * Store a newly created supplier.
     */
    public function store(Request $request): RedirectResponse
    {
        $restaurant = $request->user()->restaurant;

        $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'contact_name' => ['nullable', 'string', 'max:100'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:500'],
            'city' => ['nullable', 'string', 'max:100'],
            'min_order_amount' => ['nullable', 'integer', 'min:0'],
            'delivery_days' => ['nullable', 'integer', 'min:0'],
            'payment_terms' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        Supplier::create(array_merge($request->all(), [
            'restaurant_id' => $restaurant->id,
        ]));

        return back()->with('success', 'Fournisseur créé avec succès.');
    }

    /**
     * Display the specified supplier.
     */
    public function show(Supplier $supplier): View
    {
        $supplier->load('ingredients');

        return view('pages.restaurant.supplier-show', compact('supplier'));
    }

    /**
     * Update the specified supplier.
     */
    public function update(Request $request, Supplier $supplier): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'contact_name' => ['nullable', 'string', 'max:100'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:500'],
            'city' => ['nullable', 'string', 'max:100'],
            'min_order_amount' => ['nullable', 'integer', 'min:0'],
            'delivery_days' => ['nullable', 'integer', 'min:0'],
            'payment_terms' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:500'],
            'is_active' => ['boolean'],
        ]);

        $supplier->update($request->all());

        return back()->with('success', 'Fournisseur mis à jour.');
    }

    /**
     * Remove the specified supplier.
     */
    public function destroy(Supplier $supplier): RedirectResponse
    {
        $supplier->delete();

        return redirect()->route('restaurant.suppliers')
            ->with('success', 'Fournisseur supprimé.');
    }

    /**
     * Link ingredient to supplier.
     */
    public function linkIngredient(Request $request, Supplier $supplier): RedirectResponse
    {
        $request->validate([
            'ingredient_id' => ['required', 'exists:ingredients,id'],
            'unit_price' => ['required', 'integer', 'min:0'],
            'supplier_sku' => ['nullable', 'string', 'max:50'],
            'is_preferred' => ['boolean'],
        ]);

        $supplier->ingredients()->syncWithoutDetaching([
            $request->ingredient_id => [
                'unit_price' => $request->unit_price,
                'supplier_sku' => $request->supplier_sku,
                'is_preferred' => $request->boolean('is_preferred'),
            ],
        ]);

        return back()->with('success', 'Ingrédient associé au fournisseur.');
    }

    /**
     * Unlink ingredient from supplier.
     */
    public function unlinkIngredient(Supplier $supplier, int $ingredientId): RedirectResponse
    {
        $supplier->ingredients()->detach($ingredientId);

        return back()->with('success', 'Association supprimée.');
    }
}

