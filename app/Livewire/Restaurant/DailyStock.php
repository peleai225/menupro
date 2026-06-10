<?php

namespace App\Livewire\Restaurant;

use App\Models\Category;
use App\Models\Dish;
use Livewire\Attributes\Computed;
use Livewire\Component;

class DailyStock extends Component
{
    public array $quantities = [];
    public ?int $filterCategory = null;
    public string $search = '';
    public bool $showOnlyTracked = false;

    public function mount(): void
    {
        $this->loadQuantities();
    }

    #[Computed]
    public function categories()
    {
        return Category::where('restaurant_id', auth()->user()->restaurant_id)
            ->where('is_active', true)
            ->ordered()
            ->get();
    }

    #[Computed]
    public function dishes()
    {
        $query = Dish::where('restaurant_id', auth()->user()->restaurant_id)
            ->where('is_active', true)
            ->with('category');

        if ($this->filterCategory) {
            $query->where('category_id', $this->filterCategory);
        }

        if ($this->search) {
            $query->where('name', 'like', "%{$this->search}%");
        }

        if ($this->showOnlyTracked) {
            $query->where('track_stock', true);
        }

        return $query->ordered()->get();
    }

    #[Computed]
    public function stats()
    {
        $restaurant_id = auth()->user()->restaurant_id;
        $tracked = Dish::where('restaurant_id', $restaurant_id)->where('track_stock', true);

        return [
            'total_dishes' => Dish::where('restaurant_id', $restaurant_id)->where('is_active', true)->count(),
            'tracked' => (clone $tracked)->count(),
            'out_of_stock' => (clone $tracked)->where('stock_quantity', '<=', 0)->count(),
            'low_stock' => (clone $tracked)->where('stock_quantity', '>', 0)->where('stock_quantity', '<=', 5)->count(),
        ];
    }

    public function loadQuantities(): void
    {
        $dishes = Dish::where('restaurant_id', auth()->user()->restaurant_id)
            ->where('is_active', true)
            ->get(['id', 'stock_quantity', 'track_stock']);

        foreach ($dishes as $dish) {
            $this->quantities[$dish->id] = [
                'qty' => $dish->stock_quantity ?? 0,
                'track' => $dish->track_stock,
            ];
        }
    }

    public function toggleTrack(int $dishId): void
    {
        $dish = Dish::where('id', $dishId)
            ->where('restaurant_id', auth()->user()->restaurant_id)
            ->firstOrFail();

        $dish->track_stock = !$dish->track_stock;
        $dish->save();

        $this->quantities[$dishId]['track'] = $dish->track_stock;
    }

    public function updateQuantity(int $dishId, $quantity): void
    {
        $quantity = max(0, (int) $quantity);

        $dish = Dish::where('id', $dishId)
            ->where('restaurant_id', auth()->user()->restaurant_id)
            ->firstOrFail();

        $dish->stock_quantity = $quantity;
        if (!$dish->track_stock) {
            $dish->track_stock = true;
        }
        $dish->save();

        $this->quantities[$dishId] = [
            'qty' => $quantity,
            'track' => true,
        ];
    }

    public function quickSet(int $dishId, int $amount): void
    {
        $this->updateQuantity($dishId, $amount);
    }

    public function resetAll(): void
    {
        Dish::where('restaurant_id', auth()->user()->restaurant_id)
            ->where('track_stock', true)
            ->update(['stock_quantity' => 0]);

        $this->loadQuantities();
        session()->flash('stock_success', 'Tous les stocks ont été remis à zéro.');
    }

    public function saveAll(): void
    {
        $restaurantId = auth()->user()->restaurant_id;

        foreach ($this->quantities as $dishId => $data) {
            Dish::where('id', $dishId)
                ->where('restaurant_id', $restaurantId)
                ->update([
                    'track_stock' => $data['track'],
                    'stock_quantity' => max(0, (int) $data['qty']),
                ]);
        }

        session()->flash('stock_success', 'Stock journalier mis à jour avec succès.');
    }

    public function render()
    {
        $restaurant = auth()->user()->restaurant;
        $subscription = $restaurant?->activeSubscription;

        return view('livewire.restaurant.daily-stock')
            ->layout('components.layouts.admin-restaurant', [
                'title' => 'Stock Journalier',
                'restaurant' => $restaurant,
                'subscription' => $subscription,
            ]);
    }
}
