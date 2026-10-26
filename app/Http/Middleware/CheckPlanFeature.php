<?php

namespace App\Http\Middleware;

use App\Services\PlanLimiter;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Fonction réservée à un plan supérieur (config('plans.features')) : jamais de 403 brute
 * pour une page, mais un renvoi vers les offres avec « Passer au plan X ».
 */
class CheckPlanFeature
{
    public function handle(Request $request, Closure $next, string $feature): Response
    {
        $user = $request->user();

        if (!$user || $user->isSuperAdmin()) {
            return $next($request);
        }

        $restaurant = $user->restaurant;

        if ($restaurant && $restaurant->hasFeature($feature)) {
            return $next($request);
        }

        $label = PlanLimiter::featureLabel($feature);
        $plan = PlanLimiter::requiredPlanFor($feature);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Cette fonctionnalité nécessite un plan supérieur.',
                'feature' => $feature,
                'upgrade_required' => $label,
                'required_plan' => $plan?->slug,
            ], 403);
        }

        $message = $plan
            ? "« {$label} » est inclus à partir du plan {$plan->name}. Passez au plan {$plan->name} pour y accéder."
            : "« {$label} » n'est pas inclus dans votre plan.";

        // Employé : il ne peut pas changer d'abonnement, retour au tableau de bord
        if (!$user->canManageRestaurant()) {
            return redirect()->route('restaurant.dashboard')->with('warning', $message . ' Parlez-en au gérant.');
        }

        return redirect()->route('restaurant.subscription.plans', array_filter(['plan' => $plan?->slug]))
            ->with('upgrade_required', $label)
            ->with('warning', $message);
    }
}
