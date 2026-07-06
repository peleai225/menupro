<?php

namespace App\Livewire\Public;

use App\Enums\OrderStatus;
use App\Enums\OrderType;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\PromoCode;
use App\Models\Restaurant;
use App\Notifications\NewOrderNotification;
use App\Services\DeliveryPricingService;
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

    public string $customer_email = '';

    /** Indicatif pays (ex: +225) */
    public string $customer_phone_country = '+225';

    /** Numéro au format national (ex: 05 01 86 26 40) */
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
    public ?string $payment_method = null; // 'jeko' or 'cash_on_delivery'

    public function mount(string $slug): void
    {
        $this->restaurant = Restaurant::where('slug', $slug)->firstOrFail();

        // Load cart from session
        $this->cart = session()->get("cart.{$this->restaurant->id}", []);

        // If cart is empty, redirect to menu
        if (empty($this->cart)) {
            $this->redirect(route('r.menu', $slug));
        }

        // Auto-fill table number from QR code URL (?table=X)
        $tableFromUrl = request()->query('table');
        if ($tableFromUrl && is_numeric($tableFromUrl)) {
            $this->table_number = (string) $tableFromUrl;
            $this->order_type = 'dine_in';
        }

        // Restore table from session (passed from menu page)
        if (!$this->table_number) {
            $sessionTable = session()->get("table.{$this->restaurant->id}");
            if ($sessionTable) {
                $this->table_number = (string) $sessionTable;
                $this->order_type = 'dine_in';
            }
        }

        // Pré-sélectionner le mode de paiement s'il n'y en a qu'un
        $this->setDefaultPaymentMethod();
    }

    protected function setDefaultPaymentMethod(): void
    {
        if ($this->payment_method) {
            return;
        }
        $methods = array_merge(
            ($this->restaurant->cash_on_delivery ?? false) ? ['cash_on_delivery'] : [],
            $this->jekoPaymentAvailable ? ['jeko'] : []
        );
        if (count($methods) === 1) {
            $this->payment_method = $methods[0];
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
        if ($this->order_type !== 'delivery') {
            return 0;
        }

        if ($this->delivery_latitude && $this->delivery_longitude) {
            $pricing = app(DeliveryPricingService::class);
            $result = $pricing->calculate($this->restaurant, $this->delivery_latitude, $this->delivery_longitude);
            return $result['within_range'] ? $result['fee'] : 0;
        }

        return 0;
    }

    #[Computed]
    public function deliveryZoneName(): ?string
    {
        if ($this->order_type !== 'delivery' || !$this->delivery_latitude || !$this->delivery_longitude) {
            return null;
        }

        $pricing = app(DeliveryPricingService::class);
        $result = $pricing->calculate($this->restaurant, $this->delivery_latitude, $this->delivery_longitude);

        if ($result['zone_name']) {
            return $result['zone_name'];
        }

        return $result['city_name'];
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
            return (int) round($baseAmount * ($this->restaurant->tax_rate / (100 + $this->restaurant->tax_rate)));
        } else {
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

        if ($this->restaurant->service_fee_rate > 0) {
            $feeAmount += (int) round($baseAmount * ($this->restaurant->service_fee_rate / 100));
        }

        if ($this->restaurant->service_fee_fixed > 0) {
            $feeAmount += $this->restaurant->service_fee_fixed;
        }

        return $feeAmount;
    }

    #[Computed]
    public function jekoPaymentAvailable(): bool
    {
        // Jeko gateway supprimé (commit 03eba90) — toujours désactivé
        return false;
    }

    #[Computed]
    public function cashOnDeliveryAvailable(): bool
    {
        return (bool) ($this->restaurant->cash_on_delivery ?? false);
    }

    #[Computed]
    public function total(): int
    {
        $baseTotal = $this->subtotal + $this->deliveryFee - $this->discount;

        if (!$this->restaurant->tax_included) {
            $baseTotal += $this->taxAmount;
        }

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

    public static function phoneCountryOptions(): array
    {
        return [
            '+225' => '+225',
            '+221' => '+221',
            '+223' => '+223',
            '+226' => '+226',
            '+228' => '+228',
            '+229' => '+229',
            '+33'  => '+33',
        ];
    }

    public function getFullPhoneNumber(): string
    {
        $national = preg_replace('/\D/', '', $this->customer_phone);
        if ($national === '') {
            return '';
        }
        $prefix = preg_replace('/\D/', '', $this->customer_phone_country);
        if ($prefix === '') {
            $prefix = '225';
        }
        return '+' . $prefix . $national;
    }

    public function placeOrder()
    {
        try {
            return $this->processOrder();
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            \Log::error('Checkout placeOrder failed', [
                'restaurant' => $this->restaurant->id,
                'error' => $e->getMessage(),
                'file' => $e->getFile() . ':' . $e->getLine(),
            ]);
            session()->flash('error', 'Une erreur est survenue. Veuillez réessayer.');
        }
    }

    private function processOrder()
    {
        $rules = [
            'customer_name' => 'required|string|max:100',
            'customer_phone' => 'required|string|min:8|max:20',
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

        if ($this->subtotal < ($this->restaurant->min_order_amount ?? 0)) {
            session()->flash('error', "Commande minimum de " . number_format($this->restaurant->min_order_amount, 0, ',', ' ') . " F requise.");
            return;
        }

        $cashOnDeliveryAvailable = $this->restaurant->cash_on_delivery ?? false;
        $jekoAvailable = $this->jekoPaymentAvailable;

        $validMethods = array_merge(
            $cashOnDeliveryAvailable ? ['cash_on_delivery'] : [],
            $jekoAvailable ? ['jeko'] : []
        );

        if (!$this->payment_method) {
            if (count($validMethods) > 1) {
                session()->flash('error', 'Veuillez choisir un mode de paiement.');
                return;
            }
            $this->payment_method = $validMethods[0] ?? 'cash_on_delivery';
        } else {
            if (!in_array($this->payment_method, $validMethods)) {
                session()->flash('error', 'Mode de paiement invalide.');
                return;
            }
        }

        // Validate delivery zone via platform city-based pricing
        if ($this->order_type === 'delivery' && $this->delivery_latitude && $this->delivery_longitude) {
            $pricing = app(DeliveryPricingService::class);
            $deliveryResult = $pricing->calculate($this->restaurant, $this->delivery_latitude, $this->delivery_longitude);

            if (!$deliveryResult['within_range']) {
                $distance = $deliveryResult['distance_km'];
                session()->flash('error', "Cette adresse est hors de notre zone de livraison. Distance: {$distance} km.");
                return;
            }
        }

        // Create order
        $order = Order::create([
            'restaurant_id' => $this->restaurant->id,
            'customer_name' => $this->customer_name,
            'customer_email' => $this->customer_email ?: null,
            'customer_phone' => $this->getFullPhoneNumber(),
            'type' => OrderType::from($this->order_type),
            'status' => OrderStatus::PENDING_PAYMENT,
            'payment_status' => PaymentStatus::PENDING,
            'payment_method' => $this->payment_method === 'jeko' ? 'jeko' : null,
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

        // Notify restaurant
        $this->restaurant->users()
            ->whereIn('role', [\App\Enums\UserRole::RESTAURANT_ADMIN, \App\Enums\UserRole::EMPLOYEE])
            ->each(function ($user) use ($order) {
                $user->notify(new NewOrderNotification($order));
            });

        // Process payment
        // Note: 'jeko' payment method supprimé (commit 03eba90) — jekoPaymentAvailable() retourne false,
        // donc ce cas ne peut jamais être atteint via l'UI.
        // Cash on delivery
        $order->markAsPaid([
            'method' => 'cash_on_delivery',
            'metadata' => ['note' => 'Paiement à la livraison'],
        ]);

        // Redirect to order status
        $this->redirect(route('r.order.status', [$this->restaurant->slug, $order->tracking_token]));
    }

    private function calculateDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371;

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat/2) * sin($dLat/2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon/2) * sin($dLon/2);

        $c = 2 * atan2(sqrt($a), sqrt(1-$a));

        return $earthRadius * $c;
    }

    public function render()
    {
        return view('livewire.public.checkout')
            ->layout('layouts.restaurant-public', [
                'restaurant' => $this->restaurant,
            ]);
    }
}
