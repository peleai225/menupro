<?php
// tests/Feature/Restaurant/WaitersTest.php
namespace Tests\Feature\Restaurant;

use Tests\TestCase;
use App\Models\Waiter;
use App\Models\Restaurant;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

class WaitersTest extends TestCase
{
    use RefreshDatabase;

    public function test_waiter_can_be_created_with_hashed_pin(): void
    {
        $restaurant = Restaurant::factory()->create();
        $waiter = Waiter::factory()->create([
            'restaurant_id' => $restaurant->id,
            'name'          => 'Koffi',
            'pin_hash'      => Hash::make('1234'),
        ]);

        $this->assertTrue($waiter->checkPin('1234'));
        $this->assertFalse($waiter->checkPin('9999'));
        $this->assertDatabaseHas('waiters', ['name' => 'Koffi', 'restaurant_id' => $restaurant->id]);
    }

    public function test_waiter_locks_after_3_failed_attempts(): void
    {
        $waiter = Waiter::factory()->create(['failed_attempts' => 0]);

        $waiter->recordFailedAttempt();
        $waiter->recordFailedAttempt();
        $this->assertFalse($waiter->isLocked());

        $waiter->recordFailedAttempt(); // 3e tentative → verrouillage
        $this->assertTrue($waiter->isLocked());
        $this->assertEquals(0, $waiter->failed_attempts); // reset après verrouillage
    }

    public function test_order_can_be_assigned_to_waiter(): void
    {
        $restaurant = Restaurant::factory()->create();
        $waiter = Waiter::factory()->create(['restaurant_id' => $restaurant->id]);
        $order = Order::factory()->create([
            'restaurant_id' => $restaurant->id,
            'waiter_id'     => $waiter->id,
        ]);

        $this->assertEquals($waiter->id, $order->waiter_id);
        $this->assertEquals(1, Order::forWaiter($waiter->id)->count());
    }

    public function test_order_scope_for_waiter_null_returns_all(): void
    {
        $restaurant = Restaurant::factory()->create();
        $waiter = Waiter::factory()->create(['restaurant_id' => $restaurant->id]);
        Order::factory()->create(['restaurant_id' => $restaurant->id, 'waiter_id' => $waiter->id]);
        Order::factory()->create(['restaurant_id' => $restaurant->id, 'waiter_id' => null]);

        $this->assertEquals(2, Order::forWaiter(null)->count());
    }

    public function test_restaurant_has_waiters_relation(): void
    {
        $restaurant = Restaurant::factory()->create();
        Waiter::factory()->count(3)->create(['restaurant_id' => $restaurant->id]);
        $this->assertEquals(3, $restaurant->waiters()->count());
    }
}
