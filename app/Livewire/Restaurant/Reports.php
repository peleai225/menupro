<?php

namespace App\Livewire\Restaurant;

use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ReportsExport;

class Reports extends Component
{
    public string $reportType = 'sales'; // sales, dishes, customers, financial
    public string $period = '30'; // days
    public ?string $startDate = null;
    public ?string $endDate = null;
    public string $format = 'view'; // view, pdf, excel

    public function mount(): void
    {
        $this->startDate = now()->subDays(30)->format('Y-m-d');
        $this->endDate = now()->format('Y-m-d');
    }

    public function updatedPeriod(): void
    {
        if ($this->period === 'custom') {
            return;
        }

        $days = (int) $this->period;
        $this->startDate = now()->subDays($days)->format('Y-m-d');
        $this->endDate = now()->format('Y-m-d');
    }

    /**
     * Clean data before Livewire serialization
     */
    public function dehydrate(): void
    {
        try {
            // Clean all string properties to ensure valid UTF-8
            $this->reportType = $this->cleanString($this->reportType ?? 'sales');
            $this->period = $this->cleanString($this->period ?? '30');
            if ($this->startDate) {
                $this->startDate = $this->cleanString($this->startDate);
            }
            if ($this->endDate) {
                $this->endDate = $this->cleanString($this->endDate);
            }
            $this->format = $this->cleanString($this->format ?? 'view');
        } catch (\Exception $e) {
            \Log::warning('Error in dehydrate', [
                'error' => $e->getMessage()
            ]);
        }
    }

