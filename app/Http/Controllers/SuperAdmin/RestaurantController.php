<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Enums\RestaurantStatus;
use App\Enums\SubscriptionStatus;
use App\Exports\RestaurantsExport;
use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Plan;
use App\Models\User;
use App\Models\Restaurant;
use App\Models\Subscription;
use App\Notifications\RestaurantRejectedNotification;
use App\Notifications\RestaurantValidatedNotification;
use App\Services\PlatformMetrics;
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

        // GPS filter — restaurants sur la marketplace de livraison sans coordonnées valides
        if ($request->get('gps') === 'missing') {
            $query->where('is_on_platform', true)
                  ->where(function ($q) {
                      $q->whereNull('latitude')->orWhereNull('longitude');
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

        // Stats — hors comptes démo (et supprimés, via SoftDeletes)
        $metrics = app(PlatformMetrics::class);
        $stats = [
            'total' => $metrics->restaurants()->count(),
            // Actifs = abonnement payant ou essai encore valide (définition unique PlatformMetrics)
            'active' => $metrics->activeRestaurantsCount(),
            // Compteur de l'onglet de filtre « status = active » (champ restaurants.status)
            'status_active' => $metrics->restaurants()->where('status', RestaurantStatus::ACTIVE)->count(),
            'pending' => $metrics->restaurants()->where('status', RestaurantStatus::PENDING)->count(),
            'suspended' => $metrics->restaurants()->where('status', RestaurantStatus::SUSPENDED)->count(),
            'expired' => $metrics->restaurants()->where('status', RestaurantStatus::EXPIRED)->count(),
            'pending_verification' => Restaurant::whereNotNull('rccm')
                ->whereNotNull('rccm_document_path')
                ->whereNull('verified_at')
                ->count(),
            'gps_missing' => Restaurant::where('is_on_platform', true)
                ->where(function ($q) {
                    $q->whereNull('latitude')->orWhereNull('longitude');
                })
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
            // CA : commandes payées non annulées/remboursées (même définition que PlatformMetrics)
            'total_revenue' => $restaurant->orders()->withoutGlobalScope('restaurant')->revenue()->sum('total'),
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
            'current_plan_id'   => ['nullable', 'exists:plans,id'],
            'type'              => ['nullable', 'string', 'in:restaurant,bar,brasserie,maquis,traiteur,cafe'],
            'platform_category' => ['nullable', 'string', 'max:50'],
        ]);

        $data = [];

        if ($request->filled('current_plan_id')) {
            $data['current_plan_id'] = $request->current_plan_id;
        }

        if ($request->filled('type')) {
            $data['type'] = $request->type;
        }

        if ($request->has('platform_category')) {
            $data['platform_category'] = $request->platform_category ?: null;
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

        // Démarrer l'essai gelé à l'inscription (starts_at/ends_at étaient nuls).
        $trial = $restaurant->subscriptions()
            ->where('is_trial', true)
            ->whereNull('starts_at')
            ->latest()
            ->first();

        if ($trial) {
            // Démarre l'essai et clôt tout autre abonnement active/trial resté ouvert
            $trial->activateExclusively([
                'starts_at' => now(),
                'ends_at'   => now()->addDays($trial->trial_days ?? (int) config('plans.trial_days', 30)),
            ]);
        }

        // Activate pending subscription (abonnement payant en attente)
        $subscription = $restaurant->subscriptions()
            ->where('status', SubscriptionStatus::PENDING)
            ->latest()
            ->first();

        if ($subscription) {
            // Devient l'unique abonnement en cours (l'essai éventuel ci-dessus est clôturé)
            $subscription->activateExclusively(['status' => SubscriptionStatus::ACTIVE]);
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

        $amountPaid = 0; // Prolongation manuelle gratuite

        // Create new subscription
        $subscription = Subscription::create([
            'restaurant_id' => $restaurant->id,
            'plan_id' => $plan->id,
            'status' => SubscriptionStatus::ACTIVE,
            'starts_at' => now(),
            'ends_at' => now()->addDays($days),
            'amount_paid' => $amountPaid,
            'payment_method' => 'admin_manual',
            'payment_metadata' => ['reason' => $request->reason, 'admin_id' => auth()->id()],
            // Date d'encaissement seulement si la prolongation est payante
            'paid_at' => $amountPaid > 0 ? now() : null,
        ]);

        // Unique abonnement en cours : clôt les anciens, plan/date de fin à jour.
        $subscription->activateExclusively();

        // Décision explicite du super-admin : lève aussi une suspension/expiration
        $restaurant->update([
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

        ActivityLog::log('impersonation_start', auth()->user(),
            "Super admin impersonne le restaurant {$restaurant->name}",
            ['impersonated_user_id' => $owner->id, 'restaurant_id' => $restaurant->id]
        );

        // Login as the owner
        auth()->login($owner);
        request()->session()->regenerate();

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
     * Toggle demo mode for a restaurant.
     */
    public function toggleDemo(Restaurant $restaurant): RedirectResponse
    {
        $restaurant->update(['is_demo' => !$restaurant->is_demo]);

        $message = $restaurant->is_demo
            ? 'Restaurant marqué comme compte démo.'
            : 'Restaurant retiré du mode démo.';

        return back()->with('success', $message);
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

    /**
     * Stop impersonating and return to super admin.
     */
    public function stopImpersonation(Request $request): RedirectResponse
    {
        $adminId = session()->pull('impersonating_from');
        if (!$adminId) {
            return redirect()->route('super-admin.dashboard')->with('error', 'Aucune impersonation active.');
        }
        $admin = User::find($adminId);
        // Sécurité : on ne revient que vers un compte super-admin
        if (!$admin || !$admin->isSuperAdmin()) {
            return redirect()->route('login')->with('error', 'Impersonation invalide.');
        }
        ActivityLog::log('impersonation_end', $admin, "Fin d'impersonation par super admin", ['restaurant_id' => auth()->user()->restaurant_id ?? null]);
        // Le scope restaurant posé pendant l'impersonation ne doit pas suivre l'admin
        session()->forget('current_restaurant_id');
        auth()->login($admin);
        $request->session()->regenerate();
        return redirect()->route('super-admin.dashboard');
    }

}

