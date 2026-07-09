<?php

namespace App\Http\Controllers\Restaurant;

use App\Enums\SubscriptionStatus;
use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\SubscriptionAddon;
use App\Services\MoneyFusionGateway;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SubscriptionController extends Controller
{
    public function __construct(
        protected MoneyFusionGateway $moneyFusion,
    ) {}

    public function index(Request $request): View
    {
        $restaurant = $request->user()->restaurant;
        $currentPlan = $restaurant->currentPlan;
        $subscription = $restaurant->activeSubscription;
        $plans = Plan::active()->ordered()->get();

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

    public function convertTrial(Request $request): RedirectResponse
    {
        $restaurant = $request->user()->restaurant;
        $currentSubscription = $restaurant->activeSubscription;

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

        $billingPeriod = $request->billing_period ?? 'monthly';
        $priceCalculation = Subscription::calculatePriceWithDiscount($plan->price, $billingPeriod);

        $durationDays = match($billingPeriod) {
            'monthly' => 30,
            'quarterly' => 90,
            'semiannual' => 180,
            'annual' => 365,
            default => 30,
        };

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

        $totalPrice = $priceCalculation['final_price'] + $addonsTotal;

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

        $result = $this->createSubscriptionPaymentSession($subscription);

        if ($result) {
            $subscription->update([
                'payment_reference' => $result['payment_id'],
                'payment_metadata' => ['payment_url' => $result['payment_url'], 'gateway' => $result['gateway']],
            ]);
            return redirect($result['payment_url']);
        }

        return redirect()->route('restaurant.subscription')
            ->with('error', 'Le système de paiement n\'est pas configuré. Veuillez contacter le support.');
    }

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

        $billingPeriod = $request->billing_period ?? 'monthly';
        $priceCalculation = Subscription::calculatePriceWithDiscount($plan->price, $billingPeriod);

        $durationDays = match($billingPeriod) {
            'monthly' => 30,
            'quarterly' => 90,
            'semiannual' => 180,
            'annual' => 365,
            default => 30,
        };

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

        $result = $this->createSubscriptionPaymentSession($subscription);

        if ($result) {
            $subscription->update([
                'payment_reference' => $result['payment_id'],
                'payment_metadata' => ['payment_url' => $result['payment_url'], 'gateway' => $result['gateway']],
            ]);
            return redirect($result['payment_url']);
        }

        $subscription->delete();
        $errorDetail = $this->lastPaymentError ?? 'Erreur inconnue';
        return back()->with('error', "Erreur paiement : {$errorDetail}");
    }

    public function success(Request $request, Subscription $subscription): RedirectResponse
    {
        if ($subscription->status === SubscriptionStatus::ACTIVE) {
            return redirect()->route('restaurant.subscription')
                ->with('success', 'Votre abonnement est déjà actif.');
        }

        $verified = $this->verifySubscriptionPayment($subscription);
        if (!$verified) {
            return redirect()->route('restaurant.subscription')
                ->with('error', 'Le paiement n\'a pas été confirmé.');
        }

        try {
            \DB::beginTransaction();

            $restaurant = $subscription->restaurant;
            $activeTrial = $restaurant->subscriptions()
                ->where('status', SubscriptionStatus::TRIAL)
                ->where('is_trial', true)
                ->latest()
                ->first();

            if ($activeTrial) {
                $activeTrial->update([
                    'status' => SubscriptionStatus::EXPIRED,
                ]);
            }

            $subscription->update([
                'status' => SubscriptionStatus::ACTIVE,
                'is_trial' => false,
                'payment_method' => $subscription->payment_metadata['gateway'] ?? 'moneyfusion',
            ]);

            $restaurant->update([
                'current_plan_id' => $subscription->plan_id,
                'subscription_ends_at' => $subscription->ends_at,
                'orders_blocked' => false,
                'status' => \App\Enums\RestaurantStatus::ACTIVE,
            ]);

            \DB::commit();

            try {
                app(\App\Services\CommandoCommissionService::class)
                    ->creditAgentForRestaurantSubscription($restaurant, $subscription);
            } catch (\Throwable $e) {
                \Log::error('Commission error on subscription success', [
                    'subscription_id' => $subscription->id,
                    'error' => $e->getMessage(),
                ]);
            }

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

    public function cancel(Subscription $subscription): RedirectResponse
    {
        $subscription->update([
            'status' => SubscriptionStatus::CANCELLED,
        ]);

        return redirect()->route('restaurant.subscription')
            ->with('warning', 'Le paiement a été annulé.');
    }

    public function retryPayment(Request $request, Subscription $subscription): RedirectResponse
    {
        $restaurant = $request->user()->restaurant;

        // Correction : subscriptions créées avant le fix $guarded ont restaurant_id NULL
        // On les rattache au restaurant du user connecté si elles sont orphelines
        if ($subscription->restaurant_id === null) {
            $subscription->update(['restaurant_id' => $restaurant->id]);
        }

        if ($subscription->restaurant_id !== $restaurant->id) {
            abort(403);
        }

        if ($subscription->status !== SubscriptionStatus::PENDING) {
            return redirect()->route('restaurant.subscription')
                ->with('error', 'Cet abonnement ne peut pas être payé à nouveau.');
        }

        $result = $this->createSubscriptionPaymentSession($subscription);

        if ($result) {
            $subscription->update([
                'payment_reference' => $result['payment_id'],
                'payment_metadata' => ['payment_url' => $result['payment_url'], 'gateway' => $result['gateway']],
            ]);
            return redirect($result['payment_url']);
        }

        $errorDetail = $this->lastPaymentError ?? 'Erreur inconnue';
        return redirect()->route('restaurant.subscription')
            ->with('error', "Erreur paiement : {$errorDetail}");
    }

    protected ?string $lastPaymentError = null;

    private function createSubscriptionPaymentSession(Subscription $subscription): ?array
    {
        if (!$this->moneyFusion->isConfigured()) {
            $this->lastPaymentError = 'MoneyFusion non configuré (API URL manquante dans les paramètres)';
            \Log::channel('payments')->warning($this->lastPaymentError);
            return null;
        }

        $returnUrl = route('restaurant.subscription.success', $subscription);
        $result = $this->moneyFusion->createPayment($subscription, $returnUrl);

        \Log::channel('payments')->info('MoneyFusion createPayment result', [
            'subscription_id' => $subscription->id,
            'success' => $result['success'],
            'error' => $result['error'] ?? null,
            'payment_url' => $result['payment_url'] ?? null,
        ]);

        if ($result['success']) {
            return [
                'payment_id' => $result['token'],
                'payment_url' => $result['payment_url'],
                'gateway' => 'moneyfusion',
            ];
        }

        $this->lastPaymentError = $result['error'] ?? 'Erreur inconnue MoneyFusion';
        return null;
    }

    private function verifySubscriptionPayment(Subscription $subscription): bool
    {
        $ref = $subscription->payment_reference;
        if (!$ref) {
            return true;
        }

        if ($this->moneyFusion->isConfigured()) {
            $result = $this->moneyFusion->verifyPayment($ref);
            if ($result['success']) {
                return $result['paid'] ?? false;
            }
        }

        return true;
    }
}
