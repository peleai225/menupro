<?php

namespace App\Livewire\Restaurant;

use Livewire\Component;
use App\Models\RestaurantSpace;
use Illuminate\Support\Facades\Auth;

class Spaces extends Component
{
    public string $name = '';
    public string $color = '#6366f1';
    public string $description = '';
    public bool $is_active = true;
    public ?int $editingId = null;

    protected function rules(): array
    {
        return [
            'name'        => 'required|string|max:50',
            'color'       => 'required|string|regex:/^#[0-9a-fA-F]{6}$/',
            'description' => 'nullable|string|max:200',
            'is_active'   => 'boolean',
        ];
    }

    public function getRestaurantProperty()
    {
        return Auth::user()->restaurant;
    }

    public function getSpacesProperty()
    {
        return $this->restaurant->spaces()->orderBy('sort_order')->get();
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'name'        => $this->name,
            'color'       => $this->color,
            'description' => $this->description,
            'is_active'   => $this->is_active,
        ];

        if ($this->editingId) {
            $space = RestaurantSpace::where('id', $this->editingId)
                ->where('restaurant_id', $this->restaurant->id)
                ->firstOrFail();
            $space->update($data);
            session()->flash('success', 'Espace mis à jour.');
        } else {
            $data['restaurant_id'] = $this->restaurant->id;
            $data['sort_order']    = $this->restaurant->spaces()->max('sort_order') + 1;
            RestaurantSpace::create($data);
            session()->flash('success', 'Espace créé.');
        }

        $this->reset(['name', 'color', 'description', 'is_active', 'editingId']);
        $this->color = '#6366f1';
    }

    public function edit(int $id): void
    {
        $space = RestaurantSpace::where('id', $id)
            ->where('restaurant_id', $this->restaurant->id)
            ->firstOrFail();

        $this->editingId   = $space->id;
        $this->name        = $space->name;
        $this->color       = $space->color;
        $this->description = $space->description ?? '';
        $this->is_active   = $space->is_active;
    }

    public function delete(int $id): void
    {
        RestaurantSpace::where('id', $id)
            ->where('restaurant_id', $this->restaurant->id)
            ->delete();
        session()->flash('success', 'Espace supprimé.');
    }

    public function cancelEdit(): void
    {
        $this->reset(['name', 'color', 'description', 'is_active', 'editingId']);
        $this->color = '#6366f1';
    }

    public function render()
    {
        return view('livewire.restaurant.spaces')
            ->layout('components.layouts.admin-restaurant', ['title' => 'Espaces']);
    }
}
