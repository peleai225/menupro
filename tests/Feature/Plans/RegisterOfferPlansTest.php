<?php

namespace Tests\Feature\Plans;

use App\Enums\SubscriptionStatus;
use App\Models\Restaurant;
use App\Models\Subscription;
use App\Support\RestaurantNavigation;
use App\Support\VitrineGrid;

/**
 * Inscription : l'étape 3 suit le type de commerce. Une boutique peut prendre le plan Stand
 * (affiché « Boutique », même prix que le « dès » de l'accueil) ; un restaurant ne le peut pas.
 */
class RegisterOfferPlansTest extends PricingGridTestCase
{
    private function payload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Awa',
            'restaurant_name' => 'Superette Awa',
            'restaurant_type' => 'boutique',
            'phone' => '0700000091',
            'password' => 'password123',
            'plan' => 'stand',
            'terms' => '1',
        ], $overrides);
    }

    public function test_a_shop_can_register_on_the_stand_plan_with_a_trial(): void
    {
        $this->post(route('register.post'), $this->payload())
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('restaurant.pending'));

        $restaurant = Restaurant::firstOrFail();
        $this->assertSame('boutique', $restaurant->type);
        $this->assertSame($this->plan('stand')->id, $restaurant->current_plan_id);

        $subscription = Subscription::where('restaurant_id', $restaurant->id)->firstOrFail();
        $this->assertSame($this->plan('stand')->id, $subscription->plan_id);
        $this->assertSame(SubscriptionStatus::TRIAL, $subscription->status);
        $this->assertTrue((bool) $subscription->is_trial);
        $this->assertSame((int) config('plans.trial_days'), (int) $subscription->trial_days);
        $this->assertSame('monthly', $subscription->billing_period);
    }

    public function test_a_restaurant_cannot_take_the_stand_plan(): void
    {
        $this->post(route('register.post'), $this->payload(['restaurant_type' => 'maquis', 'restaurant_name' => 'Maquis Test']))
            ->assertSessionHasErrors(['plan' => 'Le plan Stand est réservé aux stands et aux boutiques. Pour un restaurant ou un maquis, choisissez le plan Restaurant ou Pro.']);

        $this->assertSame(0, Restaurant::count());
    }

    public function test_a_street_stand_keeps_the_stand_plan(): void
    {
        $this->post(route('register.post'), $this->payload(['restaurant_type' => 'kiosque', 'restaurant_name' => 'Kiosque Test', 'billing_period' => 'weekly']))
            ->assertRedirect(route('restaurant.pending'));

        $this->assertSame($this->plan('stand')->id, Restaurant::firstOrFail()->current_plan_id);
    }

    public function test_type_boutique_preselects_the_boutique_plan_at_the_homepage_price(): void
    {
        $response = $this->get(route('register', ['type' => 'boutique']))->assertOk();

        $offerPlans = $response->viewData('offerPlans');
        $this->assertSame('stand', $offerPlans['boutique']['default']);
        $boutique = collect($offerPlans['boutique']['plans'])->firstWhere('slug', 'stand');
        $this->assertSame('Boutique', $boutique['name']);
        $this->assertSame(VitrineGrid::price('stand'), $boutique['price']);
        // Même montant que le « dès » de l'accueil pour l'offre Boutique
        $this->assertSame(VitrineGrid::offerFrom('boutique')['amount'], VitrineGrid::money($boutique['price']));
        $this->assertSame(['stand', 'essentiel', 'pro', 'gold'], array_column($offerPlans['boutique']['plans'], 'slug'));

        // Restaurant : pas de plan Stand ; Stand : plan Stand seul
        $this->assertNotContains('stand', array_column($offerPlans['restaurant']['plans'], 'slug'));
        $this->assertSame(['stand'], array_column($offerPlans['stand']['plans'], 'slug'));

        $response->assertSee("offer: 'boutique'", false)
            ->assertSee('value="stand"', false)
            ->assertSee('5 000 F');
    }

    public function test_step_three_uses_shop_wording_for_a_boutique(): void
    {
        $offerPlans = $this->get(route('register', ['type' => 'boutique']))->viewData('offerPlans');
        $features = collect($offerPlans['boutique']['plans'])->pluck('features')->flatten()->implode(' | ');

        $this->assertStringContainsString('articles maximum', $features);
        $this->assertStringNotContainsString('plats', $features);
        $this->assertStringNotContainsString('Chambres', $features);
        $this->assertStringNotContainsString('ingrédient', $features);
        $this->assertSame('boutique', collect($offerPlans['boutique']['plans'])->firstWhere('slug', 'stand')['pack']);

        $html = $this->get(route('register', ['type' => 'boutique']))->getContent();
        $boutiqueBlock = str($html)->after('data-offer-plans="boutique"')->before('data-welcome-pack')->toString();
        $this->assertStringContainsString('Pack Boutique', $boutiqueBlock);
        $this->assertStringContainsString('Inclus dans le plan Boutique', $boutiqueBlock);
        $this->assertStringNotContainsString('Pack Maquis', $boutiqueBlock);
        $this->assertStringNotContainsString('Chambres', $boutiqueBlock);
    }

    public function test_restaurant_step_three_is_unchanged(): void
    {
        $offerPlans = $this->get(route('register'))->assertOk()->assertSee("offer: 'restaurant'", false)->viewData('offerPlans');
        $this->assertSame('essentiel', $offerPlans['restaurant']['default']);
        $essentiel = collect($offerPlans['restaurant']['plans'])->firstWhere('slug', 'essentiel');
        $this->assertSame('essentiel', $essentiel['pack']);
        $this->assertSame($this->plan('essentiel')->name, $essentiel['name']);
    }

    public function test_a_shop_on_the_stand_plan_sees_a_catalogue_without_kitchen_or_rooms(): void
    {
        $shop = $this->subscribedRestaurant('stand', ['type' => 'boutique', 'name' => 'Superette Awa']);
        $items = collect(RestaurantNavigation::items($shop->fresh(), $this->adminOf($shop)));
        $nav = $items->keyBy('key');

        $this->assertSame('Articles', $nav['dishes']['label']);
        $this->assertSame('Catalogue', $nav['dishes']['section']);
        foreach (['rooms', 'spaces', 'waiters', 'reservations', 'ingredients', 'suppliers', 'stock-alerts'] as $hidden) {
            $this->assertArrayNotHasKey($hidden, $nav->all());
        }
        $labels = $items->pluck('label')->merge($items->pluck('section'))->implode(' ');
        $this->assertStringNotContainsStringIgnoringCase('cuisine', $labels);
        $this->assertStringNotContainsString('Plats', $labels);
    }

    public function test_pricing_page_announces_the_boutique_price(): void
    {
        $this->get(route('pricing'))
            ->assertOk()
            ->assertSee('Boutique : dès ' . VitrineGrid::money(VitrineGrid::price('stand')) . ' F/mois (plan Boutique)');
    }
}
