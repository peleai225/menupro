<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Notifications\NewReservationNotification;
use App\Notifications\ReservationReceivedNotification;
use App\Models\Restaurant;
use App\Models\Reservation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;

class ReservationController extends Controller
{
    /**
     * Store a new reservation.
     */
    public function store(Request $request, string $slug): RedirectResponse
    {
        $restaurant = Restaurant::where('slug', $slug)
            ->where('status', \App\Enums\RestaurantStatus::ACTIVE)
            ->firstOrFail();

        $validated = $request->validate([
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_email' => ['required', 'email', 'max:255'],
            'customer_phone' => ['required', 'string', 'max:20'],
            'number_of_guests' => ['required', 'integer', 'min:1', 'max:50'],
            'reservation_date' => ['required', 'date', 'after:now'],
            'reservation_time' => ['required', 'date_format:H:i'],
            'special_requests' => ['nullable', 'string', 'max:1000'],
        ], [
            'customer_name.required' => 'Le nom est obligatoire.',
            'customer_email.required' => 'L\'email est obligatoire.',
            'customer_email.email' => 'L\'email n\'est pas valide.',
            'customer_phone.required' => 'Le téléphone est obligatoire.',
            'number_of_guests.required' => 'Le nombre de personnes est obligatoire.',
            'number_of_guests.min' => 'Le nombre de personnes doit être d\'au moins 1.',
            'reservation_date.required' => 'La date est obligatoire.',
            'reservation_date.after' => 'La date doit être dans le futur.',
            'reservation_time.required' => 'L\'heure est obligatoire.',
        ]);

        // Combine date and time
        $reservationDateTime = \Carbon\Carbon::parse($validated['reservation_date'] . ' ' . $validated['reservation_time']);

        // Check if restaurant will be open at reservation time
        if (!$this->isRestaurantOpenAt($restaurant, $reservationDateTime)) {
            return back()->with('error', 'Le restaurant sera fermé à cette date et heure. Veuillez choisir un autre créneau.');
        }

        // Create reservation
        $reservation = Reservation::create([
            'restaurant_id' => $restaurant->id,
            'customer_name' => $validated['customer_name'],
            'customer_email' => $validated['customer_email'],
            'customer_phone' => $validated['customer_phone'],
            'number_of_guests' => $validated['number_of_guests'],
            'reservation_date' => $reservationDateTime,
            'special_requests' => $validated['special_requests'] ?? null,
            'status' => 'pending',
        ]);

        // Send notification to restaurant owner (email + database for push)
        if ($restaurant->owner) {
            $restaurant->owner->notify(new NewReservationNotification($reservation));
        }

        // Send confirmation email to customer
        try {
            Notification::route('mail', $validated['customer_email'])
                ->notify(new ReservationReceivedNotification($reservation));
        } catch (\Exception $e) {
            // Log error but don't fail the reservation
            \Log::warning('Failed to send reservation confirmation email: ' . $e->getMessage());
        }

        return redirect()->route('r.menu', $slug)
            ->with('success', 'Votre réservation a été enregistrée ! Vous recevrez une confirmation par email.');
    }

    /**
     * Check if restaurant is open at a specific date and time
     */
    private function isRestaurantOpenAt(Restaurant $restaurant, \Carbon\Carbon $datetime): bool
    {
        if (!$restaurant->opening_hours) {
            return true; // Default to open if no hours set
        }

        $datetime->setTimezone($restaurant->timezone ?? 'Africa/Abidjan');
        $dayOfWeek = strtolower($datetime->format('l'));
        
        $dayHours = $restaurant->opening_hours[$dayOfWeek] ?? null;
        
        // Check if restaurant is closed for this day
        if (!$dayHours || !($dayHours['is_open'] ?? false)) {
            return false;
        }

        $openTime = $dayHours['open'] ?? '00:00';
        $closeTime = $dayHours['close'] ?? '23:59';

        $reservationTime = $datetime->format('H:i');

        return $reservationTime >= $openTime && $reservationTime <= $closeTime;
    }
}
