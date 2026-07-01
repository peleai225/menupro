<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureDeliveryDriver
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user || $user->role !== UserRole::DELIVERY_DRIVER) {
            return response()->json(['message' => 'Accès réservé aux livreurs.'], 403);
        }

        $driver = $user->deliveryDriver;

        if (!$driver) {
            return response()->json(['message' => 'Profil livreur introuvable.'], 403);
        }

        if (!$driver->isApproved()) {
            return response()->json([
                'message' => 'Votre compte est en cours de vérification. Revenez bientôt.',
                'verification_status' => $driver->verification_status,
            ], 403);
        }

        return $next($request);
    }
}
