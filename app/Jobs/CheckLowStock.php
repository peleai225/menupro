<?php

namespace App\Jobs;

use App\Models\Ingredient;
use App\Models\Restaurant;
use App\Notifications\LowStockNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CheckLowStock implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('Checking low stock levels...');

        // Get restaurants with stock management feature
        $restaurants = Restaurant::query()
            ->whereHas('currentPlan', fn($q) => $q->where('has_stock_management', true))
            ->with(['owner', 'notificationSettings'])
            ->get();

        foreach ($restaurants as $restaurant) {
            $this->checkRestaurantStock($restaurant);
        }
    }

    /**
     * Check stock for a single restaurant
     */
    protected function checkRestaurantStock(Restaurant $restaurant): void
    {
        // Check notification settings
        $settings = $restaurant->notificationSettings;
        if ($settings && !$settings->email_low_stock) {
            return;
        }

        // Get low stock ingredients
        $lowStockIngredients = Ingredient::query()
            ->where('restaurant_id', $restaurant->id)
            ->where('is_active', true)
            ->whereColumn('current_quantity', '<=', 'min_quantity')
            ->get();

        if ($lowStockIngredients->isEmpty()) {
            return;
        }

        // Send notification to owner
        if ($restaurant->owner) {
            $restaurant->owner->notify(new LowStockNotification($restaurant, $lowStockIngredients));

            Log::info("Low stock alert sent for restaurant: {$restaurant->name}", [
                'restaurant_id' => $restaurant->id,
                'ingredients_count' => $lowStockIngredients->count(),
            ]);
        }
    }
}

