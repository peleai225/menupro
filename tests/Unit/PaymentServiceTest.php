<?php

namespace Tests\Unit;

use App\Enums\PaymentGateway;
use App\Services\JekoGateway;
use App\Services\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_gateway_throws_for_unknown_gateway()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Gateway inconnu: fake_gateway');

        PaymentService::gateway('fake_gateway');
    }

    public function test_gateway_throws_for_cash()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Cash ne supporte pas les paiements en ligne');

        PaymentService::gateway('cash');
    }

    public function test_payment_gateway_enum_has_all_cases()
    {
        $this->assertEquals('wave', PaymentGateway::WAVE->value);
        $this->assertEquals('jeko', PaymentGateway::JEKO->value);
        $this->assertEquals('cinetpay', PaymentGateway::CINETPAY->value);
        $this->assertEquals('cash', PaymentGateway::CASH->value);
    }

    public function test_payment_gateway_enum_labels()
    {
        $this->assertEquals('Wave CI', PaymentGateway::WAVE->label());
        $this->assertEquals('Jeko Mobile Money', PaymentGateway::JEKO->label());
        $this->assertEquals('CinetPay', PaymentGateway::CINETPAY->label());
        $this->assertEquals('Espèces', PaymentGateway::CASH->label());
    }

    public function test_payment_gateway_enum_supports_online_payment()
    {
        $this->assertTrue(PaymentGateway::WAVE->supportsOnlinePayment());
        $this->assertTrue(PaymentGateway::JEKO->supportsOnlinePayment());
        $this->assertTrue(PaymentGateway::CINETPAY->supportsOnlinePayment());
        $this->assertFalse(PaymentGateway::CASH->supportsOnlinePayment());
    }

    public function test_payment_service_returns_jeko_gateway_instance()
    {
        $gateway = PaymentService::gateway('jeko');

        $this->assertInstanceOf(JekoGateway::class, $gateway);
    }

    public function test_payment_service_throws_for_cinetpay_not_implemented()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('CinetPay pas encore implémenté');

        PaymentService::gateway('cinetpay');
    }

    public function test_gateway_throws_for_wave_not_yet_adapted()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('WaveGateway pas encore adapté à l\'interface');

        PaymentService::gateway('wave');
    }
}
