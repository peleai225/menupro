<?php

namespace App\Http\Controllers\Restaurant;

use App\Enums\SubscriptionStatus;
use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\SubscriptionAddon;
use App\Services\LygosGateway;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SubscriptionController extends Controller
{
    public function __construct(
        protected LygosGateway $lygosGateway
    ) {}

    /**
     * Display subscription info.
     */
    public function index(Request $request): View
    {
        $restaurant = $request->user()->restaurant;
        $currentPlan = $restaurant->currentPlan;
        $subscription = $restaurant->activeSubscription;
        $plans = Plan::active()->ordered()->get();

        // Subscription history
        $history = Subscription::where('restaurant_id', $restaurant->id)
            ->with('plan')
            ->latest()
            ->limit(10)
            ->get();

        return view('pages.restaurant.subscription', compact(
            'restaurant',
            'currentPlan',
            'subscription',
            'plans',
            'history'
        ));
    }

    /**
     * Display available plans for subscription change.
     */
    public function plans(Request $request): View
    {
        $restaurant = $request->user()->restaurant;
        $currentPlan = $restaurant->currentPlan;
        $subscription = $restaurant->activeSubscription;
        $plans = Plan::active()->ordered()->get();

        return view('pages.restaurant.subscription-plans', compact(
            'restaurant',
            'currentPlan',
            'subscription',
            'plans'
        ));
    }

    /**
     * Display subscription invoices.
     */
    public function invoices(Request $request): View
    {
        $restaurant = $request->user()->restaurant;
        
        $invoices = Subscription::where('restaurant_id', $restaurant->id)
            ->with('plan')
            ->where('status', SubscriptionStatus::ACTIVE)
            ->latest()
            ->paginate(20);

        return view('pages.restaurant.subscription-invoices', compact(
            'restaurant',
            'invoices'
        ));
    }

    /**
     * Convert trial to paid subscription.
     */
    public function convertTrial(Request $request): RedirectResponse
    {
        $restaurant = $request->user()->restaurant;
        $currentSubscription = $restaurant->activeSubscription;

        // Check if restaurant has an active trial
        if (!$currentSubscription || !$currentSubscription->isTrial()) {
            return redirect()->route('restaurant.subscription')
                ->with('error', 'Vous n\'avez pas d\'essai actif à convertir.');
        }

        $request->validate([
            'plan' => ['required', 'exists:plans,slug'],
            'billing_period' => ['nullable', 'in:monthly,quarterly,semiannual,annual'],
            'addons' => ['nullable', 'array'],
            'addons.*' => ['string', 'in:priority_support,custom_domain,extra_employees,extra_dishes'],
        ]);

        $plan = Plan::where('slug', $request->plan)->firstOrFail();
        
        // Calculate price with discount based on billing period
        $billingPeriod = $request->billing_period ?? 'monthly';
        $priceCalculation = Subscription::calculatePriceWithDiscount($plan->price, $billingPeriod);
        
        // Calculate duration based on billing period
        $durationDays = match($billingPeriod) {
            'monthly' => 30,
            'quarterly' => 90,
            'semiannual' => 180,
            'annual' => 365,
            default => 30,
        };

        // Calculate add-ons total price
        $addonsTotal = 0;
        if ($request->has('addons') && is_array($request->addons)) {
            $availableAddons = SubscriptionAddon::getAvailableAddons();
            $months = $durationDays / 30;
            foreach ($request->addons as $addonType) {
                if (isset($availableAddons[$addonType])) {
                    $addonData = $availableAddons[$addonType];
                    $addonsTotal += $addonData['price'] * $months;
                }
            }
        }

        // Total price = base price (with discount) + add-ons
        $totalPrice = $priceCalculation['final_price'] + $addonsTotal;

        // Create new paid subscription (will replace trial after payment)
        $subscription = Subscription::create([
            'restaurant_id' => $restaurant->id,
            'plan_id' => $plan->id,
            'status' => SubscriptionStatus::PENDING,
            'is_trial' => false,
            'starts_at' => now(),
            'ends_at' => now()->addDays($durationDays),
            'amount_paid' => $totalPrice,
            'billing_period' => $billingPeriod,
            'discount_percentage' => $priceCalculation['discount_percentage'],
        ]);

        // Add add-ons
        if ($request->has('addons') && is_array($request->addons)) {
            $availableAddons = SubscriptionAddon::getAvailableAddons();
            foreach ($request->addons as $addonType) {
                if (isset($availableAddons[$addonType])) {
                    $addonData = $availableAddons[$addonType];
                    SubscriptionAddon::create([
                        'subscription_id' => $subscription->id,
                        'addon_type' => $addonType,
                        'name' => $addonData['name'],
                        'price' => $addonData['price'] * ($durationDays / 30),
                        'metadata' => ['description' => $addonData['description']],
                    ]);
                }
            }
        }

        // Check if platform Lygos is configured
        $platformApiKey = \App\Models\SystemSetting::get('lygos_api_key', '');
        if ($platformApiKey) {
            // Create payment session for subscription
            $result = $this->lygosGateway
                ->forPlatform()
                ->createSubscriptionPayment(
                    $subscription,
                    route('restaurant.subscription.success', $subscription),
                    route('restaurant.subscription.cancel', $subscription)
                );

            if ($result['success']) {
                $subscription->update([
                    'payment_reference' => $result['payment_id'],
                    'payment_metadata' => ['payment_url' => $result['payment_url']],
                ]);

                return redirect($result['payment_url']);
            }

            return redirect()->route('restaurant.subscription')
                ->with('error', 'Erreur lors de la création du paiement. Veuillez réessayer.');
        }

        return redirect()->route('restaurant.subscription')
            ->with('error', 'Le système de paiement n\'est pas configuré. Veuillez contacter le support.');
    }

    /**
     * Initiate plan change/renewal.
     */
    public function change(Request $request): RedirectResponse
    {
        $request->validate([
            'plan' => ['required', 'exists:plans,slug'],
            'billing_period' => ['nullable', 'in:monthly,quarterly,semiannual,annual'],
            'addons' => ['nullable', 'array'],
            'addons.*' => ['string', 'in:priority_support,custom_domain,extra_employees,extra_dishes'],
        ]);

        $restaurant = $request->user()->restaurant;
        $plan = Plan::where('slug', $request->plan)->firstOrFail();
        
        // Calculate price with discount based on billing period
        $billingPeriod = $request->billing_period ?? 'monthly';
        $priceCalculation = Subscription::calculatePriceWithDiscount($plan->price, $billingPeriod);
        
        // Calculate duration based on billing period
        $durationDays = match($billingPeriod) {
            'monthly' => 30,
            'quarterly' => 90,
            'semiannual' => 180,
            'annual' => 365,
            default => 30,
        };

        // Create pending subscription
        $subscription = Subscription::create([
            'restaurant_id' => $restaurant->id,
            'plan_id' => $plan->id,
            'status' => SubscriptionStatus::PENDING,
            'starts_at' => now(),
            'ends_at' => now()->addDays($durationDays),
            'amount_paid' => $priceCalculation['final_price'],
            'billing_period' => $billingPeriod,
            'discount_percentage' => $priceCalculation['discount_percentage'],
        ]);

        // Add add-ons if selected
        if ($request->has('addons') && is_array($request->addons)) {
            $availableAddons = SubscriptionAddon::getAvailableAddons();
            foreach ($request->addons as $addonType) {
                if (isset($availableAddons[$addonType])) {
                    $addonData = $availableAddons[$addonType];
                    SubscriptionAddon::create([
                        'subscription_id' => $subscription->id,
                        'addon_type' => $addonType,
                        'name' => $addonData['name'],
                        'price' => $addonData['price'] * ($durationDays / 30), // Prorata based on duration
                        'metadata' => ['description' => $addonData['description']],
                    ]);
                }
            }
        }

        // Check if platform Lygos is configured (for subscriptions, we use platform API key)
        $platformApiKey = \App\Models\SystemSetting::get('lygos_api_key', '');
        if ($platformApiKey) {
            // Create payment session for subscription using platform API key
            $result = $this->lygosGateway
                ->forPlatform()
                ->createSubscriptionPayment(
                    $subscription,
                    route('restaurant.subscription.success', $subscription),
                    route('restaurant.subscription.cancel', $subscription)
                );

            if ($result['success']) {
                $subscription->update([
                    'payment_reference' => $result['payment_id'],
                    'payment_metadata' => ['payment_url' => $result['payment_url']],
                ]);

                return redirect($result['payment_url']);
            }

            // Payment creation failed
            $subscription->delete();
            return back()->with('error', $result['error'] ?? 'Erreur lors de la création du paiement.');
        }

        // No payment gateway configured - mark as pending for manual validation
        return back()->with('info', 'Votre demande de changement de plan a été enregistrée. Notre équipe vous contactera pour le paiement.');
    }

    /**
     * Handle successful payment callback.
     */
    public function success(Request $request, Subscription $subscription): RedirectResponse
    {
        // Verify payment using subscription reference (order_id in Lygos)
        // Use platform API key for subscription verification
        $subscriptionReference = 'SUB-' . $subscription->id . '-' . $subscription->created_at->format('Ymd');
        $platformApiKey = \App\Models\SystemSetting::get('lygos_api_key', '');
        if ($platformApiKey) {
            $result = $this->lygosGateway
                ->forPlatform()
                ->verifyPayment($subscriptionReference); // Lygos uses order_id to verify payment

            if (!$result['success'] || !$result['paid']) {
                return redirect()->route('restaurant.subscription')
                    ->with('error', 'Le paiement n\'a pas été confirmé.');
            }
        }

        try {
            \DB::beginTransaction();

            // Check if there's an active trial to expire
            $restaurant = $subscription->restaurant;
            $activeTrial = $restaurant->subscriptions()
                ->where('status', SubscriptionStatus::TRIAL)
                ->where('is_trial', true)
                ->latest()
                ->first();

            if ($activeTrial) {
                // Expire the trial
                $activeTrial->update([
                    'status' => SubscriptionStatus::EXPIRED,
                ]);
            }

            // Activate new paid subscription
            $subscription->update([
                'status' => SubscriptionStatus::ACTIVE,
                'is_trial' => false,
                'payment_method' => 'lygos',
            ]);

            // Update restaurant
            $restaurant->update([
                'current_plan_id' => $subscription->plan_id,
                'subscription_ends_at' => $subscription->ends_at,
                'orders_blocked' => false,
                'status' => \App\Enums\RestaurantStatus::ACTIVE,
            ]);

            \DB::commit();

            $message = $activeTrial 
                ? 'Votre essai a été converti en abonnement payant avec succès !' 
                : 'Votre abonnement a été activé avec succès !';

            return redirect()->route('restaurant.subscription')
                ->with('success', $message);

        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('Subscription activation error', [
                'subscription_id' => $subscription->id,
                'error' => $e->getMessage(),
            ]);

            return redirect()->route('restaurant.subscription')
                ->with('error', 'Une erreur est survenue lors de l\'activation de l\'abonnement.');
        }
    }

    /**
     * Handle cancelled payment.
     */
    public function cancel(Subscription $subscription): RedirectResponse
    {
        $subscription->update([
            'status' => SubscriptionStatus::CANCELLED,
        ]);

        return redirect()->route('restaurant.subscription')
            ->with('warning', 'Le paiement a été annulé.');
    }

    /**
     * Retry payment for a pending subscription.
     */
    public function retryPayment(Request $request, Subscription $subscription): RedirectResponse
    {
        $restaurant = $request->user()->restaurant;

        // Verify this subscription belongs to the user's restaurant
        if ($subscription->restaurant_id !== $restaurant->id) {
            abort(403);
        }

        // Only allow retry for pending subscriptions
        if ($subscription->status !== SubscriptionStatus::PENDING) {
            return redirect()->route('restaurant.subscription')
                ->with('error', 'Cet abonnement ne peut pas être payé à nouveau.');
        }

        // Check if platform Lygos is configured
        $platformApiKey = \App\Models\SystemSetting::get('lygos_api_key', '');
        if (!$platformApiKey) {
            return redirect()->route('restaurant.subscription')
                ->with('error', 'Le système de paiement n\'est pas configuré. Veuillez contacter le support.');
        }

        // Create payment session for subscription
        $result = $this->lygosGateway
            ->forPlatform()
            ->createSubscriptionPayment(
                $subscription,
                route('restaurant.subscription.success', $subscription),
                route('restaurant.subscription.cancel', $subscription)
            );

        if ($result['success']) {
            $subscription->update([
                'payment_reference' => $result['payment_id'],
                'payment_metadata' => ['payment_url' => $result['payment_url']],
            ]);

            return redirect($result['payment_url']);
        }

        return redirect()->route('restaurant.subscription')
            ->with('error', $result['error'] ?? 'Erreur lors de la création de la session de paiement. Veuillez réessayer.');
    }
}

