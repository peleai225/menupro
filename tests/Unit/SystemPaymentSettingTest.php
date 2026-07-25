<?php

namespace Tests\Unit;

use App\Models\SystemPaymentSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SystemPaymentSettingTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_key_is_encrypted_and_decrypted()
    {
        $setting = SystemPaymentSetting::create([
            'gateway' => 'test_gateway',
            'is_active' => true,
            'mode' => 'sandbox',
        ]);

        $setting->setEncryptedApiKey('secret_key_123');
        $setting->save();

        $this->assertNotEquals('secret_key_123', $setting->api_key);
        $this->assertEquals('secret_key_123', $setting->getDecryptedApiKey());
    }

    public function test_is_active_returns_false_if_no_api_key()
    {
        $setting = SystemPaymentSetting::create([
            'gateway' => 'test_gateway',
            'is_active' => true,
            'mode' => 'sandbox',
        ]);

        $this->assertFalse($setting->isActive());
    }

    public function test_is_active_returns_true_if_api_key_present()
    {
        $setting = SystemPaymentSetting::create([
            'gateway' => 'test_gateway',
            'is_active' => true,
            'mode' => 'sandbox',
        ]);

        $setting->setEncryptedApiKey('secret_key');
        $setting->save();

        $this->assertTrue($setting->isActive());
    }

    public function test_is_sandbox_returns_correct_mode()
    {
        $setting = SystemPaymentSetting::create([
            'gateway' => 'test_gateway',
            'mode' => 'sandbox',
        ]);

        $this->assertTrue($setting->isSandbox());

        $setting->mode = 'production';
        $this->assertFalse($setting->isSandbox());
    }
}
