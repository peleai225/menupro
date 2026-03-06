<?php

use App\Jobs\CheckLowStock;
use App\Jobs\ProcessSubscriptionExpiration;
use App\Jobs\ProcessTrialExpiration;
use App\Jobs\SendSubscriptionReminders;
use App\Models\Order;
use App\Models\Restaurant;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Diagnostic 404 suivi de commande : php artisan order-status:diagnose {slug} {token}
Artisan::command('order-status:diagnose {slug} {token}', function () {
    $slug = trim($this->argument('slug'));
    $token = trim($this->argument('token'));
    $this->info("Recherche : slug={$slug}, token={$token}");
    $this->newLine();

    $restaurant = Restaurant::whereRaw('LOWER(slug) = ?', [strtolower($slug)])->first();
    if (!$restaurant) {
        $normalized = str_replace('-', '', strtolower($slug));
        $restaurant = Restaurant::whereRaw("LOWER(REPLACE(slug, '-', '')) = ?", [$normalized])->first();
    }

    if ($restaurant) {
        $this->info("✓ Restaurant trouvé : id={$restaurant->id}, slug={$restaurant->slug}, name={$restaurant->name}");
    } else {
        $this->warn('✗ Aucun restaurant avec ce slug.');
        $this->line('  Slugs existants (ex.) : ' . Restaurant::limit(5)->pluck('slug')->implode(', '));
        return 1;
    }

    $order = Order::where('tracking_token', $token)->first();
    if ($order) {
        $this->info("✓ Commande trouvée : id={$order->id}, restaurant_id={$order->restaurant_id}, reference={$order->reference}");
        if ((int) $order->restaurant_id !== (int) $restaurant->id) {
            $this->warn("  ⚠ La commande appartient au restaurant id {$order->restaurant_id}, pas à {$restaurant->slug} (id {$restaurant->id}).");
            return 1;
        }
        $this->info('  URL correcte : ' . route('r.order.status', [$restaurant->slug, $order->tracking_token]));
        return 0;
    }

    $this->warn('✗ Aucune commande avec ce tracking_token.');
    $ordersForRestaurant = Order::where('restaurant_id', $restaurant->id)->whereNotNull('tracking_token')->latest()->limit(3)->get(['id', 'reference', 'tracking_token']);
    if ($ordersForRestaurant->isNotEmpty()) {
        $this->line('  Dernières commandes de ce restaurant (avec token) :');
        foreach ($ordersForRestaurant as $o) {
            $this->line('    - ' . route('r.order.status', [$restaurant->slug, $o->tracking_token]));
        }
    }
    return 1;
})->purpose('Vérifie pourquoi une URL /r/{slug}/commande/{token} renvoie 404');

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
