<?php

namespace Tests\Feature\Restaurant;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\SubscriptionStatus;
use App\Jobs\ProcessJekoPayoutJob;
use App\Jobs\ProcessTrialExpiration;
use App\Models\Order;
use App\Models\Plan;
use App\Models\Restaurant;
use App\Models\Subscription;
use App\Services\JekoGateway;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class PayoutAfterAcceptanceTest extends TestCase
{
    use RefreshDatabase;

    private function jekoOrder(OrderStatus $status): Order
    {
        $restaurant = Restaurant::factory()->create();

        return Order::factory()->create([
            'restaurant_id'  => $restaurant->id,
            'status'         => $status,
            'payment_method' => 'jeko',
            'payment_status' => PaymentStatus::COMPLETED,
            'paid_at'        => now(),
        ]);
    }

    public function test_accepting_a_jeko_order_queues_the_payout(): void
    {
        Queue::fake();
        $order = $this->jekoOrder(OrderStatus::PAID);

        $order->transitionTo(OrderStatus::CONFIRMED);

        Queue::assertPushed(ProcessJekoPayoutJob::class, fn ($job) => $job->order->is($order));
    }

    public function test_no_payout_while_the_order_awaits_acceptance(): void
    {
        $order = $this->jekoOrder(OrderStatus::PAID);
        $gateway = $this->mock(JekoGateway::class);
        $gateway->shouldNotReceive('payout');

        (new ProcessJekoPayoutJob($order))->handle($gateway);

        $this->assertNotSame('completed', $order->fresh()->payout_status);
    }

    public function test_no_payout_for_a_cancelled_order(): void
    {
        $order = $this->jekoOrder(OrderStatus::CANCELLED);
        $gateway = $this->mock(JekoGateway::class);
        $gateway->shouldNotReceive('payout');

        (new ProcessJekoPayoutJob($order))->handle($gateway);

        $this->assertNotSame('completed', $order->fresh()->payout_status);
    }

    public function test_trial_of_a_deleted_restaurant_does_not_crash_the_job(): void
    {
        $plan = Plan::factory()->create();
        $restaurant = Restaurant::factory()->create();
        $trial = Subscription::factory()->create([
            'restaurant_id' => $restaurant->id,
            'plan_id'       => $plan->id,
            'status'        => SubscriptionStatus::TRIAL,
            'is_trial'      => true,
            'starts_at'     => now()->subDays(40),
            'ends_at'       => now()->subDay(),
        ]);
        $restaurant->delete();

        (new ProcessTrialExpiration())->handle();

        $this->assertSame(SubscriptionStatus::EXPIRED, $trial->fresh()->status);
    }
}
