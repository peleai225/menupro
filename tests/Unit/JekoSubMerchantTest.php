<?php

namespace Tests\Unit;

use App\Enums\JekoSubMerchantStatus;
use App\Enums\MobileMoneyOperator;
use App\Models\JekoSubMerchant;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JekoSubMerchantTest extends TestCase
{
    use RefreshDatabase;

    public function test_is_pending_returns_true_for_pending_status()
    {
        $restaurant = Restaurant::factory()->create();
        $subMerchant = JekoSubMerchant::create([
            'restaurant_id' => $restaurant->id,
            'status' => JekoSubMerchantStatus::PENDING,
            'legal_name' => 'Test Restaurant SARL',
            'mobile_money' => '0700000000',
            'mobile_money_operator' => MobileMoneyOperator::ORANGE,
        ]);

        $this->assertTrue($subMerchant->isPending());
        $this->assertFalse($subMerchant->isIntegrated());
    }

    public function test_is_integrated_returns_true_when_has_merchant_id()
    {
        $restaurant = Restaurant::factory()->create();
        $subMerchant = JekoSubMerchant::create([
            'restaurant_id' => $restaurant->id,
            'status' => JekoSubMerchantStatus::INTEGRATED,
            'legal_name' => 'Test Restaurant',
            'mobile_money' => '0700000000',
            'mobile_money_operator' => MobileMoneyOperator::MTN,
            'jeko_merchant_id' => 'jeko_merchant_123',
            'jeko_store_id' => 'store_123',
        ]);

        $this->assertTrue($subMerchant->isIntegrated());
        $this->assertTrue($subMerchant->canReceivePayments());
    }

    public function test_api_key_encryption()
    {
        $restaurant = Restaurant::factory()->create();
        $subMerchant = JekoSubMerchant::create([
            'restaurant_id' => $restaurant->id,
            'legal_name' => 'Test',
            'mobile_money' => '0700000000',
            'mobile_money_operator' => MobileMoneyOperator::WAVE,
        ]);

        $subMerchant->setEncryptedApiKey('secret_api_key_xyz');
        $subMerchant->save();

        $this->assertNotEquals('secret_api_key_xyz', $subMerchant->jeko_api_key);
        $this->assertEquals('secret_api_key_xyz', $subMerchant->getDecryptedApiKey());
    }

    public function test_restaurant_relation()
    {
        $restaurant = Restaurant::factory()->create();
        $subMerchant = JekoSubMerchant::create([
            'restaurant_id' => $restaurant->id,
            'legal_name' => 'Test',
            'mobile_money' => '0700000000',
            'mobile_money_operator' => MobileMoneyOperator::ORANGE,
        ]);

        $this->assertEquals($restaurant->id, $subMerchant->restaurant->id);
        $this->assertEquals($subMerchant->id, $restaurant->jekoSubMerchant->id);
    }
}
