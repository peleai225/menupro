<?php

namespace Tests\Feature\SuperAdmin;

use App\Enums\UserRole;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RestaurantsGpsVisibilityTest extends TestCase
{
    use RefreshDatabase;

    private function createSuperAdmin(): User
    {
        return User::factory()->create([
            'role' => UserRole::SUPER_ADMIN,
        ]);
    }

    public function test_restaurants_list_flags_on_platform_restaurant_missing_gps(): void
    {
        Restaurant::factory()->create([
            'name'           => 'Maquis Sans GPS',
            'is_on_platform' => true,
            'latitude'       => null,
            'longitude'      => null,
        ]);
        Restaurant::factory()->create([
            'name'           => 'Resto Avec GPS',
            'is_on_platform' => true,
            'latitude'       => 5.359,
            'longitude'      => -4.008,
        ]);
        Restaurant::factory()->create([
            'name'           => 'Resto Hors Marketplace',
            'is_on_platform' => false,
            'latitude'       => null,
            'longitude'      => null,
        ]);

        $response = $this->actingAs($this->createSuperAdmin())
            ->get(route('super-admin.restaurants.index'));

        $response->assertOk();
        $this->assertSame(1, substr_count($response->getContent(), 'GPS manquant'));
    }

    public function test_gps_missing_filter_shows_only_on_platform_restaurants_without_coordinates(): void
    {
        Restaurant::factory()->create([
            'name'           => 'Maquis Sans GPS',
            'is_on_platform' => true,
            'latitude'       => null,
            'longitude'      => null,
        ]);
        Restaurant::factory()->create([
            'name'           => 'Resto Avec GPS',
            'is_on_platform' => true,
            'latitude'       => 5.359,
            'longitude'      => -4.008,
        ]);
        Restaurant::factory()->create([
            'name'           => 'Resto Hors Marketplace',
            'is_on_platform' => false,
            'latitude'       => null,
            'longitude'      => null,
        ]);

        $response = $this->actingAs($this->createSuperAdmin())
            ->get(route('super-admin.restaurants.index', ['gps' => 'missing']));

        $response->assertOk();
        $response->assertSee('Maquis Sans GPS');
        $response->assertDontSee('Resto Avec GPS');
        $response->assertDontSee('Resto Hors Marketplace');
    }
}
