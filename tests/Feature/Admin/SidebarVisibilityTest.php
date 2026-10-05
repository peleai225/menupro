<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SidebarVisibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_employee_sidebar_hides_unauthorized_sections(): void
    {
        $emp = User::factory()->superAdmin()->create(['admin_permissions' => ['restaurants']]);
        $html = $this->actingAs($emp)->get(route('super-admin.restaurants.index'))->getContent();

        $this->assertStringContainsString(route('super-admin.restaurants.index'), $html);
        $this->assertStringNotContainsString(route('super-admin.finances.index'), $html);
        $this->assertStringNotContainsString(route('super-admin.utilisateurs.index'), $html);
    }
}
