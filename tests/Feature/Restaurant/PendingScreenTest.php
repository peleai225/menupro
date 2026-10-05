<?php

namespace Tests\Feature\Restaurant;

use App\Enums\RestaurantStatus;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PendingScreenTest extends TestCase
{
    use RefreshDatabase;

    public function test_pending_screen_is_reachable_by_authenticated_owner(): void
    {
        $restaurant = Restaurant::factory()->create(['status' => RestaurantStatus::PENDING]);
        $user = User::factory()->restaurantAdmin($restaurant)->create();

        $this->actingAs($user)
            ->get(route('restaurant.pending'))
            ->assertOk()
            ->assertSee('en attente de validation', false);
    }
}
