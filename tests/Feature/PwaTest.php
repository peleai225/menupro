<?php

namespace Tests\Feature;

use App\Models\Restaurant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * PWA : manifests, icônes et règles de cache du service worker.
 */
class PwaTest extends TestCase
{
    use RefreshDatabase;

    public function test_manifest_du_restaurant_ouvre_son_menu(): void
    {
        $restaurant = Restaurant::factory()->create(['slug' => 'chez-awa', 'name' => 'Chez Awa', 'primary_color' => '#16a34a']);

        $response = $this->get('/r/chez-awa/manifest.webmanifest');

        $response->assertOk();
        $this->assertStringContainsString('application/manifest+json', $response->headers->get('Content-Type'));
        $response->assertJson([
            'id' => '/r/chez-awa',
            'name' => 'Chez Awa',
            'start_url' => '/r/chez-awa',
            'scope' => '/r/chez-awa',
            'display' => 'standalone',
            'theme_color' => '#16a34a',
            'lang' => 'fr',
        ]);
    }

    public function test_manifest_restaurant_inconnu_404_et_couleur_invalide_ignoree(): void
    {
        $this->get('/r/inconnu/manifest.webmanifest')->assertNotFound();

        Restaurant::factory()->create(['slug' => 'stand-ali', 'primary_color' => 'red;}</style>']);
        $this->get('/r/stand-ali/manifest.webmanifest')->assertJson(['theme_color' => '#f97316']);
    }

    public function test_manifests_statiques_valides_et_icones_presentes(): void
    {
        $expected = [
            'manifest.json' => '/',
            'manifest-admin.json' => '/dashboard',
            'manifest-superadmin.json' => '/admin/login',
        ];

        foreach ($expected as $file => $startUrl) {
            $manifest = json_decode(file_get_contents(public_path($file)), true, flags: JSON_THROW_ON_ERROR);
            $this->assertSame($startUrl, $manifest['start_url'], $file);
            $this->assertSame('standalone', $manifest['display'], $file);
            $this->assertSame('fr', $manifest['lang'], $file);
            $this->assertArrayHasKey('id', $manifest, $file);

            $icons = array_merge($manifest['icons'], ...array_map(fn ($s) => $s['icons'] ?? [], $manifest['shortcuts'] ?? []));
            foreach ($icons as $icon) {
                $path = public_path(ltrim($icon['src'], '/'));
                $this->assertFileExists($path, "$file → {$icon['src']}");
                [$w, $h] = getimagesize($path);
                $this->assertSame($icon['sizes'], "{$w}x{$h}", "$file → {$icon['src']}");
            }
            $purposes = array_column($manifest['icons'], 'purpose');
            $this->assertContains('maskable', $purposes, $file);
            $this->assertContains('any', $purposes, $file);
        }

        [$w, $h] = getimagesize(public_path('apple-touch-icon.png'));
        $this->assertSame([180, 180], [$w, $h]);
    }

    public function test_raccourcis_du_tableau_de_bord_pointent_sur_des_routes_existantes(): void
    {
        $manifest = json_decode(file_get_contents(public_path('manifest-admin.json')), true);
        $urls = array_column($manifest['shortcuts'], 'url');

        $this->assertSame(['/dashboard/commandes', '/dashboard/pos', '/dashboard/commandes/kanban'], $urls);
        foreach ($urls as $url) {
            $route = app('router')->getRoutes()->match(\Illuminate\Http\Request::create($url));
            $this->assertStringStartsWith('restaurant.', $route->getName(), $url);
        }
    }

    public function test_service_worker_ne_cache_jamais_le_tableau_de_bord_ni_les_liens_sensibles(): void
    {
        $sw = file_get_contents(public_path('sw.js'));

        foreach (['/dashboard', '/admin', '/api/', '/livewire', '/commande-a-traiter', '/push-token', '/csrf-token', '/personnel/', '/cuisine/', '/livreur/'] as $prefix) {
            $this->assertStringContainsString("'{$prefix}'", $sw, "NEVER_CACHE doit contenir {$prefix}");
        }
        $this->assertStringContainsString("CACHE_VERSION = 'menupro-v2'", $sw);
        // Plus de stale-while-revalidate : les JSON de suivi / paiement ne sont jamais servis périmés
        $this->assertStringNotContainsString('staleWhileRevalidate', $sw);
        // La config Firebase (push) est toujours chargée par le service worker
        $this->assertStringContainsString('firebase-messaging-compat.js', $sw);
    }
}
