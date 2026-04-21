<?php

namespace App\Http\Controllers\Public;

use App\Enums\OrderStatus;
use App\Enums\OrderType;
use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Models\Dish;
use App\Models\Order;
use App\Models\PromoCode;
use App\Models\Restaurant;
use App\Notifications\NewOrderNotification;
use App\Services\FusionPayGateway;
use App\Services\LygosGateway;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function __construct(
        protected LygosGateway $lygosGateway
    ) {}

    /**
     * Display checkout page.
     */
    public function index(string $slug): View
    {
        $restaurant = Restaurant::where('slug', $slug)->firstOrFail();

        // Check if restaurant can accept orders
        if (!$restaurant->can_accept_orders) {
            return view('pages.restaurant-public.unavailable', [
                'restaurant' => $restaurant,
                'message' => 'Ce restaurant n\'accepte pas de commandes actuellement.',
            ]);
        }

        return view('pages.restaurant-public.checkout', compact('restaurant'));
    }

    /**
     * Process checkout and create order.
     */
    public function store(Request $request, string $slug): RedirectResponse
    {
        $restaurant = Restaurant::where('slug', $slug)->firstOrFail();

        // Check if restaurant is open
        if (!$restaurant->isOpenNow()) {
            $nextOpening = $restaurant->getNextOpeningTime();
            $message = 'Le restaurant est actuellement fermé.';
            if ($nextOpening) {
                $message .= " Réouverture : {$nextOpening}";
            }
            return back()->with('error', $message);
        }

        // Validate request
        $request->validate([
            'customer_name' => ['required', 'string', 'max:100'],
            'customer_email' => ['required', 'email', 'max:255'],
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

        // Calculate order totals
        $subtotal = 0;
        $orderItems = [];

        // Pré-charger tous les plats en une seule requête (évite N+1)
        $dishIds = collect($request->items)->pluck('dish_id')->unique()->toArray();
        $dishes = Dish::whereIn('id', $dishIds)
            ->where('restaurant_id', $restaurant->id)
            ->with('optionGroups.options')
            ->get()
            ->keyBy('id');

        foreach ($request->items as $item) {
            $dish = $dishes->get($item['dish_id']);

            // Sécurité : le plat doit exister (déjà validé via Rule::exists avec restaurant_id)
            if (!$dish) {
                return back()->with('error', 'Plat invalide.');
            }

            // Check availability
            if (!$dish->is_available) {
                return back()->with('error', "Le plat \"{$dish->name}\" n'est plus disponible.");
            }

            // Calculate options price
            $optionsPrice = 0;
            $selectedOptions = [];
            
            if (!empty($item['options'])) {
                // Recherche dans les relations déjà eager-loaded (pas de requête SQL supplémentaire)
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

        // Calculate delivery fee
        $deliveryFee = 0;
        $orderType = OrderType::from($request->type);
        
        if ($orderType === OrderType::DELIVERY) {
            $deliveryFee = $restaurant->delivery_fee;
        }

        // Apply promo code
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

        // Check minimum order
        if ($subtotal < $restaurant->min_order_amount) {
            $min = number_format($restaurant->min_order_amount, 0, ',', ' ');
            return back()->with('error', "Commande minimum de {$min} FCFA requise.");
        }

        // Calculate tax
        $taxAmount = 0;
        if ($restaurant->tax_rate && $restaurant->tax_rate > 0) {
            $baseAmount = $subtotal + $deliveryFee - $discountAmount;
            if ($restaurant->tax_included) {
                $taxAmount = (int) round($baseAmount * ($restaurant->tax_rate / (100 + $restaurant->tax_rate)));
            } else {
                $taxAmount = (int) round($baseAmount * ($restaurant->tax_rate / 100));
            }
        }

        // Calculate service fee
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

        // Calculate total
        $baseTotal = $subtotal + $deliveryFee - $discountAmount;
        if (!$restaurant->tax_included) {
            $baseTotal += $taxAmount;
        }
        $baseTotal += $serviceFee;
        $total = $baseTotal;

        // Create order — transaction atomique : commande + items + promo en une seule opération
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

            // Create order items
            foreach ($orderItems as $item) {
                $order->items()->create($item);
            }

            // Apply promo code
            if ($promoCode && $discountAmount > 0) {
                $promoCode->applyToOrder($order, $request->customer_email);
            }

            return $order;
        });

        // Send WhatsApp notifications
        try {
            $whatsapp = app(\App\Services\WhatsAppService::class);
            $whatsapp->sendNewOrderToRestaurant($order);
        } catch (\Throwable $e) {
            \Log::warning('WhatsApp new order notification failed: ' . $e->getMessage());
        }

        // Redirect to payment or confirmation
        if ($restaurant->lygos_enabled && $restaurant->getLygosApiKey()) {
            $result = $this->lygosGateway
                ->forRestaurant($restaurant)
                ->createPayment(
                    $order,
                    route('r.order.success', [$slug, $order]),
                    route('r.order.cancel', [$slug, $order])
                );

            if ($result['success']) {
                $order->update([
                    'payment_reference' => $result['payment_id'],
                    'payment_metadata' => ['payment_url' => $result['payment_url']],
                ]);

                return redirect($result['payment_url']);
            }

            // Payment creation failed - show error but keep order
            return redirect()->route('r.order.status', [$slug, $order->tracking_token])
                ->with('error', 'Erreur lors de la création du paiement. Veuillez réessayer.');
        }

        // No payment gateway - direct to order status
        return redirect()->route('r.order.status', [$slug, $order->tracking_token])
            ->with('info', 'Commande créée. Le paiement sera effectué sur place.');
    }

    /**
     * Handle successful payment.
     */
    public function success(string $slug, Order $order): RedirectResponse
    {
        $restaurant = Restaurant::where('slug', $slug)->firstOrFail();

        // If webhook already marked as paid, just redirect
        if ($order->is_paid) {
            return redirect()->route('r.order.status', [$slug, $order->tracking_token])
                ->with('success', 'Paiement confirmé ! Votre commande est en cours de préparation.');
        }

        // FusionPay: verify by payment_reference (tokenPay) - fallback si webhook pas encore reçu
        if ($order->payment_method === 'fusionpay' && $order->payment_reference && app(FusionPayGateway::class)->isEnabled()) {
            $paymentTransaction = \App\Models\PaymentTransaction::where('gateway', 'fusionpay')
                ->where('gateway_transaction_id', $order->payment_reference)->first();
            if ($paymentTransaction && $paymentTransaction->order_id === $order->id && $paymentTransaction->status !== 'ACCEPTED') {
                $gateway = app(FusionPayGateway::class);
                $verify = $gateway->verifyPayment($order->payment_reference);
                $status = $verify['data']['statut'] ?? null;
                if ($status === 'paid') {
                    \Illuminate\Support\Facades\DB::transaction(function () use ($order, $paymentTransaction, $verify, $restaurant) {
                        $paymentTransaction->update(['status' => 'ACCEPTED', 'metadata' => array_merge($paymentTransaction->metadata ?? [], ['verify' => $verify['data'] ?? []])]);
                        $order->markAsPaid([
                            'reference' => $order->payment_reference,
                            'method' => 'fusionpay',
                            'metadata' => $verify['data'] ?? [],
                        ]);
                        $phone = preg_replace('/\D/', '', $order->restaurant->phone ?? '');
                        if (str_starts_with($phone, '225')) {
                            $phone = substr($phone, 3);
                        }
                        $fullPhone = '225' . ($phone ?: '0000000000');
                        $wallet = \App\Models\RestaurantWallet::firstOrCreate(
                            ['restaurant_id' => $order->restaurant_id],
                            ['balance' => 0, 'phone' => $fullPhone, 'prefix' => '225']
                        );
                        $wallet->increment('balance', $paymentTransaction->amount);
                        $restaurant->users()
                            ->whereIn('role', [\App\Enums\UserRole::RESTAURANT_ADMIN, \App\Enums\UserRole::EMPLOYEE])
                            ->each(fn ($user) => $user->notify(new NewOrderNotification($order)));
                    });
                }
            }
        }
        // Lygos: verify by order reference
        elseif ($order->reference && $restaurant->lygos_enabled) {
            $result = $this->lygosGateway
                ->forRestaurant($restaurant)
                ->verifyPayment($order->reference);

            if ($result['success'] && ($result['paid'] ?? false)) {
                $order->markAsPaid([
                    'reference' => $order->payment_reference ?? $order->reference,
                    'method' => 'lygos',
                    'metadata' => $result,
                ]);
                $restaurant->users()
                    ->whereIn('role', [\App\Enums\UserRole::RESTAURANT_ADMIN, \App\Enums\UserRole::EMPLOYEE])
                    ->each(fn ($user) => $user->notify(new NewOrderNotification($order)));
            }
        }

        return redirect()->route('r.order.status', [$slug, $order->tracking_token])
            ->with('success', 'Paiement confirmé ! Votre commande est en cours de préparation.');
    }

    /**
     * Handle cancelled payment.
     */
    public function cancel(string $slug, Order $order): RedirectResponse
    {
        // Don't delete the order - customer might retry
        return redirect()->route('r.order.status', [$slug, $order->tracking_token])
            ->with('warning', 'Paiement annulé. Vous pouvez réessayer.');
    }

    /**
     * Apply promo code (AJAX).
     */
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
}

