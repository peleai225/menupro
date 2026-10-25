<?php

namespace App\Http\Controllers\Restaurant;

use App\Enums\SubscriptionStatus;
use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\SubscriptionAddon;
use App\Services\JekoGateway;
use App\Services\MoneyFusionGateway;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SubscriptionController extends Controller
{
    public function __construct(
        protected MoneyFusionGateway $moneyFusion,
        protected JekoGateway $jeko,
    ) {}

    public function index(Request $request): View
    {
        $restaurant = $request->user()->restaurant;
        $currentPlan = $restaurant->currentPlan;
        $subscription = $restaurant->activeSubscription;
        $plans = Plan::active()->ordered()->get(); // Stand inclus (renouvellement, paiement à la semaine)

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
        $plans = Plan::active()->ordered()->get(); // Stand inclus (renouvellement, paiement à la semaine)

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

        $request->validate($this->subscriptionRules());

        $plan = Plan::where('slug', $request->plan)->firstOrFail();

        $subscription = $this->createPendingSubscription($request, $restaurant, $plan);
        if (!$subscription) {
            return back()->with('error', "Le paiement à la semaine n'est disponible que pour le plan Stand.");
        }

        $gateway = $request->input('payment_gateway', 'jeko');
        $jekoOperator = $request->input('jeko_operator', 'wave');
        $result = $this->createSubscriptionPaymentSession($subscription, $gateway, $jekoOperator);

        if ($result) {
            $subscription->update([
                'payment_reference' => $result['payment_id'],
                'payment_metadata' => ['payment_url' => $result['payment_url'], 'gateway' => $result['gateway'], 'jeko_operator' => $result['jeko_operator'] ?? null],
            ]);
            return redirect($result['payment_url']);
        }

        return redirect()->route('restaurant.subscription')
            ->with('error', 'Le système de paiement n\'est pas configuré. Veuillez contacter le support.');
    }

    public function change(Request $request): RedirectResponse
    {
        $request->validate($this->subscriptionRules());

        $restaurant = $request->user()->restaurant;
        $plan = Plan::where('slug', $request->plan)->firstOrFail();

        $subscription = $this->createPendingSubscription($request, $restaurant, $plan);
        if (!$subscription) {
            return back()->with('error', "Le paiement à la semaine n'est disponible que pour le plan Stand.");
        }

        $gateway = $request->input('payment_gateway', 'jeko');
        $jekoOperator = $request->input('jeko_operator', 'wave');
        $result = $this->createSubscriptionPaymentSession($subscription, $gateway, $jekoOperator);

        if ($result) {
            $subscription->update([
                'payment_reference' => $result['payment_id'],
                'payment_metadata' => ['payment_url' => $result['payment_url'], 'gateway' => $result['gateway'], 'jeko_operator' => $result['jeko_operator'] ?? null],
            ]);
            return redirect($result['payment_url']);
        }

        $subscription->delete();
        $errorDetail = $this->lastPaymentError ?? 'Erreur inconnue';
        return back()->with('error', "Erreur paiement : {$errorDetail}");
    }

    public function success(Request $request, int $subscription): RedirectResponse
    {
        $subscription = Subscription::withoutGlobalScope('restaurant')->findOrFail($subscription);

        // Vérification propriétaire — empêche l'activation d'une sub d'un autre restaurant
        if ($subscription->restaurant_id !== $request->user()->restaurant?->id) {
            abort(403);
        }

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

            // Paiement vérifié : unique abonnement en cours (l'essai et tout ancien
            // abonnement actif sont clôturés), restaurant réactivé et débloqué.
            $subscription->activateExclusively([
                'status' => SubscriptionStatus::ACTIVE,
                'is_trial' => false,
                'payment_method' => $subscription->payment_metadata['gateway'] ?? 'jeko',
                'paid_at' => $subscription->paid_at ?? now(),
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

    public function cancel(int $subscription): RedirectResponse
    {
        $subscription = Subscription::withoutGlobalScope('restaurant')->findOrFail($subscription);

        $subscription->update([
            'status' => SubscriptionStatus::CANCELLED,
        ]);

        return redirect()->route('restaurant.subscription')
            ->with('warning', 'Le paiement a été annulé.');
    }

    public function retryPayment(Request $request, int $subscription): RedirectResponse
    {
        $restaurant = $request->user()->restaurant;

        // Contourner le global scope BelongsToRestaurant pour le route model binding
        $subscription = Subscription::withoutGlobalScope('restaurant')->findOrFail($subscription);

        // Rattacher les subscriptions orphelines (restaurant_id NULL — créées avant le fix $guarded)
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

        $gateway      = $subscription->payment_metadata['gateway'] ?? 'jeko';
        $jekoOperator = $subscription->payment_metadata['jeko_operator'] ?? 'wave';
        $result = $this->createSubscriptionPaymentSession($subscription, $gateway, $jekoOperator);

        if ($result) {
            $subscription->update([
                'payment_reference' => $result['payment_id'],
                'payment_metadata' => ['payment_url' => $result['payment_url'], 'gateway' => $result['gateway'], 'jeko_operator' => $result['jeko_operator'] ?? null],
            ]);
            return redirect($result['payment_url']);
        }

        $errorDetail = $this->lastPaymentError ?? 'Erreur inconnue';
        return redirect()->route('restaurant.subscription')
            ->with('error', "Erreur paiement : {$errorDetail}");
    }

    protected ?string $lastPaymentError = null;

    /**
     * Règles communes au changement de plan et à la conversion d'essai.
     */
    private function subscriptionRules(): array
    {
        return [
            'plan' => ['required', 'exists:plans,slug'],
            // 'weekly' : plan Stand uniquement (contrôlé par Subscription::quote())
            'billing_period' => ['nullable', Subscription::billingPeriodRule()],
            'addons' => ['nullable', 'array'],
            'addons.*' => ['string', 'in:priority_support,custom_domain,extra_employees,extra_dishes'],
            'payment_gateway' => ['nullable', 'in:jeko'],
            'jeko_operator'   => ['nullable', 'in:wave,orange,mtn,moov'],
        ];
    }

    /**
     * Crée l'abonnement EN ATTENTE de paiement (montant = prix de la période, prix verrouillé
     * éventuel des anciens abonnés, options au prorata). Null si la période n'est pas autorisée
     * pour ce plan (hebdomadaire hors Stand).
     */
    private function createPendingSubscription(Request $request, \App\Models\Restaurant $restaurant, Plan $plan): ?Subscription
    {
        $billingPeriod = $request->billing_period ?? 'monthly';

        try {
            $quote = Subscription::quote($plan, $billingPeriod, $restaurant);
        } catch (\InvalidArgumentException $e) {
            return null;
        }

        $months = Subscription::monthsFor($billingPeriod);
        $availableAddons = SubscriptionAddon::getAvailableAddons();
        $addons = collect(is_array($request->addons) ? $request->addons : [])
            ->filter(fn ($type) => isset($availableAddons[$type]));
        $addonsTotal = (int) round($addons->sum(fn ($type) => $availableAddons[$type]['price'] * $months));

        $subscription = Subscription::create([
            'restaurant_id' => $restaurant->id,
            'plan_id' => $plan->id,
            'status' => SubscriptionStatus::PENDING,
            'is_trial' => false,
            'starts_at' => now(),
            'ends_at' => now()->addDays($quote['duration_days']),
            'amount_paid' => $quote['final_price'] + $addonsTotal,
            'billing_period' => $quote['billing_period'],
            'discount_percentage' => $quote['discount_percentage'],
        ]);

        foreach ($addons as $addonType) {
            $addonData = $availableAddons[$addonType];
            SubscriptionAddon::create([
                'subscription_id' => $subscription->id,
                'addon_type' => $addonType,
                'name' => $addonData['name'],
                'price' => (int) round($addonData['price'] * $months),
                'metadata' => ['description' => $addonData['description']],
            ]);
        }

        return $subscription;
    }

    private function createSubscriptionPaymentSession(Subscription $subscription, string $gateway = 'jeko', string $jekoOperator = 'wave'): ?array
    {
        $successUrl = route('restaurant.subscription.success', $subscription);

        if ($gateway === 'jeko') {
            $jekoConfigured = $this->jeko->forMarketplacePlatform()->isConfigured();
            if (!$jekoConfigured) {
                $this->lastPaymentError = 'Jeko non configuré';
                \Log::channel('payments')->warning($this->lastPaymentError);
                return null;
            }

            $errorUrl = route('restaurant.subscription');
            $result   = $this->jeko->createPayment($subscription, $successUrl, $errorUrl, $jekoOperator);

            \Log::channel('payments')->info('Jeko subscription payment created', [
                'subscription_id' => $subscription->id,
                'success'         => $result['success'],
                'error'           => $result['error'] ?? null,
                'payment_url'     => $result['payment_url'] ?? null,
                'operator'        => $jekoOperator,
            ]);

            if ($result['success']) {
                return [
                    'payment_id'    => $result['payment_id'],
                    'payment_url'   => $result['payment_url'],
                    'gateway'       => 'jeko',
                    'jeko_operator' => $jekoOperator,
                ];
            }

            $this->lastPaymentError = $result['error'] ?? 'Erreur inconnue Jeko';
            return null;
        }

        // MoneyFusion (défaut)
        if (!$this->moneyFusion->isConfigured()) {
            $this->lastPaymentError = 'MoneyFusion non configuré ou désactivé';
            \Log::channel('payments')->warning($this->lastPaymentError);
            return null;
        }

        $result = $this->moneyFusion->createPayment($subscription, $successUrl);

        \Log::channel('payments')->info('MoneyFusion createPayment result', [
            'subscription_id' => $subscription->id,
            'success'         => $result['success'],
            'error'           => $result['error'] ?? null,
            'payment_url'     => $result['payment_url'] ?? null,
        ]);

        if ($result['success']) {
            return [
                'payment_id'  => $result['token'],
                'payment_url' => $result['payment_url'],
                'gateway'     => 'moneyfusion',
            ];
        }

        $this->lastPaymentError = $result['error'] ?? 'Erreur inconnue MoneyFusion';
        return null;
    }

    private function verifySubscriptionPayment(Subscription $subscription): bool
    {
        $ref     = $subscription->payment_reference;
        $gateway = $subscription->payment_metadata['gateway'] ?? 'jeko';

        // Pas de référence = aucune session de paiement n'a jamais réussi (ex.
        // createSubscriptionPaymentSession a échoué et laissé la ligne PENDING
        // sans référence). Ne jamais considérer ça comme payé.
        if (!$ref) {
            return false;
        }

        // Jeko — seul le webhook (signé) active l'abonnement. Si on arrive ici
        // sans que le statut soit déjà ACTIVE, le paiement n'est pas confirmé.
        if ($gateway === 'jeko') {
            return $subscription->fresh()->status === SubscriptionStatus::ACTIVE;
        }

        if ($this->moneyFusion->isConfigured()) {
            $result = $this->moneyFusion->verifyPayment($ref);
            if ($result['success']) {
                return $result['paid'] ?? false;
            }
        }

        return false;
    }
}
