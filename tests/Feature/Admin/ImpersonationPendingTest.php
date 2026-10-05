<?php

namespace Tests\Feature\Admin;

use App\Enums\RestaurantStatus;
use App\Http\Middleware\EnsureRestaurantActive;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class ImpersonationPendingTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_impersonating_a_pending_restaurant_can_reach_dashboard(): void
    {
        $admin = User::factory()->superAdmin()->create(['admin_permissions' => null]);
        $restaurant = Restaurant::factory()->create(['status' => RestaurantStatus::PENDING]);
        $owner = User::factory()->restaurantAdmin($restaurant)->create();

        $this->actingAs($admin)
            ->post(route('super-admin.restaurants.impersonate', $restaurant))
            ->assertRedirect();

        // La session courante est désormais celle de l'owner (impersonation),
        // avec impersonating_from posé par le contrôleur.
        $this->assertAuthenticatedAs($owner);
        $this->assertTrue(session()->has('impersonating_from'));

        // Vérifie directement le middleware (sans dépendre du rendu Livewire
        // du dashboard, qui utilise du SQL MySQL-only hors scope ici) : avec
        // impersonating_from en session, il doit laisser passer sans
        // rediriger vers l'écran d'attente, même pour un resto PENDING.
        $request = Request::create('/dashboard', 'GET');
        $request->setLaravelSession(session()->driver());
        $request->setUserResolver(fn () => $owner);
        $request->setRouteResolver(fn () => \Illuminate\Support\Facades\Route::getRoutes()->getByName('restaurant.dashboard'));

        $middleware = new EnsureRestaurantActive();
        $response = $middleware->handle($request, fn ($req) => new \Symfony\Component\HttpFoundation\Response('ok', 200));

        $this->assertSame(200, $response->getStatusCode());
    }
}
