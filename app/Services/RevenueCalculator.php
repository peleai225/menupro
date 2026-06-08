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
