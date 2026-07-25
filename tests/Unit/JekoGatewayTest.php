<?php

namespace Tests\Unit;

use App\Models\SystemPaymentSetting;
use App\Services\JekoGateway;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class JekoGatewayTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $setting = SystemPaymentSetting::create([
            'gateway' => 'jeko_normal',
            'is_active' => true,
            'mode' => 'production',
        ]);
        $setting->setEncryptedApiKey('test_jeko_api_key');
        $setting->setEncryptedWebhookSecret('test_webhook_secret');
        $setting->save();
    }

    public function test_is_configured_returns_true_when_api_key_present(): void
    {
        $gateway = new JekoGateway();
        $gateway->forPlatform();

        $this->assertTrue($gateway->isConfigured());
    }

    public function test_is_configured_returns_false_when_no_api_key(): void
    {
        SystemPaymentSetting::where('gateway', 'jeko_normal')->delete();

        $gateway = new JekoGateway();
        $gateway->forPlatform();

        $this->assertFalse($gateway->isConfigured());
    }

    public function test_verify_webhook_signature_validates_correct_signature(): void
    {
        $gateway = new JekoGateway();
        $gateway->forPlatform();

        $payload = '{"event":"payment.success","id":"123"}';
        $signature = hash_hmac('sha256', $payload, 'test_webhook_secret');

        $this->assertTrue($gateway->verifyWebhookSignature($payload, $signature));
    }

    public function test_verify_webhook_signature_rejects_invalid_signature(): void
    {
        $gateway = new JekoGateway();
        $gateway->forPlatform();

        $payload = '{"event":"payment.success","id":"123"}';

        $this->assertFalse($gateway->verifyWebhookSignature($payload, 'invalid_signature'));
    }

    public function test_verify_webhook_signature_rejects_empty_signature(): void
    {
        $gateway = new JekoGateway();
        $gateway->forPlatform();

        $this->assertFalse($gateway->verifyWebhookSignature('{"event":"test"}', ''));
    }

    public function test_create_payment_returns_error_when_not_configured(): void
    {
        SystemPaymentSetting::where('gateway', 'jeko_normal')->delete();

        $gateway = new JekoGateway();
        $gateway->forPlatform();

        $order = new \App\Models\Order(['id' => 1, 'total' => 50000, 'reference' => 'ORD-001']);

        $result = $gateway->createPayment($order, 'https://example.com/success', 'https://example.com/error');

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('not configured', $result['error']);
    }

    public function test_create_payment_returns_payment_url_on_success(): void
    {
        Http::fake([
            '*/demandes-de-paiement' => Http::response([
                'id' => 'pay_abc123',
                'url_redirection' => 'https://jeko.africa/pay/abc123',
                'statut' => 'pending',
            ], 200),
        ]);

        $gateway = new JekoGateway();
        $gateway->forPlatform();

        $order = new \App\Models\Order(['id' => 1, 'total' => 50000, 'reference' => 'ORD-001']);

        $result = $gateway->createPayment($order, 'https://example.com/success', 'https://example.com/error');

        $this->assertTrue($result['success']);
        $this->assertEquals('pay_abc123', $result['payment_id']);
        $this->assertEquals('https://jeko.africa/pay/abc123', $result['payment_url']);
    }

    public function test_create_payment_returns_error_on_api_failure(): void
    {
        Http::fake([
            '*/demandes-de-paiement' => Http::response(['message' => 'Unauthorized'], 401),
        ]);

        $gateway = new JekoGateway();
        $gateway->forPlatform();

        $order = new \App\Models\Order(['id' => 1, 'total' => 50000, 'reference' => 'ORD-001']);

        $result = $gateway->createPayment($order, 'https://example.com/success', 'https://example.com/error');

        $this->assertFalse($result['success']);
        $this->assertEquals('Unauthorized', $result['error']);
    }

    public function test_payout_returns_error_when_amount_too_low(): void
    {
        $gateway = new JekoGateway();
        $gateway->forPlatform();

        $result = $gateway->payout('+22507000000', 0);

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('trop faible', $result['error']);
    }

    public function test_payout_throws_when_reference_is_empty(): void
    {
        Http::fake();
        $gateway = new JekoGateway();
        $gateway->forPlatform();

        try {
            $gateway->payout('22500000001', 10000, '', '', '');
            $this->fail('Expected InvalidArgumentException was not thrown');
        } catch (\InvalidArgumentException $e) {
            $this->assertStringContainsString('idempotency key', $e->getMessage());
            Http::assertNothingSent();
        }
    }

    public function test_payout_succeeds_when_api_returns_ok(): void
    {
        Http::fake([
            '*/contacts' => Http::response(['id' => 'contact_xyz'], 200),
            '*/virements' => Http::response([
                'id' => 'transfer_789',
                'statut' => 'processing',
            ], 200),
        ]);

        $gateway = new JekoGateway();
        $gateway->forPlatform();

        $result = $gateway->payout('+22507000000', 10000, 'Restaurant Koffi', 'Reversement commandes', 'REF-001');

        $this->assertTrue($result['success']);
        $this->assertEquals('transfer_789', $result['transfer_id']);
        $this->assertEquals('processing', $result['status']);
    }

    public function test_get_payment_status_returns_status_on_success(): void
    {
        Http::fake([
            '*/demandes-de-paiement/pay_123' => Http::response([
                'id' => 'pay_123',
                'statut' => 'completed',
            ], 200),
        ]);

        $gateway = new JekoGateway();
        $gateway->forPlatform();

        $result = $gateway->getPaymentStatus('pay_123');

        $this->assertTrue($result['success']);
        $this->assertEquals('completed', $result['status']);
    }

    public function test_marketplace_mode_loads_marketplace_credentials(): void
    {
        $setting = SystemPaymentSetting::create([
            'gateway' => 'jeko_marketplace',
            'is_active' => true,
            'mode' => 'production',
        ]);
        $setting->setEncryptedApiKey('marketplace_api_key');
        $setting->setEncryptedWebhookSecret('marketplace_secret');
        $setting->save();

        $restaurant = new \App\Models\Restaurant(['id' => 1]);

        $gateway = new JekoGateway();
        $gateway->forMarketplace($restaurant);

        $this->assertTrue($gateway->isConfigured());
    }
}
