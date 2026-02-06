<?php

namespace App\Http\Controllers\Restaurant;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Notifications\ReservationConfirmedNotification;
use App\Notifications\ReservationCancelledNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\View\View;

class ReservationController extends Controller
{
    /**
     * Display a listing of reservations.
     */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Reservation::class);
        
        $restaurant = auth()->user()->restaurant;
        
        $query = $restaurant->reservations()->with('restaurant');

        // Filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date')) {
            $query->whereDate('reservation_date', $request->date);
        }

        // Default: show upcoming reservations
        if (!$request->filled('status') && !$request->filled('date')) {
            $query->upcoming();
        }

        $reservations = $query->orderBy('reservation_date', 'asc')->paginate(20)->withQueryString();

        // Stats
        $stats = [
            'pending' => $restaurant->reservations()->pending()->count(),
            'confirmed' => $restaurant->reservations()->confirmed()->count(),
            'today' => $restaurant->reservations()->whereDate('reservation_date', today())->count(),
            'upcoming' => $restaurant->reservations()->upcoming()->count(),
        ];

        return view('pages.restaurant.reservations', compact('reservations', 'stats'));
    }

    /**
     * Update reservation status.
     */
    public function updateStatus(Request $request, Reservation $reservation): RedirectResponse
    {
        $this->authorize('update', $reservation);

        $request->validate([
            'status' => ['required', 'in:pending,confirmed,cancelled,completed'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'cancellation_reason' => ['nullable', 'string', 'max:500'],
        ]);

        $oldStatus = $reservation->status;
        $newStatus = $request->status;

        $reservation->update([
            'status' => $newStatus,
            'notes' => $request->notes ?? $reservation->notes,
        ]);

        // Send notification to customer based on status change
        $this->sendCustomerNotification($reservation, $oldStatus, $newStatus, $request->cancellation_reason);

        $statusLabels = [
            'pending' => 'en attente',
            'confirmed' => 'confirmée',
            'cancelled' => 'annulée',
            'completed' => 'complétée',
        ];

        return back()->with('success', "Réservation marquée comme {$statusLabels[$newStatus]}. Le client a été notifié par email.");
    }

    /**
     * Send notification to customer based on status change.
     */
    private function sendCustomerNotification(Reservation $reservation, string $oldStatus, string $newStatus, ?string $cancellationReason = null): void
    {
        if ($oldStatus === $newStatus) {
            return;
        }

        try {
            if ($newStatus === 'confirmed') {
                Notification::route('mail', $reservation->customer_email)
                    ->notify(new ReservationConfirmedNotification($reservation));
            } elseif ($newStatus === 'cancelled') {
                Notification::route('mail', $reservation->customer_email)
                    ->notify(new ReservationCancelledNotification($reservation, $cancellationReason));
            }
        } catch (\Exception $e) {
            \Log::warning('Failed to send reservation status notification: ' . $e->getMessage());
        }
    }

    /**
     * Show a reservation.
     */
    public function show(Reservation $reservation): View
    {
        $this->authorize('view', $reservation);

        return view('pages.restaurant.reservation-show', compact('reservation'));
    }
}
