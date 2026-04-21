<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Enums\RestaurantStatus;
use App\Enums\SubscriptionStatus;
use App\Exports\RestaurantsExport;
use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\Restaurant;
use App\Models\Subscription;
use App\Notifications\RestaurantRejectedNotification;
use App\Notifications\RestaurantValidatedNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;

class RestaurantController extends Controller
{
    /**
     * Display a listing of restaurants.
     */
    public function index(Request $request): View
    {
        $query = Restaurant::with(['currentPlan', 'owner']);

        // Filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('plan')) {
            $query->where('current_plan_id', $request->plan);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('city', 'like', "%{$search}%");
            });
        }

        // Verification filter
        if ($request->filled('verification')) {
            switch ($request->verification) {
                case 'verified':
                    $query->whereNotNull('verified_at');
                    break;
                case 'pending_verification':
                    $query->whereNotNull('rccm')
                          ->whereNotNull('rccm_document_path')
                          ->whereNull('verified_at');
                    break;
                case 'no_rccm':
                    $query->where(function ($q) {
                        $q->whereNull('rccm')
                          ->orWhereNull('rccm_document_path');
                    });
                    break;
            }
        }

        $restaurants = $query->latest()->paginate(20)->withQueryString();

        $plans = Plan::active()->ordered()->get();

        // Stats
        $stats = [
            'total' => Restaurant::count(),
            'active' => Restaurant::where('status', RestaurantStatus::ACTIVE)->count(),
            'pending' => Restaurant::where('status', RestaurantStatus::PENDING)->count(),
            'suspended' => Restaurant::where('status', RestaurantStatus::SUSPENDED)->count(),
            'expired' => Restaurant::where('status', RestaurantStatus::EXPIRED)->count(),
            'pending_verification' => Restaurant::whereNotNull('rccm')
                ->whereNotNull('rccm_document_path')
                ->whereNull('verified_at')
                ->count(),
        ];

        return view('pages.super-admin.restaurants', compact('restaurants', 'plans', 'stats'));
    }

    /**
     * Display the specified restaurant.
     */
    public function show(Restaurant $restaurant): View
    {
        $restaurant->load([
            'currentPlan',
            'owner',
            'subscriptions' => fn($q) => $q->with('plan')->latest()->limit(10),
            'categories' => fn($q) => $q->withCount('dishes'),
        ]);

        // Stats
        $stats = [
            'dishes_count' => $restaurant->dishes()->count(),
            'categories_count' => $restaurant->categories()->count(),
            'orders_count' => $restaurant->orders()->count(),
            'total_revenue' => $restaurant->orders()->where('payment_status', 'completed')->sum('total'),
            'recent_orders' => $restaurant->orders()->latest()->limit(5)->get(),
        ];

        $plans = Plan::active()->ordered()->get();

        return view('pages.super-admin.restaurant-show', compact('restaurant', 'stats', 'plans'));
    }

    /**
     * Update the specified restaurant.
     */
    public function update(Request $request, Restaurant $restaurant): RedirectResponse
    {
        $request->validate([
            'current_plan_id' => ['nullable', 'exists:plans,id'],
            'type' => ['nullable', 'string', 'in:restaurant,bar,brasserie,maquis,traiteur,cafe'],
        ]);

        $data = [];

        if ($request->filled('current_plan_id')) {
            $data['current_plan_id'] = $request->current_plan_id;
        }

        if ($request->filled('type')) {
            $data['type'] = $request->type;
        }

        if (!empty($data)) {
            $restaurant->update($data);
            return back()->with('success', 'Restaurant mis à jour avec succès.');
        }

        return back()->with('info', 'Aucune modification effectuée.');
    }

    /**
     * Approve a pending restaurant.
     */
    public function approve(Restaurant $restaurant): RedirectResponse
    {
        if ($restaurant->status !== RestaurantStatus::PENDING) {
            return back()->with('error', 'Ce restaurant n\'est pas en attente de validation.');
        }

        // Activate restaurant using the validate method
        $restaurant->validate();

        // Activate pending subscription
        $subscription = $restaurant->subscriptions()
            ->where('status', SubscriptionStatus::PENDING)
            ->latest()
            ->first();

        if ($subscription) {
            $subscription->update([
                'status' => SubscriptionStatus::ACTIVE,
            ]);

            $restaurant->update([
                'subscription_ends_at' => $subscription->ends_at,
            ]);
        }

        // Notify owner
        if ($restaurant->owner) {
            $restaurant->owner->notify(new RestaurantValidatedNotification($restaurant));
        }

        return back()->with('success', 'Restaurant validé avec succès.');
    }

    /**
     * Reject a pending restaurant.
     */
    public function reject(Request $request, Restaurant $restaurant): RedirectResponse
    {
        if ($restaurant->status !== RestaurantStatus::PENDING) {
            return back()->with('error', 'Ce restaurant n\'est pas en attente de validation.');
        }

        $request->validate([
            'rejection_reason' => ['required', 'string', 'max:1000'],
        ]);

        $restaurant->update([
            'status' => RestaurantStatus::SUSPENDED,
            'suspended_at' => now(),
            'suspension_reason' => 'Rejeté lors de la validation : ' . $request->rejection_reason,
        ]);

        // Notify owner
        if ($restaurant->owner) {
            $restaurant->owner->notify(new RestaurantRejectedNotification($restaurant, $request->rejection_reason));
        }

        return back()->with('success', 'Restaurant rejeté. Le propriétaire a été notifié.');
    }

    /**
     * Suspend a restaurant.
     */
    public function suspend(Request $request, Restaurant $restaurant): RedirectResponse
    {
        $request->validate([
            'reason' => ['required', 'string', 'max:500'],
        ]);

        $restaurant->suspend($request->reason);

        return back()->with('success', 'Restaurant suspendu.');
    }

    /**
     * Reactivate a suspended restaurant.
     */
    public function reactivate(Restaurant $restaurant): RedirectResponse
    {
        if (!in_array($restaurant->status, [RestaurantStatus::SUSPENDED, RestaurantStatus::EXPIRED])) {
            return back()->with('error', 'Ce restaurant ne peut pas être réactivé.');
        }

        $restaurant->validate();

        return back()->with('success', 'Restaurant réactivé.');
    }

    /**
     * Extend subscription manually.
     */
    public function extendSubscription(Request $request, Restaurant $restaurant): RedirectResponse
    {
        $request->validate([
            'plan_id' => ['required', 'exists:plans,id'],
            'days' => ['required', 'integer', 'min:1', 'max:365'],
            'reason' => ['required', 'string', 'max:255'],
        ]);

        $plan = Plan::findOrFail($request->plan_id);
        $days = (int) $request->days;

        // Create new subscription
        Subscription::create([
            'restaurant_id' => $restaurant->id,
            'plan_id' => $plan->id,
            'status' => SubscriptionStatus::ACTIVE,
            'starts_at' => now(),
            'ends_at' => now()->addDays($days),
            'amount_paid' => 0, // Manual extension
            'payment_method' => 'admin_manual',
            'payment_metadata' => ['reason' => $request->reason, 'admin_id' => auth()->id()],
        ]);

        // Update restaurant
        $restaurant->update([
            'current_plan_id' => $plan->id,
            'subscription_ends_at' => now()->addDays($days),
            'status' => RestaurantStatus::ACTIVE,
            'orders_blocked' => false,
        ]);

        return back()->with('success', "Abonnement prolongé de {$days} jours.");
    }

    /**
     * Impersonate a restaurant's owner.
     */
    public function impersonate(Restaurant $restaurant): RedirectResponse
    {
        $owner = $restaurant->owner;

        if (!$owner) {
            return back()->with('error', 'Ce restaurant n\'a pas de propriétaire.');
        }

        // Store original admin ID in session
        session(['impersonating_from' => auth()->id()]);
        
        // Login as the owner
        auth()->login($owner);

        return redirect()->route('restaurant.dashboard')
            ->with('info', "Vous êtes maintenant connecté en tant que {$owner->name}.");
    }

    /**
     * Mark a restaurant as verified (RCCM validated).
     */
    public function verify(Restaurant $restaurant): RedirectResponse
    {
        if (!$restaurant->rccm || !$restaurant->rccm_document_path) {
            return back()->with('error', 'Ce restaurant n\'a pas fourni de documents RCCM à vérifier.');
        }

        $restaurant->update([
            'verified_at' => now(),
            'verified_by' => auth()->id(),
        ]);

        return back()->with('success', 'Restaurant marqué comme vérifié. Le badge "Vérifié" sera affiché sur sa page publique.');
    }

    /**
     * Remove verification from a restaurant.
     */
    public function unverify(Restaurant $restaurant): RedirectResponse
    {
        $restaurant->update([
            'verified_at' => null,
            'verified_by' => null,
        ]);

        return back()->with('success', 'Vérification retirée.');
    }

    /**
     * Export restaurants list to Excel.
     */
    public function export(Request $request)
    {
        $filename = 'restaurants_' . now()->format('Y-m-d') . '.xlsx';

        return Excel::download(new RestaurantsExport($request), $filename);
    }

    /**
     * Delete a restaurant.
     */
    public function destroy(Restaurant $restaurant): RedirectResponse
    {
        $this->authorize('delete', $restaurant);

        $restaurant->delete();

        return redirect()->route('super-admin.restaurants.index')
            ->with('success', 'Restaurant supprimé.');
    }
}

