<?php

namespace App\Livewire\Restaurant;

use App\Models\Category;
use App\Models\Dish;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class Dishes extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $category = '';

    #[Url]
    public string $status = '';

    public string $sortField = 'created_at';
    public string $sortDirection = 'desc';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingCategory(): void
    {
        $this->resetPage();
    }

    public function updatingStatus(): void
    {
        $this->resetPage();
    }

    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    #[Computed]
    public function categories()
    {
        return Category::where('restaurant_id', auth()->user()->restaurant_id)
            ->ordered()
            ->get();
    }

    #[Computed]
    public function dishes()
    {
        return Dish::where('restaurant_id', auth()->user()->restaurant_id)
            ->with('category')
            ->when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->when($this->category, fn($q) => $q->where('category_id', $this->category))
            ->when($this->status === 'available', fn($q) => $q->where('is_available', true))
            ->when($this->status === 'unavailable', fn($q) => $q->where('is_available', false))
            ->when($this->status === 'featured', fn($q) => $q->where('is_featured', true))
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate(12);
    }

    public function toggleAvailability(int $id): void
    {
        $dish = Dish::findOrFail($id);
        $dish->update(['is_available' => !$dish->is_available]);
        session()->flash('message', $dish->is_available ? 'Plat disponible' : 'Plat indisponible');
    }

    public function toggleFeatured(int $id): void
    {
        $dish = Dish::findOrFail($id);
        $dish->update(['is_featured' => !$dish->is_featured]);
    }

    public function delete(int $id): void
    {
        $dish = Dish::findOrFail($id);
        
        // Check if dish has orders
        if ($dish->orderItems()->exists()) {
            session()->flash('error', 'Impossible de supprimer un plat ayant des commandes.');
            return;
        }

        // Delete image if exists
        if ($dish->image) {
            \Storage::disk('public')->delete($dish->image);
        }

        $dish->delete();
        session()->flash('success', 'Plat supprimé avec succès.');
    }

    public function render()
    {
        $restaurant = auth()->user()->restaurant;
        $subscription = $restaurant?->activeSubscription;
        
        return view('livewire.restaurant.dishes')
            ->layout('components.layouts.admin-restaurant', [
                'title' => 'Plats',
                'restaurant' => $restaurant,
                'subscription' => $subscription,
            ]);
    }
}

