<?php

namespace App\Http\Controllers\Public;

use App\Enums\RestaurantStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Restaurant;
use Illuminate\View\View;

class HomeController extends Controller
{
    /**
     * Display the homepage.
     */
    public function index(): View
    {
        $stats = $this->getStats();
        
        return view('pages.public.home', compact('stats'));
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
