<?php

namespace Tests\Feature\Restaurant;

use App\Enums\UserRole;
use App\Livewire\Restaurant\Settings;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SettingsGpsRequiredTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsOwnerOf(Restaurant $restaurant): User
    {
        $user = User::factory()->create([
            'role'          => UserRole::RESTAURANT_ADMIN,
            'restaurant_id' => $restaurant->id,
        ]);

        $this->actingAs($user);

        return $user;
    }

    public function test_restaurant_on_platform_cannot_clear_gps_coordinates(): void
    {
        $restaurant = Restaurant::factory()->create([
            'is_on_platform' => true,
            'latitude'       => 5.359,
            'longitude'      => -4.008,
        ]);
        $this->actingAsOwnerOf($restaurant);

        Livewire::test(Settings::class)
            ->set('name', $restaurant->name)
            ->set('latitude', null)
            ->set('longitude', null)
            ->call('saveGeneral')
            ->assertHasErrors(['latitude', 'longitude']);

        $restaurant->refresh();
        $this->assertNotNull($restaurant->latitude);
        $this->assertNotNull($restaurant->longitude);
    }

    public function test_restaurant_off_platform_can_save_without_gps_coordinates(): void
    {
        $restaurant = Restaurant::factory()->create([
            'is_on_platform' => false,
            'latitude'       => null,
            'longitude'      => null,
        ]);
        $this->actingAsOwnerOf($restaurant);

        Livewire::test(Settings::class)
            ->set('name', $restaurant->name)
            ->set('description', 'Nouvelle description sans GPS')
            ->set('latitude', null)
            ->set('longitude', null)
            ->call('saveGeneral')
            ->assertHasNoErrors(['latitude', 'longitude']);

        $restaurant->refresh();
        $this->assertSame('Nouvelle description sans GPS', $restaurant->description);
        $this->assertNull($restaurant->latitude);
        $this->assertNull($restaurant->longitude);
    }
}
