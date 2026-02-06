<?php

namespace App\Livewire\Public;

use App\Enums\OrderStatus;
use App\Enums\OrderType;
use App\Models\Order;
use App\Models\PromoCode;
use App\Models\Restaurant;
use App\Notifications\NewOrderNotification;
use App\Services\LygosGateway;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Rule;
use Livewire\Component;

class Checkout extends Component
{
    public Restaurant $restaurant;
    public array $cart = [];

    // Customer Info
    #[Rule('required|string|max:100')]
    public string $customer_name = '';

    #[Rule('required|email|max:255')]
    public string $customer_email = '';

    #[Rule('required|string|max:20')]
    public string $customer_phone = '';

    // Order Type
    #[Rule('required|in:dine_in,takeaway,delivery')]
    public string $order_type = 'takeaway';

    // Delivery Info
    public ?string $delivery_address = null;
    public ?string $delivery_city = null;
    public ?string $delivery_instructions = null;
    public ?float $delivery_latitude = null;
    public ?float $delivery_longitude = null;

    // Dine In
    public ?string $table_number = null;

    // Notes
    public ?string $customer_notes = null;

    // Promo Code
    public ?string $promo_code = null;
    public ?array $appliedPromo = null;
    public ?string $promoError = null;

    // Payment Method
    public ?string $payment_method = null; // 'lygos' or 'cash_on_delivery'

    public function mount(string $slug): void
    {
        $this->restaurant = Restaurant::where('slug', $slug)->firstOrFail();

        // Load cart from session
        $this->cart = session()->get("cart.{$this->restaurant->id}", []);

        // If cart is empty, redirect to menu
        if (empty($this->cart)) {
            $this->redirect(route('r.menu', $slug));
        }
    }

    #[Computed]
    public function subtotal(): int
    {
        return collect($this->cart)->sum('total');
    }

    #[Computed]
    public function deliveryFee(): int
    {
        if ($this->order_type === 'delivery') {
            return $this->restaurant->delivery_fee ?? 0;
        }
        return 0;
    }

    #[Computed]
    public function discount(): int
    {
        return $this->appliedPromo['amount'] ?? 0;
    }

    #[Computed]
    public function taxAmount(): int
    {
        if (!$this->restaurant->tax_rate || $this->restaurant->tax_rate <= 0) {
            return 0;
        }

        $baseAmount = $this->subtotal + $this->deliveryFee - $this->discount;
        
        if ($this->restaurant->tax_included) {
            // Tax is included, extract it
            return (int) round($baseAmount * ($this->restaurant->tax_rate / (100 + $this->restaurant->tax_rate)));
        } else {
            // Tax is added on top
            return (int) round($baseAmount * ($this->restaurant->tax_rate / 100));
        }
    }

    #[Computed]
    public function serviceFee(): int
    {
        if (!$this->restaurant->service_fee_enabled) {
            return 0;
        }

        $baseAmount = $this->subtotal + $this->deliveryFee - $this->discount;
        $feeAmount = 0;
        
        // Percentage fee
        if ($this->restaurant->service_fee_rate > 0) {
            $feeAmount += (int) round($baseAmount * ($this->restaurant->service_fee_rate / 100));
        }
        
        // Fixed fee
        if ($this->restaurant->service_fee_fixed > 0) {
            $feeAmount += $this->restaurant->service_fee_fixed;
        }
        
        return $feeAmount;
    }

    #[Computed]
    public function total(): int
    {
        $baseTotal = $this->subtotal + $this->deliveryFee - $this->discount;
        
        // Add tax if not included
        if (!$this->restaurant->tax_included) {
            $baseTotal += $this->taxAmount;
        }
        
        // Add service fee
        $baseTotal += $this->serviceFee;
        
        return $baseTotal;
    }

    public function applyPromoCode(): void
    {
        $this->promoError = null;
        $this->appliedPromo = null;

        if (empty($this->promo_code)) {
            return;
        }

        $promoCode = PromoCode::where('restaurant_id', $this->restaurant->id)
            ->where('code', strtoupper($this->promo_code))
            ->valid()
            ->first();

        if (!$promoCode) {
            $this->promoError = 'Code promo invalide ou expiré.';
            return;
        }

        $error = $promoCode->getValidationError($this->subtotal, $this->customer_email);
        
        if ($error) {
            $this->promoError = $error;
            return;
        }

        $discount = $promoCode->calculateDiscount($this->subtotal);

        $this->appliedPromo = [
            'id' => $promoCode->id,
            'code' => $promoCode->code,
            'amount' => $discount,
            'label' => $promoCode->discount_label,
        ];
    }

