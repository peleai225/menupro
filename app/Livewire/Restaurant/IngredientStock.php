<?php

namespace App\Livewire\Restaurant;

use App\Models\Ingredient;
use App\Models\Supplier;
use App\Services\StockManager;
use Livewire\Attributes\Computed;
use Livewire\Component;

class IngredientStock extends Component
{
    public Ingredient $ingredient;

    public string $activeTab = 'entry';

    // Entry form
    public float|string $entryQuantity = '';
    public int|string $entryUnitCost = '';
    public ?int $entrySupplierId = null;
    public string $entryReason = '';

    // Exit form
    public float|string $exitQuantity = '';
    public string $exitReason = '';

    // Adjustment form
    public float|string $adjustQuantity = '';
    public string $adjustReason = '';

    // Waste form
    public float|string $wasteQuantity = '';
    public string $wasteReason = '';

    public function mount(Ingredient $ingredient): void
    {
        $this->ingredient = $ingredient;
        $this->entryUnitCost = $ingredient->unit_cost ?? 0;
        $this->adjustQuantity = (float) $ingredient->current_quantity;
    }

    #[Computed]
    public function suppliers()
    {
        return Supplier::active()->get(['id', 'name']);
    }

    #[Computed]
    public function recentMovements()
    {
        return $this->ingredient->movements()->with('user')->latest()->limit(10)->get();
    }

    public function addStock(StockManager $stockManager): void
    {
        $this->validate([
            'entryQuantity' => ['required', 'numeric', 'min:0.001'],
            'entryUnitCost' => ['nullable', 'integer', 'min:0'],
        ], [
            'entryQuantity.required' => 'La quantité est obligatoire.',
            'entryQuantity.min' => 'La quantité doit être positive.',
        ]);

        $this->authorize('adjustStock', $this->ingredient);

        $stockManager->forRestaurant($this->ingredient->restaurant);
        $stockManager->entry(
            $this->ingredient,
            (float) $this->entryQuantity,
            $this->entryUnitCost !== '' ? (int) $this->entryUnitCost : null,
            [
                'reason' => $this->entryReason ?: 'Entrée de stock',
                'reference_type' => $this->entrySupplierId ? Supplier::class : null,
                'reference_id' => $this->entrySupplierId,
            ]
        );

        $this->ingredient->refresh();
        $this->entryQuantity = '';
        $this->adjustQuantity = (float) $this->ingredient->current_quantity;
        unset($this->recentMovements);

        $this->dispatch('stock-updated');
        session()->flash('stock_success', 'Stock ajouté avec succès.');
    }

    public function removeStock(StockManager $stockManager): void
    {
        $this->validate([
            'exitQuantity' => ['required', 'numeric', 'min:0.001', 'max:' . $this->ingredient->current_quantity],
            'exitReason' => ['required', 'string', 'max:255'],
        ], [
            'exitQuantity.required' => 'La quantité est obligatoire.',
            'exitQuantity.max' => 'La quantité dépasse le stock disponible.',
            'exitReason.required' => 'La raison est obligatoire.',
        ]);

        $this->authorize('adjustStock', $this->ingredient);

        $stockManager->forRestaurant($this->ingredient->restaurant);
        $stockManager->exit($this->ingredient, (float) $this->exitQuantity, $this->exitReason);

        $this->ingredient->refresh();
        $this->exitQuantity = '';
        $this->exitReason = '';
        $this->adjustQuantity = (float) $this->ingredient->current_quantity;
        unset($this->recentMovements);

        $this->dispatch('stock-updated');
        session()->flash('stock_success', 'Stock retiré avec succès.');
    }

    public function adjustStock(StockManager $stockManager): void
    {
        $this->validate([
            'adjustQuantity' => ['required', 'numeric', 'min:0'],
            'adjustReason' => ['required', 'string', 'max:255'],
        ], [
            'adjustQuantity.required' => 'La nouvelle quantité est obligatoire.',
            'adjustReason.required' => 'La raison est obligatoire.',
        ]);

        $this->authorize('adjustStock', $this->ingredient);

        $stockManager->forRestaurant($this->ingredient->restaurant);
        $stockManager->adjust($this->ingredient, (float) $this->adjustQuantity, $this->adjustReason);

        $this->ingredient->refresh();
        $this->adjustReason = '';
        unset($this->recentMovements);

        $this->dispatch('stock-updated');
        session()->flash('stock_success', 'Stock ajusté avec succès.');
    }

    public function recordWaste(StockManager $stockManager): void
    {
        $this->validate([
            'wasteQuantity' => ['required', 'numeric', 'min:0.001', 'max:' . $this->ingredient->current_quantity],
            'wasteReason' => ['nullable', 'string', 'max:255'],
        ], [
            'wasteQuantity.required' => 'La quantité est obligatoire.',
            'wasteQuantity.max' => 'La quantité dépasse le stock disponible.',
        ]);

        $this->authorize('adjustStock', $this->ingredient);

        $stockManager->forRestaurant($this->ingredient->restaurant);
        $stockManager->waste($this->ingredient, (float) $this->wasteQuantity, $this->wasteReason ?: 'Perte/Gaspillage');

        $this->ingredient->refresh();
        $this->wasteQuantity = '';
        $this->wasteReason = '';
        $this->adjustQuantity = (float) $this->ingredient->current_quantity;
        unset($this->recentMovements);

        $this->dispatch('stock-updated');
        session()->flash('stock_success', 'Perte enregistrée.');
    }

    public function render()
    {
        return view('livewire.restaurant.ingredient-stock');
    }
}
