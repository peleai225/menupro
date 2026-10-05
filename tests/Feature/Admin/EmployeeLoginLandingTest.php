<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmployeeLoginLandingTest extends TestCase
{
    use RefreshDatabase;

    public function test_employee_with_no_section_lands_on_a_reachable_get_page(): void
    {
        $emp = User::factory()->superAdmin()->create(['admin_permissions' => []]);

        $response = $this->actingAs($emp)->get(route($emp->getDashboardRoute()));

        $response->assertOk();
    }

    public function test_admin_login_redirects_employee_to_their_own_section_not_dashboard(): void
    {
        $emp = User::factory()->superAdmin()->create([
            'admin_permissions' => ['restaurants'],
            'password' => bcrypt('password123'),
        ]);

        $response = $this->post(route('admin.login.post'), [
            'login' => $emp->email,
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('super-admin.restaurants.index'));
    }
}
