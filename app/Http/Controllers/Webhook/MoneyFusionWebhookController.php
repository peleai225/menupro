<?php

namespace App\Http\Controllers\Webhook;

use App\Enums\SubscriptionStatus;
use App\Http\Controllers\Controller;
use App\Models\Subscription;
use App\Services\MoneyFusionGateway;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MoneyFusionWebhookController extends Controller
{
    public function handle(Request $request, MoneyFusionGateway $moneyFusion)
    {
        $payload = $request->all();

        Log::channel('payments')->info('MoneyFusion webhook received', [
            'status' => $payload['statut'] ?? null,
            'reference' => $payload['reference'] ?? null,
            'token' => $payload['token'] ?? null,
        ]);

        $status = $payload['statut'] ?? null;
        $reference = $payload['reference'] ?? null;
        $token = $payload['token'] ?? null;

        if (!$reference && !$token) {
            return response()->json(['error' => 'Missing reference'], 400);
        }

        // Trouver l'abonnement via le token (payment_reference) ou la reference dans metadata
        $subscription = $this->findSubscription($token, $reference);

        if (!$subscription) {
            Log::channel('payments')->warning('MoneyFusion webhook: subscription not found', [
                'token' => $token,
                'reference' => $reference,
            ]);
            return response()->json(['error' => 'Subscription not found'], 404);
        }

        if ($status === 'paid') {
            return $this->handlePaid($subscription, $payload);
        }

        if (in_array($status, ['failed', 'cancelled', 'expired'])) {
            return $this->handleFailed($subscription, $payload);
        }

        return response()->json(['success' => true]);
    }

    protected function handlePaid(Subscription $subscription, array $payload)
    {
        $activated = DB::transaction(function () use ($subscription, $payload) {
            $subscription = Subscription::where('id', $subscription->id)
                ->lockForUpdate()
                ->first();

            if ($subscription->status === SubscriptionStatus::ACTIVE) {
                return false;
            }

            $subscription->update([
                'status' => SubscriptionStatus::ACTIVE,
                'payment_method' => 'moneyfusion',
                'payment_metadata' => array_merge(
                    $subscription->payment_metadata ?? [],
                    ['moneyfusion_callback' => $payload]
                ),
            ]);

            $restaurant = $subscription->restaurant;

            $restaurant->subscriptions()
                ->where('status', SubscriptionStatus::TRIAL)
                ->where('is_trial', true)
                ->update(['status' => SubscriptionStatus::EXPIRED]);

            $restaurant->update([
                'current_plan_id' => $subscription->plan_id,
                'subscription_ends_at' => $subscription->ends_at,
                'orders_blocked' => false,
            ]);

            return true;
        });

        if ($activated) {
            try {
                app(\App\Services\CommandoCommissionService::class)
                    ->creditAgentForRestaurantSubscription(
                        $subscription->restaurant,
                        $subscription
                    );
            } catch (\Throwable $e) {
                Log::channel('payments')->error('MoneyFusion webhook: commando commission error', [
                    'subscription_id' => $subscription->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        Log::channel('payments')->info('MoneyFusion webhook: subscription activated', [
            'subscription_id' => $subscription->id,
        ]);

        return response()->json(['success' => true]);
    }

    protected function handleFailed(Subscription $subscription, array $payload)
    {
        if ($subscription->status !== SubscriptionStatus::ACTIVE) {
            $subscription->update([
                'payment_metadata' => array_merge(
                    $subscription->payment_metadata ?? [],
                    ['moneyfusion_failure' => $payload]
                ),
            ]);
        }

        Log::channel('payments')->warning('MoneyFusion payment failed/cancelled', [
            'subscription_id' => $subscription->id,
            'status' => $payload['statut'] ?? null,
        ]);

        return response()->json(['success' => true]);
    }

    protected function findSubscription(?string $token, ?string $reference): ?Subscription
    {
        if ($token) {
            $sub = Subscription::where('payment_reference', $token)->first();
            if ($sub) return $sub;
        }

        if ($reference && preg_match('/^SUB-(\d+)-/', $reference, $matches)) {
            return Subscription::find($matches[1]);
        }

        return null;
    }
}
