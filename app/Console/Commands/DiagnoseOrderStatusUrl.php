<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Models\Restaurant;
use Illuminate\Console\Command;

class DiagnoseOrderStatusUrl extends Command
{
    protected $signature = 'order-status:diagnose {slug} {token}';

    protected $description = 'Vérifie pourquoi une URL de suivi de commande (/r/{slug}/commande/{token}) renvoie 404';

    public function handle(): int
    {
        $slug = trim($this->argument('slug'));
        $token = trim($this->argument('token'));

        $this->info("Recherche : slug={$slug}, token={$token}");
        $this->newLine();

        // Restaurant
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
            return self::FAILURE;
        }

        // Order
        $order = Order::where('tracking_token', $token)->first();
        if ($order) {
            $this->info("✓ Commande trouvée : id={$order->id}, restaurant_id={$order->restaurant_id}, reference={$order->reference}");
            if ((int) $order->restaurant_id !== (int) $restaurant->id) {
                $this->warn("  ⚠ La commande appartient au restaurant id {$order->restaurant_id}, pas à {$restaurant->slug} (id {$restaurant->id}).");
                return self::FAILURE;
            }
            $this->info('  URL correcte : ' . route('r.order.status', [$restaurant->slug, $order->tracking_token]));
            return self::SUCCESS;
        }

        $this->warn('✗ Aucune commande avec ce tracking_token.');
        $ordersForRestaurant = Order::where('restaurant_id', $restaurant->id)->whereNotNull('tracking_token')->latest()->limit(3)->get(['id', 'reference', 'tracking_token']);
        if ($ordersForRestaurant->isNotEmpty()) {
            $this->line('  Dernières commandes de ce restaurant (avec token) :');
            foreach ($ordersForRestaurant as $o) {
                $this->line('    - ' . route('r.order.status', [$restaurant->slug, $o->tracking_token]));
            }
        }
        return self::FAILURE;
    }
}
