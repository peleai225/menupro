<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRestaurant
{
    /**
     * Handle an incoming request.
     *
     * Ensure the authenticated user belongs to a restaurant.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        // Super admins can access without restaurant
        if ($user->isSuperAdmin()) {
            return $next($request);
        }

        // Check if user has a restaurant
        if (!$user->restaurant_id) {
            return redirect()->route('register')
                ->with('error', 'Vous devez créer un restaurant pour accéder à cette page.');
        }

        // Check if restaurant exists and is valid
        $restaurant = $user->restaurant;
        
        if (!$restaurant) {
            auth()->logout();
            return redirect()->route('login')
                ->with('error', 'Votre restaurant n\'existe plus.');
        }

        return $next($request);
    }
}

