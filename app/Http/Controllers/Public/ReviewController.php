<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Review;
use App\Models\Restaurant;
use App\Notifications\NewReviewNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReviewController extends Controller
{
    /**
     * Show review form for an order.
     */
    public function create(string $slug, Order $order): View
    {
        $restaurant = Restaurant::where('slug', $slug)->firstOrFail();

        // Verify order belongs to restaurant
        if ($order->restaurant_id !== $restaurant->id) {
            abort(404);
        }

        // Check if order is completed
        if ($order->status->value !== 'completed') {
            return redirect()
                ->route('r.order.status', [$slug, $order])
                ->with('error', 'Vous ne pouvez laisser un avis que pour une commande terminée.');
        }

        // Check if review already exists
        if ($order->review) {
            return redirect()
                ->route('r.order.status', [$slug, $order])
                ->with('info', 'Vous avez déjà laissé un avis pour cette commande.');
        }

        return view('pages.restaurant-public.review-form', compact(
            'restaurant',
            'order'
        ));
    }

    /**
     * Store a new review.
     */
    public function store(Request $request, string $slug, Order $order): RedirectResponse
    {
        $restaurant = Restaurant::where('slug', $slug)->firstOrFail();

        // Verify order belongs to restaurant
        if ($order->restaurant_id !== $restaurant->id) {
            abort(404);
        }

        // Check if order is completed
        if ($order->status->value !== 'completed') {
            return back()->with('error', 'Vous ne pouvez laisser un avis que pour une commande terminée.');
        }

        // Check if review already exists
        if ($order->review) {
            return back()->with('error', 'Vous avez déjà laissé un avis pour cette commande.');
        }

        // Validate request
        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'required|email|max:255',
        ]);

        // Verify customer email matches order
        if ($validated['customer_email'] !== $order->customer_email) {
            return back()->with('error', 'L\'adresse email ne correspond pas à celle de la commande.');
        }

        try {
            // Create review
            $review = Review::create([
                'restaurant_id' => $restaurant->id,
                'order_id' => $order->id,
                'customer_name' => $validated['customer_name'],
                'customer_email' => $validated['customer_email'],
                'rating' => $validated['rating'],
                'comment' => $validated['comment'] ?? null,
                'is_approved' => false, // Require approval
                'is_visible' => false,
            ]);

            // Notify restaurant users
            $restaurant->users->each(function ($user) use ($review) {
                $user->notify(new NewReviewNotification($review));
            });

            return redirect()
                ->route('r.order.status', [$slug, $order])
                ->with('success', 'Merci pour votre avis ! Il sera publié après modération.');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Une erreur est survenue : ' . $e->getMessage());
        }
    }
}

