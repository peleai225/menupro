<?php

namespace Tests\Feature\Admin;

use App\Enums\DeliveryStatus;
use App\Enums\OrderStatus;
use App\Models\Delivery;
use App\Models\DeliveryDriver;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UnstickDeliveryTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_unsticks_delivery_not_yet_picked_up_and_it_gets_reassignable(): void
    {
        $admin = User::factory()->superAdmin()->create(['admin_permissions' => null]);
        $driver = DeliveryDriver::factory()->create(['is_available' => false]);
        $delivery = Delivery::factory()->create([
            'driver_id' => $driver->id,
            'status'    => DeliveryStatus::HEADING_TO_RESTAURANT,
        ]);

        $this->actingAs($admin)
            ->post(route('super-admin.drivers.unstick-delivery', $delivery), [
                'reason' => 'Livreur bloqué, app plantée',
            ])
            ->assertRedirect();

        $delivery->refresh();
        $this->assertSame(DeliveryStatus::PENDING, $delivery->status);
        $this->assertNull($delivery->driver_id);
        $this->assertSame(OrderStatus::CONFIRMED, $delivery->order->status);

        $driver->refresh();
        $this->assertNull($driver->activeDelivery());
    }

    public function test_admin_unsticks_delivery_already_picked_up_and_order_is_cancelled(): void
    {
        $admin = User::factory()->superAdmin()->create(['admin_permissions' => null]);
        $driver = DeliveryDriver::factory()->create(['is_available' => false]);
        $delivery = Delivery::factory()->create([
            'driver_id' => $driver->id,
            'status'    => DeliveryStatus::DELIVERING,
        ]);

        $this->actingAs($admin)
            ->post(route('super-admin.drivers.unstick-delivery', $delivery), [
                'reason' => 'Livreur introuvable, course en livraison depuis 5h',
            ])
            ->assertRedirect();

        $delivery->refresh();
        $this->assertSame(DeliveryStatus::CANCELLED, $delivery->status);
        $this->assertSame('admin', $delivery->cancelled_by);
        $this->assertSame(OrderStatus::CANCELLED, $delivery->order->status);

        $driver->refresh();
        $this->assertNull($driver->activeDelivery());
    }

    public function test_restricted_employee_without_deliveries_section_cannot_unstick(): void
    {
        $emp = User::factory()->superAdmin()->create(['admin_permissions' => ['finance']]);
        $driver = DeliveryDriver::factory()->create();
        $delivery = Delivery::factory()->create([
            'driver_id' => $driver->id,
            'status'    => DeliveryStatus::DELIVERING,
        ]);

        $this->actingAs($emp)
            ->post(route('super-admin.drivers.unstick-delivery', $delivery), [
                'reason' => 'test',
            ])
            ->assertForbidden();
    }
}