    protected function getReportData(): array
    {
        try {
            $restaurant = auth()->user()->restaurant;

            if (!$restaurant) {
                return [];
            }

            $startDate = $this->startDate ? \Carbon\Carbon::parse($this->startDate)->startOfDay() : now()->subDays(30)->startOfDay();
            $endDate = $this->endDate ? \Carbon\Carbon::parse($this->endDate)->endOfDay() : now()->endOfDay();

            $data = match ($this->reportType) {
                'sales' => $this->getSalesReport($restaurant->id, $startDate, $endDate),
                'dishes' => $this->getDishesReport($restaurant->id, $startDate, $endDate),
                'customers' => $this->getCustomersReport($restaurant->id, $startDate, $endDate),
                'financial' => $this->getFinancialReport($restaurant->id, $startDate, $endDate),
                default => [],
            };

            // Clean all data recursively to ensure valid UTF-8
            return $this->cleanArray($data);
        } catch (\Exception $e) {
            \Log::error('Error in getReportData: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return [];
        }
    }

    protected function getSalesReport(int $restaurantId, $startDate, $endDate): array
    {
        $orders = Order::where('restaurant_id', $restaurantId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->whereNotNull('paid_at')
            ->get();

        $totalRevenue = $orders->sum('total');
        $totalOrders = $orders->count();
        $averageOrder = $totalOrders > 0 ? round($totalRevenue / $totalOrders) : 0;

        // Sales by day
        $salesByDay = Order::where('restaurant_id', $restaurantId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->whereNotNull('paid_at')
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as orders'),
                DB::raw('SUM(total) as revenue')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(function ($item) {
                return [
                    'date' => $this->cleanString($item->date ?? ''),
                    'orders' => (int) ($item->orders ?? 0),
                    'revenue' => (float) ($item->revenue ?? 0),
                ];
            })
            ->values()
            ->toArray();

        // Sales by type
        $salesByType = Order::where('restaurant_id', $restaurantId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->whereNotNull('paid_at')
            ->select('type', DB::raw('COUNT(*) as count'), DB::raw('SUM(total) as revenue'))
            ->groupBy('type')
            ->get()
            ->map(function ($item) {
                $type = is_object($item->type) ? $item->type->value : (string) $item->type;
                return [
                    'type' => $this->cleanString($type),
                    'count' => (int) $item->count,
                    'revenue' => (float) $item->revenue,
                ];
            })
            ->values()
            ->toArray();

        // Sales by status
        $salesByStatus = Order::where('restaurant_id', $restaurantId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->get()
            ->map(function ($item) {
                $status = is_object($item->status) ? $item->status->value : (string) $item->status;
                return [
                    'status' => $this->cleanString($status),
                    'count' => (int) $item->count,
                ];
            })
            ->values()
            ->toArray();

        return [
            'total_revenue' => (float) $totalRevenue,
            'total_orders' => (int) $totalOrders,
            'average_order' => (float) $averageOrder,
            'sales_by_day' => $salesByDay,
            'sales_by_type' => $salesByType,
            'sales_by_status' => $salesByStatus,
            'orders' => $orders->take(50)->map(function ($order) {
                return [
                    'id' => (int) $order->id,
                    'reference' => $this->cleanString($order->reference ?? ''),
                    'total' => (float) $order->total,
                    'status' => $this->cleanString(is_object($order->status) ? $order->status->value : (string) $order->status),
                    'created_at' => $order->created_at ? $this->cleanString($order->created_at->toDateTimeString()) : null,
                ];
            })->values()->toArray(),
        ];
    }

    protected function getDishesReport(int $restaurantId, $startDate, $endDate): array
    {
        $topDishes = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('dishes', 'order_items.dish_id', '=', 'dishes.id')
            ->where('orders.restaurant_id', $restaurantId)
            ->whereBetween('orders.created_at', [$startDate, $endDate])
            ->whereNotNull('orders.paid_at')
            ->select(
                'dishes.id',
                'dishes.name',
                'dishes.price',
                DB::raw('SUM(order_items.quantity) as total_sold'),
                DB::raw('SUM(order_items.total_price) as total_revenue'),
                DB::raw('AVG(order_items.unit_price) as avg_price')
            )
            ->groupBy('dishes.id', 'dishes.name', 'dishes.price')
            ->orderByDesc('total_sold')
            ->get()
            ->map(function ($item) {
                return [
                    'id' => (int) $item->id,
                    'name' => $this->cleanString($item->name ?? ''),
                    'price' => (float) $item->price,
                    'total_sold' => (int) $item->total_sold,
                    'total_revenue' => (float) $item->total_revenue,
                    'avg_price' => (float) $item->avg_price,
                ];
            })
            ->values()
            ->toArray();

        // Dishes by category
        $dishesByCategory = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('dishes', 'order_items.dish_id', '=', 'dishes.id')
            ->join('categories', 'dishes.category_id', '=', 'categories.id')
            ->where('orders.restaurant_id', $restaurantId)
            ->whereBetween('orders.created_at', [$startDate, $endDate])
            ->whereNotNull('orders.paid_at')
            ->select(
                'categories.id',
                'categories.name',
                DB::raw('SUM(order_items.quantity) as total_sold'),
                DB::raw('SUM(order_items.total_price) as total_revenue')
            )
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc('total_revenue')
            ->get()
            ->map(function ($item) {
                return [
                    'id' => (int) $item->id,
                    'name' => $this->cleanString($item->name ?? ''),
                    'total_sold' => (int) $item->total_sold,
                    'total_revenue' => (float) $item->total_revenue,
                ];
            })
            ->values()
            ->toArray();

        return [
            'top_dishes' => $topDishes,
            'dishes_by_category' => $dishesByCategory,
        ];
    }

    protected function getCustomersReport(int $restaurantId, $startDate, $endDate): array
    {
        $topCustomers = Order::where('restaurant_id', $restaurantId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->whereNotNull('paid_at')
            ->select(
                'customer_email',
                'customer_name',
                'customer_phone',
                DB::raw('COUNT(*) as orders_count'),
                DB::raw('SUM(total) as total_spent'),
                DB::raw('AVG(total) as avg_order_value')
            )
            ->groupBy('customer_email', 'customer_name', 'customer_phone')
            ->orderByDesc('total_spent')
            ->limit(50)
            ->get()
            ->map(function ($item) {
                return [
                    'customer_email' => $this->cleanString($item->customer_email ?? ''),
                    'customer_name' => $this->cleanString($item->customer_name ?? ''),
                    'customer_phone' => $this->cleanString($item->customer_phone ?? ''),
                    'orders_count' => (int) $item->orders_count,
                    'total_spent' => (float) $item->total_spent,
                    'avg_order_value' => (float) $item->avg_order_value,
                ];
            })
            ->values()
            ->toArray();

        // Customer acquisition by day
        $newCustomers = Order::where('restaurant_id', $restaurantId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->whereNotNull('paid_at')
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(DISTINCT customer_email) as new_customers')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(function ($item) {
                return [
                    'date' => $item->date,
                    'new_customers' => (int) $item->new_customers,
                ];
            })
            ->values()
            ->toArray();

        // Total unique customers
        $totalCustomers = Order::where('restaurant_id', $restaurantId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->whereNotNull('paid_at')
            ->distinct('customer_email')
            ->count('customer_email');

        return [
            'top_customers' => $topCustomers,
            'new_customers' => $newCustomers,
            'total_customers' => (int) $totalCustomers,
        ];
    }

    protected function getFinancialReport(int $restaurantId, $startDate, $endDate): array
    {
        $orders = Order::where('restaurant_id', $restaurantId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->whereNotNull('paid_at')
            ->get();

        $totalRevenue = $orders->sum('total');
        $totalSubtotal = $orders->sum('subtotal');
        $totalDeliveryFees = $orders->sum('delivery_fee');
        $totalDiscounts = $orders->sum('discount_amount');

        // Revenue by payment method
        $revenueByPayment = Order::where('restaurant_id', $restaurantId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->whereNotNull('paid_at')
            ->select(
                DB::raw("COALESCE(JSON_EXTRACT(payment_metadata, '$.method'), 'on_site') as payment_method"),
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(total) as revenue')
            )
            ->groupBy('payment_method')
            ->get()
            ->map(function ($item) {
                $method = $item->payment_method ?? 'on_site';
                // Remove quotes from JSON_EXTRACT result
                $method = trim($method, '"\'');
                return [
                    'payment_method' => $this->cleanString($method),
                    'count' => (int) $item->count,
                    'revenue' => (float) $item->revenue,
                ];
            })
            ->values()
            ->toArray();

        // Daily revenue trend
        $dailyRevenue = Order::where('restaurant_id', $restaurantId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->whereNotNull('paid_at')
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(total) as revenue'),
                DB::raw('SUM(subtotal) as subtotal'),
                DB::raw('SUM(delivery_fee) as delivery_fees'),
                DB::raw('SUM(discount_amount) as discounts')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(function ($item) {
                return [
                    'date' => $item->date,
                    'revenue' => (float) $item->revenue,
                    'subtotal' => (float) $item->subtotal,
                    'delivery_fees' => (float) $item->delivery_fees,
                    'discounts' => (float) $item->discounts,
                ];
            })
            ->values()
            ->toArray();

        return [
            'total_revenue' => (float) $totalRevenue,
            'total_subtotal' => (float) $totalSubtotal,
            'total_delivery_fees' => (float) $totalDeliveryFees,
            'total_discounts' => (float) $totalDiscounts,
            'revenue_by_payment' => $revenueByPayment,
            'daily_revenue' => $dailyRevenue,
        ];
    }

    /**
     * Clean string to ensure valid UTF-8 encoding
     */
    protected function cleanString(?string $value): string
    {
        if ($value === null) {
            return '';
        }

        try {
            // Convert to string if not already
            $value = (string) $value;

            // If empty, return early
            if ($value === '') {
                return '';
            }

            // Use iconv with //IGNORE to remove invalid UTF-8 sequences
            if (function_exists('iconv')) {
                $value = @iconv('UTF-8', 'UTF-8//IGNORE', $value);
                if ($value === false) {
                    $value = '';
                }
            }

            // Try to detect and convert encoding if not UTF-8
            if (!mb_check_encoding($value, 'UTF-8')) {
                $detected = mb_detect_encoding($value, ['UTF-8', 'ISO-8859-1', 'Windows-1252', 'ASCII'], true);
                if ($detected && $detected !== 'UTF-8') {
                    $value = @mb_convert_encoding($value, 'UTF-8', $detected);
                }
            }

            // Remove control characters except newlines, tabs, and carriage returns
            $value = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $value);
            
            // Final validation and cleanup
            if (!mb_check_encoding($value, 'UTF-8')) {
                // Last resort: remove all non-printable characters
                $value = preg_replace('/[^\x20-\x7E\xA0-\xFF]/u', '', $value);
                if ($value === null) {
                    $value = '';
                }
            }

            // Ensure it's a valid UTF-8 string
            $value = mb_convert_encoding($value, 'UTF-8', 'UTF-8');
            
            // Final check - if still invalid, return empty string
            if (!mb_check_encoding($value, 'UTF-8')) {
                return '';
            }

            return $value;
        } catch (\Exception $e) {
            \Log::warning('Error cleaning string', [
                'error' => $e->getMessage(),
                'value_length' => strlen($value ?? '')
            ]);
            return '';
        }
    }

    /**
     * Clean array recursively to ensure all strings are valid UTF-8
     */
    protected function cleanArray(array $data): array
    {
        $cleaned = [];
        
        foreach ($data as $key => $value) {
            try {
                $cleanKey = is_string($key) ? $this->cleanString($key) : $key;
                
                if (is_array($value)) {
                    $cleaned[$cleanKey] = $this->cleanArray($value);
                } elseif (is_string($value)) {
                    $cleaned[$cleanKey] = $this->cleanString($value);
                } elseif (is_object($value)) {
                    // Skip objects, they should have been converted to arrays already
                    continue;
                } elseif (is_null($value)) {
                    $cleaned[$cleanKey] = null;
                } elseif (is_bool($value)) {
                    $cleaned[$cleanKey] = $value;
                } elseif (is_numeric($value)) {
                    $cleaned[$cleanKey] = $value;
                } else {
                    // Convert to string and clean for any other type
                    $cleaned[$cleanKey] = $this->cleanString((string) $value);
                }
            } catch (\Exception $e) {
                // Skip problematic values
                \Log::warning('Error cleaning array value', [
                    'key' => $key,
                    'error' => $e->getMessage()
                ]);
                continue;
            }
        }
        
        return $cleaned;
    }

    public function export(string $format)
    {
        $restaurant = auth()->user()->restaurant;
        
        if (!$restaurant) {
            session()->flash('error', 'Restaurant introuvable.');
            return redirect()->back();
        }

        $startDate = $this->startDate ? \Carbon\Carbon::parse($this->startDate)->startOfDay() : now()->subDays(30)->startOfDay();
        $endDate = $this->endDate ? \Carbon\Carbon::parse($this->endDate)->endOfDay() : now()->endOfDay();
        
        $reportData = $this->getReportData();
        $reportType = $this->reportType;
        $period = $this->period;

        if ($format === 'pdf') {
            try {
                // Clean restaurant data before PDF generation
                $restaurantName = $this->cleanString($restaurant->name ?? 'Restaurant');
                $restaurantDescription = $this->cleanString($restaurant->description ?? '');
                
                $pdf = Pdf::loadView('livewire.restaurant.reports-pdf', [
                    'restaurant' => $restaurant,
                    'reportData' => $reportData,
                    'reportType' => $reportType,
                    'startDate' => $startDate,
                    'endDate' => $endDate,
                ]);
                
                $filename = 'rapport-' . $this->cleanString($reportType) . '-' . $startDate->format('Y-m-d') . '-' . $endDate->format('Y-m-d') . '.pdf';
                
                return $pdf->download($filename);
            } catch (\Exception $e) {
                \Log::error('PDF export error', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                session()->flash('error', 'Erreur lors de la génération du PDF. Veuillez utiliser l\'export Excel à la place.');
                return redirect()->back();
            }
        }

        if ($format === 'excel') {
            return Excel::download(
                new ReportsExport($restaurant, $reportData, $reportType, $startDate, $endDate),
                'rapport-' . $reportType . '-' . $startDate->format('Y-m-d') . '-' . $endDate->format('Y-m-d') . '.xlsx'
            );
        }

        session()->flash('error', 'Format d\'export non supporté.');
        return redirect()->back();
    }

    public function render()
    {
        try {
            $restaurant = auth()->user()->restaurant;
            $subscription = $restaurant?->activeSubscription;

            // Get report data (not as computed property to avoid serialization issues)
            $reportData = $this->getReportData();

            return view('livewire.restaurant.reports', [
                'reportData' => $reportData,
            ])
                ->layout('components.layouts.admin-restaurant', [
                    'title' => 'Rapports Détaillés',
                    'restaurant' => $restaurant,
                    'subscription' => $subscription,
                ]);
        } catch (\Exception $e) {
            \Log::error('Error in Reports render: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            return view('livewire.restaurant.reports', [
                'reportData' => [],
            ])
                ->layout('components.layouts.admin-restaurant', [
                    'title' => 'Rapports Détaillés',
                    'restaurant' => auth()->user()->restaurant,
                    'subscription' => null,
                ]);
        }
    }
}

