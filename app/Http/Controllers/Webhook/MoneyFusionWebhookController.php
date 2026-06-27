<?php

namespace App\Http\Controllers\Webhook;

use App\Enums\SubscriptionStatus;
use App\Http\Controllers\Controller;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MoneyFusionWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $payload = $request->all();

        $event = $payload['event'] ?? null;
        $tokenPay = $payload['tokenPay'] ?? null;
        $personalInfo = $payload['personal_Info'] ?? [];

        Log::channel('payments')->info('MoneyFusion webhook received', [
            'event' => $event,
            'tokenPay' => $tokenPay,
        ]);

        if (!$tokenPay) {
            return response()->json(['error' => 'Missing tokenPay'], 400);
        }

        $subscription = $this->findSubscription($tokenPay, $personalInfo);

        if (!$subscription) {
            Log::channel('payments')->warning('MoneyFusion webhook: subscription not found', [
                'tokenPay' => $tokenPay,
            ]);
            return response()->json(['error' => 'Subscription not found'], 404);
        }

        return match ($event) {
            'payin.session.completed' => $this->handleCompleted($subscription, $payload),
            'payin.session.cancelled' => $this->handleCancelled($subscription, $payload),
            default => response()->json(['success' => true]), // pending ou inconnu : ignorer
        };
    }

    protected function handleCompleted(Subscription $subscription, array $payload)
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
                    ['moneyfusion_event' => $payload]
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
                Log::channel('payments')->error('MoneyFusion webhook: commission error', [
                    'subscription_id' => $subscription->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        Log::channel('payments')->info('MoneyFusion: subscription activated', [
            'subscription_id' => $subscription->id,
        ]);

        return response()->json(['success' => true]);
    }

    protected function handleCancelled(Subscription $subscription, array $payload)
    {
        if ($subscription->status !== SubscriptionStatus::ACTIVE) {
            $subscription->update([
                'payment_metadata' => array_merge(
                    $subscription->payment_metadata ?? [],
                    ['moneyfusion_cancelled' => $payload]
                ),
            ]);
        }

        Log::channel('payments')->warning('MoneyFusion payment cancelled', [
            'subscription_id' => $subscription->id,
        ]);

        return response()->json(['success' => true]);
    }

    protected function findSubscription(string $tokenPay, array $personalInfo): ?Subscription
    {
        // 1. Chercher via payment_reference (token stocké à l'initiation)
        $sub = Subscription::where('payment_reference', $tokenPay)->first();
        if ($sub) return $sub;

        // 2. Chercher via personal_Info.subscription_id
        foreach ($personalInfo as $info) {
            if (!empty($info['subscription_id'])) {
                $sub = Subscription::find($info['subscription_id']);
                if ($sub) return $sub;
            }
        }

        return null;
    }
}
