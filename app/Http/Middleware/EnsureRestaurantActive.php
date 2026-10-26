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

        // Super admin en train d'impersonate un owner (session posée par
        // RestaurantController::impersonate) : doit pouvoir gérer un resto
        // même PENDING pour l'inspecter avant de l'approuver.
        if ($request->session()->has('impersonating_from')) {
            return $next($request);
        }

        // Check status for dashboard
        if ($this->isDashboardRoute($request)) {
            // Restaurant en attente de validation : accès dashboard bloqué, écran d'attente.
            if ($restaurant->status === RestaurantStatus::PENDING
                && !$request->routeIs('restaurant.pending')) {
                return redirect()->route('restaurant.pending');
            }

            if ($restaurant->status === RestaurantStatus::SUSPENDED) {
                return redirect()->route('home')
                    ->with('error', 'Votre restaurant est suspendu. Contactez le support.');
            }
            
            // Abonnement expiré / essai terminé / paiement en attente : accès limité à
            // l'abonnement (paiement toujours possible), aux paramètres et au profil.
            $limiter = (clone app(\App\Services\PlanLimiter::class))->forRestaurant($restaurant);
            if ($limiter->isAccessLimited() && !\App\Services\PlanLimiter::isRouteAllowedWhenLimited($request)) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'message' => 'Abonnement expiré : renouvelez pour réactiver votre espace.',
                        'subscription_expired' => true,
                    ], 402);
                }

                // Un employé ne peut pas payer (page Abonnement réservée au gérant) : sans ce
                // cas, abonnement → tableau de bord → abonnement… bouclait à l'infini.
                if (!$request->user()?->canManageRestaurant()) {
                    return redirect()->route('restaurant.profile')
                        ->with('warning', 'L\'abonnement du restaurant a expiré. Prévenez le gérant pour qu\'il le renouvelle.');
                }

                return redirect()->route('restaurant.subscription')
                    ->with('warning', 'Votre abonnement a expiré. Renouvelez-le pour réactiver toutes les fonctions.');
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
