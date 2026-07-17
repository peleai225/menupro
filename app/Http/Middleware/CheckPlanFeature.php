<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

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

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Cette fonctionnalité nécessite un plan supérieur.',
                'feature' => $feature,
                'upgrade_required' => $this->featureLabel($feature),
            ], 403);
        }

        return redirect()->route('restaurant.subscription')
            ->with('upgrade_required', $this->featureLabel($feature));
    }

    protected function featureLabel(string $feature): string
    {
        return match ($feature) {
            'analytics' => 'Statistiques, Rapports et Codes Promo',
            'stock' => 'Gestion de Stock',
            'delivery' => 'Gestion des Livraisons',
            'hotel_rooms' => 'Chambres Hôtel (QR par chambre)',
            default => ucfirst($feature),
        };
    }
}
