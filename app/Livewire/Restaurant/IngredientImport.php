<?php

namespace App\Livewire\Restaurant;

use App\Services\IngredientLibrary;
use Livewire\Component;

class IngredientImport extends Component
{
    public array $selected = [];
    public bool $showModal = false;
    public string $importResult = '';

    public function openModal(): void
    {
        $this->showModal = true;
        $this->selected = [];
        $this->importResult = '';
    }

    public function toggleCategory(string $category): void
    {
        $library = IngredientLibrary::getLibrary();

        if (!isset($library[$category])) {
            return;
        }

        $categoryKeys = array_map(
            fn($i) => $category . '::' . $i['name'],
            $library[$category]
        );

        $allSelected = count(array_intersect($categoryKeys, $this->selected)) === count($categoryKeys);

        if ($allSelected) {
            $this->selected = array_values(array_diff($this->selected, $categoryKeys));
        } else {
            $this->selected = array_values(array_unique(array_merge($this->selected, $categoryKeys)));
        }
    }

    public function toggleItem(string $key): void
    {
        if (in_array($key, $this->selected)) {
            $this->selected = array_values(array_diff($this->selected, [$key]));
        } else {
            $this->selected[] = $key;
        }
    }

    public function selectAll(): void
    {
        $library = IngredientLibrary::getLibrary();
        $this->selected = [];

        foreach ($library as $categoryName => $ingredients) {
            foreach ($ingredients as $ingredient) {
                $this->selected[] = $categoryName . '::' . $ingredient['name'];
            }
        }
    }

    public function deselectAll(): void
    {
        $this->selected = [];
    }

    public function import(): void
    {
        if (empty($this->selected)) {
            $this->importResult = 'error:Sélectionnez au moins un ingrédient.';
            return;
        }

        $restaurant = auth()->user()->restaurant;
        $count = IngredientLibrary::importForRestaurant($restaurant, $this->selected);

        if ($count > 0) {
            $this->importResult = "success:{$count} ingrédient(s) importé(s) avec succès.";
            $this->dispatch('ingredients-imported');
        } else {
            $this->importResult = 'info:Tous les ingrédients sélectionnés existent déjà.';
        }
    }

    public function closeModal(): void
    {
        $this->showModal = false;

        if (str_starts_with($this->importResult, 'success:')) {
            $this->redirect(route('restaurant.stock.ingredients.index'), navigate: true);
        }
    }

    public function render()
    {
        return view('livewire.restaurant.ingredient-import', [
            'library' => IngredientLibrary::getLibrary(),
        ]);
    }
}
