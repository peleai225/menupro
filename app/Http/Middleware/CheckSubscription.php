<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckSubscription
{
    /**
     * Routes that don't require an active subscription
     */
    protected array $except = [
        'restaurant.subscription*',
        'restaurant.settings*',
        'logout',
    ];

    /**
     * Handle an incoming request.
     *
     * Check if the restaurant has an active subscription.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Skip for super admins
        if (!$user || $user->isSuperAdmin()) {
            return $next($request);
        }

        // Skip for excepted routes
        if ($this->shouldSkip($request)) {
            return $next($request);
        }

        $restaurant = $user->restaurant;

        if (!$restaurant) {
            return $next($request);
        }

        // Get active subscription (including trial)
        $subscription = $restaurant->activeSubscription;

        // Check if subscription is expired
        if ($restaurant->is_subscription_expired) {
            // Check if it was a trial
            if ($subscription && $subscription->isTrial()) {
                return redirect()->route('restaurant.subscription')
                    ->with('error', 'Votre essai gratuit a expiré. Veuillez souscrire à un abonnement pour continuer.');
            }
            
            return redirect()->route('restaurant.subscription')
                ->with('warning', 'Votre abonnement a expiré. Veuillez le renouveler pour continuer.');
        }

        // Warn if trial is expiring soon
        if ($subscription && $subscription->isTrial()) {
            $daysLeft = $restaurant->days_until_expiration;
            
            if ($daysLeft !== null && $daysLeft <= 3) {
                session()->flash('trial_warning', 
                    "⏰ Votre essai gratuit expire dans {$daysLeft} jour(s). Souscrivez maintenant pour continuer !"
                );
            }
        }

        // Warn if paid subscription is expiring soon (within 7 days)
        if ($subscription && !$subscription->isTrial() && $restaurant->days_until_expiration !== null && $restaurant->days_until_expiration <= 7) {
            session()->flash('subscription_warning', 
                "Votre abonnement expire dans {$restaurant->days_until_expiration} jour(s)."
            );
        }

        return $next($request);
    }

    /**
     * Check if the request should skip subscription check
     */
    protected function shouldSkip(Request $request): bool
    {
        foreach ($this->except as $pattern) {
            if ($request->routeIs($pattern)) {
                return true;
            }
        }

        return false;
    }
}

