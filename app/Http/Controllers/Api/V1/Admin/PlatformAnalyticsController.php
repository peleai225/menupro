<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Enums\DeliveryStatus;
use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Delivery;
use App\Models\DeliveryDriver;
use App\Models\DriverEarning;
use App\Models\Order;
use App\Models\Restaurant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PlatformAnalyticsController extends Controller
{
    /**
     * Dashboard global plateforme de livraison.
     */
    public function dashboard(Request $request): JsonResponse
    {
        $days = (int) ($request->days ?? 30);
        $from = now()->subDays($days)->startOfDay();

        return response()->json([
            'period_days' => $days,
            'orders'      => $this->ordersStats($from),
            'deliveries'  => $this->deliveriesStats($from),
            'drivers'     => $this->driversStats(),
            'revenue'     => $this->revenueStats($from),
            'customers'   => $this->customersStats($from),
            'top_restaurants' => $this->topRestaurants($from),
        ]);
    }

    /**
     * Liste des livraisons actives en temps réel.
     */
    public function liveDeliveries(): JsonResponse
    {
        $deliveries = Delivery::whereIn('status', [
            DeliveryStatus::ASSIGNED->value,
            DeliveryStatus::HEADING_TO_RESTAURANT->value,
            DeliveryStatus::PICKED_UP->value,
            DeliveryStatus::DELIVERING->value,
        ])
        ->with([
            'order:id,reference,customer_name,customer_phone,total',
            'driver:id,name,phone,latitude,longitude,vehicle_type,rating',
            'restaurant:id,name,city,latitude,longitude',
        ])
        ->latest()
        ->limit(100)
        ->get()
        ->map(fn($d) => [
            'delivery_id'      => $d->id,
            'status'           => $d->status,
            'status_label'     => DeliveryStatus::from($d->status)->label(),
            'order_ref'        => $d->order?->reference,
            'customer_name'    => $d->order?->customer_name,
            'customer_phone'   => $d->order?->customer_phone,
            'restaurant'       => $d->restaurant?->name,
            'restaurant_city'  => $d->restaurant?->city,
            'driver_name'      => $d->driver?->name,
            'driver_phone'     => $d->driver?->phone,
            'driver_lat'       => $d->driver?->latitude,
            'driver_lng'       => $d->driver?->longitude,
            'delivery_address' => $d->delivery_address,
            'assigned_at'      => $d->assigned_at,
            'elapsed'          => $d->assigned_at?->diffForHumans(),
        ]);

        return response()->json(['data' => $deliveries, 'count' => $deliveries->count()]);
    }

    /**
     * Commissions perçues par commande.
     */
    public function commissions(Request $request): JsonResponse
    {
        $days = (int) ($request->days ?? 30);
        $from = now()->subDays($days)->startOfDay();

        $commissions = Order::where('source', 'platform_web')
            ->where('payment_status', 'completed')
            ->where('created_at', '>=', $from)
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as orders_count'),
                DB::raw('SUM(platform_commission) as total_commission'),
                DB::raw('SUM(total) as total_gmv'),
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $totals = Order::where('source', 'platform_web')
            ->where('payment_status', 'completed')
            ->where('created_at', '>=', $from)
            ->selectRaw('SUM(platform_commission) as total_commission, SUM(total) as total_gmv, COUNT(*) as orders_count')
            ->first();

        return response()->json([
            'period_days'      => $days,
            'total_commission' => (int) $totals->total_commission,
            'total_gmv'        => (int) $totals->total_gmv,
            'orders_count'     => (int) $totals->orders_count,
            'daily'            => $commissions,
        ]);
    }

    /**
     * Gains livreurs — vue agrégée.
     */
    public function driverEarnings(Request $request): JsonResponse
    {
        $days = (int) ($request->days ?? 30);
        $from = now()->subDays($days)->startOfDay();

        $earnings = DriverEarning::where('created_at', '>=', $from)
            ->join('delivery_drivers', 'driver_earnings.driver_id', '=', 'delivery_drivers.id')
            ->select(
                'delivery_drivers.id',
                'delivery_drivers.name',
                'delivery_drivers.city',
                DB::raw('COUNT(driver_earnings.id) as deliveries'),
                DB::raw('SUM(driver_earnings.gross_amount) as gross'),
                DB::raw('SUM(driver_earnings.platform_cut) as platform_cut'),
                DB::raw('SUM(driver_earnings.net_amount) as net'),
            )
            ->groupBy('delivery_drivers.id', 'delivery_drivers.name', 'delivery_drivers.city')
            ->orderByDesc('net')
            ->limit(50)
            ->get();

        return response()->json(['data' => $earnings]);
    }

    // -------------------------------------------------------------------------

    private function ordersStats(\Carbon\Carbon $from): array
    {
        $base = Order::where('source', 'platform_web')->where('created_at', '>=', $from);

        return [
            'total'     => (clone $base)->count(),
            'completed' => (clone $base)->where('status', 'completed')->count(),
            'cancelled' => (clone $base)->where('status', 'cancelled')->count(),
            'pending'   => (clone $base)->whereIn('status', ['paid', 'confirmed', 'preparing', 'ready', 'delivering'])->count(),
        ];
    }

    private function deliveriesStats(\Carbon\Carbon $from): array
    {
        return [
            'total'         => Delivery::where('created_at', '>=', $from)->count(),
            'delivered'     => Delivery::where('status', DeliveryStatus::DELIVERED->value)->where('created_at', '>=', $from)->count(),
            'cancelled'     => Delivery::where('status', DeliveryStatus::CANCELLED->value)->where('created_at', '>=', $from)->count(),
            'avg_time_min'  => (int) Delivery::where('status', DeliveryStatus::DELIVERED->value)
                ->where('created_at', '>=', $from)
                ->whereNotNull('assigned_at')
                ->whereNotNull('delivered_at')
                ->selectRaw('AVG(TIMESTAMPDIFF(MINUTE, assigned_at, delivered_at)) as avg_min')
                ->value('avg_min'),
        ];
    }

    private function driversStats(): array
    {
        return [
            'total'     => DeliveryDriver::count(),
            'approved'  => DeliveryDriver::where('verification_status', 'approved')->count(),
            'pending'   => DeliveryDriver::where('verification_status', 'pending')->count(),
            'online'    => DeliveryDriver::where('is_active', true)->where('is_available', true)->count(),
        ];
    }

    private function revenueStats(\Carbon\Carbon $from): array
    {
        $row = Order::where('source', 'platform_web')
            ->where('payment_status', 'completed')
            ->where('created_at', '>=', $from)
            ->selectRaw('SUM(total) as gmv, SUM(delivery_fee) as delivery_revenue, SUM(platform_commission) as commission')
            ->first();

        return [
            'gmv'              => (int) $row->gmv,
            'delivery_revenue' => (int) $row->delivery_revenue,
            'commission'       => (int) $row->commission,
        ];
    }

    private function customersStats(\Carbon\Carbon $from): array
    {
        return [
            'total'     => Customer::count(),
            'new'       => Customer::where('created_at', '>=', $from)->count(),
            'active'    => Customer::where('last_order_at', '>=', $from)->count(),
        ];
    }

    private function topRestaurants(\Carbon\Carbon $from): \Illuminate\Support\Collection
    {
        return Order::where('source', 'platform_web')
            ->where('payment_status', 'completed')
            ->where('created_at', '>=', $from)
            ->join('restaurants', 'orders.restaurant_id', '=', 'restaurants.id')
            ->select(
                'restaurants.id',
                'restaurants.name',
                'restaurants.city',
                DB::raw('COUNT(orders.id) as orders_count'),
                DB::raw('SUM(orders.total) as revenue'),
                DB::raw('SUM(orders.platform_commission) as commission'),
            )
            ->groupBy('restaurants.id', 'restaurants.name', 'restaurants.city')
            ->orderByDesc('revenue')
            ->limit(10)
            ->get();
    }
}
