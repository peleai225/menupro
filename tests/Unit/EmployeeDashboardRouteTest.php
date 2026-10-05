<?php

namespace Tests\Unit;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmployeeDashboardRouteTest extends TestCase
{
    use RefreshDatabase;

    public function test_full_admin_lands_on_dashboard(): void
    {
        $admin = User::factory()->superAdmin()->create(['admin_permissions' => null]);
        $this->assertSame('super-admin.dashboard', $admin->getDashboardRoute());
    }

    public function test_restricted_employee_lands_on_first_section(): void
    {
        $emp = User::factory()->superAdmin()->create(['admin_permissions' => ['orders', 'restaurants']]);
        // L'ordre canonique de DELEGABLE place 'restaurants' avant 'orders'
        $this->assertSame('super-admin.restaurants.index', $emp->getDashboardRoute());
    }

    public function test_employee_with_no_section_lands_on_safe_fallback(): void
    {
        $emp = User::factory()->superAdmin()->create(['admin_permissions' => []]);
        $this->assertSame('logout', $emp->getDashboardRoute());
    }
}
