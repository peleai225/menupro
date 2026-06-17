<?php

namespace App\Observers;

use App\Enums\UserRole;
use App\Models\Restaurant;
use App\Models\User;
use App\Notifications\Admin\NewRestaurantRegisteredNotification;

class AdminNotificationObserver
{
    public function created(Restaurant $restaurant): void
    {
        $admins = User::where('role', UserRole::SUPER_ADMIN)->get();

        foreach ($admins as $admin) {
            $admin->notify(new NewRestaurantRegisteredNotification($restaurant));
        }
    }
}
