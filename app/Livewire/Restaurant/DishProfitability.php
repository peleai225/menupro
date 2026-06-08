<?php

namespace App\Livewire\Restaurant;

use App\Models\Dish;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;

class DishProfitability extends Component
{
    #[Url]
    public string $sort = 'margin_desc';

    #[Url]
    public string $period = '30';

    #[Computed]
    public function dishes()
    {
        $restaurant = auth()->user()->restaurant;
        if (!$restaurant) return collect();

        $since = now()->subDays((int) $this->period);

        $dishes = Dish::where('restaurant_id', $restaurant->id)
            ->active()
            ->with('ingredients')
            ->withCount(['orderItems as total_sold' => function ($q) use ($since) {
                $q->whereHas('order', fn($o) => $o->whereNotNull('paid_at')->where('created_at', '>=', $since));
            }])
            ->withSum(['orderItems as total_revenue' => function ($q) use ($since) {
                $q->whereHas('order', fn($o) => $o->whereNotNull('paid_at')->where('created_at', '>=', $since));
            }], 'total_price')
            ->get()
            ->map(function ($dish) {
                $cost = $dish->ingredients->sum(fn($i) => ($i->pivot->quantity ?? 0) * ($i->unit_cost ?? 0));
                $margin = $dish->price > 0 ? round((($dish->price - $cost) / $dish->price) * 100, 1) : 0;
                $totalProfit = ($dish->price - $cost) * ($dish->total_sold ?? 0);

                $dish->food_cost = (int) $cost;
                $dish->margin_percent = $margin;
                $dish->total_profit = (int) $totalProfit;
                $dish->total_sold = (int) ($dish->total_sold ?? 0);
                $dish->total_revenue = (int) ($dish->total_revenue ?? 0);

                return $dish;
            });

        return match ($this->sort) {
            'margin_desc' => $dishes->sortByDesc('margin_percent')->values(),
            'margin_asc' => $dishes->sortBy('margin_percent')->values(),
            'profit_desc' => $dishes->sortByDesc('total_profit')->values(),
            'sold_desc' => $dishes->sortByDesc('total_sold')->values(),
            'cost_desc' => $dishes->sortByDesc('food_cost')->values(),
            default => $dishes->sortByDesc('margin_percent')->values(),
        };
    }

    #[Computed]
    public function totals(): array
    {
        $dishes = $this->dishes;
        $totalRevenue = $dishes->sum('total_revenue');
        $totalCost = $dishes->sum(fn($d) => $d->food_cost * $d->total_sold);
        $avgMargin = $dishes->where('total_sold', '>', 0)->avg('margin_percent') ?? 0;

        return [
            'total_revenue' => $totalRevenue,
            'total_food_cost' => $totalCost,
            'total_profit' => $totalRevenue - $totalCost,
            'avg_margin' => round($avgMargin, 1),
            'dishes_without_cost' => $dishes->where('food_cost', 0)->count(),
        ];
    }

    public function render()
    {
        return view('livewire.restaurant.dish-profitability')
            ->layout('components.layouts.admin-restaurant', [
                'title' => 'Rentabilité des plats',
            ]);
    }
}
