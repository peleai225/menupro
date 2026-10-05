<?php

namespace Tests\Feature\Admin;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmployeeCreationTest extends TestCase
{
    use RefreshDatabase;

    public function test_full_admin_creates_restricted_employee(): void
    {
        $admin = User::factory()->superAdmin()->create(['admin_permissions' => null]);

        $this->actingAs($admin)->post(route('super-admin.utilisateurs.store'), [
            'name' => 'Awa', 'email' => 'awa@example.com', 'phone' => '0701010101',
            'password' => 'password123', 'account_type' => 'employee',
            'admin_permissions' => ['restaurants', 'orders'],
        ])->assertRedirect();

        $awa = User::where('email', 'awa@example.com')->firstOrFail();
        $this->assertSame(UserRole::SUPER_ADMIN, $awa->role);
        $this->assertEqualsCanonicalizing(['restaurants', 'orders'], $awa->admin_permissions);
        $this->assertFalse($awa->isFullAdmin());
    }

    public function test_invalid_section_is_rejected(): void
    {
        $admin = User::factory()->superAdmin()->create(['admin_permissions' => null]);
        $this->actingAs($admin)->post(route('super-admin.utilisateurs.store'), [
            'name' => 'X', 'email' => 'x@example.com', 'password' => 'password123',
            'account_type' => 'employee', 'admin_permissions' => ['payment-settings'],
        ])->assertSessionHasErrors('admin_permissions.0');
    }

    public function test_restricted_employee_cannot_create_users(): void
    {
        $emp = User::factory()->superAdmin()->create(['admin_permissions' => ['restaurants']]);
        $this->actingAs($emp)->post(route('super-admin.utilisateurs.store'), [
            'name' => 'Y', 'email' => 'y@example.com', 'password' => 'password123',
            'account_type' => 'full',
        ])->assertForbidden();
    }
}
