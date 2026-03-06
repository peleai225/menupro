<?php

namespace App\Http\Controllers\Restaurant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Restaurant\StockAdjustmentRequest;
use App\Http\Requests\Restaurant\StockEntryRequest;
use App\Http\Requests\Restaurant\StoreIngredientRequest;
use App\Models\Ingredient;
use App\Models\IngredientCategory;
use App\Models\Supplier;
use App\Services\StockManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class IngredientController extends Controller
{
    public function __construct(
        protected StockManager $stockManager
    ) {}

    /**
     * Display a listing of ingredients.
     */
    public function index(Request $request): View
    {
        $restaurant = $request->user()->restaurant;

        // Check feature access
        if (!$restaurant->hasFeature('stock')) {
            return view('pages.restaurant.stock-upgrade');
        }

        $this->stockManager->forRestaurant($restaurant);

        $query = Ingredient::with('category');

        // Filters
        if ($request->filled('category')) {
            $query->where('ingredient_category_id', $request->category);
        }

        if ($request->filled('status')) {
            match ($request->status) {
                'low' => $query->lowStock(),
                'out' => $query->outOfStock(),
                'in' => $query->inStock(),
                default => null,
            };
        }

        if ($request->filled('search')) {
            $query->search($request->search);
        }

        $ingredients = $query->orderBy('name')->paginate(20)->withQueryString();

        $categories = IngredientCategory::ordered()->get();

        // Stats
        $stats = [
            'total' => Ingredient::count(),
            'low_stock' => Ingredient::lowStock()->count(),
            'out_of_stock' => Ingredient::outOfStock()->count(),
            'total_value' => $this->stockManager->getTotalStockValue(),
        ];

        return view('pages.restaurant.ingredients', compact(
            'ingredients',
            'categories',
            'stats'
        ));
    }

    /**
     * Show the form for editing the specified ingredient.
     */
    public function edit(Request $request, Ingredient $ingredient): View
    {
        $this->authorize('update', $ingredient);

        $restaurant = $request->user()->restaurant;
        if (!$restaurant->hasFeature('stock')) {
            return view('pages.restaurant.stock-upgrade');
        }

        $categories = IngredientCategory::ordered()->get();

        return view('pages.restaurant.ingredient-edit', compact('ingredient', 'categories'));
    }

    /**
     * Store a newly created ingredient.
     */
    public function store(StoreIngredientRequest $request): RedirectResponse
    {
        Ingredient::create($request->validated());

        return back()->with('success', 'Ingrédient créé avec succès.');
    }

    /**
     * Display the specified ingredient.
     */
    public function show(Ingredient $ingredient): View
    {
        $this->authorize('view', $ingredient);

        $ingredient->load(['category', 'suppliers', 'movements' => fn($q) => $q->latest()->limit(20)]);

        $suppliers = Supplier::active()->get();

        return view('pages.restaurant.ingredient-show', compact('ingredient', 'suppliers'));
    }

    /**
     * Update the specified ingredient.
     */
    public function update(Request $request, Ingredient $ingredient): RedirectResponse
    {
        $this->authorize('update', $ingredient);

        $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'sku' => ['nullable', 'string', 'max:50', 'unique:ingredients,sku,' . $ingredient->id],
            'ingredient_category_id' => ['nullable', 'exists:ingredient_categories,id'],
            'unit' => ['required', \Illuminate\Validation\Rule::enum(\App\Enums\Unit::class)],
            'current_quantity' => ['required', 'numeric', 'min:0'],
            'min_quantity' => ['required', 'numeric', 'min:0'],
            'max_quantity' => ['nullable', 'numeric', 'min:0', 'gte:min_quantity'],
            'unit_cost' => ['required', 'integer', 'min:0'],
            'track_expiry' => ['boolean'],
            'default_expiry_days' => ['nullable', 'integer', 'min:1'],
            'notes' => ['nullable', 'string', 'max:500'],
            'is_active' => ['boolean'],
        ]);

        $ingredient->update($request->only([
            'name', 'sku', 'ingredient_category_id', 'unit', 'current_quantity',
            'min_quantity', 'max_quantity', 'unit_cost', 'track_expiry',
            'default_expiry_days', 'notes', 'is_active',
        ]));

        return back()->with('success', 'Ingrédient mis à jour.');
    }

    /**
     * Remove the specified ingredient.
     */
    public function destroy(Ingredient $ingredient): RedirectResponse
    {
        $this->authorize('delete', $ingredient);

        $ingredient->delete();

        return redirect()->route('restaurant.stock.ingredients.index')
            ->with('success', 'Ingrédient supprimé.');
    }

    /**
     * Add stock entry.
     */
    public function addStock(StockEntryRequest $request, Ingredient $ingredient): RedirectResponse
    {
        $restaurant = $request->user()->restaurant;
        $this->stockManager->forRestaurant($restaurant);

        $this->stockManager->entry(
            $ingredient,
            $request->quantity,
            $request->unit_cost,
            [
                'expiry_date' => $request->expiry_date,
                'batch_number' => $request->batch_number,
                'reason' => $request->reason ?? 'Entrée de stock',
                'reference_type' => $request->supplier_id ? Supplier::class : null,
                'reference_id' => $request->supplier_id,
            ]
        );

        return back()->with('success', 'Stock ajouté avec succès.');
    }

    /**
     * Remove stock (manual exit).
     */
    public function removeStock(Request $request, Ingredient $ingredient): RedirectResponse
    {
        $this->authorize('adjustStock', $ingredient);

        $request->validate([
            'quantity' => ['required', 'numeric', 'min:0.001'],
            'reason' => ['required', 'string', 'max:255'],
        ]);

        $restaurant = $request->user()->restaurant;
        $this->stockManager->forRestaurant($restaurant);

        $this->stockManager->exit($ingredient, $request->quantity, $request->reason);

        return back()->with('success', 'Stock retiré avec succès.');
    }

    /**
     * Record waste.
     */
    public function recordWaste(Request $request, Ingredient $ingredient): RedirectResponse
    {
        $this->authorize('adjustStock', $ingredient);

        $request->validate([
            'quantity' => ['required', 'numeric', 'min:0.001'],
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        $restaurant = $request->user()->restaurant;
        $this->stockManager->forRestaurant($restaurant);

        $this->stockManager->waste($ingredient, $request->quantity, $request->reason);

        return back()->with('success', 'Perte enregistrée.');
    }

    /**
     * Adjust stock (inventory).
     */
    public function adjust(StockAdjustmentRequest $request, Ingredient $ingredient): RedirectResponse
    {
        $restaurant = $request->user()->restaurant;
        $this->stockManager->forRestaurant($restaurant);

        $this->stockManager->adjust($ingredient, $request->new_quantity, $request->reason);

        return back()->with('success', 'Stock ajusté.');
    }

    /**
     * Display movements for an ingredient.
     */
    public function movements(Ingredient $ingredient): View
    {
        $this->authorize('view', $ingredient);

        $movements = $ingredient->movements()
            ->with('user')
            ->latest()
            ->paginate(50);

        return view('pages.restaurant.ingredient-movements', compact('ingredient', 'movements'));
    }

    /**
     * Display stock report.
     */
    public function report(Request $request): View
    {
        $restaurant = $request->user()->restaurant;

        if (!$restaurant->hasFeature('stock')) {
            return view('pages.restaurant.stock-upgrade');
        }

        $ingredients = Ingredient::with('category')
            ->orderBy('name')
            ->get();

        $totalValue = $ingredients->sum(fn($i) => $i->current_quantity * ($i->unit_cost ?? 0));
        
        $lowStockCount = $ingredients->filter(fn($i) => $i->current_quantity > 0 && $i->current_quantity <= $i->min_quantity)->count();
        $outOfStockCount = $ingredients->filter(fn($i) => $i->current_quantity <= 0)->count();

        return view('pages.restaurant.stock-report', compact('ingredients', 'totalValue', 'lowStockCount', 'outOfStockCount'));
    }

    /**
     * Display stock alerts.
     */
    public function alerts(Request $request): View
    {
        $restaurant = $request->user()->restaurant;

        if (!$restaurant->hasFeature('stock')) {
            return view('pages.restaurant.stock-upgrade');
        }

        $lowStock = Ingredient::lowStock()->with('category')->get();
        $outOfStock = Ingredient::outOfStock()->with('category')->get();

        return view('pages.restaurant.stock-alerts', compact('lowStock', 'outOfStock'));
    }
}

