<?php

namespace App\Http\Controllers\Public;

use App\Enums\OrderStatus;
use App\Enums\OrderType;
use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Models\Dish;
use App\Models\Order;
use App\Models\PaymentTransaction;
use App\Models\PromoCode;
use App\Models\Restaurant;
use App\Notifications\NewOrderNotification;
use App\Services\WaveGateway;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function __construct(
        protected WaveGateway $waveGateway,
    ) {}

    public function index(string $slug): View
    {
        $restaurant = Restaurant::where('slug', $slug)->firstOrFail();

        if (!$restaurant->can_accept_orders) {
            return view('pages.restaurant-public.unavailable', [
                'restaurant' => $restaurant,
                'message' => 'Ce restaurant n\'accepte pas de commandes actuellement.',
            ]);
        }

        return view('pages.restaurant-public.checkout', compact('restaurant'));
    }

    public function store(Request $request, string $slug): RedirectResponse
    {
        $restaurant = Restaurant::where('slug', $slug)->firstOrFail();

        if (!$restaurant->isOpenNow()) {
            $nextOpening = $restaurant->getNextOpeningTime();
            $message = 'Le restaurant est actuellement fermé.';
            if ($nextOpening) {
                $message .= " Réouverture : {$nextOpening}";
            }
            return back()->with('error', $message);
        }

        $request->validate([
            'customer_name' => ['required', 'string', 'max:100'],
            'customer_phone' => ['required', 'string', 'max:20'],
            'type' => ['required', 'in:dine_in,takeaway,delivery'],
            'delivery_address' => ['required_if:type,delivery', 'nullable', 'string', 'max:500'],
            'delivery_city' => ['required_if:type,delivery', 'nullable', 'string', 'max:100'],
            'delivery_instructions' => ['nullable', 'string', 'max:500'],
            'table_number' => ['required_if:type,dine_in', 'nullable', 'string', 'max:20'],
            'customer_notes' => ['nullable', 'string', 'max:500'],
            'scheduled_at' => ['nullable', 'date', 'after:now'],
            'promo_code' => ['nullable', 'string', 'max:50'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.dish_id' => [
                'required',
                Rule::exists('dishes', 'id')->where('restaurant_id', $restaurant->id),
            ],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:99'],
            'items.*.options' => ['nullable', 'array'],
            'items.*.special_instructions' => ['nullable', 'string', 'max:200'],
        ]);

        $subtotal = 0;
        $orderItems = [];

        $dishIds = collect($request->items)->pluck('dish_id')->unique()->toArray();
        $dishes = Dish::whereIn('id', $dishIds)
            ->where('restaurant_id', $restaurant->id)
            ->with('optionGroups.options')
            ->get()
            ->keyBy('id');

        foreach ($request->items as $item) {
            $dish = $dishes->get($item['dish_id']);

            if (!$dish) {
                return back()->with('error', 'Plat invalide.');
            }

            if (!$dish->is_available) {
                return back()->with('error', "Le plat \"{$dish->name}\" n'est plus disponible.");
            }

            $optionsPrice = 0;
            $selectedOptions = [];

            if (!empty($item['options'])) {
                $allOptions = $dish->optionGroups->flatMap(fn ($g) => $g->options);
                foreach ($item['options'] as $optionId) {
                    $option = $allOptions->firstWhere('id', $optionId);
                    if ($option) {
                        $optionsPrice += $option->price_adjustment;
                        $selectedOptions[] = [
                            'id' => $option->id,
                            'name' => $option->name,
                            'price_adjustment' => $option->price_adjustment,
                        ];
                    }
                }
            }

            $unitPrice = $dish->price + $optionsPrice;
            $totalPrice = $unitPrice * $item['quantity'];
            $subtotal += $totalPrice;

            $orderItems[] = [
                'dish_id' => $dish->id,
                'dish_name' => $dish->name,
                'unit_price' => $unitPrice,
                'quantity' => $item['quantity'],
                'total_price' => $totalPrice,
                'selected_options' => $selectedOptions,
                'options_price' => $optionsPrice,
                'special_instructions' => $item['special_instructions'] ?? null,
            ];
        }

        $deliveryFee = 0;
        $orderType = OrderType::from($request->type);

        if ($orderType === OrderType::DELIVERY) {
            $deliveryFee = $restaurant->delivery_fee;
        }

        $discountAmount = 0;
        $promoCode = null;

        if ($request->filled('promo_code')) {
            $promoCode = PromoCode::where('restaurant_id', $restaurant->id)
                ->where('code', strtoupper($request->promo_code))
                ->valid()
                ->first();

            if ($promoCode) {
                $error = $promoCode->getValidationError($subtotal, $request->customer_email);

                if ($error) {
                    return back()->with('error', $error);
                }

                $discountAmount = $promoCode->calculateDiscount($subtotal);
            } else {
                return back()->with('error', 'Code promo invalide.');
            }
        }

        if ($subtotal < $restaurant->min_order_amount) {
            $min = number_format($restaurant->min_order_amount, 0, ',', ' ');
            return back()->with('error', "Commande minimum de {$min} FCFA requise.");
        }

        $taxAmount = 0;
        if ($restaurant->tax_rate && $restaurant->tax_rate > 0) {
            $baseAmount = $subtotal + $deliveryFee - $discountAmount;
            if ($restaurant->tax_included) {
                $taxAmount = (int) round($baseAmount * ($restaurant->tax_rate / (100 + $restaurant->tax_rate)));
            } else {
                $taxAmount = (int) round($baseAmount * ($restaurant->tax_rate / 100));
            }
        }

        $serviceFee = 0;
        if ($restaurant->service_fee_enabled) {
            $baseAmount = $subtotal + $deliveryFee - $discountAmount;
            if ($restaurant->service_fee_rate > 0) {
                $serviceFee += (int) round($baseAmount * ($restaurant->service_fee_rate / 100));
            }
            if ($restaurant->service_fee_fixed > 0) {
                $serviceFee += $restaurant->service_fee_fixed;
            }
        }

        $baseTotal = $subtotal + $deliveryFee - $discountAmount;
        if (!$restaurant->tax_included) {
            $baseTotal += $taxAmount;
        }
        $baseTotal += $serviceFee;
        $total = $baseTotal;

        try {
            $order = DB::transaction(function () use (
                $restaurant, $request, $orderType, $subtotal, $deliveryFee,
                $discountAmount, $taxAmount, $serviceFee, $total, $orderItems, $promoCode
            ) {
                $order = Order::create([
                    'restaurant_id' => $restaurant->id,
                    'customer_name' => $request->customer_name,
                    'customer_email' => $request->customer_email,
                    'customer_phone' => $request->customer_phone,
                    'type' => $orderType,
                    'status' => OrderStatus::PENDING_PAYMENT,
                    'subtotal' => $subtotal,
                    'delivery_fee' => $deliveryFee,
                    'discount_amount' => $discountAmount,
                    'tax_amount' => $taxAmount,
                    'service_fee' => $serviceFee,
                    'total' => $total,
                    'delivery_address' => $request->delivery_address,
                    'delivery_city' => $request->delivery_city,
                    'delivery_latitude' => $request->delivery_latitude,
                    'delivery_longitude' => $request->delivery_longitude,
                    'delivery_instructions' => $request->delivery_instructions,
                    'table_number' => $request->table_number,
                    'customer_notes' => $request->customer_notes,
                    'scheduled_at' => $request->scheduled_at,
                    'estimated_prep_time' => $restaurant->estimated_prep_time,
                ]);

                foreach ($orderItems as $item) {
                    $order->items()->create($item);
                }

                if ($promoCode && $discountAmount > 0) {
                    $promoCode->applyToOrder($order, $request->customer_email);
                }

                return $order;
            });
        } catch (\App\Exceptions\QuotaExceededException $e) {
            return back()->withErrors(['order' => 'Ce restaurant a atteint sa limite de commandes pour ce mois. Veuillez réessayer plus tard.']);
        }

        try {
            $whatsapp = app(\App\Services\WhatsAppService::class);
            $whatsapp->sendNewOrderToRestaurant($order);
        } catch (\Throwable $e) {
            \Log::warning('WhatsApp new order notification failed: ' . $e->getMessage());
        }

        // Priority 1: Wave Checkout (restaurant direct ou plateforme)
        $wave = $this->resolveWaveGateway($restaurant);
        if ($wave->isConfigured()) {
            try {
                $successUrl = route('r.order.success', [$slug, $order]);
                $errorUrl = route('r.order.cancel', [$slug, $order]);

                $result = $wave->createCheckoutSession($order, $successUrl, $errorUrl);

                if ($result['success']) {
                    $order->update([
                        'payment_reference' => $result['checkout_id'],
                        'payment_method' => 'wave',
                        'payment_metadata' => [
                            'wave_checkout_id' => $result['checkout_id'],
                            'wave_launch_url' => $result['wave_launch_url'],
                            'wave_mode' => $wave->isRestaurantMode() ? 'restaurant_direct' : 'platform',
                        ],
                    ]);

                    return redirect($result['wave_launch_url']);
                }
            } catch (\Exception $e) {
                \Log::error('Wave checkout exception', ['order_id' => $order->id, 'error' => $e->getMessage()]);
            }
        }

        return redirect()->route('r.order.status', [$slug, $order->tracking_token])
            ->with('info', 'Commande créée. Le paiement sera effectué sur place.');
    }

    public function success(string $slug, Order $order): RedirectResponse
    {
        $restaurant = Restaurant::where('slug', $slug)->firstOrFail();

        if ($order->is_paid) {
            return redirect()->route('r.order.status', [$slug, $order->tracking_token])
                ->with('success', 'Paiement confirmé ! Votre commande est en cours de préparation.');
        }

        // Wave: verify checkout session status
        if ($order->payment_method === 'wave' && $order->payment_reference) {
            try {
                $wave = app(WaveGateway::class);
                $result = $wave->getCheckoutSession($order->payment_reference);
                if ($result['success'] && ($result['data']['payment_status'] ?? '') === 'succeeded') {
                    $order->markAsPaid([
                        'reference' => $result['data']['transaction_id'] ?? $order->payment_reference,
                        'method' => 'wave',
                        'transaction_id' => $result['data']['transaction_id'] ?? null,
                        'metadata' => $result['data'],
                    ]);

                    $payment = PaymentTransaction::create([
                        'order_id' => $order->id,
                        'restaurant_id' => $order->restaurant_id,
                        'gateway' => 'wave',
                        'gateway_transaction_id' => $result['data']['transaction_id'] ?? null,
                        'wave_checkout_id' => $order->payment_reference,
                        'wave_payment_id' => $result['data']['transaction_id'] ?? null,
                        'amount' => $order->total,
                        'currency' => 'XOF',
                        'status' => 'completed',
                        'client_reference' => $result['data']['client_reference'] ?? null,
                        'metadata' => $result['data'],
                    ]);

                    app(\App\Services\WalletService::class)->creditWallet($order->restaurant_id, $payment->id);

                    $restaurant->users()
                        ->whereIn('role', [\App\Enums\UserRole::RESTAURANT_ADMIN, \App\Enums\UserRole::EMPLOYEE])
                        ->each(fn ($user) => $user->notify(new NewOrderNotification($order)));
                }
            } catch (\Exception $e) {
                \Log::warning('Wave verify on success fallback failed: ' . $e->getMessage());
            }
        }

        return redirect()->route('r.order.status', [$slug, $order->tracking_token])
            ->with('success', 'Paiement confirmé ! Votre commande est en cours de préparation.');
    }

    public function cancel(string $slug, Order $order): RedirectResponse
    {
        return redirect()->route('r.order.status', [$slug, $order->tracking_token])
            ->with('warning', 'Paiement annulé. Vous pouvez réessayer.');
    }

    public function applyPromo(Request $request, string $slug)
    {
        $restaurant = Restaurant::where('slug', $slug)->firstOrFail();

        $request->validate([
            'code' => ['required', 'string', 'max:50'],
            'subtotal' => ['required', 'integer', 'min:0'],
            'email' => ['nullable', 'email'],
        ]);

        $promoCode = PromoCode::where('restaurant_id', $restaurant->id)
            ->where('code', strtoupper($request->code))
            ->valid()
            ->first();

        if (!$promoCode) {
            return response()->json([
                'success' => false,
                'message' => 'Code promo invalide ou expiré.',
            ]);
        }

        $error = $promoCode->getValidationError($request->subtotal, $request->email);

        if ($error) {
            return response()->json([
                'success' => false,
                'message' => $error,
            ]);
        }

        $discount = $promoCode->calculateDiscount($request->subtotal);

        return response()->json([
            'success' => true,
            'discount' => $discount,
            'discount_label' => $promoCode->discount_label,
            'message' => "Code promo appliqué : -{$promoCode->discount_label}",
        ]);
    }

    /**
     * Résout le gateway Wave à utiliser :
     * - Si le restaurant a son propre Wave Business → paiement direct au restaurant
     * - Sinon → paiement sur le wallet plateforme + auto-payout
     */
    protected function resolveWaveGateway(Restaurant $restaurant): WaveGateway
    {
        if ($restaurant->hasWaveBusiness()) {
            return app(WaveGateway::class)->forRestaurant($restaurant);
        }

        return app(WaveGateway::class)->forPlatform();
    }
}
