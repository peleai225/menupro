<?php

namespace Tests\Feature\Subscription;

use App\Enums\RestaurantStatus;
use App\Enums\SubscriptionStatus;
use App\Models\Restaurant;
use App\Models\User;
use App\Services\PlanLimiter;
use App\Support\RestaurantNavigation;
use Tests\Feature\Plans\PricingGridTestCase;

/**
 * Onglets du back-office selon le plan et l'état de l'abonnement (source unique :
 * config('plans.features') → RestaurantNavigation / middleware `feature:`).
 */
class PlanNavigationTest extends PricingGridTestCase
{
    private function navState(Restaurant $restaurant, ?User $user = null): array
    {
        $user ??= $this->adminOf($restaurant);

        return collect(RestaurantNavigation::items($restaurant->fresh(), $user))
            ->mapWithKeys(fn ($item) => [$item['key'] => $item['state']])
            ->all();
    }

    private function page(Restaurant $restaurant): \Illuminate\Testing\TestResponse
    {
        // La page Abonnement utilise le layout du tableau de bord dans tous les états
        return $this->actingAs($this->adminOf($restaurant))->get(route('restaurant.subscription'))->assertOk();
    }

    public function test_stand_sees_a_lock_instead_of_ingredient_stock(): void
    {
        $stand = $this->subscribedRestaurant('stand');
        $nav = $this->navState($stand);

        $this->assertSame('locked', $nav['ingredients']);
        $this->assertSame('locked', $nav['stock-daily']);
        $this->assertSame('locked', $nav['analytics']);
        $this->assertSame('locked', $nav['pos']);
        $this->assertSame('ok', $nav['orders']);
        $this->assertSame('ok', $nav['dishes']);
        $this->assertArrayNotHasKey('suppliers', $nav); // détail d'une fonction verrouillée : masqué

        $this->page($stand)
            ->assertSee('data-nav="ingredients" data-nav-state="locked"', false)
            ->assertSee('Inclus à partir du plan Pro', false)
            ->assertSee('Inclus à partir du plan Restaurant', false)
            ->assertDontSee('href="' . route('restaurant.stock.ingredients.index') . '"', false);
    }

    public function test_locked_feature_page_offers_the_upgrade_instead_of_a_raw_403(): void
    {
        $stand = $this->subscribedRestaurant('stand');
        $owner = $this->adminOf($stand);

        $this->actingAs($owner)->get(route('restaurant.stock.ingredients.index'))
            ->assertRedirect(route('restaurant.subscription.plans', ['plan' => 'pro']))
            ->assertSessionHas('warning', fn ($m) => str_contains($m, 'Passez au plan Pro'));

        $this->actingAs($owner)->get(route('restaurant.analytics'))
            ->assertRedirect(route('restaurant.subscription.plans', ['plan' => 'essentiel']))
            ->assertSessionHas('warning', fn ($m) => str_contains($m, 'Passez au plan Restaurant'));

        $this->followRedirects($this->actingAs($owner)->get(route('restaurant.stock.daily')))
            ->assertOk()
            ->assertSee('Passez au plan Restaurant')
            ->assertSee('data-upgrade-required', false);
    }

    public function test_restaurant_plan_sees_simple_stock_and_statistics(): void
    {
        $restaurant = $this->subscribedRestaurant('essentiel');
        $nav = $this->navState($restaurant);

        $this->assertSame('ok', $nav['stock-daily']);
        $this->assertSame('ok', $nav['analytics']);
        $this->assertSame('ok', $nav['reports']);
        $this->assertSame('ok', $nav['pos']);
        $this->assertSame('ok', $nav['team']);
        $this->assertSame('locked', $nav['ingredients']);

        $this->page($restaurant)
            ->assertSee('data-nav="stock-daily" data-nav-state="ok"', false)
            ->assertSee('data-nav="analytics" data-nav-state="ok"', false)
            ->assertSee('href="' . route('restaurant.stock.daily') . '"', false)
            ->assertSee('href="' . route('restaurant.analytics') . '"', false);
    }

