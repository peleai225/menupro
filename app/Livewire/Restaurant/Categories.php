<?php

namespace App\Livewire\Restaurant;

use App\Models\Category;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Rule;
use Livewire\Component;

class Categories extends Component
{
    public bool $showModal = false;
    public ?int $editingId = null;

    #[Rule('required|string|max:100')]
    public string $name = '';

    #[Rule('nullable|string|max:500')]
    public ?string $description = null;

    public bool $is_active = true;

    #[Computed]
    public function categories()
    {
        return Category::where('restaurant_id', auth()->user()->restaurant_id)
            ->withCount(['dishes' => fn($q) => $q->active()])
            ->ordered()
            ->get();
    }

    public function openModal(?int $id = null): void
    {
        $this->resetValidation();
        $this->reset(['name', 'description', 'is_active']);
        
        if ($id) {
            $category = Category::findOrFail($id);
            $this->editingId = $id;
            $this->name = $category->name;
            $this->description = $category->description;
            $this->is_active = $category->is_active;
        } else {
            $this->editingId = null;
        }

        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->editingId = null;
    }

    public function save(): void
    {
        $this->validate();

        try {
            $data = [
                'name' => $this->name,
                'description' => $this->description,
                'is_active' => $this->is_active,
            ];

            if ($this->editingId) {
                $category = Category::findOrFail($this->editingId);
                $category->update($data);
                session()->flash('success', 'Catégorie mise à jour avec succès.');
            } else {
                // Check quota before creating
                $restaurant = auth()->user()->restaurant;
                $planLimiter = app(\App\Services\PlanLimiter::class)->forRestaurant($restaurant);
                
                try {
                    $planLimiter->validateOrFail('categories');
                } catch (\App\Exceptions\QuotaExceededException $e) {
                    session()->flash('error', $e->getMessage());
                    return;
                }
                
                $data['restaurant_id'] = auth()->user()->restaurant_id;
                $maxOrder = Category::where('restaurant_id', auth()->user()->restaurant_id)->max('sort_order');
                $data['sort_order'] = ($maxOrder ?? 0) + 1;
                Category::create($data);
                session()->flash('success', 'Catégorie créée avec succès.');
            }

            $this->closeModal();
        } catch (\Exception $e) {
            session()->flash('error', 'Une erreur est survenue : ' . $e->getMessage());
        }
    }

    public function delete(int $id): void
    {
        try {
            $category = Category::findOrFail($id);

            // Check if category belongs to user's restaurant
            if ($category->restaurant_id !== auth()->user()->restaurant_id) {
                session()->flash('error', 'Vous n\'avez pas la permission de supprimer cette catégorie.');
                return;
            }

            if ($category->dishes()->exists()) {
                session()->flash('error', 'Impossible de supprimer une catégorie contenant des plats.');
                return;
            }

            if ($category->image_path) {
                Storage::disk('public')->delete($category->image_path);
            }

            $category->delete();
            session()->flash('success', 'Catégorie supprimée avec succès.');
        } catch (\Exception $e) {
            session()->flash('error', 'Une erreur est survenue : ' . $e->getMessage());
        }
    }

    public function toggleActive(int $id): void
    {
        try {
            $category = Category::findOrFail($id);
            
            // Check if category belongs to user's restaurant
            if ($category->restaurant_id !== auth()->user()->restaurant_id) {
                session()->flash('error', 'Vous n\'avez pas la permission de modifier cette catégorie.');
                return;
            }
            
            $category->update(['is_active' => !$category->is_active]);
            session()->flash('success', $category->is_active ? 'Catégorie activée.' : 'Catégorie masquée.');
        } catch (\Exception $e) {
            session()->flash('error', 'Une erreur est survenue : ' . $e->getMessage());
        }
    }

    public function updateOrder(array $orderedIds): void
    {
        foreach ($orderedIds as $order => $id) {
            Category::where('id', $id)
                ->where('restaurant_id', auth()->user()->restaurant_id)
                ->update(['sort_order' => $order]);
        }
    }

    public function render()
    {
        $restaurant = auth()->user()->restaurant;
        $subscription = $restaurant?->activeSubscription;
        
        return view('livewire.restaurant.categories')
            ->layout('components.layouts.admin-restaurant', [
                'title' => 'Catégories',
                'restaurant' => $restaurant,
                'subscription' => $subscription,
            ]);
    }
}

