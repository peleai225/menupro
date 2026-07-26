<?php

namespace Tests\Feature\Jeko;

use App\Enums\JekoSubMerchantStatus;
use App\Enums\MobileMoneyOperator;
use App\Enums\UserRole;
use App\Models\JekoSubMerchant;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class OnboardingRequestTest extends TestCase
{
    use RefreshDatabase;

    // ─── Helpers ────────────────────────────────────────────────────────────────

    private function createRestaurantAdmin(): User
    {
        $restaurant = Restaurant::factory()->create();

        return User::factory()->create([
            'role'          => UserRole::RESTAURANT_ADMIN,
            'restaurant_id' => $restaurant->id,
        ]);
    }

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'legal_name'            => 'Restaurant Le Délice SARL',
            'business_type'         => 'restaurant',
            'mobile_money'          => '0707000000',
            'mobile_money_operator' => MobileMoneyOperator::ORANGE->value,
            'email'                 => 'contact@ledelice.ci',
        ], $overrides);
    }

    // ─── Tests ───────────────────────────────────────────────────────────────────

    public function test_authenticated_restaurant_admin_can_see_onboarding_form(): void
    {
        $user = $this->createRestaurantAdmin();

        $response = $this->actingAs($user)->get('/dashboard/jeko');

        $response->assertStatus(200);
        $response->assertViewIs('restaurant.jeko-onboarding');
    }

    public function test_restaurant_can_submit_onboarding_request(): void
    {
        Notification::fake();

        $user = $this->createRestaurantAdmin();

        $response = $this->actingAs($user)->post('/dashboard/jeko', $this->validPayload());

        $response->assertRedirect('/dashboard/jeko');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('jeko_sub_merchants', [
            'restaurant_id'         => $user->restaurant_id,
            'status'                => JekoSubMerchantStatus::PENDING->value,
            'legal_name'            => 'Restaurant Le Délice SARL',
            'mobile_money'          => '0707000000',
            'mobile_money_operator' => MobileMoneyOperator::ORANGE->value,
        ]);
    }

    public function test_duplicate_request_is_rejected(): void
    {
        Notification::fake();

        $user = $this->createRestaurantAdmin();

        // First submission
        JekoSubMerchant::create([
            'restaurant_id'         => $user->restaurant_id,
            'status'                => JekoSubMerchantStatus::PENDING,
            'legal_name'            => 'Existing SARL',
            'business_type'         => 'restaurant',
            'mobile_money'          => '0707000001',
            'mobile_money_operator' => MobileMoneyOperator::MTN->value,
            'email'                 => 'existing@test.ci',
        ]);

        // Second attempt should be rejected
        $response = $this->actingAs($user)->post('/dashboard/jeko', $this->validPayload());

        $response->assertRedirect();
        $response->assertSessionHas('error');

        // Ensure no second record was created
        $this->assertDatabaseCount('jeko_sub_merchants', 1);
    }

    public function test_validation_fails_for_missing_required_fields(): void
    {
        $user = $this->createRestaurantAdmin();

        $response = $this->actingAs($user)->post('/dashboard/jeko', [
            'legal_name'   => '',
            'mobile_money' => '',
        ]);

        $response->assertSessionHasErrors(['legal_name', 'mobile_money']);
        $this->assertDatabaseCount('jeko_sub_merchants', 0);
    }
}
