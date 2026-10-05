<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CrmSectionAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_employee_without_crm_section_cannot_reach_crm_admin_area(): void
    {
        $emp = User::factory()->superAdmin()->create(['admin_permissions' => ['restaurants']]);

        $this->actingAs($emp)->get(route('crm.dashboard'))->assertForbidden();
        $this->actingAs($emp)->get(route('crm.admin.agents'))->assertForbidden();
        $this->actingAs($emp)->get(route('crm.admin.withdrawals'))->assertForbidden();
    }

    public function test_employee_with_crm_section_can_reach_crm_area(): void
    {
        $emp = User::factory()->superAdmin()->create(['admin_permissions' => ['crm']]);

        $this->actingAs($emp)->get(route('crm.dashboard'))->assertOk();
    }

    public function test_full_admin_still_reaches_crm_admin_area(): void
    {
        $admin = User::factory()->superAdmin()->create(['admin_permissions' => null]);

        $this->actingAs($admin)->get(route('crm.dashboard'))->assertOk();
    }
}
