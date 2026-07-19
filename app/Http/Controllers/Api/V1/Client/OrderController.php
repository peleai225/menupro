<?php

namespace App\Http\Controllers\Api\V1\Client;

use App\Enums\DeliveryStatus;
use App\Enums\OrderStatus;
use App\Enums\OrderType;
use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Delivery;
use App\Models\Dish;
use App\Models\Order;
use App\Models\Restaurant;
use App\Services\DeliveryPricingService;
use App\Services\DriverAssignmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    public function __construct(
        private DeliveryPricingService $pricing,
        private DriverAssignmentService $driverAssignment,
    ) {}

    /**
     * Créer une commande depuis la plateforme.
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'restaurant_id'               => 'required|integer|exists:restaurants,id',
            'items'                       => 'required|array|min:1',
            'items.*.dish_id'             => 'required|integer|exists:dishes,id',
            'items.*.quantity'            => 'required|integer|min:1|max:20',
            'items.*.notes'               => 'nullable|string|max:200',
            'items.*.selected_options'    => 'nullable|array',
            'items.*.selected_options.*'  => 'integer|exists:dish_options,id',
            'delivery_lat'                => 'required|numeric|between:-90,90',
            'delivery_lng'                => 'required|numeric|between:-180,180',
            'delivery_address'            => 'required|string|max:300',
            'delivery_city'               => 'required|string|max:100',
            'delivery_instructions'       => 'nullable|string|max:300',
            'customer_notes'              => 'nullable|string|max:300',
            'payment_method'              => 'required|in:wave,orange_money,mtn_money,cash_on_delivery',
        ]);

        $user     = $request->user()->load('customer.user');
        $customer = $user->customer;

        if (!$customer) {
            return response()->json(['message' => 'Profil client introuvable.'], 403);
        }

        $restaurant = Restaurant::where('is_on_platform', true)
            ->where('status', 'active')
            ->findOrFail($data['restaurant_id']);

        if ($data['payment_method'] === 'cash_on_delivery' && !$restaurant->cash_on_delivery) {
            return response()->json([
                'message' => 'Ce restaurant n\'accepte pas le paiement à la livraison.',
            ], 422);
        }

        // Vérifier que le restaurant livre à cette adresse
        $pricing = $this->pricing->calculate(
            $restaurant,
            (float) $data['delivery_lat'],
            (float) $data['delivery_lng']
        );

        if (!$pricing['within_range']) {
            return response()->json([
                'message' => 'Ce restaurant ne livre pas à votre adresse.',
            ], 422);
        }

        // Calculer les montants
        $items     = $this->resolveItems($data['items'], $restaurant->id);
        $subtotal  = $items->sum('total_price');
        $deliveryFee = $pricing['fee'];

        if ($restaurant->min_order_amount && $subtotal < $restaurant->min_order_amount) {
            return response()->json([
                'message' => 'Commande minimum : ' . number_format($restaurant->min_order_amount / 100, 0) . ' FCFA',
            ], 422);
        }

        $commissionRate = $restaurant->platform_commission_rate ?? 12.00;
        $commission = (int) round($subtotal * $commissionRate / 100);
        $total = $subtotal + $deliveryFee;

        $isCashOnDelivery = $data['payment_method'] === 'cash_on_delivery';

        $order = DB::transaction(function () use (
            $data, $user, $customer, $restaurant, $items,
            $subtotal, $deliveryFee, $commission, $total, $pricing, $isCashOnDelivery
        ) {
            $order = Order::create([
                'restaurant_id'       => $restaurant->id,
                'customer_id'         => $customer->id,
                'reference'           => 'PLT-' . strtoupper(Str::random(8)),
                'tracking_token'      => Str::random(32),
                'customer_name'       => $user->name,
                'customer_email'      => $user->email,
                'customer_phone'      => $customer->phone,
                'type'                => OrderType::DELIVERY->value,
                'source'              => 'platform_web',
                'status'              => $isCashOnDelivery
                    ? OrderStatus::CONFIRMED->value
                    : OrderStatus::PENDING_PAYMENT->value,
                'subtotal'            => $subtotal,
                'delivery_fee'        => $deliveryFee,
                'platform_commission' => $commission,
                'total'               => $total,
                'delivery_address'    => $data['delivery_address'],
                'delivery_city'       => $data['delivery_city'],
                'delivery_latitude'   => $data['delivery_lat'],
                'delivery_longitude'  => $data['delivery_lng'],
                'delivery_instructions' => $data['delivery_instructions'] ?? null,
                'customer_notes'      => $data['customer_notes'] ?? null,
                'payment_method'      => $data['payment_method'],
                'payment_status'      => PaymentStatus::PENDING->value,
                'estimated_prep_time' => $pricing['estimated_minutes'],
            ]);

            foreach ($items as $item) {
                $order->items()->create([
                    'dish_id'          => $item['dish_id'],
                    'dish_name'        => $item['dish_name'],
                    'unit_price'       => $item['unit_price'],
                    'quantity'         => $item['quantity'],
                    'total_price'      => $item['total_price'],
                    'options_price'    => $item['options_price'] ?? 0,
                    'selected_options' => $item['selected_options'] ?? null,
                    'special_instructions' => $item['notes'] ?? null,
                ]);
            }

            // Créer le dossier livraison
            Delivery::create([
                'order_id'          => $order->id,
                'restaurant_id'     => $restaurant->id,
                'status'            => DeliveryStatus::PENDING->value,
                'delivery_address'  => $data['delivery_address'],
                'delivery_phone'    => $customer->phone,
                'delivery_instructions' => $data['delivery_instructions'] ?? null,
                'pickup_latitude'   => $restaurant->latitude,
                'pickup_longitude'  => $restaurant->longitude,
                'delivery_latitude' => $data['delivery_lat'],
                'delivery_longitude' => $data['delivery_lng'],
                'estimated_minutes' => $pricing['estimated_minutes'],
            ]);

            $customer->increment('total_orders');
            $customer->update(['last_order_at' => now()]);

            return $order;
        });

        $response = [
            'order'          => $this->formatOrder($order),
            'tracking_token' => $order->tracking_token,
        ];

        if ($isCashOnDelivery) {
            $response['next_step'] = 'track';
        } else {
            $response['next_step']    = 'payment';
            $response['payment_url']  = route('payment.initiate', $order->id);
        }

        return response()->json($response, 201);
    }

    /**
     * Suivi temps réel d'une commande (par tracking_token — pas besoin d'être connecté).
     */
    public function track(string $token): JsonResponse
    {
        $order = Order::where('tracking_token', $token)
            ->with(['delivery.driver', 'items', 'restaurant'])
            ->firstOrFail();

        $delivery = $order->delivery;
        $driver   = $delivery?->driver;
        $status   = OrderStatus::from($order->status);
        $isFinal  = $status->isFinal();

        $progress = [
            ['key' => 'ordered',   'label' => 'Commande reçue',      'time' => $order->created_at],
            ['key' => 'confirmed', 'label' => 'Confirmée',            'time' => $order->confirmed_at],
            ['key' => 'preparing', 'label' => 'En préparation',       'time' => $order->preparing_at],
            ['key' => 'ready',     'label' => 'Prête',                'time' => $order->ready_at],
            ['key' => 'delivering','label' => 'En livraison',          'time' => $order->picked_up_at],
            ['key' => 'completed', 'label' => 'Livrée',               'time' => $order->completed_at],
        ];

        $statusOrder = ['draft','pending_payment','paid','confirmed','preparing','ready','delivering','completed'];
        $currentIdx  = array_search($order->status, $statusOrder);

        $progress = array_map(function ($step, $idx) use ($currentIdx) {
            $stepOrder = ['ordered' => 0, 'confirmed' => 3, 'preparing' => 4, 'ready' => 5, 'delivering' => 6, 'completed' => 7];
            $stepIdx   = $stepOrder[$step['key']] ?? 0;
            return array_merge($step, [
                'completed' => $currentIdx !== false && $currentIdx > $stepIdx,
                'current'   => $currentIdx !== false && $currentIdx === $stepIdx,
            ]);
        }, $progress, array_keys($progress));

        return response()->json([
            'order_status'       => $order->status,
            'order_status_label' => $status->label(),
            'estimated_minutes'  => $order->estimated_prep_time,
            'is_final'           => $isFinal,
            'payment_status'     => $order->payment_status,
            'order' => [
                'id'             => $order->id,
                'reference'      => $order->reference,
                'tracking_token' => $order->tracking_token,
                'status'         => $order->status,
                'status_label'   => $status->label(),
                'payment_status' => $order->payment_status,
                'payment_method' => $order->payment_method,
                'subtotal'       => $order->subtotal,
                'delivery_fee'   => $order->delivery_fee,
                'total'          => $order->total,
                'estimated_minutes' => $order->estimated_prep_time,
                'items' => $order->items->map(fn($i) => [
                    'name'       => $i->dish_name,
                    'quantity'   => $i->quantity,
                    'unit_price' => $i->unit_price,
                    'total'      => $i->total_price,
                ]),
                'restaurant' => $order->restaurant ? [
                    'id'       => $order->restaurant->id,
                    'name'     => $order->restaurant->name,
                    'logo_url' => $order->restaurant->logo_path
                        ? asset('storage/' . $order->restaurant->logo_path)
                        : null,
                ] : null,
                'created_at' => $order->created_at,
            ],
            'delivery' => $delivery ? [
                'status'       => $delivery->status,
                'status_label' => DeliveryStatus::from($delivery->status)->label(),
                'driver'       => $driver ? [
                    'name'      => $driver->name,
                    'phone'     => $driver->phone,
                    'latitude'  => $driver->latitude,
                    'longitude' => $driver->longitude,
                    'vehicle'   => $driver->vehicle_type,
                    'rating'    => $driver->rating,
                ] : null,
            ] : null,
            'timeline' => [
                'ordered_at'         => $order->created_at,
                'confirmed_at'       => $order->confirmed_at,
                'preparing_at'       => $order->preparing_at,
                'ready_at'           => $order->ready_at,
                'driver_assigned_at' => $order->driver_assigned_at,
                'picked_up_at'       => $order->picked_up_at,
                'completed_at'       => $order->completed_at,
            ],
            'progress' => $progress,
        ]);
    }

    /**
     * Historique des commandes du client connecté.
     */
    public function history(Request $request): JsonResponse
    {
        $customer = $request->user()->customer;

        $orders = Order::where('customer_id', $customer->id)
            ->with(['items.dish', 'delivery'])
            ->latest()
            ->paginate(15);

        return response()->json([
            'data' => $orders->map(fn($o) => $this->formatOrder($o)),
            'meta' => [
                'current_page' => $orders->currentPage(),
                'last_page'    => $orders->lastPage(),
                'total'        => $orders->total(),
            ],
        ]);
    }

    /**
     * Annuler une commande (avant confirmation restaurant).
     */
    public function cancel(Request $request, int $id): JsonResponse
    {
        $customer = $request->user()->customer;
        $order    = Order::where('customer_id', $customer->id)->findOrFail($id);

        if (!$order->status()->canBeCancelled()) {
            return response()->json([
                'message' => 'Cette commande ne peut plus être annulée.',
            ], 422);
        }

        $order->cancel('Annulation client');

        return response()->json(['message' => 'Commande annulée.']);
    }

    // -------------------------------------------------------------------------

    private function resolveItems(array $rawItems, int $restaurantId)
    {
        return collect($rawItems)->map(function ($item) use ($restaurantId) {
            $dish = Dish::where('id', $item['dish_id'])
                ->whereIn('category_id', function ($q) use ($restaurantId) {
                    $q->select('id')->from('categories')->where('restaurant_id', $restaurantId);
                })
                ->where('is_active', true)
                ->firstOrFail();

            $optionsPrice   = 0;
            $selectedOptions = [];
            if (!empty($item['selected_options'])) {
                $options = \App\Models\DishOption::whereIn('id', $item['selected_options'])
                    ->where('is_active', true)
                    ->get();
                foreach ($options as $opt) {
                    $optionsPrice += $opt->price_adjustment;
                    $selectedOptions[] = [
                        'id'               => $opt->id,
                        'name'             => $opt->name,
                        'price_adjustment' => $opt->price_adjustment,
                    ];
                }
            }

            $unitPrice = $dish->price + $optionsPrice;

            return [
                'dish_id'         => $dish->id,
                'dish_name'       => $dish->name,
                'unit_price'      => $unitPrice,
                'quantity'        => $item['quantity'],
                'total_price'     => $unitPrice * $item['quantity'],
                'options_price'   => $optionsPrice,
                'selected_options' => $selectedOptions ?: null,
                'notes'           => $item['notes'] ?? null,
            ];
        });
    }

    private function formatOrder(Order $order): array
    {
        $status        = $order->status instanceof OrderStatus  ? $order->status  : OrderStatus::from($order->status);
        $paymentStatus = $order->payment_status instanceof PaymentStatus ? $order->payment_status : PaymentStatus::from($order->payment_status);

        return [
            'id'             => $order->id,
            'reference'      => $order->reference,
            'tracking_token' => $order->tracking_token,
            'status'         => $status->value,
            'status_label'   => $status->label(),
            'payment_status' => $paymentStatus->value,
            'payment_method' => $order->payment_method,
            'subtotal'       => $order->subtotal,
            'delivery_fee'   => $order->delivery_fee,
            'total'          => $order->total,
            'estimated_minutes' => $order->estimated_prep_time,
            'items'          => $order->items?->map(fn($i) => [
                'name'       => $i->dish_name,
                'quantity'   => $i->quantity,
                'unit_price' => $i->unit_price,
                'total'      => $i->total_price,
            ]),
            'created_at' => $order->created_at,
        ];
    }
}
