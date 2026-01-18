<?php

namespace App\Http\Controllers\Restaurant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Restaurant\StoreCategoryRequest;
use App\Http\Requests\Restaurant\UpdateCategoryRequest;
use App\Models\Category;
use App\Services\MediaUploader;
use App\Services\PlanLimiter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function __construct(
        protected MediaUploader $mediaUploader,
        protected PlanLimiter $planLimiter
    ) {}

    /**
     * Display a listing of categories.
     */
    public function index(Request $request): View
    {
        $restaurant = $request->user()->restaurant;
        
        $categories = Category::with(['dishes' => fn($q) => $q->where('is_active', true)])
            ->ordered()
            ->get();

        $this->planLimiter->forRestaurant($restaurant);
        $canCreate = $this->planLimiter->canCreate('categories');
        $quotas = $this->planLimiter->getQuotasSummary()['categories'];

        return view('pages.restaurant.categories', compact('categories', 'canCreate', 'quotas'));
    }

    /**
     * Store a newly created category.
     */
    public function store(StoreCategoryRequest $request): RedirectResponse
    {
        $restaurant = $request->user()->restaurant;

        // Check quota
        $this->planLimiter->forRestaurant($restaurant)->validateOrFail('categories');

        $data = $request->validated();

        // Handle image upload
        if ($request->hasFile('image')) {
            $data['image_path'] = $this->mediaUploader->uploadCategoryImage(
                $request->file('image'),
                $restaurant->id
            );
        }

        // Set sort order
        $data['sort_order'] = Category::max('sort_order') + 1;

        Category::create($data);

        return back()->with('success', 'Catégorie créée avec succès.');
    }

    /**
     * Update the specified category.
     */
    public function update(UpdateCategoryRequest $request, Category $category): RedirectResponse
    {
        $data = $request->validated();

        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image
            $this->mediaUploader->delete($category->image_path);
            
            $data['image_path'] = $this->mediaUploader->uploadCategoryImage(
                $request->file('image'),
                $category->restaurant_id
            );
        }

        $category->update($data);

        return back()->with('success', 'Catégorie mise à jour avec succès.');
    }

    /**
     * Remove the specified category.
     */
    public function destroy(Category $category): RedirectResponse
    {
        $this->authorize('delete', $category);

        // Delete image if exists
        $this->mediaUploader->delete($category->image_path);

        $category->delete();

        return back()->with('success', 'Catégorie supprimée avec succès.');
    }

    /**
     * Reorder categories.
     */
    public function reorder(Request $request): RedirectResponse
    {
        $this->authorize('reorder', Category::class);

        $request->validate([
            'order' => ['required', 'array'],
            'order.*' => ['integer', 'exists:categories,id'],
        ]);

        foreach ($request->order as $index => $categoryId) {
            Category::where('id', $categoryId)->update(['sort_order' => $index]);
        }

        return back()->with('success', 'Ordre des catégories mis à jour.');
    }

    /**
     * Toggle category active status.
     */
    public function toggle(Category $category): RedirectResponse
    {
        $this->authorize('update', $category);

        $category->update(['is_active' => !$category->is_active]);

        $status = $category->is_active ? 'activée' : 'désactivée';
        return back()->with('success', "Catégorie {$status}.");
    }
}

