<?php

namespace Tests\Feature\Jeko;

use App\Enums\PaymentStatus;
use App\Enums\SubscriptionStatus;
use App\Jobs\ProcessJekoPayoutJob;
use App\Models\Order;
use App\Models\Plan;
use App\Models\Restaurant;
use App\Models\Subscription;
use App\Services\JekoGateway;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class WebhookTest extends TestCase
{
    use RefreshDatabase;

    private const WEBHOOK_SECRET = 'test-webhook-secret-jeko';

    // ─── Helpers ────────────────────────────────────────────────────────────────

    private function sign(string $rawPayload): string
    {
        return hash_hmac('sha256', $rawPayload, self::WEBHOOK_SECRET);
    }

    private function postWebhook(array $payload, ?string $signature = null): \Illuminate\Testing\TestResponse
    {
        $rawPayload = json_encode($payload);

        if ($signature === null) {
            $signature = $this->sign($rawPayload);
        }

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

    private function mockGatewayValid(): void
    {
        $mock = $this->mock(JekoGateway::class);

        $mock->shouldReceive('forPlatform')
            ->andReturnSelf();

        $mock->shouldReceive('verifyWebhookSignature')
            ->andReturn(true);
    }

    private function mockGatewayInvalid(): void
    {
        $mock = $this->mock(JekoGateway::class);

        $mock->shouldReceive('forPlatform')
            ->andReturnSelf();

        $mock->shouldReceive('verifyWebhookSignature')
            ->andReturn(false);
    }

    // ─── Tests ───────────────────────────────────────────────────────────────────

    public function test_webhook_rejects_invalid_signature(): void
    {
        $this->mockGatewayInvalid();
        Queue::fake();

        $response = $this->postWebhook(
            ['event' => 'payment.success', 'payment_id' => 'PAY-123'],
            'bad-signature'
        );

        $response->assertStatus(401);
        $response->assertJson(['error' => 'Invalid signature']);
        Queue::assertNothingPushed();
    }

    public function test_payment_success_marks_order_paid(): void
    {
        $this->mockGatewayValid();
        Queue::fake();

        $restaurant = Restaurant::factory()->create();
        $order = Order::factory()->create([
            'restaurant_id'  => $restaurant->id,
            'payment_status' => PaymentStatus::PENDING,
            'payment_reference' => null,
            'total'          => 5000,
        ]);

        $paymentId = 'PAY-ORDER-' . $order->id;

        // Store payment_reference so webhook can find the order
        $order->update(['payment_reference' => $paymentId]);

        $response = $this->postWebhook([
            'event'      => 'payment.success',
            'payment_id' => $paymentId,
        ]);

        $response->assertStatus(200);
        $response->assertJson(['status' => 'ok']);

        $order->refresh();
        $this->assertEquals(PaymentStatus::COMPLETED, $order->payment_status);
    }

    public function test_payment_success_dispatches_payout_job(): void
    {
        $this->mockGatewayValid();
        Queue::fake();

        $restaurant = Restaurant::factory()->create();
        $order = Order::factory()->create([
            'restaurant_id'     => $restaurant->id,
            'payment_status'    => PaymentStatus::PENDING,
            'payment_reference' => null,
            'total'             => 5000,
        ]);

        $paymentId = 'PAY-ORDER-' . $order->id;
        $order->update(['payment_reference' => $paymentId]);

        $response = $this->postWebhook([
            'event'      => 'payment.success',
            'payment_id' => $paymentId,
        ]);

        $response->assertStatus(200);
        Queue::assertPushed(ProcessJekoPayoutJob::class, function ($job) use ($order) {
            return $job->order->id === $order->id;
        });
    }

    public function test_payment_success_activates_subscription(): void
    {
        $this->mockGatewayValid();
        Queue::fake();

        $restaurant = Restaurant::factory()->create();
        $plan = Plan::create([
            'name'         => 'MenuPro',
            'slug'         => 'menupro',
            'price'        => 15000,
            'duration_days' => 30,
            'is_active'    => true,
        ]);
        $subscription = Subscription::create([
            'restaurant_id'    => $restaurant->id,
            'plan_id'          => $plan->id,
            'status'           => SubscriptionStatus::PENDING,
            'starts_at'        => now(),
            'ends_at'          => now()->addDays(30),
            'amount_paid'      => 15000,
            'payment_reference' => null,
        ]);

        $paymentId = 'PAY-SUB-' . $subscription->id;
        $subscription->update(['payment_reference' => $paymentId]);

        $response = $this->postWebhook([
            'event'      => 'payment.success',
            'payment_id' => $paymentId,
        ]);

        $response->assertStatus(200);

        $subscription->refresh();
        $this->assertEquals(SubscriptionStatus::ACTIVE, $subscription->status);
    }

    public function test_payment_failed_marks_order_failed(): void
    {
        $this->mockGatewayValid();
        Queue::fake();

        $restaurant = Restaurant::factory()->create();
        $order = Order::factory()->create([
            'restaurant_id'     => $restaurant->id,
            'payment_status'    => PaymentStatus::PENDING,
            'payment_reference' => null,
        ]);

        $paymentId = 'PAY-FAIL-' . $order->id;
        $order->update(['payment_reference' => $paymentId]);

        $response = $this->postWebhook([
            'event'      => 'payment.failed',
            'payment_id' => $paymentId,
        ]);

        $response->assertStatus(200);
        $response->assertJson(['status' => 'ok']);

        $order->refresh();
        $this->assertEquals(PaymentStatus::FAILED, $order->payment_status);
        Queue::assertNothingPushed();
    }
}
