<?php

namespace App\Http\Controllers\Api\V1\Restaurant;

use App\Enums\DeliveryStatus;
use App\Enums\OrderStatus;
use App\Events\DeliveryStatusChanged;
use App\Http\Controllers\Controller;
use App\Models\Delivery;
use App\Models\Order;
use App\Services\DeliveryPricingService;
use App\Services\DriverAssignmentService;
use App\Services\GeocodingService;
use App\Services\StockManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DeliveryOrderController extends Controller
{
    public function __construct(
        private DriverAssignmentService $driverAssignment,
        private StockManager $stockManager,
        private GeocodingService $geo,
    ) {}

    /**
     * Commandes plateforme en attente de confirmation.
     */
    public function pending(Request $request): JsonResponse
    {
        $restaurantId = $request->user()->restaurant_id;

        $orders = Order::where('restaurant_id', $restaurantId)
            ->where('source', 'platform_web')
            ->whereIn('status', [OrderStatus::PAID->value, OrderStatus::CONFIRMED->value])
            ->with(['items.dish', 'delivery.driver'])
            ->latest()
            ->get()
            ->map(fn($o) => $this->formatOrder($o));

        return response()->json(['data' => $orders]);
    }

    /**
     * Confirmer une commande + déduire le stock.
     */
    public function confirm(Request $request, int $orderId): JsonResponse
    {
        $restaurantId = $request->user()->restaurant_id;

        $order = Order::where('restaurant_id', $restaurantId)
            ->whereIn('status', [OrderStatus::PAID->value, OrderStatus::CONFIRMED->value])
            ->findOrFail($orderId);

        $data = $request->validate([
            'estimated_prep_time' => 'nullable|integer|min:5|max:120',
        ]);

        DB::transaction(function () use ($order, $data, $restaurantId) {
            if ($data['estimated_prep_time'] ?? null) {
                $order->estimated_prep_time = $data['estimated_prep_time'];
            }

            $order->transitionTo(OrderStatus::CONFIRMED);

            $restaurant = $order->restaurant;
            if ($restaurant->hasFeature('stock')) {
                $this->stockManager->forRestaurant($restaurant)->deductForOrder($order);
            }
        });

        return response()->json([
            'message' => 'Commande confirmée.',
            'order'   => $this->formatOrder($order->fresh()->load(['items.dish', 'delivery'])),
        ]);
    }

    /**
     * Marquer la commande comme prête — livreur peut venir chercher.
     */
    public function markReady(Request $request, int $orderId): JsonResponse
    {
        $restaurantId = $request->user()->restaurant_id;

        $order = Order::where('restaurant_id', $restaurantId)
            ->whereIn('status', [OrderStatus::CONFIRMED->value, OrderStatus::PREPARING->value])
            ->findOrFail($orderId);

        DB::transaction(function () use ($order) {
            $order->transitionTo(OrderStatus::READY);

            $delivery = $order->delivery;
            if ($delivery && $delivery->status === DeliveryStatus::ASSIGNED->value) {
                // Si livreur déjà assigné, il peut maintenant venir chercher
                $delivery->update(['status' => DeliveryStatus::HEADING_TO_RESTAURANT->value]);

                broadcast(new DeliveryStatusChanged(
                    delivery:  $delivery->fresh()->load(['order', 'driver', 'restaurant']),
                    oldStatus: DeliveryStatus::ASSIGNED->value,
                    newStatus: DeliveryStatus::HEADING_TO_RESTAURANT->value,
                ));
            } elseif ($delivery && $delivery->status === DeliveryStatus::PENDING->value) {
                // Pas encore de livreur → re-chercher
                $this->driverAssignment->assign($delivery);
            }
        });

        return response()->json(['message' => 'Commande prête. Livreur notifié.']);
    }

    /**
     * Paramètres de livraison du restaurant (zone, tarifs).
     */
    public function getDeliverySettings(Request $request): JsonResponse
    {
        $restaurant = $request->user()->restaurant;

        $city = ($restaurant->latitude && $restaurant->longitude)
            ? $this->geo->detectDeliveryCity((float) $restaurant->latitude, (float) $restaurant->longitude)
            : null;

        return response()->json([
            'is_on_platform'           => $restaurant->is_on_platform,
            'avg_prep_time_minutes'    => $restaurant->avg_prep_time_minutes ?? 20,
            'platform_commission_rate' => $restaurant->platform_commission_rate ?? 12.00,
            'platform_category'        => $restaurant->platform_category,
            'city_pricing' => $city ? [
                'city_name'               => $city->name,
                'delivery_base_fee'       => $city->delivery_base_fee,
                'delivery_fee_per_km'     => $city->delivery_fee_per_km,
                'max_delivery_distance_km'=> $city->max_delivery_distance_km,
                'peak_surcharge_percent'  => $city->peak_hour_surcharge_percent,
                'is_active'               => $city->is_active,
            ] : null,
        ]);
    }

    /**
     * Mettre à jour les paramètres de livraison.
     */
    public function updateDeliverySettings(Request $request): JsonResponse
    {
        $data = $request->validate([
            'avg_prep_time_minutes' => 'sometimes|integer|min:5|max:120',
            'platform_category'     => 'sometimes|nullable|string|max:50',
        ]);

        $request->user()->restaurant->update($data);

        return response()->json(['message' => 'Paramètres mis à jour.']);
    }

    private function formatOrder(Order $order): array
    {
        $delivery = $order->delivery;
        $driver   = $delivery?->driver;

        return [
            'id'               => $order->id,
            'reference'        => $order->reference,
            'status'           => $order->status,
            'status_label'     => OrderStatus::from($order->status)->label(),
            'customer_name'    => $order->customer_name,
            'customer_phone'   => $order->customer_phone,
            'subtotal'         => $order->subtotal,
            'delivery_fee'     => $order->delivery_fee,
            'total'            => $order->total,
            'estimated_prep_time' => $order->estimated_prep_time,
            'delivery_address' => $order->delivery_address,
            'delivery_city'    => $order->delivery_city,
            'items'            => $order->items?->map(fn($i) => [
                'name'     => $i->dish_name,
                'quantity' => $i->quantity,
                'total'    => $i->total_price,
                'notes'    => $i->notes,
            ]),
            'delivery' => $delivery ? [
                'status'       => $delivery->status,
                'status_label' => DeliveryStatus::from($delivery->status)->label(),
                'driver'       => $driver ? [
                    'name'    => $driver->name,
                    'phone'   => $driver->phone,
                    'vehicle' => $driver->vehicle_type,
                    'rating'  => $driver->rating,
                ] : null,
                'assigned_at'  => $delivery->assigned_at,
                'picked_up_at' => $delivery->picked_up_at,
            ] : null,
            'created_at' => $order->created_at,
        ];
    }
}
