<?php

namespace App\Http\Controllers\Restaurant;

use App\Enums\SubscriptionStatus;
use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\Restaurant;
use App\Models\Subscription;
use App\Models\SubscriptionAddon;
use App\Services\JekoGateway;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * Abonnement du restaurant : choix du plan / de la période / de l'opérateur Mobile Money,
 * puis redirection vers la page de paiement Jeko (unique passerelle d'abonnement).
 *
 * Seul le webhook Jeko (signé) active l'abonnement. Le retour navigateur aboutit sur une
 * page PUBLIQUE (SubscriptionPaymentReturnController) : sur mobile, l'appli Wave / Orange
 * Money ou la PWA rouvre souvent ce lien dans un autre navigateur, sans la session.
 * Chaque échec d'initiation est journalisé (laravel.log + payments.log) et expliqué en
 * français à l'utilisateur — jamais d'échec muet.
 */
class SubscriptionController extends Controller
{
    /** Opérateurs Mobile Money acceptés par Jeko (paymentMethod). */
    public const OPERATORS = [
        'wave'   => 'Wave',
        'orange' => 'Orange Money',
        'mtn'    => 'MTN MoMo',
        'moov'   => 'Moov Money',
    ];

    /** Un abonnement en attente identique et récent est réutilisé (double appui, retour arrière). */
    private const REUSE_PENDING_MINUTES = 30;

    public function __construct(
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
        $highlightPlan = $request->query('plan');

        return view('pages.restaurant.subscription-plans', compact(
            'restaurant',
            'currentPlan',
            'subscription',
            'plans',
            'highlightPlan'
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
            // Essai déjà terminé : on traite la demande comme un (ré)abonnement normal plutôt
            // que de renvoyer une erreur (page restée ouverte pendant la fin de l'essai).
            return $this->change($request);
        }

        return $this->startPayment($request, $restaurant);
    }

    public function change(Request $request): RedirectResponse
    {
        return $this->startPayment($request, $request->user()->restaurant);
    }

    /**
     * Ancien lien de retour (avant 2026-10-26) : renvoie vers la page de retour publique.
     */
    public function success(Request $request, int $subscription): RedirectResponse
    {
        $subscription = Subscription::withoutGlobalScope('restaurant')->findOrFail($subscription);

        // Vérification propriétaire — empêche de consulter la sub d'un autre restaurant
        if ($subscription->restaurant_id !== $request->user()->restaurant?->id) {
            abort(403);
        }

        if ($subscription->status === SubscriptionStatus::ACTIVE) {
            return redirect()->route('restaurant.subscription')
                ->with('success', 'Votre abonnement est déjà actif.');
        }

        // Jeko : seul le webhook active. Pas de session de paiement = rien à confirmer.
        if (!$subscription->payment_reference) {
            return redirect()->route('restaurant.subscription')
                ->with('error', 'Le paiement n\'a pas été confirmé : aucune demande de paiement n\'a abouti pour cet abonnement. Réessayez depuis cette page.');
        }

        return redirect()->to(self::returnUrl($subscription, 'succes'));
    }

    public function cancel(Request $request, int $subscription): RedirectResponse
    {
        $subscription = Subscription::withoutGlobalScope('restaurant')->findOrFail($subscription);

        if ($subscription->restaurant_id !== $request->user()->restaurant?->id) {
            abort(403);
        }

        if ($subscription->status === SubscriptionStatus::PENDING) {
            $subscription->update(['status' => SubscriptionStatus::CANCELLED]);
        }

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
                ->with('error', $subscription->status === SubscriptionStatus::ACTIVE
                    ? 'Ce paiement a déjà été confirmé : votre abonnement est actif.'
                    : 'Ce paiement a été annulé. Choisissez votre plan ci-dessous pour payer à nouveau.');
        }

        $request->validate(['jeko_operator' => ['nullable', 'in:' . implode(',', array_keys(self::OPERATORS))]]);

        $operator = $request->input('jeko_operator')
            ?: ($subscription->payment_metadata['jeko_operator'] ?? null)
            ?: 'wave';

        return $this->redirectToJeko($request, $subscription, $operator, isNew: false);
    }

    // =========================================================================
    // INITIATION DU PAIEMENT
    // =========================================================================

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
            'jeko_operator'   => ['nullable', 'in:' . implode(',', array_keys(self::OPERATORS))],
        ];
    }

    private function subscriptionMessages(): array
    {
        return [
            'plan.required' => 'Choisissez un plan avant de payer.',
            'plan.exists' => 'Ce plan n\'existe plus. Rechargez la page et choisissez un plan.',
            'billing_period.in' => 'Période de paiement inconnue. Choisissez « 1 semaine », « 1 mois »…',
            'jeko_operator.in' => 'Choisissez votre opérateur Mobile Money : Wave, Orange Money, MTN MoMo ou Moov Money.',
            'payment_gateway.in' => 'Le paiement des abonnements se fait uniquement par Mobile Money (Jeko).',
        ];
    }

    private function startPayment(Request $request, Restaurant $restaurant): RedirectResponse
    {
        $validator = validator($request->all(), $this->subscriptionRules(), $this->subscriptionMessages());

        if ($validator->fails()) {
            $this->logInitiationFailure($request, null, 'validation', implode(' | ', $validator->errors()->all()));

            return redirect()->route('restaurant.subscription')
                ->withErrors($validator)
                ->with('error', $validator->errors()->first());
        }

        $plan = Plan::where('slug', $request->plan)->first();

        if (!$plan->is_active || $plan->is_on_quote) {
            return redirect()->route('restaurant.subscription')->with('error', $plan->is_on_quote
                ? "Le plan {$plan->name} se souscrit sur devis : contactez-nous, nous préparons votre offre."
                : "Le plan {$plan->name} n'est plus proposé. Choisissez un autre plan.");
        }

        [$subscription, $isNew] = $this->findOrCreatePendingSubscription($request, $restaurant, $plan);

        if (!$subscription) {
            return redirect()->route('restaurant.subscription')
                ->with('error', "Le paiement à la semaine n'est disponible que pour le plan Stand. Choisissez « 1 mois » ou plus.");
        }

        $operator = $request->input('jeko_operator') ?: 'wave';

        return $this->redirectToJeko($request, $subscription, $operator, $isNew);
    }

    /**
     * Crée la session de paiement Jeko et redirige l'acheteur vers sa page de paiement.
     * En cas d'échec : log explicite + message clair, et l'abonnement en attente créé pour
     * l'occasion est supprimé (rien n'a été payé).
     */
    private function redirectToJeko(Request $request, Subscription $subscription, string $operator, bool $isNew): RedirectResponse
    {
        $returnToken = $subscription->payment_metadata['return_token'] ?? Str::random(40);
        $subscription->payment_metadata = array_merge($subscription->payment_metadata ?? [], ['return_token' => $returnToken]);

        [$result, $error] = $this->createSubscriptionPaymentSession($subscription, $operator);

        if ($result) {
            $subscription->update([
                'payment_reference' => $result['payment_id'],
                'payment_metadata' => array_merge($subscription->payment_metadata ?? [], [
                    'payment_url' => $result['payment_url'],
                    'gateway' => 'jeko',
                    'jeko_operator' => $operator,
                    'jeko_reference' => $result['reference'],
                    'return_token' => $returnToken,
                    'initiated_at' => now()->toIso8601String(),
                ]),
            ]);

            Log::channel('payments')->info('Abonnement : redirection vers Jeko', [
                'subscription_id' => $subscription->id,
                'restaurant_id' => $subscription->restaurant_id,
                'plan' => $subscription->plan?->slug,
                'billing_period' => $subscription->billing_period,
                'amount' => $subscription->amount_paid,
                'operator' => $operator,
                'user_agent' => Str::limit((string) $request->userAgent(), 180),
            ]);

            return redirect()->away($result['payment_url']);
        }

        $this->logInitiationFailure($request, $subscription, $error['code'], $error['detail'], $operator);

        if ($isNew) {
            SubscriptionAddon::where('subscription_id', $subscription->id)->delete();
            $subscription->delete();
        }

        return redirect()->route('restaurant.subscription')
            ->with('error', $error['message'])
            ->with('payment_error_code', $error['code']);
    }

    /**
     * @return array{0: ?array, 1: ?array{code: string, detail: string, message: string}}
     */
    private function createSubscriptionPaymentSession(Subscription $subscription, string $operator): array
    {
        $operatorLabel = self::OPERATORS[$operator] ?? $operator;
        $token = $subscription->payment_metadata['return_token'];

        try {
            if (!$this->jeko->forMarketplacePlatform()->isConfigured()) {
                return [null, [
                    'code' => 'jeko_not_configured',
                    'detail' => 'Jeko marketplace inactif ou clés API manquantes (system_payment_settings.jeko_marketplace)',
                    'message' => 'Le paiement Mobile Money est momentanément indisponible. Réessayez dans quelques minutes ; si cela continue, contactez le support MenuPro.',
                ]];
            }

            $result = $this->jeko->createPayment(
                $subscription,
                self::returnUrl($subscription, 'succes', $token),
                self::returnUrl($subscription, 'echec', $token),
                $operator
            );
        } catch (\Throwable $e) {
            return [null, [
                'code' => 'exception',
                'detail' => $e::class . ': ' . $e->getMessage(),
                'message' => 'Le service de paiement ne répond pas. Vérifiez votre connexion internet et réessayez.',
            ]];
        }

        if (!($result['success'] ?? false)) {
            $detail = (string) ($result['error'] ?? 'Erreur inconnue Jeko');

            return [null, [
                'code' => $detail === 'Impossible de contacter Jeko' ? 'jeko_unreachable' : 'jeko_refused',
                'detail' => $detail,
                'message' => $detail === 'Impossible de contacter Jeko'
                    ? 'Le service de paiement ne répond pas. Vérifiez votre connexion internet et réessayez.'
                    : "Jeko n'a pas pu préparer le paiement {$operatorLabel}. Réessayez, ou choisissez un autre opérateur. (Détail : {$detail})",
            ]];
        }

        if (empty($result['payment_url'])) {
            return [null, [
                'code' => 'jeko_no_url',
                'detail' => 'Réponse Jeko sans redirectUrl',
                'message' => "Jeko n'a pas renvoyé de page de paiement {$operatorLabel}. Réessayez, ou choisissez un autre opérateur.",
            ]];
        }

        return [['payment_id' => $result['payment_id'], 'payment_url' => $result['payment_url'], 'reference' => $result['reference'] ?? null], null];
    }

    /**
     * URL de retour PUBLIQUE (sans session) : jeton aléatoire dans le chemin plutôt qu'une
     * signature d'URL, que Jeko casserait en ajoutant ses propres paramètres.
     */
    public static function returnUrl(Subscription $subscription, string $issue, ?string $token = null): string
    {
        $token ??= $subscription->payment_metadata['return_token'] ?? null;

        if (!$token) {
            $token = Str::random(40);
            $subscription->update(['payment_metadata' => array_merge($subscription->payment_metadata ?? [], ['return_token' => $token])]);
        }

        return route('subscription.payment.return', [
            'subscription' => $subscription->id,
            'token' => $token,
            'issue' => $issue,
        ]);
    }

    private function logInitiationFailure(Request $request, ?Subscription $subscription, string $code, string $detail, ?string $operator = null): void
    {
        $context = [
            'code' => $code,
            'detail' => $detail,
            'subscription_id' => $subscription?->id,
            'restaurant_id' => $request->user()?->restaurant_id,
            'user_id' => $request->user()?->id,
            'plan' => $subscription?->plan?->slug ?? $request->input('plan'),
            'billing_period' => $subscription?->billing_period ?? $request->input('billing_period'),
            'amount' => $subscription?->amount_paid,
            'operator' => $operator ?? $request->input('jeko_operator'),
            'route' => $request->route()?->getName(),
            'user_agent' => Str::limit((string) $request->userAgent(), 180),
        ];

        // Les deux journaux : laravel.log (consulté en premier) et payments.log
        Log::error("Échec d'initiation du paiement d'abonnement [{$code}]", $context);
        Log::channel('payments')->error("Échec d'initiation du paiement d'abonnement [{$code}]", $context);
    }

    /**
     * Abonnement EN ATTENTE de paiement (montant = prix de la période, prix verrouillé
     * éventuel des anciens abonnés, options au prorata). Un abonnement en attente identique
     * créé il y a moins de 30 min est réutilisé (double appui sur « Payer », retour arrière
     * depuis Jeko) : on évite les doublons.
     *
     * @return array{0: ?Subscription, 1: bool} [abonnement (null si période refusée), créé maintenant ?]
     */
    private function findOrCreatePendingSubscription(Request $request, Restaurant $restaurant, Plan $plan): array
    {
        $billingPeriod = $request->billing_period ?: 'monthly';

        try {
            $quote = Subscription::quote($plan, $billingPeriod, $restaurant);
        } catch (\InvalidArgumentException $e) {
            return [null, false];
        }

        $months = Subscription::monthsFor($billingPeriod);
        $availableAddons = SubscriptionAddon::getAvailableAddons();
        $addons = collect(is_array($request->addons) ? $request->addons : [])
            ->filter(fn ($type) => isset($availableAddons[$type]));
        $addonsTotal = (int) round($addons->sum(fn ($type) => $availableAddons[$type]['price'] * $months));
        $amount = $quote['final_price'] + $addonsTotal;

        if ($addons->isEmpty()) {
            $existing = Subscription::withoutGlobalScope('restaurant')
                ->where('restaurant_id', $restaurant->id)
                ->where('plan_id', $plan->id)
                ->where('status', SubscriptionStatus::PENDING)
                ->where('billing_period', $quote['billing_period'])
                ->where('amount_paid', $amount)
                ->where('created_at', '>=', now()->subMinutes(self::REUSE_PENDING_MINUTES))
                ->latest('id')
                ->first();

            if ($existing && !$existing->addons()->exists()) {
                return [$existing, false];
            }
        }

        $subscription = Subscription::create([
            'restaurant_id' => $restaurant->id,
            'plan_id' => $plan->id,
            'status' => SubscriptionStatus::PENDING,
            'is_trial' => false,
            // Période définitive recalculée au paiement (Subscription::paidPeriodAttributes)
            'starts_at' => now(),
            'ends_at' => now()->addDays($quote['duration_days']),
            'amount_paid' => $amount,
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

        return [$subscription, true];
    }
}
