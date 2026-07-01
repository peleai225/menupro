<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class ConfigController extends Controller
{
    /**
     * Retourne la configuration publique de la plateforme.
     * Utilisé par l'app client/livreur pour charger les tokens (Mapbox, etc.)
     * Aucun secret ne doit apparaître ici — uniquement les clés publiques.
     */
    public function public(): JsonResponse
    {
        $config = Cache::remember('platform.public_config', 3600, function () {
            return [
                'mapbox' => [
                    'token' => SystemSetting::get('mapbox_public_token', ''),
                    'style' => SystemSetting::get('mapbox_style', 'streets-v12'),
                ],
                'app' => [
                    'name' => SystemSetting::get('app_name', config('app.name', 'MenuPro')),
                ],
            ];
        });

        return response()->json($config);
    }
}
