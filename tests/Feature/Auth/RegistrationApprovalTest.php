<?php

namespace Tests\Feature\Auth;

use App\Enums\RestaurantStatus;
use App\Enums\SubscriptionStatus;
use App\Models\Plan;
use App\Models\Restaurant;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationApprovalTest extends TestCase
{
    use RefreshDatabase;

    private function registrationPayload(): array
    {
        return [
            'name' => 'Koné',
            'restaurant_name' => 'Chez Koné',
            'restaurant_type' => 'restaurant',
            'phone' => '0700000000',
            'email' => 'kone@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'restaurant_city' => 'Bouaké',
            'plan' => 'stand',
            'terms' => '1',
        ];
    }

    public function test_registration_creates_pending_restaurant_with_frozen_trial(): void
    {
        Plan::firstOrCreate(
            ['slug' => 'stand'],
            ['name' => 'Stand', 'price' => 5000, 'duration_days' => 30, 'is_active' => true]
        );

        $this->post(route('register.post'), $this->registrationPayload())
            ->assertRedirect(route('restaurant.pending'));

        $restaurant = Restaurant::firstOrFail();
        $this->assertSame(RestaurantStatus::PENDING, $restaurant->status);

        $sub = Subscription::firstOrFail();
        $this->assertNull($sub->starts_at);
        $this->assertNull($sub->ends_at);
    }

    public function test_pending_restaurant_cannot_reach_dashboard(): void
    {
        $restaurant = Restaurant::factory()->create(['status' => RestaurantStatus::PENDING]);
        $user = User::factory()->restaurantAdmin($restaurant)->create();

        $this->actingAs($user)
            ->get(route('restaurant.dashboard'))
            ->assertRedirect(route('restaurant.pending'));
    }

    public function test_approval_activates_and_starts_trial(): void
    {
        $restaurant = Restaurant::factory()->create(['status' => RestaurantStatus::PENDING]);
        $sub = Subscription::factory()->create([
            'restaurant_id' => $restaurant->id,
            'status' => SubscriptionStatus::TRIAL,
            'is_trial' => true,
            'trial_days' => 7,
            'starts_at' => null,
            'ends_at' => null,
        ]);
        $admin = User::factory()->superAdmin()->create();

        $this->actingAs($admin)
            ->post(route('super-admin.restaurants.approve', $restaurant))
            ->assertRedirect();

        $restaurant->refresh();
        $sub->refresh();
        $this->assertSame(RestaurantStatus::ACTIVE, $restaurant->status);
        $this->assertNotNull($sub->starts_at);
        $this->assertTrue($sub->ends_at->greaterThan(now()->addDays(6)));
    }
}
