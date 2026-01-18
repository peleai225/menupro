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

        // Activate subscription
        $subscription->update([
            'status' => SubscriptionStatus::ACTIVE,
            'payment_method' => 'lygos',
        ]);

        // Update restaurant
        $subscription->restaurant->update([
            'current_plan_id' => $subscription->plan_id,
            'subscription_ends_at' => $subscription->ends_at,
            'orders_blocked' => false,
            'status' => 'active',
        ]);

        return redirect()->route('restaurant.subscription')
            ->with('success', 'Votre abonnement a été activé avec succès !');
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
}

