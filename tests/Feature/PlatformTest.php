<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Enums\RestaurantStatus;
use App\Enums\SubscriptionStatus;
use App\Enums\UserRole;
use App\Models\Order;
use App\Models\Plan;
use App\Models\Restaurant;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class PlatformTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /**
     * Test 1: Inscription avec essai gratuit
     */
    public function test_registration_creates_trial_subscription(): void
    {
        $plan = Plan::factory()->create(['slug' => 'menupro', 'is_active' => true]);

        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'phone' => '+225 0712345678',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'restaurant_name' => 'Test Restaurant',
            'restaurant_type' => 'restaurant',
            'restaurant_address' => '123 Test Street',
            'restaurant_city' => 'Abidjan',
        ]);

        $response->assertRedirect();

        // Vérifier que le restaurant est créé avec statut ACTIVE
        $restaurant = Restaurant::where('email', 'test@example.com')->first();
        $this->assertNotNull($restaurant);
        $this->assertEquals(RestaurantStatus::ACTIVE, $restaurant->status);
        $this->assertFalse($restaurant->orders_blocked);

        // Vérifier qu'un essai gratuit est créé
        $subscription = $restaurant->activeSubscription;
        $this->assertNotNull($subscription);
        $this->assertTrue($subscription->isTrial());
        $this->assertEquals(SubscriptionStatus::TRIAL, $subscription->status);
        $this->assertEquals(14, $subscription->trial_days);
        $this->assertEquals(0, $subscription->amount_paid);
    }

    /**
     * Test 2: Essai gratuit expire correctement
     */
    public function test_trial_expiration_blocks_restaurant(): void
    {
        $plan = Plan::factory()->create(['slug' => 'menupro', 'is_active' => true]);
        $restaurant = Restaurant::factory()->create([
            'status' => RestaurantStatus::ACTIVE,
            'orders_blocked' => false,
        ]);

        // Créer un essai expiré
        $subscription = Subscription::factory()->create([
            'restaurant_id' => $restaurant->id,
            'plan_id' => $plan->id,
            'status' => SubscriptionStatus::TRIAL,
            'is_trial' => true,
            'trial_days' => 14,
            'starts_at' => now()->subDays(15),
            'ends_at' => now()->subDay(),
            'amount_paid' => 0,
        ]);

        $restaurant->update(['subscription_ends_at' => $subscription->ends_at]);

        // Vérifier que le restaurant est considéré comme expiré
        $this->assertTrue($restaurant->is_subscription_expired);

        // Exécuter le job d'expiration
        $job = new \App\Jobs\ProcessTrialExpiration();
        $job->handle();

        $subscription->refresh();
        $restaurant->refresh();

        // Vérifier que l'essai est expiré
        $this->assertEquals(SubscriptionStatus::EXPIRED, $subscription->status);
        $this->assertTrue($restaurant->orders_blocked);
    }

    /**
     * Test 3: Modification de commande par gestionnaire
     */
    public function test_manager_can_modify_order(): void
    {
        $user = User::factory()->create(['role' => UserRole::RESTAURANT_ADMIN]);
        $restaurant = $user->restaurant;
        $order = Order::factory()->create([
            'restaurant_id' => $restaurant->id,
            'status' => OrderStatus::PAID,
        ]);

        $this->actingAs($user);

        // Tester l'ajout d'un article
        $response = $this->postJson("/dashboard/commandes/{$order->id}/items", [
            'dish_id' => $restaurant->dishes()->first()->id,
            'quantity' => 1,
        ]);

        $response->assertSuccessful();
        $this->assertDatabaseHas('order_items', [
            'order_id' => $order->id,
        ]);
    }

    /**
     * Test 4: Vue Kanban fonctionne
     */
    public function test_kanban_view_loads(): void
    {
        $user = User::factory()->create(['role' => UserRole::RESTAURANT_ADMIN]);
        
        $this->actingAs($user);

        $response = $this->get('/dashboard/commandes/kanban');
        $response->assertSuccessful();
        $response->assertViewIs('pages.restaurant.orders-kanban');
    }

    /**
     * Test 5: Mode Rush fonctionne
     */
    public function test_rush_mode_loads(): void
    {
        $user = User::factory()->create(['role' => UserRole::RESTAURANT_ADMIN]);
        
        $this->actingAs($user);

        $response = $this->get('/dashboard/commandes/rush');
        $response->assertSuccessful();
        $response->assertViewIs('pages.restaurant.orders-rush');
    }

    /**
     * Test 6: Conversion essai en abonnement payant
     */
    public function test_trial_conversion_requires_payment(): void
    {
        $plan = Plan::factory()->create(['slug' => 'menupro', 'is_active' => true]);
        $user = User::factory()->create(['role' => UserRole::RESTAURANT_ADMIN]);
        $restaurant = $user->restaurant;

        // Créer un essai actif
        $trial = Subscription::factory()->create([
            'restaurant_id' => $restaurant->id,
            'plan_id' => $plan->id,
            'status' => SubscriptionStatus::TRIAL,
            'is_trial' => true,
            'trial_days' => 14,
            'starts_at' => now(),
            'ends_at' => now()->addDays(14),
            'amount_paid' => 0,
        ]);

        $this->actingAs($user);

        // Tenter de convertir
        $response = $this->post('/dashboard/abonnement/convert-trial', [
            'plan' => $plan->slug,
            'billing_period' => 'monthly',
        ]);

        // Devrait rediriger vers le paiement (si Lygos configuré) ou afficher une erreur
        $response->assertRedirect();
    }
}
