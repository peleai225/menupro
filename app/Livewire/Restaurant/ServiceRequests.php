<?php

namespace App\Livewire\Restaurant;

use App\Models\ServiceRequest;
use Livewire\Component;

class ServiceRequests extends Component
{
    public int $lastRequestId = 0;
    public bool $hasNew = false;
    public bool $audioUnlocked = false;

    public function mount(): void
    {
        $restaurantId = auth()->user()->restaurant_id;
        $this->lastRequestId = ServiceRequest::where('restaurant_id', $restaurantId)
            ->where('status', 'pending')
            ->max('id') ?? 0;
    }

    public function checkNew(): void
    {
        $restaurantId = auth()->user()->restaurant_id;
        $latestId = ServiceRequest::where('restaurant_id', $restaurantId)
            ->where('status', 'pending')
            ->max('id') ?? 0;

        if ($latestId > $this->lastRequestId && $this->lastRequestId > 0) {
            $this->hasNew = true;
            $latest = ServiceRequest::where('restaurant_id', $restaurantId)
                ->where('status', 'pending')
                ->orderByDesc('id')
                ->first();
            $this->dispatch('new-service-request', [
                'table'      => $latest?->table_number ?? '',
                'type_label' => $latest?->typeLabel() ?? '',
            ]);
        }

        $this->lastRequestId = $latestId;
    }

    public function markDone(int $id): void
    {
        $restaurantId = auth()->user()->restaurant_id;
        $request = ServiceRequest::where('restaurant_id', $restaurantId)
            ->where('id', $id)
            ->first();

        if ($request) {
            $request->update(['status' => 'done', 'done_at' => now()]);
            $this->hasNew = false;
        }
    }

    public function render()
    {
        $restaurantId = auth()->user()->restaurant_id;

        $requests = ServiceRequest::where('restaurant_id', $restaurantId)
            ->where('status', 'pending')
            ->orderBy('created_at', 'asc')
            ->get();

        return view('livewire.restaurant.service-requests', compact('requests'));
    }
}
