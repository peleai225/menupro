<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeactivatedEmployeeAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_deactivated_employee_loses_access_even_with_live_session(): void
    {
        $emp = User::factory()->superAdmin()->create([
            'admin_permissions' => ['restaurants'],
            'is_active' => true,
        ]);

        // Session déjà ouverte avant la désactivation (remember-me, etc.)
        $this->actingAs($emp)
            ->get(route('super-admin.restaurants.index'))
            ->assertOk();

        $emp->update(['is_active' => false]);

        $this->actingAs($emp)
            ->get(route('super-admin.restaurants.index'))
            ->assertForbidden();
    }
}
