<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class GateBypassTest extends TestCase
{
    use RefreshDatabase;

    public function test_full_admin_bypasses_all_gates(): void
    {
        $admin = User::factory()->superAdmin()->create(['admin_permissions' => null]);
        $this->assertTrue(Gate::forUser($admin)->allows('any-undefined-ability'));
    }

    public function test_restricted_employee_does_not_bypass_gates(): void
    {
        $emp = User::factory()->superAdmin()->create(['admin_permissions' => ['restaurants']]);
        $this->assertFalse(Gate::forUser($emp)->allows('any-undefined-ability'));
    }
}
