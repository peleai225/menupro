<?php

namespace Tests\Feature\Restaurant;

use App\Livewire\Restaurant\DishForm;
use App\Models\Category;
use App\Models\Dish;
use App\Models\Restaurant;
use App\Support\BusinessType;
use App\Support\RestaurantNavigation;
use Livewire\Livewire;
use Tests\Feature\Plans\PricingGridTestCase;

/**
 * Boutiques (restaurants.type = 'boutique') : inscription, vocabulaire « Articles / Catalogue »,
 * onglets sans objet masqués, API client (business_type + filtre ?type=) et menu public.
 */
class BoutiqueTest extends PricingGridTestCase
{
    private function shop(string $plan = 'pro', array $attributes = []): Restaurant
    {
        return $this->subscribedRestaurant($plan, array_merge(['type' => 'boutique', 'name' => 'Superette Awa'], $attributes));
    }

    private function navKeys(Restaurant $restaurant): array
    {
        return collect(RestaurantNavigation::items($restaurant->fresh(), $this->adminOf($restaurant)))
            ->keyBy('key')
            ->all();
    }

    // -------------------------------------------------------------------------
    // Type de commerce
    // -------------------------------------------------------------------------

    public function test_business_types_config_describes_the_shop(): void
    {
        $this->assertContains('boutique', BusinessType::values());
        $this->assertTrue(BusinessType::isShop('boutique'));
        $this->assertFalse(BusinessType::isShop('maquis'));
        $this->assertFalse(BusinessType::isShop(null), 'Type absent = restaurant');
        $this->assertSame('Boutique', BusinessType::labelOf('boutique'));
        $this->assertNotContains('boutique', BusinessType::valuesOfKind('food'));
        $this->assertContains('stand', BusinessType::valuesOfKind('food'));

        $this->assertSame('Articles', business_label('dishes', 'boutique'));
        $this->assertSame('Catalogue', business_label('menu', 'boutique'));
        $this->assertSame('Préparation', business_label('kitchen', 'boutique'));
        $this->assertSame('Plats', business_label('dishes', 'maquis'));
        $this->assertSame('Menu', business_label('menu', 'restaurant'));
    }

    public function test_a_shop_can_register_on_any_plan(): void
    {
        $this->post(route('register.post'), [
            'name' => 'Awa',
            'restaurant_name' => 'Superette Awa',
            'restaurant_type' => 'boutique',
            'phone' => '0700000077',
            'password' => 'password123',
            'plan' => 'essentiel',
            'terms' => '1',
        ])->assertRedirect(route('restaurant.pending'));

        $restaurant = Restaurant::firstOrFail();
        $this->assertSame('boutique', $restaurant->type);
        $this->assertTrue($restaurant->isShop());
        $this->assertSame('shop', $restaurant->businessKind());
        $this->assertSame($this->plan('essentiel')->id, $restaurant->current_plan_id);
    }

    public function test_registration_rejects_an_unknown_business_type(): void
    {
        $this->post(route('register.post'), [
            'name' => 'Awa',
            'restaurant_name' => 'Superette Awa',
            'restaurant_type' => 'garage',
            'phone' => '0700000078',
            'password' => 'password123',
            'plan' => 'essentiel',
            'terms' => '1',
        ])->assertSessionHasErrors('restaurant_type');

        $this->assertSame(0, Restaurant::count());
    }

    // -------------------------------------------------------------------------
    // Tableau de bord
    // -------------------------------------------------------------------------

    public function test_shop_navigation_uses_shop_vocabulary_and_hides_restaurant_only_tabs(): void
    {
        $nav = $this->navKeys($this->shop('pro'));

        $this->assertSame('Articles', $nav['dishes']['label']);
        $this->assertSame('Catalogue', $nav['dishes']['section']);
        $this->assertSame('Stock articles', $nav['stock-daily']['label']);
        foreach (['rooms', 'spaces', 'waiters', 'reservations', 'ingredients', 'suppliers', 'stock-alerts'] as $hidden) {
            $this->assertArrayNotHasKey($hidden, $nav, "Onglet « {$hidden} » sans objet pour une boutique");
        }
        // Le reste du plan Pro reste disponible
        foreach (['orders', 'pos', 'customers', 'analytics', 'team', 'subscription'] as $kept) {
            $this->assertSame('ok', $nav[$kept]['state']);
        }
    }

    public function test_restaurant_navigation_is_unchanged(): void
    {
        $nav = $this->navKeys($this->subscribedRestaurant('pro', ['type' => 'maquis']));

        $this->assertSame('Plats', $nav['dishes']['label']);
        $this->assertSame('Menu', $nav['dishes']['section']);
        $this->assertArrayHasKey('reservations', $nav);
        $this->assertArrayHasKey('ingredients', $nav);
    }

    public function test_shop_dashboard_pages_say_articles_and_catalogue(): void
    {
        $shop = $this->shop('essentiel');
        $owner = $this->adminOf($shop);

        $this->actingAs($owner)->get(route('restaurant.plats.index'))
            ->assertOk()
            ->assertSee('Articles - Superette Awa', false)
            ->assertSee('Nouvel article')
            ->assertSee('au catalogue')
            ->assertDontSee('Nouveau plat');

        $this->actingAs($owner)->get(route('restaurant.plats.create'))
            ->assertOk()
            ->assertSee("Nom de l'article")
            ->assertSee('Référence')
            ->assertDontSee('Informations nutritionnelles');

        $this->actingAs($owner)->get(route('restaurant.categories.index'))
            ->assertOk()
            ->assertSee('Organisez vos articles par catégorie');
    }

