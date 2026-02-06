<?php

namespace App\Jobs;

use App\Enums\RestaurantStatus;
use App\Enums\SubscriptionStatus;
use App\Models\Restaurant;
use App\Models\Subscription;
use App\Notifications\TrialExpiringNotification;
use App\Notifications\TrialExpiredNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessTrialExpiration implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('[Trial] Processing trial subscriptions...');

        // Find expiring trials (3 days before expiration)
        $expiringTrials = Subscription::where('status', SubscriptionStatus::TRIAL)
            ->where('ends_at', '<=', now()->addDays(3))
            ->where('ends_at', '>', now()->addDays(2))
            ->whereNull('reminder_sent_at')
            ->with('restaurant.users')
            ->get();

        foreach ($expiringTrials as $subscription) {
            $this->sendExpiringNotification($subscription);
        }

        // Find trials expiring tomorrow (1 day before)
        $trialsExpiringTomorrow = Subscription::where('status', SubscriptionStatus::TRIAL)
            ->where('ends_at', '<=', now()->addDay())
            ->where('ends_at', '>', now())
            ->whereNull('reminder_sent_at')
            ->with('restaurant.users')
            ->get();

        foreach ($trialsExpiringTomorrow as $subscription) {
            $this->sendExpiringNotification($subscription, 1);
        }

        // Find expired trials
        $expiredTrials = Subscription::where('status', SubscriptionStatus::TRIAL)
            ->where('ends_at', '<=', now())
            ->whereNull('expired_notification_sent_at')
            ->with('restaurant')
            ->get();

        foreach ($expiredTrials as $subscription) {
            $this->expireTrial($subscription);
        }

        Log::info('[Trial] Processed trials', [
            'expiring' => $expiringTrials->count(),
            'expiring_tomorrow' => $trialsExpiringTomorrow->count(),
            'expired' => $expiredTrials->count(),
        ]);
    }

    /**
     * Send expiring notification
     */
    protected function sendExpiringNotification(Subscription $subscription, int $daysLeft = 3): void
    {
        $restaurant = $subscription->restaurant;
        
        $restaurant->users()
            ->whereIn('role', [\App\Enums\UserRole::RESTAURANT_ADMIN])
            ->each(function ($user) use ($subscription, $daysLeft) {
                $user->notify(new TrialExpiringNotification($subscription, $daysLeft));
            });

        $subscription->markReminderSent();
    }

    /**
     * Expire trial subscription
     */
    protected function expireTrial(Subscription $subscription): void
    {
        $restaurant = $subscription->restaurant;

        // Mark subscription as expired
        $subscription->update([
            'status' => SubscriptionStatus::EXPIRED,
        ]);

        // Block restaurant orders
        $restaurant->update([
            'orders_blocked' => true,
        ]);

        // Send expiration notification
        $restaurant->users()
            ->whereIn('role', [\App\Enums\UserRole::RESTAURANT_ADMIN])
            ->each(function ($user) use ($subscription) {
                $user->notify(new TrialExpiredNotification($subscription));
            });

        $subscription->markExpiredNotificationSent();

        Log::info('[Trial] Trial expired', [
            'restaurant_id' => $restaurant->id,
            'subscription_id' => $subscription->id,
        ]);
    }
}
