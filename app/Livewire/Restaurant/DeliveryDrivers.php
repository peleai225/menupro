<?php

namespace App\Livewire\Restaurant;

use App\Models\DeliveryDriver;
use Illuminate\Support\Str;
use Livewire\Attributes\Rule;
use Livewire\Component;

class DeliveryDrivers extends Component
{
    #[Rule('required|string|max:100')]
    public string $name = '';

    #[Rule('required|string|max:20')]
    public string $phone = '';

    #[Rule('required|string|max:50')]
    public string $vehicle_type = 'moto';

    #[Rule('nullable|string|max:20')]
    public ?string $vehicle_plate = null;

    public bool $showModal = false;
    public ?int $editingId = null;

    public function getDriversProperty()
    {
        return DeliveryDriver::where('restaurant_id', auth()->user()->restaurant_id)
            ->orderByDesc('is_active')
            ->orderBy('name')
            ->get();
    }

    public function create(): void
    {
        $this->reset(['name', 'phone', 'vehicle_type', 'vehicle_plate', 'editingId']);
        $this->vehicle_type = 'moto';
        $this->showModal = true;
    }

    public function edit(int $id): void
    {
        $driver = DeliveryDriver::where('restaurant_id', auth()->user()->restaurant_id)->findOrFail($id);
        $this->editingId = $driver->id;
        $this->name = $driver->name;
        $this->phone = $driver->phone;
        $this->vehicle_type = $driver->vehicle_type;
        $this->vehicle_plate = $driver->vehicle_plate;
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'restaurant_id' => auth()->user()->restaurant_id,
            'name' => $this->name,
            'phone' => $this->phone,
            'vehicle_type' => $this->vehicle_type,
            'vehicle_plate' => $this->vehicle_plate,
        ];

        if ($this->editingId) {
            DeliveryDriver::where('restaurant_id', auth()->user()->restaurant_id)
                ->where('id', $this->editingId)
                ->update($data);
        } else {
            $data['token'] = Str::random(64);
            DeliveryDriver::create($data);
        }

        $this->showModal = false;
        $this->reset(['name', 'phone', 'vehicle_type', 'vehicle_plate', 'editingId']);
    }

    public function toggleActive(int $id): void
    {
        $driver = DeliveryDriver::where('restaurant_id', auth()->user()->restaurant_id)->findOrFail($id);
        $driver->update(['is_active' => !$driver->is_active]);
    }

    public function regenerateToken(int $id): void
    {
        $driver = DeliveryDriver::where('restaurant_id', auth()->user()->restaurant_id)->findOrFail($id);
        $driver->update(['token' => Str::random(64)]);
    }

    public function render()
    {
        return view('livewire.restaurant.delivery-drivers')
            ->layout('components.layouts.admin-restaurant', [
                'title' => 'Livreurs',
                'restaurant' => auth()->user()->restaurant,
                'subscription' => auth()->user()->restaurant?->activeSubscription,
            ]);
    }
}
