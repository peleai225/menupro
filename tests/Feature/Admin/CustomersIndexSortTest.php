<?php

namespace Tests\Feature\Admin;

use App\Enums\OrderStatus;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomersIndexSortTest extends TestCase
{
    use RefreshDatabase;

    private function makeCustomer(string $name, \Carbon\Carbon $registeredAt): Customer
    {
        // Eloquent écrase created_at/updated_at à l'insert initial, même si on le
        // passe explicitement à create() — il faut le forcer après coup.
        $user = User::factory()->create(['name' => $name]);
        $user->role = \App\Enums\UserRole::CUSTOMER;
        $user->save();
        $user->forceFill(['created_at' => $registeredAt])->saveQuietly();

        $customer = Customer::create([
            'user_id' => $user->id,
            'phone' => '070000'.random_int(1000, 9999),
        ]);
        $customer->forceFill(['created_at' => $registeredAt])->saveQuietly();

        return $customer;
    }

    public function test_index_shows_real_order_aggregates_and_registration_date(): void
    {
        $admin = User::factory()->superAdmin()->create(['admin_permissions' => null]);
        $restaurant = Restaurant::factory()->create();

        $old = $this->makeCustomer('Ancien Client', now()->subMonths(2));
        $recent = $this->makeCustomer('Client Récent', now()->subDay());

        Order::factory()->create([
            'restaurant_id' => $restaurant->id,
            'customer_id' => $recent->id,
            'status' => OrderStatus::COMPLETED,
            'total' => 5000,
        ]);
        Order::factory()->create([
            'restaurant_id' => $restaurant->id,
            'customer_id' => $recent->id,
            'status' => OrderStatus::COMPLETED,
            'total' => 3000,
        ]);

        // Défaut : tri par inscription la plus récente -> "Client Récent" doit apparaître avant "Ancien Client".
        $response = $this->actingAs($admin)->get(route('super-admin.customers.index'));
        $response->assertOk();

        $content = $response->getContent();
        $posRecent = strpos($content, 'Client Récent');
        $posOld = strpos($content, 'Ancien Client');
        $this->assertNotFalse($posRecent);
        $this->assertNotFalse($posOld);
        $this->assertLessThan($posOld, $posRecent, 'Le client le plus récemment inscrit doit apparaître en premier par défaut.');

        // Les vrais agrégats (2 commandes, 8000 FCFA) doivent apparaître pour "Client Récent".
        $response->assertSee('8 000 FCFA', false);

        // La date d'inscription doit être affichée.
        $response->assertSee($recent->created_at->format('d/m/Y'));
        $response->assertSee($old->created_at->format('d/m/Y'));
    }

    public function test_sort_oldest_first_reorders_list(): void
    {
        $admin = User::factory()->superAdmin()->create(['admin_permissions' => null]);

        $old = $this->makeCustomer('Ancien Client', now()->subMonths(2));
        $recent = $this->makeCustomer('Client Récent', now()->subDay());

        $response = $this->actingAs($admin)
            ->get(route('super-admin.customers.index', ['sort' => 'oldest']));
        $response->assertOk();

        $content = $response->getContent();
        $posRecent = strpos($content, 'Client Récent');
        $posOld = strpos($content, 'Ancien Client');
        $this->assertLessThan($posRecent, $posOld, 'Avec sort=oldest, le client le plus ancien doit apparaître en premier.');
    }

    public function test_stats_use_real_order_counts_not_stale_columns(): void
    {
        $admin = User::factory()->superAdmin()->create(['admin_permissions' => null]);
        $restaurant = Restaurant::factory()->create();
        $customer = $this->makeCustomer('Client Stats', now());

        Order::factory()->create([
            'restaurant_id' => $restaurant->id,
            'customer_id' => $customer->id,
            'status' => OrderStatus::COMPLETED,
            'total' => 10000,
        ]);

        $response = $this->actingAs($admin)->get(route('super-admin.customers.index'));
        $response->assertOk();
        // Chiffre d'affaires réel (10 000 F) doit apparaître dans la carte stats.
        $response->assertSee('10 000 F', false);
    }
}
