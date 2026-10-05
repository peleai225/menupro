<?php

namespace Tests\Unit;

use App\Models\User;
use App\Support\AdminSections;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPermissionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_full_admin_has_null_permissions_and_can_access_everything(): void
    {
        $u = User::factory()->superAdmin()->create(['admin_permissions' => null]);
        $this->assertTrue($u->isFullAdmin());
        $this->assertTrue($u->canAdminSection('restaurants'));
        $this->assertTrue($u->canAdminSection('finance'));
    }

    public function test_restricted_employee_only_accesses_granted_sections(): void
    {
        $u = User::factory()->superAdmin()->create(['admin_permissions' => ['restaurants']]);
        $this->assertFalse($u->isFullAdmin());
        $this->assertTrue($u->canAdminSection('restaurants'));
        $this->assertFalse($u->canAdminSection('finance'));
    }

    public function test_route_name_maps_to_section(): void
    {
        $this->assertSame('restaurants', AdminSections::sectionForRouteName('super-admin.restaurants.index'));
        $this->assertSame('deliveries', AdminSections::sectionForRouteName('super-admin.drivers.show'));
        $this->assertSame('finance', AdminSections::sectionForRouteName('super-admin.finances.index'));
        // Réservé / inconnu → null (traité comme réservé aux admins complets)
        $this->assertNull(AdminSections::sectionForRouteName('super-admin.utilisateurs.index'));
        $this->assertNull(AdminSections::sectionForRouteName('super-admin.parametres.whatever'));
        $this->assertNull(AdminSections::sectionForRouteName('super-admin.zone-future.index'));
    }
}
