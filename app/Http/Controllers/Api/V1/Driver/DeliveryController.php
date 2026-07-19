<?php

namespace App\Http\Controllers\Api\V1\Driver;

use App\Enums\DeliveryStatus;
use App\Enums\OrderStatus;
use App\Events\DeliveryStatusChanged;
use App\Events\DriverLocationUpdated;
use App\Http\Controllers\Controller;
use App\Models\Delivery;
use App\Models\DeliveryDriver;
use App\Models\Order;
use App\Services\DriverAssignmentService;
use App\Services\GeocodingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DeliveryController extends Controller
{
    public function __construct(
        private DriverAssignmentService $assignment,
        private GeocodingService $geo,
    ) {}

    /**
     * Mettre le livreur en ligne ou hors ligne.
     */
    public function setStatus(Request $request): JsonResponse
    {
        $data = $request->validate([
            'online' => 'required|boolean',
        ]);

        $driver = $request->user()->deliveryDriver;
        $driver->update(['is_available' => $data['online']]);

        return response()->json([
            'is_available' => $driver->is_available,
            'message'      => $data['online'] ? 'Vous êtes en ligne.' : 'Vous êtes hors ligne.',
        ]);
    }

    /**
     * Mettre à jour la position GPS du livreur.
     */
    public function updateLocation(Request $request): JsonResponse
    {
        $data = $request->validate([
            'latitude'  => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'accuracy'  => 'nullable|numeric|min:0',
            'speed'     => 'nullable|numeric|min:0',
            'heading'   => 'nullable|numeric|between:0,360',
        ]);

        $driver = $request->user()->deliveryDriver;
        $driver->updateLocation((float) $data['latitude'], (float) $data['longitude'], $data);

        // Si une livraison active, mettre à jour la position et broadcaster
        $activeDelivery = $driver->activeDelivery();
        if ($activeDelivery) {
            $activeDelivery->update([
                'driver_latitude'    => $data['latitude'],
                'driver_longitude'   => $data['longitude'],
                'driver_location_at' => now(),
            ]);

            broadcast(new DriverLocationUpdated(
                deliveryId: $activeDelivery->id,
                latitude:   (float) $data['latitude'],
                longitude:  (float) $data['longitude'],
                driverName: $driver->name,
                status:     $activeDelivery->status,
            ));
        }

        return response()->json(['message' => 'Position mise à jour.']);
    }

    /**
     * Courses disponibles dans la zone du livreur.
     */
    public function pendingOrders(Request $request): JsonResponse
    {
        $driver = $request->user()->deliveryDriver;

        // Un livreur ne peut voir les courses que s'il est disponible
        if (!$driver->is_available) {
            return response()->json(['data' => [], 'message' => 'Passez en ligne pour voir les courses.']);
        }

        // Courses en attente dans la même ville
        $deliveries = Delivery::where('status', DeliveryStatus::PENDING->value)
            ->whereHas('order', fn($q) => $q->where('status', OrderStatus::PAID->value))
            ->whereHas('restaurant', fn($q) => $q->where('city', $driver->city))
            ->with(['order', 'restaurant'])
            ->latest()
            ->get()
            ->map(fn($d) => $this->formatDeliveryForDriver($d, $driver));

        return response()->json(['data' => $deliveries]);
    }

    /**
     * Accepter une course.
     */
    public function accept(Request $request, int $deliveryId): JsonResponse
    {
        $driver   = $request->user()->deliveryDriver;
        $delivery = Delivery::where('status', DeliveryStatus::PENDING->value)->findOrFail($deliveryId);

        if (!$driver->is_available) {
            return response()->json(['message' => 'Vous n\'êtes pas disponible.'], 422);
        }

        if ($driver->activeDelivery()) {
            return response()->json(['message' => 'Vous avez déjà une course en cours.'], 422);
        }

        DB::transaction(function () use ($delivery, $driver) {
            $delivery->update([
                'driver_id'   => $driver->id,
                'status'      => DeliveryStatus::ASSIGNED->value,
                'assigned_at' => now(),
            ]);

            $driver->update(['is_available' => false]);

            $delivery->order->update(['driver_assigned_at' => now()]);
        });

        return response()->json([
            'message'  => 'Course acceptée.',
            'delivery' => $this->formatDeliveryDetail($delivery->fresh()),
        ]);
    }

    /**
     * Refuser une course assignée automatiquement.
     */
    public function decline(Request $request, int $deliveryId): JsonResponse
    {
        $driver   = $request->user()->deliveryDriver;
        $delivery = Delivery::where('driver_id', $driver->id)
            ->where('status', DeliveryStatus::ASSIGNED->value)
            ->findOrFail($deliveryId);

        $this->assignment->unassign($delivery, 'Refus livreur');

        return response()->json(['message' => 'Course refusée. Un autre livreur sera cherché.']);
    }

    /**
     * Mettre à jour le statut d'une course en cours.
     * Transitions autorisées :
     *   assigned → heading_to_restaurant → picked_up → delivering → delivered
     */
    public function updateDeliveryStatus(Request $request, int $deliveryId): JsonResponse
    {
        $data = $request->validate([
            'status' => 'required|in:heading_to_restaurant,picked_up,delivering,delivered',
        ]);

        $driver   = $request->user()->deliveryDriver;
        $delivery = Delivery::where('driver_id', $driver->id)
            ->whereIn('status', ['assigned', 'heading_to_restaurant', 'picked_up', 'delivering'])
            ->findOrFail($deliveryId);

        $newStatus = DeliveryStatus::from($data['status']);

        $this->validateStatusTransition($delivery, $newStatus);

        $oldStatus = $delivery->status;

        DB::transaction(function () use ($delivery, $newStatus, $driver) {
            $updates = ['status' => $newStatus->value];

            if ($newStatus === DeliveryStatus::PICKED_UP) {
                $updates['picked_up_at'] = now();
                $delivery->order->update(['picked_up_at' => now()]);
            }

            if ($newStatus === DeliveryStatus::DELIVERED) {
                $updates['delivered_at'] = now();
                $delivery->order->update([
                    'status'       => OrderStatus::COMPLETED->value,
                    'completed_at' => now(),
                ]);
                $delivery->update($updates);
                $this->assignment->completeDelivery($delivery->fresh());
                return;
            }

            $delivery->update($updates);
        });

        broadcast(new DeliveryStatusChanged(
            delivery:  $delivery->fresh()->load(['order', 'driver', 'restaurant']),
            oldStatus: $oldStatus,
            newStatus: $data['status'],
        ));

        return response()->json([
            'message' => DeliveryStatus::from($data['status'])->label(),
            'status'  => $data['status'],
        ]);
    }

    /**
     * Détail de la course active du livreur.
     */
    public function activeDelivery(Request $request): JsonResponse
    {
        $driver  = $request->user()->deliveryDriver;
        $delivery = $driver->activeDelivery();

        if (!$delivery) {
            return response()->json(['data' => null, 'message' => 'Aucune course en cours.']);
        }

        return response()->json(['data' => $this->formatDeliveryDetail($delivery->load(['order', 'restaurant']))]);
    }

    // -------------------------------------------------------------------------

    private function validateStatusTransition(Delivery $delivery, DeliveryStatus $new): void
    {
        $current = DeliveryStatus::from($delivery->status);

        $allowed = [
            DeliveryStatus::ASSIGNED->value              => DeliveryStatus::HEADING_TO_RESTAURANT,
            DeliveryStatus::HEADING_TO_RESTAURANT->value => DeliveryStatus::PICKED_UP,
            DeliveryStatus::PICKED_UP->value             => DeliveryStatus::DELIVERING,
            DeliveryStatus::DELIVERING->value            => DeliveryStatus::DELIVERED,
        ];

        if (!isset($allowed[$current->value]) || $allowed[$current->value] !== $new) {
            abort(422, "Transition '{$current->value}' → '{$new->value}' non autorisée.");
        }
    }

    private function formatDeliveryForDriver(Delivery $delivery, DeliveryDriver $driver): array
    {
        $pickupLat = (float) $delivery->pickup_latitude;
        $pickupLng = (float) $delivery->pickup_longitude;
        $driverLat = (float) $driver->latitude;
        $driverLng = (float) $driver->longitude;

        $distToPickup = ($driverLat && $driverLng)
            ? round($this->geo->distanceKm($driverLat, $driverLng, $pickupLat, $pickupLng), 1)
            : null;

        return [
            'id'              => $delivery->id,
            'pickup_address'  => $delivery->restaurant->address ?? '',
            'pickup_name'     => $delivery->restaurant->name ?? '',
            'pickup_lat'      => $pickupLat,
            'pickup_lng'      => $pickupLng,
            'delivery_address' => $delivery->delivery_address,
            'delivery_lat'    => $delivery->delivery_latitude,
            'delivery_lng'    => $delivery->delivery_longitude,
            'distance_to_pickup_km' => $distToPickup,
            'delivery_fee'    => $delivery->order->delivery_fee,
            'driver_earning'  => (int) round($delivery->order->delivery_fee * 0.80),
            'items_count'     => $delivery->order->items()->count(),
            'estimated_minutes' => $delivery->estimated_minutes,
            'created_at'      => $delivery->created_at,
        ];
    }

    private function formatDeliveryDetail(Delivery $delivery): array
    {
        $order = $delivery->order;
        return [
            'id'               => $delivery->id,
            'status'           => $delivery->status,
            'status_label'     => DeliveryStatus::from($delivery->status)->label(),
            'pickup' => [
                'name'    => $delivery->restaurant->name ?? '',
                'address' => $delivery->restaurant->address ?? '',
                'phone'   => $delivery->restaurant->phone ?? '',
                'lat'     => $delivery->pickup_latitude,
                'lng'     => $delivery->pickup_longitude,
            ],
            'dropoff' => [
                'address'      => $delivery->delivery_address,
                'phone'        => $delivery->delivery_phone,
                'instructions' => $delivery->delivery_instructions,
                'lat'          => $delivery->delivery_latitude,
                'lng'          => $delivery->delivery_longitude,
            ],
            'order' => [
                'reference'    => $order->reference,
                'items_count'  => $order->items()->count(),
                'total'        => $order->total,
                'delivery_fee' => $order->delivery_fee,
                'driver_earning' => (int) round($order->delivery_fee * 0.80),
            ],
            'assigned_at'   => $delivery->assigned_at,
            'picked_up_at'  => $delivery->picked_up_at,
            'delivered_at'  => $delivery->delivered_at,
        ];
    }
}
