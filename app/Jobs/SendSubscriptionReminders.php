<?php

namespace App\Jobs;

use App\Enums\SubscriptionStatus;
use App\Models\Subscription;
use App\Notifications\SubscriptionExpiringNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendSubscriptionReminders implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected int $daysBeforeExpiry;

    public function __construct(int $daysBeforeExpiry = 7)
    {
        $this->daysBeforeExpiry = $daysBeforeExpiry;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info("Sending subscription reminders for {$this->daysBeforeExpiry} days before expiry...");

        // Get active subscriptions expiring within the specified days
        $expiringSubscriptions = Subscription::query()
            ->where('status', SubscriptionStatus::ACTIVE)
            ->where('ends_at', '<=', now()->addDays($this->daysBeforeExpiry))
            ->where('ends_at', '>', now())
            ->whereNull('reminder_sent_at')
            ->with('restaurant.owner')
            ->get();

        foreach ($expiringSubscriptions as $subscription) {
            $this->sendReminder($subscription);
        }

        Log::info("Sent {$expiringSubscriptions->count()} subscription reminders.");
    }

    /**
     * Send reminder for a subscription
     */
    protected function sendReminder(Subscription $subscription): void
    {
        try {
            $restaurant = $subscription->restaurant;
            
            if (!$restaurant->owner) {
                return;
            }

            // Send notification
            $restaurant->owner->notify(new SubscriptionExpiringNotification($subscription));

            // Mark reminder as sent
            $subscription->update(['reminder_sent_at' => now()]);

            Log::info("Sent reminder for restaurant: {$restaurant->name}", [
                'restaurant_id' => $restaurant->id,
                'days_remaining' => $subscription->days_remaining,
            ]);

        } catch (\Exception $e) {
            Log::error("Failed to send subscription reminder", [
                'subscription_id' => $subscription->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}

