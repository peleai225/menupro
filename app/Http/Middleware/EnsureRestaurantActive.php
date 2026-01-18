<?php

namespace App\Http\Middleware;

use App\Enums\RestaurantStatus;
use App\Models\Restaurant;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRestaurantActive
{
    /**
     * Handle an incoming request.
     *
     * Ensure the restaurant is active.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $restaurant = null;

        // First try to get restaurant from authenticated user (for dashboard routes)
        if ($request->user() && $request->user()->restaurant_id) {
            $restaurant = $request->user()->restaurant;
        }

        // If no restaurant from user, try route parameters (for public routes)
        if (!$restaurant) {
            if ($slug = $request->route('slug')) {
                $restaurant = Restaurant::where('slug', $slug)->first();
            } elseif ($routeRestaurant = $request->route('restaurant')) {
                $restaurant = $routeRestaurant instanceof Restaurant 
                    ? $routeRestaurant 
                    : Restaurant::find($routeRestaurant);
            }
        }

        // Restaurant not found
        if (!$restaurant) {
            abort(404, 'Restaurant non trouvé');
        }

        // Super admins bypass status check
        if ($request->user()?->isSuperAdmin()) {
            return $next($request);
        }

        // Check status for dashboard (restaurant owners can access even if pending)
        if ($this->isDashboardRoute($request)) {
            // Allow access for pending restaurants (owner can still manage)
            if ($restaurant->status === RestaurantStatus::SUSPENDED) {
                return redirect()->route('home')
                    ->with('error', 'Votre restaurant est suspendu. Contactez le support.');
            }
            
            if ($restaurant->status === RestaurantStatus::EXPIRED) {
                // Allow access to subscription page only
                if (!$request->routeIs('restaurant.subscription*')) {
                    return redirect()->route('restaurant.subscription')
                        ->with('warning', 'Votre abonnement a expiré. Veuillez le renouveler.');
                }
            }
            
            return $next($request);
        }

        // For public pages, require active status
        if ($restaurant->status !== RestaurantStatus::ACTIVE) {
            return $this->handleInactiveRestaurant($restaurant);
        }

        // Check if orders are blocked
        if ($restaurant->orders_blocked && $this->isOrderRoute($request)) {
            return redirect()->route('r.menu', $restaurant->slug)
                ->with('error', 'Ce restaurant ne peut pas accepter de commandes pour le moment.');
        }

        return $next($request);
    }

    /**
     * Check if current route is a dashboard route
     */
    protected function isDashboardRoute(Request $request): bool
    {
        return $request->routeIs('restaurant.*');
    }

    /**
     * Handle inactive restaurant
     */
    protected function handleInactiveRestaurant(Restaurant $restaurant): Response
    {
        $message = match ($restaurant->status) {
            RestaurantStatus::PENDING => 'Ce restaurant est en attente de validation.',
            RestaurantStatus::SUSPENDED => 'Ce restaurant est temporairement suspendu.',
            RestaurantStatus::EXPIRED => 'L\'abonnement de ce restaurant a expiré.',
            default => 'Ce restaurant n\'est pas disponible.',
        };

        return response()->view('pages.restaurant-public.unavailable', [
            'restaurant' => $restaurant,
            'message' => $message,
        ], 503);
    }

    /**
     * Check if current route is an order route
     */
    protected function isOrderRoute(Request $request): bool
    {
        return $request->routeIs('r.checkout*') || $request->routeIs('r.order*');
    }
}
