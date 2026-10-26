<?php

namespace App\Http\Controllers;

use App\Enums\SubscriptionStatus;
use App\Models\Subscription;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * Retour de la page de paiement Jeko d'un abonnement — PUBLIC (sans session).
 *
 * Sur mobile, le retour depuis Wave / Orange Money ou la PWA installée s'ouvre souvent
 * dans un autre navigateur où la vendeuse n'est pas connectée : l'ancienne URL
 * authentifiée finissait sur la page de connexion, ou sur « Le paiement n'a pas été
 * confirmé » quand le webhook n'était pas encore arrivé. Cette page est protégée par un
 * jeton aléatoire (payment_metadata.return_token), n'active jamais rien elle-même (seul le
 * webhook signé active) et attend la confirmation en interrogeant {@see status()}.
 */
class SubscriptionPaymentReturnController extends Controller
{
    public function show(Request $request, int $subscription, string $token, string $issue): View
    {
        $subscription = $this->resolve($subscription, $token);

        if ($issue === 'echec' && $subscription->status === SubscriptionStatus::PENDING) {
            $context = [
                'subscription_id' => $subscription->id,
                'restaurant_id' => $subscription->restaurant_id,
                'operator' => $subscription->payment_metadata['jeko_operator'] ?? null,
                'amount' => $subscription->amount_paid,
                'query' => Str::limit(json_encode($request->query()), 300),
                'user_agent' => Str::limit((string) $request->userAgent(), 180),
            ];
            Log::warning('Abonnement : retour Jeko en échec (paiement non abouti)', $context);
            Log::channel('payments')->warning('Abonnement : retour Jeko en échec (paiement non abouti)', $context);
        }

        return view('pages.subscription-payment-return', [
            'subscription' => $subscription->loadMissing('plan', 'restaurant'),
            'state' => $this->state($subscription, $issue),
            'issue' => $issue,
            'statusUrl' => route('subscription.payment.status', ['subscription' => $subscription->id, 'token' => $token]),
            'isOwner' => $request->user()?->restaurant_id === $subscription->restaurant_id,
        ]);
    }

    public function status(int $subscription, string $token): JsonResponse
    {
        $subscription = $this->resolve($subscription, $token);

        return response()->json([
            'state' => $this->state($subscription, null),
            'ends_at' => $subscription->ends_at?->locale('fr')->isoFormat('D MMMM YYYY'),
        ])->header('Cache-Control', 'no-store');
    }

    private function resolve(int $id, string $token): Subscription
    {
        $subscription = Subscription::withoutGlobalScopes()->find($id);
        $expected = $subscription?->payment_metadata['return_token'] ?? null;

        abort_unless($subscription && $expected && hash_equals($expected, $token), 404);

        return $subscription;
    }

    /** active | pending | failed */
    private function state(Subscription $subscription, ?string $issue): string
    {
        return match (true) {
            $subscription->status === SubscriptionStatus::ACTIVE,
            $subscription->status === SubscriptionStatus::EXPIRED && $subscription->paid_at !== null => 'active',
            $subscription->status === SubscriptionStatus::PENDING && $issue !== 'echec' => 'pending',
            default => 'failed',
        };
    }
}
