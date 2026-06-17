<?php

namespace App\Http\Controllers\Public;

use App\Enums\DeliveryStatus;
use App\Events\DriverLocationUpdated;
use App\Http\Controllers\Controller;
use App\Models\Delivery;
use App\Models\DeliveryDriver;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DriverController extends Controller
{
    public function dashboard(string $token): View
    {
        $driver = DeliveryDriver::where('token', $token)->where('is_active', true)->with('restaurant')->firstOrFail();

        $activeDeliveries = Delivery::where('driver_id', $driver->id)
            ->whereIn('status', [
                DeliveryStatus::ASSIGNED,
                DeliveryStatus::HEADING_TO_RESTAURANT,
                DeliveryStatus::PICKED_UP,
                DeliveryStatus::DELIVERING,
            ])
            ->with(['order', 'restaurant'])
            ->get();

        $completedToday = Delivery::where('driver_id', $driver->id)
            ->where('status', DeliveryStatus::DELIVERED)
            ->whereDate('delivered_at', today())
            ->count();

        $deliveriesJson = $activeDeliveries->map(fn($d) => [
            'id' => $d->id,
            'status' => $d->status->value,
            'status_label' => $d->status->label(),
            'order_reference' => $d->order->reference,
            'customer_name' => $d->order->customer_name,
            'delivery_address' => $d->delivery_address,
            'delivery_phone' => $d->delivery_phone,
            'delivery_instructions' => $d->delivery_instructions,
            'delivery_latitude' => $d->delivery_latitude,
            'delivery_longitude' => $d->delivery_longitude,
            'restaurant_name' => $d->restaurant->name ?? '',
            'restaurant_address' => $d->restaurant->address ?? '',
            'restaurant_latitude' => $d->restaurant->latitude ?? null,
            'restaurant_longitude' => $d->restaurant->longitude ?? null,
            'assigned_at' => $d->assigned_at?->format('H:i'),
        ])->values();

        return view('pages.driver.dashboard', compact('driver', 'deliveriesJson', 'completedToday', 'token'));
    }

    public function data(string $token): JsonResponse
    {
        $driver = DeliveryDriver::where('token', $token)->where('is_active', true)->firstOrFail();

        $deliveries = Delivery::where('driver_id', $driver->id)
            ->whereIn('status', [
                DeliveryStatus::ASSIGNED,
                DeliveryStatus::HEADING_TO_RESTAURANT,
                DeliveryStatus::PICKED_UP,
                DeliveryStatus::DELIVERING,
            ])
            ->with('order')
            ->get()
            ->map(fn($d) => [
                'id' => $d->id,
                'status' => $d->status->value,
                'status_label' => $d->status->label(),
                'order_reference' => $d->order->reference,
                'customer_name' => $d->order->customer_name,
                'delivery_address' => $d->delivery_address,
                'delivery_phone' => $d->delivery_phone,
                'delivery_instructions' => $d->delivery_instructions,
                'delivery_latitude' => $d->delivery_latitude,
                'delivery_longitude' => $d->delivery_longitude,
                'restaurant_name' => $d->restaurant->name ?? '',
                'restaurant_address' => $d->restaurant->address ?? '',
                'restaurant_latitude' => $d->restaurant->latitude ?? null,
                'restaurant_longitude' => $d->restaurant->longitude ?? null,
                'assigned_at' => $d->assigned_at?->format('H:i'),
            ]);

        $completedToday = Delivery::where('driver_id', $driver->id)
            ->where('status', DeliveryStatus::DELIVERED)
            ->whereDate('delivered_at', today())
            ->count();

        return response()->json([
            'deliveries' => $deliveries,
            'completed_today' => $completedToday,
            'driver_name' => $driver->name,
        ]);
    }

    public function updateStatus(string $token, Delivery $delivery, Request $request): JsonResponse
    {
        $driver = DeliveryDriver::where('token', $token)->where('is_active', true)->firstOrFail();

        if ((int) $delivery->driver_id !== (int) $driver->id) {
            abort(403);
        }

        $action = $request->input('action');

        $newStatus = match ($action) {
            'heading' => DeliveryStatus::HEADING_TO_RESTAURANT,
            'pickup' => DeliveryStatus::PICKED_UP,
            'delivering' => DeliveryStatus::DELIVERING,
            'delivered' => DeliveryStatus::DELIVERED,
            default => null,
        };

        if (!$newStatus) {
            return response()->json(['error' => 'Action invalide'], 400);
        }

        if (!$delivery->transitionTo($newStatus)) {
            return response()->json(['error' => 'Transition impossible'], 422);
        }

        if ($newStatus === DeliveryStatus::DELIVERED) {
            $driver->increment('total_deliveries');
            $driver->update(['is_available' => true]);

            if ($delivery->order) {
                $delivery->order->transitionTo(\App\Enums\OrderStatus::COMPLETED);
            }
        } else {
            $driver->update(['is_available' => false]);
        }

        return response()->json(['success' => true, 'new_status' => $newStatus->value]);
    }

    public function updateLocation(string $token, Request $request): JsonResponse
    {
        $driver = DeliveryDriver::where('token', $token)->where('is_active', true)->firstOrFail();

        $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
        ]);

        $driver->update([
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'location_updated_at' => now(),
        ]);

        $activeDelivery = $driver->activeDelivery();
        if ($activeDelivery) {
            $activeDelivery->update([
                'driver_latitude' => $request->latitude,
                'driver_longitude' => $request->longitude,
                'driver_location_at' => now(),
            ]);

            broadcast(new DriverLocationUpdated(
                deliveryId: $activeDelivery->id,
                latitude: $request->latitude,
                longitude: $request->longitude,
                driverName: $driver->name,
                status: $activeDelivery->status->value,
            ));
        }

        return response()->json(['success' => true]);
    }
}
