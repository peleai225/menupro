<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminSectionAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_full_admin_accesses_any_section(): void
    {
        $admin = User::factory()->superAdmin()->create(['admin_permissions' => null]);
        $this->actingAs($admin)->get(route('super-admin.restaurants.index'))->assertOk();

        // finances.index : le contrôleur utilise DATE_FORMAT (MySQL, non supporté par
        // SQLite) — hors scope RBAC. On vérifie seulement que le gate admin.section
        // laisse passer l'admin complet (pas de 403).
        $status = $this->actingAs($admin)->get(route('super-admin.finances.index'))->status();
        $this->assertNotSame(403, $status);
    }

    public function test_restricted_employee_allowed_only_on_granted_section(): void
    {
        $emp = User::factory()->superAdmin()->create(['admin_permissions' => ['restaurants']]);
        $this->actingAs($emp)->get(route('super-admin.restaurants.index'))->assertOk();
        $this->actingAs($emp)->get(route('super-admin.finances.index'))->assertForbidden();
    }

    public function test_restricted_employee_cannot_reach_reserved_section(): void
    {
        $emp = User::factory()->superAdmin()->create(['admin_permissions' => ['restaurants']]);
        $this->actingAs($emp)->get(route('super-admin.utilisateurs.index'))->assertForbidden();
        $this->actingAs($emp)->get(route('super-admin.settings'))->assertForbidden();
    }
}
