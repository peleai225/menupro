<?php

namespace Tests\Feature\Plans;

/**
 * Site vitrine refondu : trois offres (dont Boutique), aucune affirmation invérifiable,
 * témoignages masqués tant qu'aucun vrai témoignage n'est configuré, inscription par type de commerce.
 */
class VitrinePagesTest extends PricingGridTestCase
{
    public function test_homepage_presents_the_three_offers_with_prices_from_the_grid(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Restaurant &amp; maquis', false)
            ->assertSee('Stand de rue')
            ->assertSee('Vendez vos articles en ligne : catalogue, paiement Mobile Money, livraison')
            ->assertSee(route('register', ['type' => 'boutique']), false)
            ->assertSee('1 500')
            ->assertSee('15 000')
            ->assertSee('app.menupro.ci');
    }

    public function test_public_pages_carry_no_unverifiable_claims(): void
    {
        foreach (['/', route('pricing'), route('faq'), route('login'), route('register')] as $url) {
            $this->get($url)
                ->assertOk()
                ->assertDontSee('N°1')
                ->assertDontSee('99.9')
                ->assertDontSee('99,9')
                ->assertDontSee('Koffi Adjoumani')
                ->assertDontSee('RGPD');
        }
    }

    public function test_testimonials_are_hidden_until_real_ones_are_configured(): void
    {
        config(['vitrine.testimonials' => []]);
        $this->get('/')->assertOk()->assertDontSee('Ils utilisent MenuPro');

        config(['vitrine.testimonials' => [['name' => 'Aya Test', 'role' => 'Gérante', 'city' => 'Daloa', 'text' => 'Mes clients commandent depuis leur téléphone.']]]);
        $this->get('/')->assertOk()->assertSee('Ils utilisent MenuPro')->assertSee('Aya Test');
    }

    public function test_register_offers_three_business_types_and_preselects_boutique(): void
    {
        $this->get(route('register'))
            ->assertOk()
            ->assertSee('Restaurant / Maquis')
            ->assertSee('Stand de rue')
            ->assertSee('Boutique')
            ->assertSee('name="restaurant_type"', false);

        $this->get(route('register', ['type' => 'boutique']))
            ->assertOk()
            ->assertSee("offer: 'boutique'", false)
            ->assertSee("restaurant_type: 'boutique'", false)
            ->assertSee('Pack Maquis'); // grille Restaurant/Pro proposée, pas le plan Stand forcé
    }

    public function test_login_accepts_whatsapp_number_or_email(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('Numéro WhatsApp ou e-mail')
            ->assertSee('Créer mon commerce')
            ->assertSee('Données protégées (loi ivoirienne n° 2013-450)');
    }
}
