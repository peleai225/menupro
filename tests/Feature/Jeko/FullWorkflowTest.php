<?php

namespace Tests\Feature\Jeko;

use App\Enums\JekoSubMerchantStatus;
use App\Enums\MobileMoneyOperator;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\UserRole;
use App\Jobs\IntegrateJekoSubMerchantJob;
use App\Jobs\ProcessJekoPayoutJob;
use App\Models\Customer;
use App\Models\JekoSubMerchant;
use App\Models\Order;
use App\Models\PaymentTransaction;
use App\Models\Restaurant;
use App\Models\User;
use App\Services\JekoGateway;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class FullWorkflowTest extends TestCase
{
    use RefreshDatabase;

    // ─── Helpers ────────────────────────────────────────────────────────────────

    private function createSuperAdmin(): User
    {
        return User::factory()->create([
            'role' => UserRole::SUPER_ADMIN,
        ]);
    }

    private function createRestaurantAdmin(): array
    {
        $restaurant = Restaurant::factory()->create();

        $user = User::factory()->create([
            'role'          => UserRole::RESTAURANT_ADMIN,
            'restaurant_id' => $restaurant->id,
        ]);

        return [$user, $restaurant];
    }

    private function createCustomerUser(): array
    {
        $user     = User::factory()->create();
        $customer = Customer::create([
            'user_id'   => $user->id,
            'phone'     => '+225 07' . str_pad((string) rand(10000000, 99999999), 8, '0'),
            'is_active' => true,
        ]);

        return [$user, $customer];
    }

    private function createIntegratedSubMerchant(int $restaurantId): JekoSubMerchant
    {
        return JekoSubMerchant::create([
            'restaurant_id'         => $restaurantId,
            'status'                => JekoSubMerchantStatus::INTEGRATED,
            'legal_name'            => 'Restaurant Test SARL',
            'business_type'         => 'restaurant',
            'mobile_money'          => '+2250707123456',
            'mobile_money_operator' => MobileMoneyOperator::ORANGE->value,
            'email'                 => 'test@restaurant.ci',
            'jeko_merchant_id'      => 'MERCH-TEST-001',
        ]);
    }

    /**
     * Post a webhook with a proper signature using mockGatewayValid pattern.
     */
    private function mockGatewayValid(): void
    {
        $mock = $this->mock(JekoGateway::class);
        $mock->shouldReceive('forPlatform')->andReturnSelf();
        $mock->shouldReceive('verifyWebhookSignature')->andReturn(true);
    }

    private function postWebhook(array $payload): \Illuminate\Testing\TestResponse
    {
        $rawPayload = json_encode($payload);

        return $this->call(
            'POST',
            '/webhooks/jeko',
            [],
            [],
            [],
            ['HTTP_X_JEKO_SIGNATURE' => 'valid-sig', 'CONTENT_TYPE' => 'application/json'],
            $rawPayload
        );
    }

    // ─── Tests ───────────────────────────────────────────────────────────────────

    public function test_full_jeko_onboarding_to_integration_flow(): void
    {
        Http::fake();
        Queue::fake();

        $superAdmin = $this->createSuperAdmin();
        [$adminUser, $restaurant] = $this->createRestaurantAdmin();

        // Step 1: Restaurant admin submits onboarding request
        $response = $this->actingAs($adminUser)->post('/dashboard/jeko', [
            'legal_name'            => 'Restaurant Le Délice SARL',
            'business_type'         => 'restaurant',
            'mobile_money'          => '0707000000',
            'mobile_money_operator' => MobileMoneyOperator::ORANGE->value,
            'email'                 => 'contact@ledelice.ci',
        ]);

        $response->assertRedirect();

        // Step 2: Assert JekoSubMerchant with PENDING status created
        $this->assertDatabaseHas('jeko_sub_merchants', [
            'restaurant_id' => $adminUser->restaurant_id,
            'status'        => JekoSubMerchantStatus::PENDING->value,
        ]);

        $subMerchant = JekoSubMerchant::where('restaurant_id', $adminUser->restaurant_id)->first();
        $this->assertNotNull($subMerchant);
        $this->assertEquals(JekoSubMerchantStatus::PENDING, $subMerchant->status);

        // Step 3: Super admin approves
        $approvalResponse = $this->actingAs($superAdmin)
            ->post("/admin/jeko/{$subMerchant->id}/approve");

        $approvalResponse->assertRedirect();
        $approvalResponse->assertSessionHas('success');

        // Step 4: Assert status becomes APPROVED
        $subMerchant->refresh();
        $this->assertEquals(JekoSubMerchantStatus::APPROVED, $subMerchant->status);

        // Step 5: Assert IntegrateJekoSubMerchantJob was dispatched
        Queue::assertPushed(IntegrateJekoSubMerchantJob::class, function ($job) use ($subMerchant) {
            return $job->subMerchant->id === $subMerchant->id;
        });
    }

    public function test_payment_initiation_and_webhook_success_flow(): void
    {
        Http::fake();
        Queue::fake();

        $jekoMock = $this->mock(JekoGateway::class);
        $jekoMock->shouldReceive('forMarketplace')->once()->andReturnSelf();
        $jekoMock->shouldReceive('createPayment')->once()->andReturn([
            'success'     => true,
            'payment_id'  => 'JEKO-PAY-FLOW-001',
            'payment_url' => 'https://jeko.ci/pay/JEKO-PAY-FLOW-001',
        ]);

        // Step 1: Create restaurant with INTEGRATED sub-merchant
        $restaurant = Restaurant::factory()->create();
        $this->createIntegratedSubMerchant($restaurant->id);

        // Step 2: Create customer + order
        [$user, $customer] = $this->createCustomerUser();
        $order = Order::factory()->create([
            'restaurant_id'  => $restaurant->id,
            'customer_id'    => $customer->id,
            'status'         => OrderStatus::PENDING_PAYMENT,
            'payment_status' => PaymentStatus::PENDING,
            'payment_method' => 'jeko',
            'total'          => 7500,
            'subtotal'       => 7500,
        ]);

        // Steps 3-4: POST to payment initiate — assert 200, payment_url returned
        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/client/payment/{$order->id}/initiate");

        $response->assertStatus(200);
        $this->assertNotNull($response->json('payment_url'));

        // Step 5: Assert PaymentTransaction created with gateway=jeko
        $this->assertDatabaseHas('payment_transactions', [
            'order_id' => $order->id,
            'gateway'  => 'jeko',
            'status'   => 'pending',
        ]);

        // Retrieve the stored payment_reference for webhook
        $order->refresh();
        $paymentReference = $order->payment_reference ?? 'JEKO-PAY-FLOW-001';

        // Configure mock for webhook verification
        $this->mockGatewayValid();
        Queue::fake(); // Reset Queue fake for clean assertion below

        // Step 6: Simulate webhook POST with payment.success event
        $webhookResponse = $this->postWebhook([
            'event'      => 'payment.success',
            'payment_id' => $paymentReference,
        ]);

        $webhookResponse->assertStatus(200);

        // Step 7: Assert order payment_status = COMPLETED
        $order->refresh();
        $this->assertEquals(PaymentStatus::COMPLETED, $order->payment_status);

        // Step 8: Assert ProcessJekoPayoutJob was dispatched
        Queue::assertPushed(ProcessJekoPayoutJob::class, function ($job) use ($order) {
            return $job->order->id === $order->id;
        });
    }

    public function test_payment_webhook_payout_job_executes(): void
    {
        Http::fake();

        // Step 1: Create INTEGRATED restaurant + paid order
        $restaurant = Restaurant::factory()->create();
        $this->createIntegratedSubMerchant($restaurant->id);

        $order = Order::factory()->create([
            'restaurant_id'     => $restaurant->id,
            'payment_status'    => PaymentStatus::COMPLETED,
            'payment_reference' => 'PAY-REF-PAYOUT-TEST',
            'total'             => 5000,
        ]);

        // Step 2: Create ProcessJekoPayoutJob instance
        $job = new ProcessJekoPayoutJob($order);

        // Steps 3-4: Mock the gateway so no real HTTP is needed;
        // verifies that payout() is called with correct parameters
        $gatewayMock = $this->mock(JekoGateway::class);
        $gatewayMock->shouldReceive('forMarketplace')
            ->once()
            ->with(\Mockery::type(Restaurant::class))
            ->andReturnSelf();
        $gatewayMock->shouldReceive('payout')
            ->once()
            ->withArgs(function ($phone, $amount, $name, $reason, $reference) use ($order) {
                return $amount === (int) $order->total
                    && str_contains($reference, 'PAYOUT-ORDER-' . $order->id);
            })
            ->andReturn([
                'success'     => true,
                'transfer_id' => 'TRANSFER-MOCK-001',
                'status'      => 'processing',
            ]);

        // Step 5: Execute the job — should not throw any exception
        $job->handle($gatewayMock);

        // Step 6: Assert payout was called (verified by Mockery expectation above)
    }

    public function test_rejected_restaurant_cannot_accept_jeko_payments(): void
    {
        Http::fake();

        // Step 1: Restaurant with REJECTED sub-merchant
        $restaurant = Restaurant::factory()->create();

        JekoSubMerchant::create([
            'restaurant_id'         => $restaurant->id,
            'status'                => JekoSubMerchantStatus::REJECTED,
            'legal_name'            => 'Restaurant Rejeté SARL',
            'business_type'         => 'restaurant',
            'mobile_money'          => '+2250707000001',
            'mobile_money_operator' => MobileMoneyOperator::MTN->value,
            'email'                 => 'rejected@test.ci',
            'rejected_reason'       => 'Documents insuffisants',
        ]);

        // Step 2: Create order with payment_method=jeko
        [$user, $customer] = $this->createCustomerUser();
        $order = Order::factory()->create([
            'restaurant_id'  => $restaurant->id,
            'customer_id'    => $customer->id,
            'status'         => OrderStatus::PENDING_PAYMENT,
            'payment_status' => PaymentStatus::PENDING,
            'payment_method' => 'jeko',
            'total'          => 3000,
            'subtotal'       => 3000,
        ]);

        // Step 3: POST to payment initiate — assert 422
        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/client/payment/{$order->id}/initiate");

        $response->assertStatus(422);

        // Step 4: Assert no PaymentTransaction created
        $this->assertDatabaseMissing('payment_transactions', [
            'order_id' => $order->id,
        ]);
    }
}
