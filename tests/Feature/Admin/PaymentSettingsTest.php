<?php

namespace Tests\Feature\Admin;

use App\Enums\UserRole;
use App\Models\SystemPaymentSetting;
use App\Models\User;
use Database\Seeders\SystemPaymentSettingsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentSettingsTest extends TestCase
{
    use RefreshDatabase;

    // ─── Helpers ────────────────────────────────────────────────────────────────

    private function createSuperAdmin(): User
    {
        return User::factory()->create([
            'role' => UserRole::SUPER_ADMIN,
        ]);
    }

    private function createRegularUser(): User
    {
        return User::factory()->create([
            'role' => UserRole::RESTAURANT_ADMIN,
        ]);
    }

    private function makeSetting(): SystemPaymentSetting
    {
        return SystemPaymentSetting::create([
            'gateway'   => 'jeko_marketplace',
            'is_active' => false,
            'mode'      => 'sandbox',
        ]);
    }

    // ─── Tests ───────────────────────────────────────────────────────────────────

    public function test_super_admin_can_see_payment_settings(): void
    {
        $this->seed(SystemPaymentSettingsSeeder::class);
        $superAdmin = $this->createSuperAdmin();

        $response = $this->actingAs($superAdmin)->get('/admin/payment-settings');

        $response->assertStatus(200);
    }

    public function test_super_admin_can_update_gateway_settings(): void
    {
        $superAdmin = $this->createSuperAdmin();
        $setting    = $this->makeSetting();

        $response = $this->actingAs($superAdmin)->put("/admin/payment-settings/{$setting->id}", [
            'mode'      => 'production',
            'is_active' => '1',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $setting->refresh();
        $this->assertTrue($setting->is_active);
        $this->assertSame('production', $setting->mode);
    }

    public function test_api_key_is_stored_encrypted(): void
    {
        $superAdmin = $this->createSuperAdmin();
        $setting    = $this->makeSetting();

        $plainKey = 'test_secret_key_plain';

        $this->actingAs($superAdmin)->put("/admin/payment-settings/{$setting->id}", [
            'mode'    => 'sandbox',
            'api_key' => $plainKey,
        ]);

        $setting->refresh();

        // The raw DB value must not be the plain text key
        $this->assertNotEquals($plainKey, $setting->api_key);

        // The raw DB value must not be empty either — it was stored
        $this->assertNotEmpty($setting->api_key);

        // Decrypted value must match what was provided
        $this->assertSame($plainKey, $setting->getDecryptedApiKey());
    }

    public function test_non_super_admin_cannot_access_payment_settings(): void
    {
        $this->seed(SystemPaymentSettingsSeeder::class);
        $regularUser = $this->createRegularUser();

        $response = $this->actingAs($regularUser)->get('/admin/payment-settings');

        $response->assertForbidden();
    }
}
