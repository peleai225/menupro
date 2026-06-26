<?php

namespace App\Http\Middleware;

use App\Enums\SubscriptionStatus;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckSubscription
{
    protected array $except = [
        'restaurant.subscription*',
        'restaurant.settings*',
        'logout',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user || $user->isSuperAdmin()) {
            return $next($request);
        }

        if ($this->shouldSkip($request)) {
            return $next($request);
        }

        $restaurant = $user->restaurant;

        if (!$restaurant) {
            return $next($request);
        }

        $subscription = $restaurant->activeSubscription;

        // Auto-expire subscription if date has passed (replaces cron job)
        if ($subscription && $subscription->ends_at && $subscription->ends_at->isPast()) {
            $subscription->update(['status' => SubscriptionStatus::EXPIRED]);
            $restaurant->update(['orders_blocked' => true]);
        }

        if ($restaurant->is_subscription_expired) {
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
                    "Votre essai gratuit expire dans {$daysLeft} jour(s). Souscrivez maintenant pour continuer !"
                );
            }
        }

        // Warn if paid subscription is expiring soon
        if ($subscription && !$subscription->isTrial() && $restaurant->days_until_expiration !== null && $restaurant->days_until_expiration <= 7) {
            session()->flash('subscription_warning',
                "Votre abonnement expire dans {$restaurant->days_until_expiration} jour(s)."
            );
        }

        return $next($request);
    }

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

