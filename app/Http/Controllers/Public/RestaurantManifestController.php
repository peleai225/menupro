<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Restaurant;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

/**
 * Manifest PWA propre à chaque restaurant (/r/{slug}/manifest.webmanifest).
 *
 * Le bandeau « Installer {restaurant} » du menu public installait jusqu'ici le manifest
 * générique (/manifest.json, start_url « / ») : l'application installée ouvrait la page
 * d'accueil MenuPro au lieu du menu du restaurant. Ici, start_url et scope pointent sur
 * le menu, le nom et la couleur sont ceux du restaurant.
 */
class RestaurantManifestController extends Controller
{
    public function __invoke(string $slug): JsonResponse
    {
        $restaurant = Restaurant::where('slug', $slug)->firstOrFail();

        $base = '/r/' . rawurlencode($restaurant->slug);
        $color = $this->hexColor($restaurant->primary_color) ?? '#f97316';
        $name = trim((string) $restaurant->name) !== '' ? $restaurant->name : 'MenuPro';

        $manifest = [
            'id' => $base,
            'name' => $name,
            'short_name' => Str::limit($name, 12, ''),
            'description' => 'Menu et commande en ligne — ' . $name,
            'start_url' => $base,
            'scope' => $base,
            'display' => 'standalone',
            'orientation' => 'portrait',
            'background_color' => '#ffffff',
            'theme_color' => $color,
            'lang' => 'fr',
            'dir' => 'ltr',
            'categories' => ['food', 'shopping'],
            'icons' => [
                ['src' => '/icon-192.png', 'sizes' => '192x192', 'type' => 'image/png', 'purpose' => 'any'],
                ['src' => '/icon-512.png', 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'any'],
                ['src' => '/icon-192-maskable.png', 'sizes' => '192x192', 'type' => 'image/png', 'purpose' => 'maskable'],
                ['src' => '/icon-512-maskable.png', 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'maskable'],
            ],
            'prefer_related_applications' => false,
        ];

        return response()
            ->json($manifest, 200, [
                'Content-Type' => 'application/manifest+json; charset=utf-8',
                'Cache-Control' => 'public, max-age=3600',
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    private function hexColor(?string $value): ?string
    {
        $value = trim((string) $value);

        return preg_match('/^#(?:[0-9a-fA-F]{3}){1,2}$/', $value) ? $value : null;
    }
}
