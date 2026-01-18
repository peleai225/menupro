<?php

namespace App\Jobs;

use App\Enums\RestaurantStatus;
use App\Enums\SubscriptionStatus;
use App\Models\Restaurant;
use App\Models\Subscription;
use App\Notifications\SubscriptionExpiredNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessSubscriptionExpiration implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('Processing subscription expirations...');

        // Get all active subscriptions that have expired
        $expiredSubscriptions = Subscription::query()
            ->where('status', SubscriptionStatus::ACTIVE)
            ->where('ends_at', '<', now())
            ->with('restaurant.owner')
            ->get();

        foreach ($expiredSubscriptions as $subscription) {
            $this->processExpiredSubscription($subscription);
        }

        Log::info("Processed {$expiredSubscriptions->count()} expired subscriptions.");
    }

    /**
     * Process a single expired subscription
     */
    protected function processExpiredSubscription(Subscription $subscription): void
    {
        try {
            // Mark subscription as expired
            $subscription->update([
                'status' => SubscriptionStatus::EXPIRED,
            ]);

            // Update restaurant status
            $restaurant = $subscription->restaurant;
            $restaurant->update([
                'status' => RestaurantStatus::EXPIRED,
                'orders_blocked' => true,
            ]);

            // Send notification if not already sent
            if (!$subscription->expired_notification_sent_at && $restaurant->owner) {
                $restaurant->owner->notify(new SubscriptionExpiredNotification($subscription));
                $subscription->update(['expired_notification_sent_at' => now()]);
            }

            Log::info("Subscription expired for restaurant: {$restaurant->name}", [
                'restaurant_id' => $restaurant->id,
                'subscription_id' => $subscription->id,
            ]);

        } catch (\Exception $e) {
            Log::error("Failed to process subscription expiration", [
                'subscription_id' => $subscription->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}

