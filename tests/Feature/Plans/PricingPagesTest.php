<?php

namespace Tests\Feature\Plans;

use App\Models\User;

/**
 * Les écrans qui affichent la grille s'affichent et portent les nouveaux messages.
 */
class PricingPagesTest extends PricingGridTestCase
{
    public function test_public_pricing_page_shows_new_grid(): void
    {
        $this->get(route('pricing'))
            ->assertOk()
            ->assertSee('Livraison incluse — commission de 12 % uniquement sur les commandes livrées')
            ->assertSee("Aucune commande n'est jamais bloquée")
            ->assertSee('1 500 F / semaine')
            ->assertSee('Sur devis')
            ->assertSee('30 000');
    }

    public function test_homepage_pricing_section_shows_new_grid(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Livraison incluse.', false)
            ->assertSee('Demander un devis')
            ->assertSee('30 000');
    }

    public function test_register_page_for_stand_offers_weekly_payment(): void
    {
        $this->get(route('register', ['type' => 'stand']))
            ->assertOk()
            ->assertSee('À la semaine')
            ->assertSee('30 jours');
    }

    public function test_restaurant_subscription_pages_render_for_stand_and_restaurant(): void
    {
        $stand = $this->subscribedRestaurant('stand', ['subscription_ends_at' => now()->addDays(3)], ['ends_at' => now()->addDays(3)]);
        $this->actingAs($this->adminOf($stand))
            ->get(route('restaurant.subscription'))
            ->assertOk()
            ->assertSee('1 500 F / semaine');

        $restaurant = $this->subscribedRestaurant('essentiel');
        $admin = $this->adminOf($restaurant);
        $this->actingAs($admin)->get(route('restaurant.subscription'))->assertOk()->assertSee('Demander un devis');
        $this->actingAs($admin)->get(route('restaurant.subscription.plans'))->assertOk()->assertSee('Livraison incluse');
    }

    public function test_super_admin_plans_page_renders(): void
    {
        $this->actingAs(User::factory()->superAdmin()->create())
            ->get(route('super-admin.plans.index'))
            ->assertOk()
            ->assertSee('Vide = illimité')
            ->assertSee('Sur devis, à partir de');
    }
}
