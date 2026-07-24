<?php

namespace App\Livewire\Restaurant;

use Livewire\Component;
use App\Models\Waiter;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class Waiters extends Component
{
    public string $name       = '';
    public string $pin        = '';
    public string $pinConfirm = '';
    public ?int   $spaceId    = null;
    public bool   $isActive   = true;
    public ?int   $editingId  = null;

    protected function rules(): array
    {
        $pinRule = $this->editingId
            ? 'nullable|digits:4'
            : 'required|digits:4|same:pinConfirm';

        return [
            'name'       => 'required|string|max:80',
            'pin'        => $pinRule,
            'pinConfirm' => 'nullable|digits:4',
            'spaceId'    => ['nullable', Rule::exists('restaurant_spaces', 'id')->where('restaurant_id', $this->restaurant->id)],
            'isActive'   => 'boolean',
        ];
    }

    public function getRestaurantProperty()
    {
        return Auth::user()->restaurant;
    }

    public function getWaitersProperty()
    {
        return $this->restaurant->waiters()->with('space')->get();
    }

    public function getSpacesProperty()
    {
        return $this->restaurant->spaces()->active()->get();
    }

    public function save(): void
    {
        $this->validate();

        if ($this->editingId) {
            $waiter = Waiter::where('id', $this->editingId)
                ->where('restaurant_id', $this->restaurant->id)
                ->firstOrFail();

            $waiter->name      = $this->name;
            $waiter->space_id  = $this->spaceId;
            $waiter->is_active = $this->isActive;

            if ($this->pin) {
                $waiter->setPin($this->pin);
            }

            $waiter->save();
            session()->flash('success', 'Serveur mis à jour.');
        } else {
            $waiter = new Waiter([
                'restaurant_id' => $this->restaurant->id,
                'space_id'      => $this->spaceId,
                'name'          => $this->name,
                'is_active'     => $this->isActive,
            ]);
            $waiter->setPin($this->pin);
            $waiter->save();

            session()->flash('success', 'Serveur créé.');
        }

        $this->reset(['name', 'pin', 'pinConfirm', 'spaceId', 'editingId']);
        $this->isActive = true;
    }

    public function edit(int $id): void
    {
        $waiter = Waiter::where('id', $id)
            ->where('restaurant_id', $this->restaurant->id)
            ->firstOrFail();

        $this->editingId  = $waiter->id;
        $this->name       = $waiter->name;
        $this->spaceId    = $waiter->space_id;
        $this->isActive   = $waiter->is_active;
        $this->pin        = '';
        $this->pinConfirm = '';
    }

    public function delete(int $id): void
    {
        Waiter::where('id', $id)
            ->where('restaurant_id', $this->restaurant->id)
            ->delete();

        session()->flash('success', 'Serveur supprimé.');
    }

    public function cancelEdit(): void
    {
        $this->reset(['name', 'pin', 'pinConfirm', 'spaceId', 'editingId']);
        $this->isActive = true;
    }

    public function render()
    {
        return view('livewire.restaurant.waiters')
            ->layout('components.layouts.admin-restaurant', ['title' => 'Serveurs']);
    }
}
