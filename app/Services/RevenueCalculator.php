<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\CommissionLog;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class RevenueCalculator
{
    public function __construct(
        private int $restaurantId,
        private Carbon $from,
        private Carbon $to,
    ) {}

    public static function for(int $restaurantId, Carbon $from, Carbon $to): self
    {
        return new self($restaurantId, $from, $to);
    }

    public static function today(int $restaurantId): self
    {
        return new self($restaurantId, today()->startOfDay(), now());
    }

    private function baseQuery()
    {
        return Order::where('restaurant_id', $this->restaurantId)
            ->whereBetween('created_at', [$this->from, $this->to])
            ->revenue();
    }

    public function grossRevenue(): int
    {
        return (int) $this->baseQuery()->sum('total');
    }

    public function netRevenue(): int
    {
        $gross = $this->grossRevenue();
        $commissions = $this->totalCommissions();

        return $gross - $commissions;
    }

    public function totalCollected(): int
    {
        return (int) Order::where('restaurant_id', $this->restaurantId)
            ->whereBetween('created_at', [$this->from, $this->to])
            ->where('payment_status', PaymentStatus::COMPLETED)
            ->whereNotIn('status', [
                OrderStatus::DRAFT,
                OrderStatus::CANCELLED,
                OrderStatus::REFUNDED,
            ])
            ->sum('total');
    }

    public function totalCommissions(): int
    {
        return (int) CommissionLog::whereHas('order', function ($q) {
            $q->where('restaurant_id', $this->restaurantId)
                ->whereBetween('created_at', [$this->from, $this->to]);
        })->sum('amount');
    }

    public function validOrdersCount(): int
    {
        return $this->baseQuery()->count();
    }

    public function averageTicket(): int
    {
        $count = $this->validOrdersCount();

        return $count > 0 ? (int) round($this->grossRevenue() / $count) : 0;
    }

    public function revenueByPaymentMethod(): Collection
    {
        return $this->baseQuery()
            ->selectRaw('payment_method, SUM(total) as total_amount, COUNT(*) as orders_count')
            ->groupBy('payment_method')
            ->get();
    }

    public function revenueByHour(): Collection
    {
        return $this->baseQuery()
            ->selectRaw('HOUR(created_at) as hour, SUM(total) as total_amount, COUNT(*) as orders_count')
            ->groupBy('hour')
            ->orderBy('hour')
            ->get();
    }

    public function cancellationStats(): array
    {
        $cancelled = Order::where('restaurant_id', $this->restaurantId)
            ->whereBetween('created_at', [$this->from, $this->to])
            ->where('status', \App\Enums\OrderStatus::CANCELLED)
            ->selectRaw('COUNT(*) as count, SUM(total) as total_lost')
            ->first();

        return [
            'count'      => (int) ($cancelled->count ?? 0),
            'total_lost' => (int) ($cancelled->total_lost ?? 0),
        ];
    }

    public function revenueByPaymentMethodDetailed(): Collection
    {
        $rows = $this->baseQuery()
            ->selectRaw('payment_method, SUM(total) as total_amount, COUNT(*) as orders_count')
            ->groupBy('payment_method')
            ->get();

        $cashMethods = ['cash', 'cash_on_delivery', 'on_site', null];
        $labels = [
            'cash'             => 'Espèces',
            'cash_on_delivery' => 'Espèces (livraison)',
            'on_site'          => 'Sur place',
            'wave'             => 'Wave',
            'jeko'             => 'Jeko / Mobile Money',
            'orange'           => 'Orange Money',
            'mtn'              => 'MTN MoMo',
            'moov'             => 'Moov Money',
        ];

        return $rows->map(function ($row) use ($cashMethods, $labels) {
            $method = $row->payment_method;
            return [
                'method'       => $method ?? 'inconnu',
                'label'        => $labels[$method] ?? ucfirst($method ?? 'Inconnu'),
                'is_cash'      => in_array($method, $cashMethods),
                'total_amount' => (int) $row->total_amount,
                'orders_count' => (int) $row->orders_count,
            ];
        });
    }

    public function revenueByHourWithPayment(): Collection
    {
        return $this->baseQuery()
            ->selectRaw("
                HOUR(paid_at) as hour,
                COUNT(*) as orders_count,
                SUM(total) as total_amount,
                SUM(CASE WHEN payment_method IN ('cash','cash_on_delivery','on_site') OR payment_method IS NULL
                    THEN total ELSE 0 END) as cash_amount,
                SUM(CASE WHEN payment_method NOT IN ('cash','cash_on_delivery','on_site') AND payment_method IS NOT NULL
                    THEN total ELSE 0 END) as mobile_amount
            ")
            ->groupByRaw('HOUR(paid_at)')
            ->orderByRaw('HOUR(paid_at)')
            ->get()
            ->map(fn($row) => [
                'hour'          => (int) $row->hour,
                'hour_label'    => sprintf('%02dh-%02dh', $row->hour, $row->hour + 1),
                'orders_count'  => (int) $row->orders_count,
                'total_amount'  => (int) $row->total_amount,
                'cash_amount'   => (int) $row->cash_amount,
                'mobile_amount' => (int) $row->mobile_amount,
            ]);
    }

    public function revenueByCategory(): Collection
    {
        return $this->baseQuery()
            ->join('order_items', 'orders.id', '=', 'order_items.order_id')
            ->join('dishes', 'order_items.dish_id', '=', 'dishes.id')
            ->join('categories', 'dishes.category_id', '=', 'categories.id')
            ->selectRaw('categories.name as category_name, SUM(order_items.total_price) as total_amount, SUM(order_items.quantity) as total_quantity')
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc('total_amount')
            ->get();
    }

    public function topProducts(int $limit = 10): Collection
    {
        return $this->baseQuery()
            ->join('order_items', 'orders.id', '=', 'order_items.order_id')
            ->join('dishes', 'order_items.dish_id', '=', 'dishes.id')
            ->selectRaw('dishes.name as dish_name, SUM(order_items.quantity) as total_sold, SUM(order_items.total_price) as total_revenue')
            ->groupBy('dishes.id', 'dishes.name')
            ->orderByDesc('total_revenue')
            ->limit($limit)
            ->get();
    }

    public function summary(): array
    {
        return [
            'gross_revenue' => $this->grossRevenue(),
            'net_revenue' => $this->netRevenue(),
            'total_collected' => $this->totalCollected(),
            'commissions' => $this->totalCommissions(),
            'valid_orders_count' => $this->validOrdersCount(),
            'average_ticket' => $this->averageTicket(),
            'period' => [
                'from' => $this->from->toDateString(),
                'to' => $this->to->toDateString(),
            ],
        ];
    }
}
