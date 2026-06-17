<?php

namespace App\Observers;

use App\Enums\SubscriptionStatus;
use App\Enums\UserRole;
use App\Models\Subscription;
use App\Models\User;
use App\Notifications\Admin\NewPaymentReceivedNotification;
use App\Notifications\Admin\SubscriptionExpiredAdminNotification;

class SubscriptionAdminObserver
{
    public function updated(Subscription $subscription): void
    {
        if ($subscription->isDirty('status')) {
            $newStatus = $subscription->status;

            if ($newStatus === SubscriptionStatus::ACTIVE && $subscription->amount_paid > 0) {
                $this->notifyAdmins(new NewPaymentReceivedNotification($subscription));
            }

            if ($newStatus === SubscriptionStatus::EXPIRED && $subscription->restaurant) {
                $this->notifyAdmins(new SubscriptionExpiredAdminNotification($subscription->restaurant));
            }
        }
    }

    protected function notifyAdmins($notification): void
    {
        $admins = User::where('role', UserRole::SUPER_ADMIN)->get();

        foreach ($admins as $admin) {
            $admin->notify($notification);
        }
    }
}
