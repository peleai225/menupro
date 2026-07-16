<?php

namespace App\Livewire\Restaurant;

use App\Models\Reservation;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class Reservations extends Component
{
    use WithPagination;

    #[Url]
    public string $status = '';

    #[Url]
    public string $date = '';

    public int $lastReservationId = 0;
    public bool $hasNewReservation = false;

    public function mount(): void
    {
        $restaurantId = auth()->user()->restaurant_id;
        $this->lastReservationId = Reservation::where('restaurant_id', $restaurantId)->max('id') ?? 0;
    }

    public function updatingStatus(): void
    {
        $this->resetPage();
    }

    public function updatingDate(): void
    {
        $this->resetPage();
    }

    public function checkNewReservations(): void
    {
        $restaurantId = auth()->user()->restaurant_id;
        $latestId = Reservation::where('restaurant_id', $restaurantId)->max('id') ?? 0;

        if ($latestId > $this->lastReservationId && $this->lastReservationId > 0) {
            $this->hasNewReservation = true;
        }

        $this->lastReservationId = $latestId;
    }

    public function resetNewBadge(): void
    {
        $this->hasNewReservation = false;
    }

    public function confirmReservation(int $reservationId): void
    {
        $restaurant = auth()->user()->restaurant;
        $reservation = Reservation::where('restaurant_id', $restaurant->id)->findOrFail($reservationId);
        $reservation->update(['status' => 'confirmed']);

        try {
            if (!empty($reservation->customer_email)) {
                \Illuminate\Support\Facades\Notification::route('mail', $reservation->customer_email)
                    ->notify(new \App\Notifications\ReservationConfirmedNotification($reservation));
            }
        } catch (\Exception $e) {
            \Log::warning('Failed to send reservation confirmed notification: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $restaurant = auth()->user()->restaurant;

        $query = $restaurant->reservations()->with('restaurant');

        if ($this->status) {
            $query->where('status', $this->status);
        }

        if ($this->date) {
            $query->whereDate('reservation_date', $this->date);
        }

        $reservations = $query->orderBy('reservation_date', 'desc')->paginate(20);

        $stats = [
            'pending'   => $restaurant->reservations()->pending()->count(),
            'confirmed' => $restaurant->reservations()->confirmed()->count(),
            'today'     => $restaurant->reservations()->whereDate('reservation_date', today())->count(),
            'upcoming'  => $restaurant->reservations()->upcoming()->count(),
        ];

        return view('livewire.restaurant.reservations', compact('reservations', 'stats'))
            ->layout('components.layouts.admin-restaurant', ['title' => 'Réservations']);
    }
}
