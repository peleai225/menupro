<?php

use App\Jobs\CheckLowStock;
use App\Jobs\ProcessSubscriptionExpiration;
use App\Jobs\ProcessTrialExpiration;
use App\Jobs\SendSubscriptionReminders;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Scheduled Tasks
|--------------------------------------------------------------------------
*/

// Process subscription expirations daily at midnight
Schedule::job(new ProcessSubscriptionExpiration)
    ->daily()
    ->at('00:00')
    ->name('process-subscription-expiration')
    ->withoutOverlapping();

// Process trial expirations daily at 1 AM
Schedule::job(new ProcessTrialExpiration)
    ->daily()
    ->at('01:00')
    ->name('process-trial-expiration')
    ->withoutOverlapping();

// Send subscription reminders (7 days before) daily at 9 AM
Schedule::job(new SendSubscriptionReminders(7))
    ->daily()
    ->at('09:00')
    ->name('send-subscription-reminders')
    ->withoutOverlapping();

// Check low stock alerts daily at 8 AM
Schedule::job(new CheckLowStock)
    ->daily()
    ->at('08:00')
    ->name('check-low-stock')
    ->withoutOverlapping();

// Cleanup unpaid registrations (older than 48h) daily at 2 AM
Schedule::job(new \App\Jobs\CleanupUnpaidRegistrations)
    ->daily()
    ->at('02:00')
    ->name('cleanup-unpaid-registrations')
    ->withoutOverlapping();
