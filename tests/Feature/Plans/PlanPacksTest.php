<?php

namespace Tests\Feature\Plans;

use App\Enums\SubscriptionStatus;

/**
 * Packs de supports imprimés (config('plans.packs') + welcome_pack) affichés sur les pages
 * d'offres : tarifs publics, inscription, page Abonnement du restaurant.
 */
class PlanPacksTest extends PricingGridTestCase
{
    public function test_packs_are_defined_once_in_config(): void
    {
        $this->assertSame('Pack Stand', $this->plan('stand')->pack['name']);
        $this->assertSame('Pack Maquis', $this->plan('essentiel')->pack['name']);
        $this->assertContains('2 chevalets de comptoir', $this->plan('pro')->pack['items']);
        $this->assertSame('sur devis', $this->plan('gold')->pack['note']);
    }

    public function test_public_pricing_page_shows_every_pack_and_the_welcome_poster(): void
    {
        $this->get(route('pricing'))
            ->assertOk()
            ->assertSee('Pack Stand')
            ->assertSee('4 autocollants QR de table + 1 affiche vitrine A4')
            ->assertSee('Pack Maquis')
            ->assertSee('15 autocollants de table + 1 affiche vitrine A4 + 1 autocollant de porte A3')
            ->assertSee('Pack Pro')
            ->assertSee('Supports personnalisés')
            ->assertSee(config('plans.welcome_pack'));
    }

    public function test_registration_shows_the_pack_of_the_offer(): void
    {
        $this->get(route('register', ['type' => 'stand']))
            ->assertOk()->assertSee('Pack Stand')->assertSee('offerts dès le 1er paiement');

        $this->get(route('register', ['plan' => 'essentiel']))
            ->assertOk()->assertSee('Pack Maquis')->assertSee('Pack Pro');
    }

    public function test_restaurant_subscription_page_shows_packs(): void
    {
        $trial = $this->subscribedRestaurant('essentiel', ['subscription_ends_at' => now()->addDays(10)], [
            'status' => SubscriptionStatus::TRIAL, 'is_trial' => true, 'amount_paid' => 0, 'ends_at' => now()->addDays(10),
        ]);

        $this->actingAs($this->adminOf($trial))->get(route('restaurant.subscription'))
            ->assertOk()->assertSee('Pack Stand')->assertSee('Pack Maquis')->assertSee('Pack Pro');

        $stand = $this->subscribedRestaurant('stand', ['subscription_ends_at' => now()->addDays(3)], ['ends_at' => now()->addDays(3)]);
        $this->actingAs($this->adminOf($stand))->get(route('restaurant.subscription'))
            ->assertOk()->assertSee('Pack Stand');
    }
}
