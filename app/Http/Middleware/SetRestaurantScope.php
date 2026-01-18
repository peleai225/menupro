<?php

namespace App\Http\Middleware;

use App\Models\Restaurant;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetRestaurantScope
{
    /**
     * Handle an incoming request.
     *
     * Set the restaurant context for the current session based on:
     * 1. Route parameter (for public restaurant pages)
     * 2. Authenticated user's restaurant
     */
    public function handle(Request $request, Closure $next): Response
    {
        $restaurantId = null;
        $restaurant = null;

        // Priority 1: Route parameter (slug)
        if ($slug = $request->route('slug')) {
            $restaurant = Restaurant::where('slug', $slug)->first();
            if ($restaurant) {
                $restaurantId = $restaurant->id;
            }
        }
        
        // Priority 2: Route parameter (restaurant id)
        elseif ($routeRestaurant = $request->route('restaurant')) {
            if ($routeRestaurant instanceof Restaurant) {
                $restaurant = $routeRestaurant;
                $restaurantId = $restaurant->id;
            } elseif (is_numeric($routeRestaurant)) {
                $restaurant = Restaurant::find($routeRestaurant);
                $restaurantId = $restaurant?->id;
            }
        }
        
        // Priority 3: Authenticated user's restaurant
        elseif ($request->user() && $request->user()->restaurant_id) {
            $restaurantId = $request->user()->restaurant_id;
            $restaurant = $request->user()->restaurant;
        }

        // Store in session for global scope usage
        if ($restaurantId) {
            session(['current_restaurant_id' => $restaurantId]);
        }

        // Share with views
        if ($restaurant) {
            view()->share('restaurant', $restaurant);
            view()->share('subscription', $restaurant->activeSubscription);
        }

        return $next($request);
    }
}

