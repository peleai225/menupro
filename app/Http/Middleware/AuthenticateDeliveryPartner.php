<?php

namespace App\Http\Middleware;

use App\Models\DeliveryPartner;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * API partenaires : Authorization: Bearer <clé d'API>.
 * Le partenaire résolu est placé dans $request->attributes['delivery_partner'].
 */
class AuthenticateDeliveryPartner
{
    public function handle(Request $request, Closure $next): Response
    {
        $partner = DeliveryPartner::findByApiKey($request->bearerToken());

        if (!$partner) {
            return response()->json([
                'message' => 'Clé d\'API absente ou invalide.',
                'error_code' => 'invalid_api_key',
            ], 401);
        }

        if (!$partner->is_active) {
            return response()->json([
                'message' => 'Votre compte partenaire est désactivé. Contactez MenuPro.',
                'error_code' => 'partner_disabled',
            ], 403);
        }

        // Au plus une écriture par minute (évite une écriture à chaque requête)
        if (!$partner->last_used_at || $partner->last_used_at->lt(now()->subMinute())) {
            DeliveryPartner::whereKey($partner->id)->update(['last_used_at' => now()]);
        }

        $request->attributes->set('delivery_partner', $partner);

        return $next($request);
    }
}
