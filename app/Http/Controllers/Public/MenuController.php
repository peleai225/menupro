<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Dish;
use App\Models\Restaurant;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MenuController extends Controller
{
    /**
     * Display the restaurant menu.
     */
    public function index(string $slug): View
    {
        $restaurant = Restaurant::where('slug', $slug)
            ->with(['categories' => fn($q) => $q->active()->ordered()->withCount(['dishes' => fn($d) => $d->active()])])
            ->firstOrFail();

        // Get active categories with active dishes
        $categories = $restaurant->categories
            ->filter(fn($c) => $c->dishes_count > 0);

        // Get featured dishes
        $featuredDishes = Dish::where('restaurant_id', $restaurant->id)
            ->active()
            ->featured()
            ->limit(6)
            ->get();

        // Get new dishes
        $newDishes = Dish::where('restaurant_id', $restaurant->id)
            ->active()
            ->new()
            ->limit(4)
            ->get();

        // Check if restaurant is open
        $isOpen = $restaurant->isOpenNow();

        return view('pages.restaurant-public.menu', compact(
            'restaurant',
            'categories',
            'featuredDishes',
            'newDishes',
            'isOpen'
        ));
    }

    /**
     * Get dishes by category (AJAX).
     */
    public function category(string $slug, Category $category): View
    {
        $restaurant = Restaurant::where('slug', $slug)->firstOrFail();

        // Verify category belongs to restaurant
        if ($category->restaurant_id !== $restaurant->id) {
            abort(404);
        }

        $dishes = Dish::where('category_id', $category->id)
            ->active()
            ->ordered()
            ->get();

        return view('pages.restaurant-public.partials.dishes-grid', compact('dishes', 'restaurant'));
    }

    /**
     * Get dish details (AJAX/Modal).
     */
    public function dish(string $slug, Dish $dish): View
    {
        $restaurant = Restaurant::where('slug', $slug)->firstOrFail();

        // Verify dish belongs to restaurant
        if ($dish->restaurant_id !== $restaurant->id) {
            abort(404);
        }

        $dish->load('optionGroups.activeOptions', 'category');

        return view('pages.restaurant-public.partials.dish-modal', compact('dish', 'restaurant'));
    }

    /**
     * Search dishes.
     */
    public function search(Request $request, string $slug): View
    {
        $restaurant = Restaurant::where('slug', $slug)->firstOrFail();

        $query = $request->get('q', '');

        $dishes = collect();

        if (strlen($query) >= 2) {
            $dishes = Dish::where('restaurant_id', $restaurant->id)
                ->active()
                ->search($query)
                ->limit(20)
                ->get();
        }

        return view('pages.restaurant-public.partials.search-results', compact('dishes', 'restaurant', 'query'));
    }
}

