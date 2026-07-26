<?php

namespace Tests\Feature\Jeko;

use App\Enums\JekoSubMerchantStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Customer;
use App\Models\JekoSubMerchant;
use App\Models\Order;
use App\Models\PaymentTransaction;
use App\Models\Restaurant;
use App\Models\User;
use App\Services\JekoGateway;
use App\Services\WaveGateway;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PaymentFlowTest extends TestCase
{
    use RefreshDatabase;

    // ─── Helpers ────────────────────────────────────────────────────────────────

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

    private function createOrder(int $restaurantId, int $customerId, string $paymentMethod): Order
    {
        return Order::factory()->create([
            'restaurant_id'  => $restaurantId,
            'customer_id'    => $customerId,
            'status'         => OrderStatus::PENDING_PAYMENT,
            'payment_status' => PaymentStatus::PENDING,
            'payment_method' => $paymentMethod,
            'total'          => 5000,
            'subtotal'       => 5000,
        ]);
    }

    // ─── Tests ───────────────────────────────────────────────────────────────────

    public function test_wave_payment_initiation_still_works(): void
    {
        Http::fake();

        $waveMock = $this->mock(WaveGateway::class);
        $waveMock->shouldReceive('createCheckoutSession')
            ->withAnyArgs()
            ->once()
            ->andReturn([
                'success'         => true,
                'checkout_id'     => 'wave-session-abc',
                'wave_launch_url' => 'https://wave.com/pay/wave-session-abc',
                'checkout_status' => 'open',
            ]);

        [$user, $customer] = $this->createCustomerUser();
        $restaurant = Restaurant::factory()->create();
        $order      = $this->createOrder($restaurant->id, $customer->id, 'wave');

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/client/payment/{$order->id}/initiate");

        $response->assertStatus(200);
        $response->assertJsonStructure(['payment_url', 'session_id', 'order_id', 'amount', 'tracking_token']);
        $this->assertNotNull($response->json('payment_url'));

        $this->assertDatabaseHas('payment_transactions', [
            'order_id' => $order->id,
            'gateway'  => 'wave',
            'status'   => 'pending',
        ]);
    }

    public function test_jeko_payment_initiation_creates_transaction(): void
    {
        Http::fake();

        $jekoMock = $this->mock(JekoGateway::class);
        $jekoMock->shouldReceive('forMarketplace')->once()->andReturnSelf();
        $jekoMock->shouldReceive('createPayment')->once()->andReturn([
            'success'     => true,
            'payment_id'  => 'JEKO-PAY-123',
            'payment_url' => 'https://jeko.ci/pay/JEKO-PAY-123',
        ]);

        [$user, $customer] = $this->createCustomerUser();
        $restaurant = Restaurant::factory()->create();

        JekoSubMerchant::create([
            'restaurant_id'        => $restaurant->id,
            'status'               => JekoSubMerchantStatus::INTEGRATED,
            'legal_name'           => 'Restaurant Test SARL',
            'mobile_money'         => '+2250701020304',
            'mobile_money_operator' => 'wave',
            'email'                => 'restaurant@test.ci',
            'jeko_merchant_id'     => 'MERCH-001',
        ]);

        $order = $this->createOrder($restaurant->id, $customer->id, 'jeko');

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/client/payment/{$order->id}/initiate");

        $response->assertStatus(200);
        $response->assertJsonStructure(['payment_url', 'payment_id', 'order_id', 'amount', 'tracking_token']);

        $this->assertDatabaseHas('payment_transactions', [
            'order_id'       => $order->id,
            'gateway'        => 'jeko',
            'jeko_payment_id' => 'JEKO-PAY-123',
            'status'         => 'pending',
        ]);
    }

    public function test_jeko_payment_fails_if_restaurant_not_integrated(): void
    {
        Http::fake();

        [$user, $customer] = $this->createCustomerUser();
        $restaurant = Restaurant::factory()->create();

        // No JekoSubMerchant created — restaurant not integrated
        $order = $this->createOrder($restaurant->id, $customer->id, 'jeko');

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/client/payment/{$order->id}/initiate");

        $response->assertStatus(422);
        $response->assertJson(['message' => 'Ce restaurant n\'est pas encore intégré avec Jeko.']);

        $this->assertDatabaseMissing('payment_transactions', [
            'order_id' => $order->id,
        ]);
    }

    public function test_unsupported_payment_method_returns_422(): void
    {
        Http::fake();

        [$user, $customer] = $this->createCustomerUser();
        $restaurant = Restaurant::factory()->create();
        $order      = $this->createOrder($restaurant->id, $customer->id, 'moneygo');

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/client/payment/{$order->id}/initiate");

        $response->assertStatus(422);
        $response->assertJsonFragment(['message' => "Méthode de paiement 'moneygo' non supportée."]);
    }
}
