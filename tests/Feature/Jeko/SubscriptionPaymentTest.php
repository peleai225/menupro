<?php

namespace Tests\Feature\Jeko;

use App\Enums\SubscriptionStatus;
use App\Models\Plan;
use App\Models\Restaurant;
use App\Models\Subscription;
use App\Services\JekoGateway;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class SubscriptionPaymentTest extends TestCase
{
    use RefreshDatabase;

    // ─── Helpers ────────────────────────────────────────────────────────────────

    private function mockGatewayValid(): void
    {
        $mock = $this->mock(JekoGateway::class);
        $mock->shouldReceive('forPlatform')->andReturnSelf();
        $mock->shouldReceive('verifyWebhookSignature')->andReturn(true);
    }

    private function mockGatewayInvalid(): void
    {
        $mock = $this->mock(JekoGateway::class);
        $mock->shouldReceive('forPlatform')->andReturnSelf();
        $mock->shouldReceive('verifyWebhookSignature')->andReturn(false);
    }

    private function postWebhook(array $payload, string $signature = 'valid-sig'): \Illuminate\Testing\TestResponse
    {
        $rawPayload = json_encode($payload);

        return $this->call(
            'POST',
            '/webhooks/jeko',
            [],
            [],
            [],
            ['HTTP_X_JEKO_SIGNATURE' => $signature, 'CONTENT_TYPE' => 'application/json'],
            $rawPayload
        );
    }

    private function createPlan(): Plan
    {
        return Plan::create([
            'name'          => 'MenuPro Gold',
            'slug'          => 'menupro-gold',
            'price'         => 15000,
            'duration_days' => 30,
            'is_active'     => true,
        ]);
    }

    private function createSubscription(int $restaurantId, int $planId, SubscriptionStatus $status, ?string $paymentReference = null): Subscription
    {
        return Subscription::create([
            'restaurant_id'     => $restaurantId,
            'plan_id'           => $planId,
            'status'            => $status,
            'starts_at'         => now(),
            'ends_at'           => now()->addDays(30),
            'amount_paid'       => 15000,
            'payment_reference' => $paymentReference,
        ]);
    }

    // ─── Tests ───────────────────────────────────────────────────────────────────

    public function test_jeko_webhook_activates_pending_subscription(): void
    {
        $this->mockGatewayValid();
        Queue::fake();

        // Step 1: Create restaurant + subscription with status=PENDING
        $restaurant = Restaurant::factory()->create(['orders_blocked' => true]);
        $plan       = $this->createPlan();
        $paymentId  = 'jeko_pay_123';

        $subscription = $this->createSubscription(
            $restaurant->id,
            $plan->id,
            SubscriptionStatus::PENDING,
            $paymentId
        );

        // Step 2: POST valid signed webhook with payment.success
        $response = $this->postWebhook([
            'event'      => 'payment.success',
            'payment_id' => $paymentId,
        ]);

        $response->assertStatus(200);
        $response->assertJson(['status' => 'ok']);

        // Step 3: Assert subscription status = ACTIVE
        $subscription->refresh();
        $this->assertEquals(SubscriptionStatus::ACTIVE, $subscription->status);

        // Step 4: Assert restaurant orders_blocked = false
        $restaurant->refresh();
        $this->assertFalse((bool) $restaurant->orders_blocked);
    }

    public function test_jeko_webhook_converts_trial_subscription(): void
    {
        $this->mockGatewayValid();
        Queue::fake();

        // Step 1: Create subscription with status=TRIAL
        $restaurant = Restaurant::factory()->create();
        $plan       = $this->createPlan();
        $paymentId  = 'jeko_pay_trial_456';

        $subscription = $this->createSubscription(
            $restaurant->id,
            $plan->id,
            SubscriptionStatus::TRIAL,
            $paymentId
        );

        // Step 2: POST webhook with payment.success
        $response = $this->postWebhook([
            'event'      => 'payment.success',
            'payment_id' => $paymentId,
        ]);

        $response->assertStatus(200);

        // Step 3: Assert subscription status = ACTIVE (trial converted)
        $subscription->refresh();
        $this->assertEquals(SubscriptionStatus::ACTIVE, $subscription->status);
    }

    public function test_jeko_webhook_fails_if_invalid_signature(): void
    {
        $this->mockGatewayInvalid();
        Queue::fake();

        $restaurant = Restaurant::factory()->create(['orders_blocked' => true]);
        $plan       = $this->createPlan();
        $paymentId  = 'jeko_pay_789';

        $subscription = $this->createSubscription(
            $restaurant->id,
            $plan->id,
            SubscriptionStatus::PENDING,
            $paymentId
        );

        // Step 1: POST to /webhooks/jeko with wrong signature
        $response = $this->postWebhook(
            ['event' => 'payment.success', 'payment_id' => $paymentId],
            'bad-invalid-signature'
        );

        // Step 2: Assert 401 response
        $response->assertStatus(401);
        $response->assertJson(['error' => 'Invalid signature']);

        // Step 3: Assert no DB changes
        $subscription->refresh();
        $this->assertEquals(SubscriptionStatus::PENDING, $subscription->status);

        $restaurant->refresh();
        $this->assertTrue((bool) $restaurant->orders_blocked);
    }
}