    public function test_restaurant_dashboard_pages_keep_dishes_wording(): void
    {
        $restaurant = $this->subscribedRestaurant('essentiel', ['type' => 'restaurant']);

        $this->actingAs($this->adminOf($restaurant))->get(route('restaurant.plats.create'))
            ->assertOk()
            ->assertSee('Nouveau plat')
            ->assertSee('Informations nutritionnelles')
            ->assertDontSee('Ex: CHG-USBC-01', false);
    }

    public function test_shop_article_keeps_its_reference_stock_and_options(): void
    {
        $shop = $this->shop('essentiel');
        $owner = $this->adminOf($shop);
        $category = Category::create(['restaurant_id' => $shop->id, 'name' => 'Accessoires', 'slug' => 'accessoires', 'is_active' => true]);

        $this->actingAs($owner);
        Livewire::test(DishForm::class)
            ->set('name', 'Chargeur USB-C')
            ->set('sku', ' CHG-USBC-01 ')
            ->set('price', 3500)
            ->set('category_id', $category->id)
            ->set('optionGroups', [[
                'id' => null, 'name' => 'Couleur', 'is_required' => true, 'max_selections' => 1,
                'options' => [
                    ['id' => null, 'name' => 'Noir', 'price_adjustment' => 0, 'is_available' => true],
                    ['id' => null, 'name' => 'Blanc', 'price_adjustment' => 0, 'is_available' => true],
                ],
            ]])
            ->call('save')
            ->assertHasNoErrors();

        $article = Dish::where('restaurant_id', $shop->id)->firstOrFail();
        $this->assertSame('CHG-USBC-01', $article->sku);
        $this->assertSame(['Couleur'], $article->optionGroups()->pluck('name')->all());

        // Stock par article (même mécanique que les plats)
        $article->update(['track_stock' => true, 'stock_quantity' => 2]);
        $this->assertTrue($article->fresh()->hasStock(2));
        $article->decreaseStock(2);
        $this->assertFalse($article->fresh()->hasStock());
    }

    // -------------------------------------------------------------------------
    // API client
    // -------------------------------------------------------------------------

    public function test_api_exposes_business_type_and_filters_shops(): void
    {
        $shop = $this->shop('essentiel', ['is_on_platform' => true]);
        $maquis = $this->subscribedRestaurant('essentiel', ['type' => 'maquis', 'is_on_platform' => true]);
        $stand = $this->subscribedRestaurant('stand', ['type' => 'stand', 'is_on_platform' => true]);

        $all = $this->getJson('/api/v1/restaurants')->assertOk()->json('data');
        $this->assertCount(3, $all);
        $byId = collect($all)->keyBy('id');
        $this->assertSame('boutique', $byId[$shop->id]['business_type']);
        $this->assertSame('shop', $byId[$shop->id]['business_kind']);
        $this->assertSame('Boutique', $byId[$shop->id]['business_label']);
        $this->assertSame('maquis', $byId[$maquis->id]['business_type']);
        $this->assertSame('food', $byId[$maquis->id]['business_kind']);
        $this->assertSame('Maquis', $byId[$maquis->id]['business_label']);

        $shops = $this->getJson('/api/v1/restaurants?type=boutique')->assertOk()->json('data');
        $this->assertSame([$shop->id], array_column($shops, 'id'));

        $food = $this->getJson('/api/v1/restaurants?type=restaurant')->assertOk()->json('data');
        $this->assertEqualsCanonicalizing([$maquis->id, $stand->id], array_column($food, 'id'));

        $exact = $this->getJson('/api/v1/restaurants?type=stand')->assertOk()->json('data');
        $this->assertSame([$stand->id], array_column($exact, 'id'));

        $this->getJson("/api/v1/restaurants/{$shop->id}")
            ->assertOk()
            ->assertJsonPath('business_type', 'boutique')
            ->assertJsonPath('business_kind', 'shop');
    }

    // -------------------------------------------------------------------------
    // Menu public
    // -------------------------------------------------------------------------

    public function test_public_page_of_a_shop_is_a_catalogue(): void
    {
        $shop = $this->shop('essentiel', ['reservations_enabled' => true]);
        $category = Category::create(['restaurant_id' => $shop->id, 'name' => 'Boissons', 'slug' => 'boissons', 'is_active' => true]);
        Dish::factory()->create(['restaurant_id' => $shop->id, 'category_id' => $category->id, 'name' => 'Eau minérale 1,5 L', 'price' => 500]);

        $this->get(route('r.menu', $shop->slug))
            ->assertOk()
            ->assertSee('Superette Awa - Catalogue', false)
            ->assertSee('Rechercher un article...', false)
            ->assertSee('Ajouter au panier', false)
            ->assertDontSee('Réserver une table');
    }

    public function test_public_page_of_a_restaurant_is_still_a_menu(): void
    {
        $restaurant = $this->subscribedRestaurant('essentiel', ['type' => 'restaurant', 'name' => 'Chez Koné']);
        $category = Category::create(['restaurant_id' => $restaurant->id, 'name' => 'Plats', 'slug' => 'plats', 'is_active' => true]);
        Dish::factory()->create(['restaurant_id' => $restaurant->id, 'category_id' => $category->id, 'name' => 'Garba', 'price' => 1000]);

        $this->get(route('r.menu', $restaurant->slug))
            ->assertOk()
            ->assertSee('Chez Koné - Menu', false)
            ->assertSee('Rechercher un plat...', false);
    }
}
