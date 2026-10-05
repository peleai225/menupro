<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DelegatedApiFeedsTest extends TestCase
{
    use RefreshDatabase;

    public function test_employee_with_orders_section_can_reach_live_orders_feed(): void
    {
        $emp = User::factory()->superAdmin()->create(['admin_permissions' => ['orders']]);

        $this->actingAs($emp)
            ->get(route('super-admin.api.live-orders'))
            ->assertOk();
    }

    public function test_employee_with_deliveries_section_can_reach_live_deliveries_feed(): void
    {
        $emp = User::factory()->superAdmin()->create(['admin_permissions' => ['deliveries']]);

        $this->actingAs($emp)
            ->get(route('super-admin.api.live-deliveries'))
            ->assertOk();
    }

    public function test_any_employee_can_reach_sidebar_badges_and_notifications(): void
    {
        $emp = User::factory()->superAdmin()->create(['admin_permissions' => ['restaurants']]);

        $this->actingAs($emp)->get(route('super-admin.api.sidebar-badges'))->assertOk();
        $this->actingAs($emp)->get(route('super-admin.api.notifications'))->assertOk();
    }
}
