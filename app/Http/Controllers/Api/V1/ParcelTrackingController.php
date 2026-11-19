<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Parcel;
use App\Support\Parcels\ParcelPresenter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Suivi public d'un colis (destinataire sans compte) et retour de paiement Jeko.
 */
class ParcelTrackingController extends Controller
{
    public function track(string $token): JsonResponse
    {
        $parcel = strlen($token) >= 32
            ? Parcel::where('tracking_token', $token)->with('driver')->first()
            : null;

        if (!$parcel) {
            return response()->json(['message' => 'Lien de suivi invalide ou expiré.', 'error_code' => 'not_found'], 404);
        }

        return response()->json(['parcel' => ParcelPresenter::forPublic($parcel)]);
    }

    /**
     * Redirection depuis Jeko après paiement : n'altère rien (le webhook signé fait foi),
     * renvoie simplement vers l'app client.
     */
    public function paymentReturn(Request $request): RedirectResponse
    {
        $token = (string) $request->query('token');
        $error = $request->query('result') === 'error' ? '&payment_error=1' : '';
        $frontend = rtrim(env('FRONTEND_URL', config('apps.client_url')), '/');

        return redirect($frontend . '/?parcel=' . urlencode($token) . $error);
    }
}
