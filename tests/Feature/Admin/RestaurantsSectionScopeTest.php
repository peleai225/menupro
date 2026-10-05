<?php

namespace Tests\Feature\Admin;

use App\Enums\RestaurantStatus;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RestaurantsSectionScopeTest extends TestCase
{
    use RefreshDatabase;

    public function test_restaurants_section_employee_cannot_impersonate_extend_or_destroy(): void
    {
        $emp = User::factory()->superAdmin()->create(['admin_permissions' => ['restaurants']]);
        $restaurant = Restaurant::factory()->create(['status' => RestaurantStatus::ACTIVE]);

        $this->actingAs($emp)
            ->post(route('super-admin.restaurants.impersonate', $restaurant))
            ->assertForbidden();

        $this->actingAs($emp)
            ->post(route('super-admin.restaurants.extend-subscription', $restaurant))
            ->assertForbidden();

        $this->actingAs($emp)
            ->delete(route('super-admin.restaurants.destroy', $restaurant))
            ->assertForbidden();

        // L'approbation reste accessible : c'est le cœur de la section "restaurants".
        $pending = Restaurant::factory()->create(['status' => RestaurantStatus::PENDING]);
        $this->actingAs($emp)
            ->post(route('super-admin.restaurants.approve', $pending))
            ->assertRedirect();
    }
}
