<?php

namespace Tests\Feature\Jeko;

use App\Enums\JekoSubMerchantStatus;
use App\Enums\MobileMoneyOperator;
use App\Enums\UserRole;
use App\Jobs\IntegrateJekoSubMerchantJob;
use App\Models\JekoSubMerchant;
use App\Models\Restaurant;
use App\Models\User;
use App\Notifications\JekoIntegrationApprovedNotification;
use App\Notifications\JekoIntegrationRejectedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class AdminApprovalTest extends TestCase
{
    use RefreshDatabase;

    // ─── Helpers ────────────────────────────────────────────────────────────────

    private function createSuperAdmin(): User
    {
        return User::factory()->create([
            'role' => UserRole::SUPER_ADMIN,
        ]);
    }

    private function createPendingSubMerchant(): JekoSubMerchant
    {
        $restaurant = Restaurant::factory()->create();

        User::factory()->create([
            'role'          => UserRole::RESTAURANT_ADMIN,
            'restaurant_id' => $restaurant->id,
        ]);

        return JekoSubMerchant::create([
            'restaurant_id'         => $restaurant->id,
            'status'                => JekoSubMerchantStatus::PENDING,
            'legal_name'            => 'Restaurant Test SARL',
            'business_type'         => 'restaurant',
            'mobile_money'          => '0707123456',
            'mobile_money_operator' => MobileMoneyOperator::ORANGE->value,
            'email'                 => 'test@restaurant.ci',
        ]);
    }

    // ─── Tests ───────────────────────────────────────────────────────────────────

    public function test_super_admin_can_see_pending_requests(): void
    {
        $superAdmin = $this->createSuperAdmin();
        $this->createPendingSubMerchant();

        $response = $this->actingAs($superAdmin)->get('/admin/jeko');

        $response->assertStatus(200);
    }

    public function test_super_admin_can_approve_request(): void
    {
        Queue::fake();
        Notification::fake();

        $superAdmin      = $this->createSuperAdmin();
        $subMerchant     = $this->createPendingSubMerchant();
        $restaurant      = $subMerchant->restaurant;
        $owner           = $restaurant->owner;

        $response = $this->actingAs($superAdmin)
            ->post("/admin/jeko/{$subMerchant->id}/approve");

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $subMerchant->refresh();
        $this->assertEquals(JekoSubMerchantStatus::APPROVED, $subMerchant->status);
        $this->assertEquals($superAdmin->id, $subMerchant->approved_by);
        $this->assertNotNull($subMerchant->approved_at);

        Queue::assertPushed(IntegrateJekoSubMerchantJob::class, function ($job) use ($subMerchant) {
            return $job->subMerchant->id === $subMerchant->id;
        });

        Notification::assertSentTo($owner, JekoIntegrationApprovedNotification::class);
    }

    public function test_super_admin_can_reject_request(): void
    {
        Notification::fake();

        $superAdmin  = $this->createSuperAdmin();
        $subMerchant = $this->createPendingSubMerchant();
        $restaurant  = $subMerchant->restaurant;
        $owner       = $restaurant->owner;

        $reason = 'Documents insuffisants pour la vérification KYC.';

        $response = $this->actingAs($superAdmin)
            ->post("/admin/jeko/{$subMerchant->id}/reject", [
                'rejected_reason' => $reason,
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $subMerchant->refresh();
        $this->assertEquals(JekoSubMerchantStatus::REJECTED, $subMerchant->status);
        $this->assertEquals($reason, $subMerchant->rejected_reason);

        Notification::assertSentTo($owner, JekoIntegrationRejectedNotification::class);
    }

    public function test_cannot_approve_non_pending_request(): void
    {
        $superAdmin  = $this->createSuperAdmin();
        $subMerchant = $this->createPendingSubMerchant();

        // Force status to INTEGRATED (no longer pending)
        $subMerchant->update([
            'status'           => JekoSubMerchantStatus::INTEGRATED,
            'jeko_merchant_id' => 'JEKO-123',
        ]);

        $response = $this->actingAs($superAdmin)
            ->post("/admin/jeko/{$subMerchant->id}/approve");

        $response->assertRedirect();
        $response->assertSessionHas('error');

        // Status must remain INTEGRATED
        $subMerchant->refresh();
        $this->assertEquals(JekoSubMerchantStatus::INTEGRATED, $subMerchant->status);
    }

    public function test_cannot_reject_non_pending_request(): void
    {
        Queue::fake();
        Notification::fake();

        $superAdmin = $this->createSuperAdmin();
        $subMerchant = $this->createPendingSubMerchant();

        $subMerchant->update([
            'status' => JekoSubMerchantStatus::INTEGRATED,
            'jeko_merchant_id' => 'JEKO-123',
        ]);

        $response = $this->actingAs($superAdmin)
            ->post("/admin/jeko/{$subMerchant->id}/reject", [
                'rejected_reason' => 'Test reason.',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');

        $subMerchant->refresh();
        $this->assertEquals(JekoSubMerchantStatus::INTEGRATED, $subMerchant->status);
    }
}
