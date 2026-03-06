<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Enums\RestaurantStatus;
use App\Models\Restaurant;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    /**
     * Generate sitemap.xml for SEO.
     * Includes: home, static pages, and public restaurant menu URLs.
     */
    public function index(): Response
    {
        $baseUrl = rtrim(config('app.url'), '/');
        $now = now()->toAtomString();

        $urls = [];

        // Static pages (priority and changefreq)
        $static = [
            ['route' => 'home', 'priority' => '1.0', 'changefreq' => 'weekly'],
            ['route' => 'pricing', 'priority' => '0.9', 'changefreq' => 'monthly'],
            ['route' => 'faq', 'priority' => '0.8', 'changefreq' => 'monthly'],
            ['route' => 'contact', 'priority' => '0.8', 'changefreq' => 'monthly'],
            ['route' => 'terms', 'priority' => '0.4', 'changefreq' => 'yearly'],
            ['route' => 'privacy', 'priority' => '0.4', 'changefreq' => 'yearly'],
        ];

        foreach ($static as $page) {
            $urls[] = [
                'loc' => $baseUrl . route($page['route'], [], false),
                'lastmod' => $now,
                'changefreq' => $page['changefreq'],
                'priority' => $page['priority'],
            ];
        }

        // Restaurant public menus (only active restaurants with slug)
        $restaurants = Restaurant::query()
            ->where('status', RestaurantStatus::ACTIVE)
            ->whereNotNull('slug')
            ->where('slug', '!=', '')
            ->select(['slug', 'updated_at'])
            ->get();

        foreach ($restaurants as $restaurant) {
            $urls[] = [
                'loc' => $baseUrl . route('r.menu', ['slug' => $restaurant->slug], false),
                'lastmod' => $restaurant->updated_at?->toAtomString() ?? $now,
                'changefreq' => 'weekly',
                'priority' => '0.7',
            ];
        }

        $xml = $this->buildXml($urls);

        return response($xml, 200, [
            'Content-Type' => 'application/xml',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }

    private function buildXml(array $urls): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        foreach ($urls as $u) {
            $xml .= '  <url>' . "\n";
            $xml .= '    <loc>' . htmlspecialchars($u['loc'], ENT_XML1, 'UTF-8') . '</loc>' . "\n";
            $xml .= '    <lastmod>' . ($u['lastmod'] ?? now()->toAtomString()) . '</lastmod>' . "\n";
            $xml .= '    <changefreq>' . ($u['changefreq'] ?? 'weekly') . '</changefreq>' . "\n";
            $xml .= '    <priority>' . ($u['priority'] ?? '0.5') . '</priority>' . "\n";
            $xml .= '  </url>' . "\n";
        }

        $xml .= '</urlset>';

        return $xml;
    }

    /**
     * Dynamic robots.txt with sitemap URL from config.
     */
    public function robots(): Response
    {
        $sitemapUrl = rtrim(config('app.url'), '/') . '/sitemap.xml';
        $content = "User-agent: *\nDisallow:\n\nSitemap: {$sitemapUrl}\n";

        return response($content, 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }
}
