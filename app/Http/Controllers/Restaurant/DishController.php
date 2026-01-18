<?php

namespace App\Http\Controllers\Restaurant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Restaurant\StoreDishRequest;
use App\Http\Requests\Restaurant\UpdateDishRequest;
use App\Models\Category;
use App\Models\Dish;
use App\Models\DishOptionGroup;
use App\Services\MediaUploader;
use App\Services\PlanLimiter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DishController extends Controller
{
    public function __construct(
        protected MediaUploader $mediaUploader,
        protected PlanLimiter $planLimiter
    ) {}

    /**
     * Display a listing of dishes.
     */
    public function index(Request $request): View
    {
        $restaurant = $request->user()->restaurant;

        $query = Dish::with('category');

        // Filters
        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        if ($request->filled('search')) {
            $query->search($request->search);
        }

        $dishes = $query->ordered()->paginate(20)->withQueryString();

        $categories = Category::ordered()->get();

        $this->planLimiter->forRestaurant($restaurant);
        $canCreate = $this->planLimiter->canCreate('dishes');
        $quotas = $this->planLimiter->getQuotasSummary()['dishes'];

        return view('pages.restaurant.dishes', compact(
            'dishes',
            'categories',
            'canCreate',
            'quotas'
        ));
    }

    /**
     * Show the form for creating a new dish.
     */
    public function create(Request $request): View
    {
        $restaurant = $request->user()->restaurant;

        // Check quota
        $this->planLimiter->forRestaurant($restaurant)->validateOrFail('dishes');

        $categories = Category::ordered()->get();
        $optionGroups = DishOptionGroup::active()->ordered()->get();

        return view('pages.restaurant.dishes-create', compact('categories', 'optionGroups'));
    }

    /**
     * Store a newly created dish.
     */
    public function store(StoreDishRequest $request): RedirectResponse
    {
        $restaurant = $request->user()->restaurant;

        // Check quota
        $this->planLimiter->forRestaurant($restaurant)->validateOrFail('dishes');

        $data = $request->validated();

        // Handle image upload
        if ($request->hasFile('image')) {
            $data['image_path'] = $this->mediaUploader->uploadDishImage(
                $request->file('image'),
                $restaurant->id
            );
        }

        // Handle gallery
        if ($request->hasFile('gallery')) {
            $gallery = [];
            foreach ($request->file('gallery') as $file) {
                $gallery[] = $this->mediaUploader->uploadDishImage($file, $restaurant->id);
            }
            $data['gallery'] = $gallery;
        }

        // Set sort order
        $data['sort_order'] = Dish::max('sort_order') + 1;

        $dish = Dish::create($data);

        // Sync option groups
        if (!empty($data['option_groups'])) {
            $dish->optionGroups()->sync($data['option_groups']);
        }

        return redirect()->route('restaurant.dishes')
            ->with('success', 'Plat créé avec succès.');
    }

    /**
     * Show the form for editing the specified dish.
     */
    public function edit(Dish $dish): View
    {
        $this->authorize('update', $dish);

        $categories = Category::ordered()->get();
        $optionGroups = DishOptionGroup::active()->ordered()->get();
        $dish->load('optionGroups', 'ingredients');

        return view('pages.restaurant.dishes-edit', compact('dish', 'categories', 'optionGroups'));
    }

    /**
     * Update the specified dish.
     */
    public function update(UpdateDishRequest $request, Dish $dish): RedirectResponse
    {
        $data = $request->validated();

        // Handle image
        if ($request->hasFile('image')) {
            $this->mediaUploader->delete($dish->image_path);
            $data['image_path'] = $this->mediaUploader->uploadDishImage(
                $request->file('image'),
                $dish->restaurant_id
            );
        } elseif ($request->boolean('remove_image')) {
            $this->mediaUploader->delete($dish->image_path);
            $data['image_path'] = null;
        }

        // Handle gallery
        if ($request->hasFile('gallery')) {
            // Delete old gallery
            if ($dish->gallery) {
                $this->mediaUploader->deleteMany($dish->gallery);
            }
            
            $gallery = [];
            foreach ($request->file('gallery') as $file) {
                $gallery[] = $this->mediaUploader->uploadDishImage($file, $dish->restaurant_id);
            }
            $data['gallery'] = $gallery;
        }

        $dish->update($data);

        // Sync option groups
        if (isset($data['option_groups'])) {
            $dish->optionGroups()->sync($data['option_groups']);
        }

        return back()->with('success', 'Plat mis à jour avec succès.');
    }

    /**
     * Remove the specified dish.
     */
    public function destroy(Dish $dish): RedirectResponse
    {
        $this->authorize('delete', $dish);

        // Delete images
        $this->mediaUploader->delete($dish->image_path);
        if ($dish->gallery) {
            $this->mediaUploader->deleteMany($dish->gallery);
        }

        $dish->delete();

        return back()->with('success', 'Plat supprimé avec succès.');
    }

    /**
     * Toggle dish active status.
     */
    public function toggle(Dish $dish): RedirectResponse
    {
        $this->authorize('toggleAvailability', $dish);

        $dish->update(['is_active' => !$dish->is_active]);

        $status = $dish->is_active ? 'activé' : 'désactivé';
        return back()->with('success', "Plat {$status}.");
    }

    /**
     * Toggle featured status.
     */
    public function toggleFeatured(Dish $dish): RedirectResponse
    {
        $this->authorize('update', $dish);

        $dish->update(['is_featured' => !$dish->is_featured]);

        $status = $dish->is_featured ? 'mis en avant' : 'retiré de la mise en avant';
        return back()->with('success', "Plat {$status}.");
    }

    /**
     * Duplicate a dish.
     */
    public function duplicate(Dish $dish): RedirectResponse
    {
        $this->authorize('create', Dish::class);

        $restaurant = request()->user()->restaurant;
        $this->planLimiter->forRestaurant($restaurant)->validateOrFail('dishes');

        $newDish = $dish->replicate();
        $newDish->name = $dish->name . ' (copie)';
        $newDish->slug = null; // Will be regenerated
        $newDish->is_active = false;
        $newDish->save();

        // Copy option groups
        $newDish->optionGroups()->sync($dish->optionGroups->pluck('id'));

        return redirect()->route('restaurant.dishes.edit', $newDish)
            ->with('success', 'Plat dupliqué avec succès.');
    }
}