    public function test_expired_restaurant_only_sees_the_subscription(): void
    {
        $expired = $this->subscribedRestaurant('essentiel', [
            'status' => RestaurantStatus::EXPIRED,
            'subscription_ends_at' => now()->subDay(),
        ], ['status' => SubscriptionStatus::EXPIRED, 'ends_at' => now()->subDay()]);

        $nav = $this->navState($expired);
        $this->assertSame(['subscription', 'settings'], array_keys($nav));

        $this->page($expired)
            ->assertSee('data-nav="subscription"', false)
            ->assertDontSee('data-nav="dishes"', false)
            ->assertDontSee('data-nav="orders"', false)
            ->assertDontSee('data-nav="analytics"', false)
            ->assertSee('Renouvelez pour réactiver');

        // Fin d'abonnement atteinte avant le passage du job d'expiration : même accès limité
        $notYetFlagged = $this->subscribedRestaurant('stand', ['subscription_ends_at' => now()->subHour()], [
            'ends_at' => now()->subHour(),
        ]);
        $owner = $this->adminOf($notYetFlagged);
        $this->actingAs($owner)->get(route('restaurant.plats.index'))->assertRedirect(route('restaurant.subscription'));
        $this->actingAs($owner)->get(route('restaurant.settings'))->assertOk();
    }

    public function test_employee_of_an_expired_restaurant_is_not_stuck_in_a_redirect_loop(): void
    {
        $expired = $this->subscribedRestaurant('essentiel', [
            'status' => RestaurantStatus::EXPIRED, 'subscription_ends_at' => now()->subDay(),
        ], ['status' => SubscriptionStatus::EXPIRED, 'ends_at' => now()->subDay()]);
        $employee = User::factory()->create(['restaurant_id' => $expired->id]);
        $employee->role = \App\Enums\UserRole::EMPLOYEE;
        $employee->restaurant_id = $expired->id;
        $employee->save();

        $this->actingAs($employee)->get(route('restaurant.orders'))
            ->assertRedirect(route('restaurant.profile'))
            ->assertSessionHas('warning', fn ($m) => str_contains($m, 'Prévenez le gérant'));
    }

    public function test_trial_sees_every_pro_feature_and_the_days_left(): void
    {
        $trial = $this->subscribedRestaurant('stand', ['subscription_ends_at' => now()->addDays(12)], [
            'status' => SubscriptionStatus::TRIAL, 'is_trial' => true, 'amount_paid' => 0, 'ends_at' => now()->addDays(12),
        ]);

        $nav = $this->navState($trial);
        foreach (['ingredients', 'suppliers', 'stock-daily', 'analytics', 'pos', 'customers', 'rooms', 'team'] as $key) {
            $this->assertSame('ok', $nav[$key] ?? null, "Essai : « {$key} » doit être ouvert");
        }
        $this->assertSame('locked', $nav['spaces']); // multi-espaces : plan Gold uniquement

        $this->assertSame(PlanLimiter::ACCESS_TRIAL, (clone app(PlanLimiter::class))->forRestaurant($trial->fresh())->accessState());
        $this->page($trial)->assertSee('Essai : 12 jours restants');
    }

    public function test_feature_catalog_is_the_single_source_for_plan_access(): void
    {
        $this->assertFalse($this->plan('stand')->hasFeature('pos'));
        $this->assertTrue($this->plan('essentiel')->hasFeature('pos'));
        $this->assertTrue($this->plan('essentiel')->hasFeature('stock'));
        $this->assertFalse($this->plan('essentiel')->hasFeature('ingredient_stock'));
        $this->assertTrue($this->plan('pro')->hasFeature('ingredient_stock'));
        $this->assertSame('essentiel', PlanLimiter::requiredPlanFor('analytics')->slug);
        $this->assertSame('pro', PlanLimiter::requiredPlanFor('hotel_rooms')->slug);
        $this->assertSame('gold', PlanLimiter::requiredPlanFor('multi_spaces')->slug);
    }
}
