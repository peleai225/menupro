<?php

namespace App\Livewire\Restaurant;

use App\Models\Ingredient;
use App\Services\StockManager;
use Livewire\Attributes\Computed;
use Livewire\Component;

class BulkStockUpdate extends Component
{
    public array $quantities = [];
    public string $reason = 'Inventaire physique';
    public int $updatedCount = 0;
    public bool $showSuccess = false;

    public function mount(): void
    {
        $this->loadIngredients();
    }

    private function loadIngredients(): void
    {
        $restaurant = auth()->user()->restaurant;
        $ingredients = Ingredient::where('restaurant_id', $restaurant->id)
            ->active()
            ->with('category')
            ->orderBy('name')
            ->get();

        $this->quantities = [];
        foreach ($ingredients as $ingredient) {
            $this->quantities[$ingredient->id] = [
                'name' => $ingredient->name,
                'unit' => $ingredient->unit->shortLabel(),
                'category' => $ingredient->category?->name ?? 'Sans catégorie',
                'current' => (float) $ingredient->current_quantity,
                'min' => (float) $ingredient->min_quantity,
                'new_qty' => (float) $ingredient->current_quantity,
                'changed' => false,
            ];
        }
    }

    public function updatedQuantities($value, $key): void
    {
        // key format: "id.new_qty"
        $parts = explode('.', $key);
        if (count($parts) === 2 && $parts[1] === 'new_qty') {
            $id = $parts[0];
            if (isset($this->quantities[$id])) {
                $newQty = (float) ($value ?? 0);
                $this->quantities[$id]['changed'] = $newQty != $this->quantities[$id]['current'];
            }
        }
    }

    #[Computed]
    public function changedCount(): int
    {
        return collect($this->quantities)->where('changed', true)->count();
    }

    public function saveAll(StockManager $stockManager): void
    {
        $restaurant = auth()->user()->restaurant;
        $stockManager->forRestaurant($restaurant);
        $count = 0;

        foreach ($this->quantities as $id => $data) {
            if (!$data['changed']) {
                continue;
            }

            $ingredient = Ingredient::where('id', $id)
                ->where('restaurant_id', $restaurant->id)
                ->first();

            if (!$ingredient) {
                continue;
            }

            $newQty = max(0, (float) $data['new_qty']);

            if ($newQty != (float) $ingredient->current_quantity) {
                $stockManager->adjust($ingredient, $newQty, $this->reason);
                $count++;
            }
        }

        $this->updatedCount = $count;
        $this->showSuccess = $count > 0;
        $this->loadIngredients();
    }

    public function render()
    {
        $restaurant = auth()->user()->restaurant;
        $subscription = $restaurant?->activeSubscription;

        return view('livewire.restaurant.bulk-stock-update')
            ->layout('components.layouts.admin-restaurant', [
                'title' => 'Mise à jour stock en masse',
                'restaurant' => $restaurant,
                'subscription' => $subscription,
            ]);
    }
}
