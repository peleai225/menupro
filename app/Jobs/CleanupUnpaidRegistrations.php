<?php

namespace App\Jobs;

use App\Enums\RestaurantStatus;
use App\Enums\SubscriptionStatus;
use App\Models\Restaurant;
use App\Models\Subscription;
use App\Services\MediaUploader;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CleanupUnpaidRegistrations implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     */
    public function handle(MediaUploader $mediaUploader): void
    {
        Log::info('Cleaning up unpaid registrations (older than 48 hours)...');

        // Get all restaurants with PENDING status and PENDING subscriptions older than 48 hours
        $unpaidRestaurants = Restaurant::query()
            ->where('status', RestaurantStatus::PENDING)
            ->whereHas('subscriptions', function ($query) {
                $query->where('status', SubscriptionStatus::PENDING)
                    ->where('created_at', '<', now()->subHours(48));
            })
            ->with(['subscriptions' => function ($query) {
                $query->where('status', SubscriptionStatus::PENDING);
            }])
            ->get();

        $deletedCount = 0;

        foreach ($unpaidRestaurants as $restaurant) {
            try {
                DB::beginTransaction();

                // Delete subscription add-ons
                foreach ($restaurant->subscriptions as $subscription) {
                    $subscription->addons()->delete();
                }

                // Delete pending subscriptions
                $restaurant->subscriptions()->where('status', SubscriptionStatus::PENDING)->delete();

                // Delete restaurant files
                if ($restaurant->logo_path) {
                    $mediaUploader->delete($restaurant->logo_path);
                }
                if ($restaurant->banner_path) {
                    $mediaUploader->delete($restaurant->banner_path);
                }
                if ($restaurant->rccm_document_path) {
                    $mediaUploader->delete($restaurant->rccm_document_path);
                }

                // Delete users associated with this restaurant
                $restaurant->users()->delete();

                // Force delete the restaurant (permanent deletion, not soft delete)
                $restaurant->forceDelete();

                DB::commit();
                $deletedCount++;

                Log::info("Deleted unpaid registration: Restaurant ID {$restaurant->id} ({$restaurant->name})");

            } catch (\Exception $e) {
                DB::rollBack();
                Log::error("Error cleaning up unpaid registration (Restaurant ID {$restaurant->id}): " . $e->getMessage());
            }
        }

        Log::info("Cleaned up {$deletedCount} unpaid registrations.");
    }
}