    public function removePromoCode(): void
    {
        $this->promo_code = null;
        $this->appliedPromo = null;
        $this->promoError = null;
    }

    public function removeCartItem(string $key): void
    {
        unset($this->cart[$key]);
        session()->put("cart.{$this->restaurant->id}", $this->cart);

        if (empty($this->cart)) {
            $this->redirect(route('r.menu', $this->restaurant->slug));
        }
    }

    public function placeOrder(): void
    {
        // Validate based on order type
        $rules = [
            'customer_name' => 'required|string|max:100',
            'customer_email' => 'required|email|max:255',
            'customer_phone' => 'required|string|max:20',
            'order_type' => 'required|in:dine_in,takeaway,delivery',
        ];

        if ($this->order_type === 'delivery') {
            $rules['delivery_address'] = 'required|string|max:500';
            $rules['delivery_city'] = 'required|string|max:100';
        }

        if ($this->order_type === 'dine_in') {
            $rules['table_number'] = 'required|string|max:20';
        }

        $this->validate($rules);

        // Check minimum order
        if ($this->subtotal < ($this->restaurant->min_order_amount ?? 0)) {
            session()->flash('error', "Commande minimum de " . number_format($this->restaurant->min_order_amount, 0, ',', ' ') . " F requise.");
            return;
        }

        // Determine payment method if not already set
        $lygos = app(LygosGateway::class)->forRestaurant($this->restaurant);
        $lygosAvailable = $this->restaurant->lygos_enabled && $lygos->isConfigured();
        $cashOnDeliveryAvailable = $this->restaurant->cash_on_delivery ?? false;

        if (!$this->payment_method) {
            if ($lygosAvailable && $cashOnDeliveryAvailable) {
                // Both available - require user to choose
                session()->flash('error', 'Veuillez choisir un mode de paiement.');
                return;
            } elseif ($lygosAvailable) {
                $this->payment_method = 'lygos';
            } elseif ($cashOnDeliveryAvailable) {
                $this->payment_method = 'cash_on_delivery';
            } else {
                // Default to cash on delivery
                $this->payment_method = 'cash_on_delivery';
            }
        } else {
            // Validate payment method if both are available
            if ($lygosAvailable && $cashOnDeliveryAvailable) {
                if (!in_array($this->payment_method, ['lygos', 'cash_on_delivery'])) {
                    session()->flash('error', 'Mode de paiement invalide.');
                    return;
                }
            }
        }

        // Validate delivery radius if delivery type
        if ($this->order_type === 'delivery' && $this->delivery_latitude && $this->delivery_longitude) {
            $restaurantLat = $this->restaurant->latitude ?? 5.3600;
            $restaurantLng = $this->restaurant->longitude ?? -4.0083;
            $deliveryRadius = $this->restaurant->delivery_radius_km ?? 10;
            
            if ($deliveryRadius > 0) {
                $distance = $this->calculateDistance(
                    $restaurantLat, 
                    $restaurantLng, 
                    $this->delivery_latitude, 
                    $this->delivery_longitude
                );
                
                if ($distance > $deliveryRadius) {
                    session()->flash('error', "Cette adresse est hors de notre zone de livraison (max: {$deliveryRadius} km). Distance: " . number_format($distance, 2) . " km.");
                    return;
                }
            }
        }

        // Create order
        $order = Order::create([
            'restaurant_id' => $this->restaurant->id,
            'customer_name' => $this->customer_name,
            'customer_email' => $this->customer_email,
            'customer_phone' => $this->customer_phone,
            'type' => OrderType::from($this->order_type),
            'status' => OrderStatus::PENDING_PAYMENT,
            'subtotal' => $this->subtotal,
            'delivery_fee' => $this->deliveryFee,
            'discount_amount' => $this->discount,
            'tax_amount' => $this->taxAmount,
            'service_fee' => $this->serviceFee,
            'total' => $this->total,
            'delivery_address' => $this->delivery_address,
            'delivery_city' => $this->delivery_city,
            'delivery_latitude' => $this->delivery_latitude,
            'delivery_longitude' => $this->delivery_longitude,
            'delivery_instructions' => $this->delivery_instructions,
            'table_number' => $this->table_number,
            'customer_notes' => $this->customer_notes,
            'estimated_prep_time' => $this->restaurant->estimated_prep_time ?? 30,
        ]);

        // Create order items
        foreach ($this->cart as $item) {
            $order->items()->create([
                'dish_id' => $item['dish_id'],
                'dish_name' => $item['name'],
                'unit_price' => $item['unit_price'],
                'quantity' => $item['quantity'],
                'total_price' => $item['total'],
                'selected_options' => $item['options'] ?? [],
                'options_price' => $item['options_price'] ?? 0,
                'special_instructions' => $item['instructions'] ?? null,
            ]);
        }

        // Apply promo code
        if ($this->appliedPromo) {
            $promoCode = PromoCode::find($this->appliedPromo['id']);
            if ($promoCode) {
                $promoCode->applyToOrder($order, $this->customer_email);
            }
        }

        // Clear cart
        session()->forget("cart.{$this->restaurant->id}");

        // Notify restaurant immediately when order is created (before payment redirect)
        // This ensures real-time notification even for online payments
        $this->restaurant->users()
            ->whereIn('role', [\App\Enums\UserRole::RESTAURANT_ADMIN, \App\Enums\UserRole::EMPLOYEE])
            ->each(function ($user) use ($order) {
                $user->notify(new NewOrderNotification($order));
            });

        // Process payment based on selected method
        if ($this->payment_method === 'lygos') {
            // Lygos payment - redirect to payment gateway
            $lygos = app(LygosGateway::class)->forRestaurant($this->restaurant);
            try {
                // Redirect to payment
                $result = $lygos->createPayment(
                    $order,
                    route('r.order.success', [$this->restaurant->slug, $order]),
                    route('r.order.cancel', [$this->restaurant->slug, $order])
                );

                if ($result['success']) {
                    $order->update([
                        'payment_reference' => $result['payment_id'],
                        'payment_metadata' => ['payment_url' => $result['payment_url']],
                    ]);

                    $this->redirect($result['payment_url']);
                    return;
                } else {
                    // Payment creation failed but no exception
                    $errorMessage = $result['message'] ?? 'Impossible de créer la session de paiement.';
                    session()->flash('error', 'Erreur de paiement : ' . $errorMessage . ' Veuillez réessayer.');
                    \Log::error('Lygos payment creation failed', [
                        'order_id' => $order->id,
                        'result' => $result,
                    ]);
                    // Keep order in PENDING_PAYMENT status - user can retry
                    $this->redirect(route('r.order.status', [$this->restaurant->slug, $order->tracking_token]));
                    return;
                }
            } catch (\Exception $e) {
                // If payment creation fails with exception
                session()->flash('error', 'Erreur lors de la création du paiement : ' . $e->getMessage() . ' Veuillez réessayer.');
                \Log::error('Lygos payment creation exception', [
                    'order_id' => $order->id,
                    'error' => $e->getMessage(),
                ]);
                // Keep order in PENDING_PAYMENT status - user can retry
                $this->redirect(route('r.order.status', [$this->restaurant->slug, $order->tracking_token]));
                return;
            }
        } elseif ($this->payment_method === 'cash_on_delivery') {
            // Cash on delivery - mark as paid (will be collected on delivery)
            $order->markAsPaid([
                'method' => 'cash_on_delivery',
                'metadata' => ['note' => 'Paiement à la livraison'],
            ]);
        } else {
            // Fallback - should not happen
            $order->markAsPaid([
                'method' => 'cash_on_delivery',
                'metadata' => ['note' => 'Paiement à la livraison'],
            ]);
        }

        // Redirect to order status
        $this->redirect(route('r.order.status', [$this->restaurant->slug, $order->tracking_token]));
    }

    /**
     * Calculate distance between two coordinates (Haversine formula)
     */
    private function calculateDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371; // Radius of the earth in km

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat/2) * sin($dLat/2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon/2) * sin($dLon/2);

        $c = 2 * atan2(sqrt($a), sqrt(1-$a));
        $distance = $earthRadius * $c; // Distance in km

        return $distance;
    }

    public function render()
    {
        return view('livewire.public.checkout')
            ->layout('layouts.restaurant-public', [
                'restaurant' => $this->restaurant,
            ]);
    }
}

