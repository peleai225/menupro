<?php

namespace App\Http\Controllers;

use App\Models\PushToken;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Enregistre le token push (FCM web) de l'appareil d'un utilisateur connecté au site
 * (restaurateur, employé, stand…), pour le notifier même onglet fermé.
 */
class PushTokenController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate(['token' => ['required', 'string', 'max:255']]);

        // Un appareil n'appartient qu'à un seul compte : le dernier connecté le récupère
        PushToken::updateOrCreate(
            ['token' => $data['token']],
            [
                'user_id'      => $request->user()->id,
                'user_agent'   => substr((string) $request->userAgent(), 0, 255),
                'last_seen_at' => now(),
            ],
        );

        return response()->json(['message' => 'Appareil enregistré.']);
    }

    public function destroy(Request $request): JsonResponse
    {
        $data = $request->validate(['token' => ['required', 'string', 'max:255']]);

        $request->user()->pushTokens()->where('token', $data['token'])->delete();

        return response()->json(['message' => 'Appareil retiré.']);
    }
}
