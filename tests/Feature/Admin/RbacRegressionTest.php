<?php

namespace Tests\Feature\Admin;

use App\Enums\RestaurantStatus;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RbacRegressionTest extends TestCase
{
    use RefreshDatabase;

    public function test_employee_with_restaurants_can_approve(): void
    {
        $emp = User::factory()->superAdmin()->create(['admin_permissions' => ['restaurants']]);
        $restaurant = Restaurant::factory()->create(['status' => RestaurantStatus::PENDING]);

        $this->actingAs($emp)
            ->post(route('super-admin.restaurants.approve', $restaurant))
            ->assertRedirect();

        $this->assertSame(RestaurantStatus::ACTIVE, $restaurant->refresh()->status);
    }

    public function test_pending_restaurant_public_page_still_blocked(): void
    {
        $restaurant = Restaurant::factory()->create([
            'status' => RestaurantStatus::PENDING, 'slug' => 'chez-test',
        ]);

        $this->get(route('r.menu', $restaurant->slug))->assertStatus(503);
    }
}
