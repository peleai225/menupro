<?php

namespace App\Http\Controllers\Public;

use App\Enums\RestaurantStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Restaurant;
use App\Models\SystemSetting;
use Illuminate\View\View;

class HomeController extends Controller
{
    /**
     * Display the homepage.
     */
    public function index(): View
    {
        $stats = $this->getStats();
        $videos = $this->getHomeVideos();

        return view('pages.public.home', compact('stats', 'videos'));
    }

    /**
     * Get home page videos from SystemSetting (dynamic, manageable by Super Admin).
     * Format: [ { title, url, description }, ... ]
     * url: YouTube embed (https://www.youtube.com/embed/VIDEO_ID) ou youtu.be/VIDEO_ID
     */
    protected function getHomeVideos(): array
    {
        $videos = SystemSetting::get('home_videos', []);

        if (! is_array($videos) || empty($videos)) {
            return [];
        }

        return array_values(array_map(function ($v) {
            $url = $v['url'] ?? '';
            $url = $this->normalizeYoutubeEmbedUrl($url);

            return [
                'title' => $v['title'] ?? 'Vidéo',
                'url' => $url,
                'description' => $v['description'] ?? '',
            ];
        }, $videos));
    }

    /**
     * Convert YouTube URL to embed format.
     */
    protected function normalizeYoutubeEmbedUrl(string $url): string
    {
        if (str_contains($url, 'youtube.com/embed/')) {
            return $url;
        }
        if (preg_match('#(?:youtube\.com/watch\?v=|youtu\.be/)([a-zA-Z0-9_-]+)#', $url, $m)) {
            return 'https://www.youtube.com/embed/' . $m[1];
        }

        return $url;
    }

    /**
     * Get public statistics.
     */
    public function getStats(): array
    {
        // Total restaurants (active only)
        $totalRestaurants = Restaurant::where('status', RestaurantStatus::ACTIVE)->count();
        
        // Total orders (all time)
        $totalOrders = Order::withoutGlobalScope('restaurant')->count();
        
        // Format numbers with + if needed
        if ($totalRestaurants >= 500) {
            $restaurantsCount = number_format($totalRestaurants, 0, ',', ' ') . '+';
        } else {
            $restaurantsCount = number_format($totalRestaurants, 0, ',', ' ');
        }
        
        if ($totalOrders >= 50000) {
            $ordersCount = number_format($totalOrders / 1000, 0, ',', ' ') . 'K+';
        } elseif ($totalOrders >= 1000) {
            $ordersCount = number_format($totalOrders / 1000, 1, ',', ' ') . 'K+';
        } else {
            $ordersCount = number_format($totalOrders, 0, ',', ' ');
        }
        
        // Uptime (can be calculated from system logs or set as default)
        // For now, we'll use 99.9% as default, but you can calculate it from actual uptime data
        $uptime = '99.9%';
        
        return [
            'restaurants' => $restaurantsCount,
            'orders' => $ordersCount,
            'uptime' => $uptime,
            'raw' => [
                'restaurants' => $totalRestaurants,
                'orders' => $totalOrders,
            ],
        ];
    }
}
