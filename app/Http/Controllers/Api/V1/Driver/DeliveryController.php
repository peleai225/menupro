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

            try {
                broadcast(new DriverLocationUpdated(
                    deliveryId: $activeDelivery->id,
                    latitude:   (float) $data['latitude'],
                    longitude:  (float) $data['longitude'],
                    driverName: $driver->name,
                    status:     $activeDelivery->status,
                ));
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('DriverLocationUpdated broadcast failed', ['error' => $e->getMessage()]);
            }
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
        // Accepter paid (paiement en ligne) ET confirmed (cash on delivery)
        $deliveries = Delivery::where('status', DeliveryStatus::PENDING->value)
            ->whereHas('order', fn($q) => $q->whereIn('status', [
                OrderStatus::PAID->value,
                OrderStatus::CONFIRMED->value,
            ]))
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
            'message' => 'Course acceptée.',
            'data'    => $this->formatDeliveryDetail($delivery->fresh()->load(['order.items', 'restaurant'])),
        ]);
    }

    /**
     * Refuser une course assignée automatiquement.
     */
    public function decline(Request $request, int $deliveryId): JsonResponse
    {
        $driver   = $request->user()->deliveryDriver;

        // Refus depuis la liste (pending) OU refus après assignation automatique (assigned)
        $delivery = Delivery::whereIn('status', [
                DeliveryStatus::PENDING->value,
                DeliveryStatus::ASSIGNED->value,
            ])
            ->where(function ($q) use ($driver) {
                $q->where('driver_id', $driver->id)
                  ->orWhereNull('driver_id'); // pending = pas encore assigné
            })
            ->findOrFail($deliveryId);

        if ($delivery->status === DeliveryStatus::ASSIGNED->value && $delivery->driver_id === $driver->id) {
            $this->assignment->unassign($delivery, 'Refus livreur');
        }
        // Pour une course pending, on ne fait rien côté DB — on retire juste du cache local

        return response()->json(['message' => 'Course ignorée.']);
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

        // $delivery->status est casté en enum par le modèle — extraire la valeur string
        $oldStatus = $delivery->status instanceof DeliveryStatus
            ? $delivery->status->value
            : (string) $delivery->status;

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

        try {
            broadcast(new DeliveryStatusChanged(
                delivery:  $delivery->fresh()->load(['order', 'driver', 'restaurant']),
                oldStatus: $oldStatus,
                newStatus: $data['status'],
            ));
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('DeliveryStatusChanged broadcast failed', ['error' => $e->getMessage()]);
        }

        return response()->json([
            'message' => DeliveryStatus::from($data['status'])->label(),
            'data'    => $this->formatDeliveryDetail($delivery->fresh()->load(['order.items', 'restaurant'])),
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
        // status peut être un enum (cast modèle) ou une string
        $current = $delivery->status instanceof DeliveryStatus
            ? $delivery->status
            : DeliveryStatus::from($delivery->status);

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

        $order = $delivery->order;
        $items = $order->items->map(fn($i) => [
            'name'     => $i->dish_name,
            'quantity' => $i->quantity,
        ])->values()->toArray();

        return [
            'id'     => $delivery->id,
            'status' => $delivery->status instanceof \App\Enums\DeliveryStatus
                ? $delivery->status->value
                : $delivery->status,
            'order'  => [
                'reference'             => $order->reference ?? '',
                'delivery_address'      => $delivery->delivery_address,
                'delivery_phone'        => $delivery->delivery_phone,
                'delivery_instructions' => $delivery->delivery_instructions,
                'delivery_latitude'     => $delivery->delivery_latitude,
                'delivery_longitude'    => $delivery->delivery_longitude,
                'total'                 => $order->total ?? 0,
                'payment_method'        => $order->payment_method ?? '',
                'items'                 => $items,
                'restaurant' => [
                    'name'      => $delivery->restaurant->name ?? '',
                    'address'   => $delivery->restaurant->address ?? '',
                    'latitude'  => $pickupLat,
                    'longitude' => $pickupLng,
                    'phone'     => $delivery->restaurant->phone ?? null,
                    'logo_url'  => $delivery->restaurant->logo_path
                        ? \Illuminate\Support\Facades\Storage::url($delivery->restaurant->logo_path)
                        : null,
                ],
            ],
            'distance_km'             => $distToPickup,
            'estimated_minutes'       => $delivery->estimated_minutes,
            'driver_earning_estimate' => (int) round(($order->delivery_fee ?? 0) * 0.80),
        ];
    }

    private function formatDeliveryDetail(Delivery $delivery): array
    {
        $order = $delivery->order;
        $statusValue = $delivery->status instanceof DeliveryStatus
            ? $delivery->status->value
            : $delivery->status;

        $items = $order->items->map(fn($i) => [
            'name'     => $i->dish_name,
            'quantity' => $i->quantity,
        ])->values()->toArray();

        return [
            'id'     => $delivery->id,
            'status' => $statusValue,
            'order'  => [
                'reference'             => $order->reference ?? '',
                'delivery_address'      => $delivery->delivery_address,
                'delivery_phone'        => $delivery->delivery_phone,
                'delivery_instructions' => $delivery->delivery_instructions,
                'delivery_latitude'     => (float) $delivery->delivery_latitude,
                'delivery_longitude'    => (float) $delivery->delivery_longitude,
                'total'                 => $order->total ?? 0,
                'payment_method'        => $order->payment_method ?? '',
                'items'                 => $items,
                'restaurant' => [
                    'name'      => $delivery->restaurant->name ?? '',
                    'address'   => $delivery->restaurant->address ?? '',
                    'latitude'  => (float) $delivery->pickup_latitude,
                    'longitude' => (float) $delivery->pickup_longitude,
                    'phone'     => $delivery->restaurant->phone ?? null,
                    'logo_url'  => $delivery->restaurant->logo_path
                        ? \Illuminate\Support\Facades\Storage::url($delivery->restaurant->logo_path)
                        : null,
                ],
            ],
            'distance_km'             => null,
            'estimated_minutes'       => $delivery->estimated_minutes,
            'driver_earning_estimate' => (int) round(($order->delivery_fee ?? 0) * 0.80),
            'assigned_at'             => $delivery->assigned_at,
            'picked_up_at'            => $delivery->picked_up_at,
            'delivered_at'            => $delivery->delivered_at,
        ];
    }
}
