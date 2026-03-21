<?php

namespace App\Livewire\Restaurant;

use App\Enums\OrderStatus;
use App\Enums\OrderType;
use App\Enums\PaymentStatus;
use App\Models\Category;
use App\Models\Dish;
use App\Models\Order;
use App\Models\OrderItem;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Illuminate\Support\Str;

class POS extends Component
{
    // Customer info
    public string $customerName = '';
    public string $customerPhone = '';
    public string $customerEmail = '';

    // Order settings
    public string $orderType = 'dine_in';
    public string $tableNumber = '';
    public string $customerNotes = '';
    public string $paymentMethod = 'cash';

    // Cart
    public array $cart = [];

    // UI state
    public string $searchDish = '';
    public ?int $selectedCategory = null;
    public bool $showConfirmModal = false;
    public ?Order $lastOrder = null;

    protected $rules = [
        'customerName' => 'required|string|max:100',
        'customerPhone' => 'nullable|string|max:20',
        'customerEmail' => 'nullable|email|max:255',
        'orderType' => 'required|in:dine_in,takeaway,delivery',
        'tableNumber' => 'required_if:orderType,dine_in|nullable|string|max:20',
    ];

    protected $messages = [
        'customerName.required' => 'Le nom du client est requis.',
        'tableNumber.required_if' => 'Le numéro de table est requis pour une commande sur place.',
    ];

    #[Computed]
    public function restaurant()
    {
        return auth()->user()->restaurant;
    }

    #[Computed]
    public function categories()
    {
        return Category::where('restaurant_id', $this->restaurant->id)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();
    }

    #[Computed]
    public function dishes()
    {
        $query = Dish::where('restaurant_id', $this->restaurant->id)
            ->where('is_active', true)
            ->with('category');

        if ($this->selectedCategory) {
            $query->where('category_id', $this->selectedCategory);
        }

        if ($this->searchDish) {
            $query->where('name', 'like', '%' . $this->searchDish . '%');
        }

        return $query->orderBy('name')->get();
    }

    #[Computed]
    public function cartSubtotal(): int
    {
        return collect($this->cart)->sum(fn($item) => $item['unit_price'] * $item['quantity']);
    }

    #[Computed]
    public function cartTax(): int
    {
        $restaurant = $this->restaurant;
        if (!$restaurant || !($restaurant->tax_rate > 0)) {
            return 0;
        }

        $base = $this->cartSubtotal;
        if ($restaurant->tax_included) {
            return (int) round($base * ($restaurant->tax_rate / (100 + $restaurant->tax_rate)));
        }
        return (int) round($base * ($restaurant->tax_rate / 100));
    }

    #[Computed]
    public function cartServiceFee(): int
    {
        $restaurant = $this->restaurant;
        if (!$restaurant || !($restaurant->service_fee_enabled ?? false)) {
            return 0;
        }

        $base = $this->cartSubtotal;
        $fee = 0;
        if (($restaurant->service_fee_rate ?? 0) > 0) {
            $fee += $base * ($restaurant->service_fee_rate / 100);
        }
        if (($restaurant->service_fee_fixed ?? 0) > 0) {
            $fee += $restaurant->service_fee_fixed;
        }
        return (int) round($fee);
    }

    #[Computed]
    public function cartTotal(): int
    {
        $total = $this->cartSubtotal;
        $restaurant = $this->restaurant;

        if ($restaurant && !($restaurant->tax_included ?? true)) {
            $total += $this->cartTax;
        }

        $total += $this->cartServiceFee;

        return $total;
    }

    #[Computed]
    public function cartItemsCount(): int
    {
        return collect($this->cart)->sum('quantity');
    }

    public function addToCart(int $dishId): void
    {
        $dish = Dish::where('restaurant_id', $this->restaurant->id)
            ->where('id', $dishId)
            ->where('is_active', true)
            ->first();

        if (!$dish) {
            return;
        }

        $key = 'dish_' . $dishId;

        if (isset($this->cart[$key])) {
            $this->cart[$key]['quantity']++;
        } else {
            $this->cart[$key] = [
                'dish_id' => $dish->id,
                'dish_name' => $dish->name,
                'unit_price' => $dish->price,
                'quantity' => 1,
                'selected_options' => [],
                'options_price' => 0,
                'special_instructions' => '',
            ];
        }
    }

    public function removeFromCart(string $key): void
    {
        unset($this->cart[$key]);
    }

    public function incrementItem(string $key): void
    {
        if (isset($this->cart[$key])) {
            $this->cart[$key]['quantity']++;
        }
    }

    public function decrementItem(string $key): void
    {
        if (isset($this->cart[$key])) {
            $this->cart[$key]['quantity']--;
            if ($this->cart[$key]['quantity'] <= 0) {
                unset($this->cart[$key]);
            }
        }
    }

    public function clearCart(): void
    {
        $this->cart = [];
        $this->customerName = '';
        $this->customerPhone = '';
        $this->customerEmail = '';
        $this->tableNumber = '';
        $this->customerNotes = '';
        $this->paymentMethod = 'cash';
        $this->lastOrder = null;
    }

    public function confirmOrder(): void
    {
        if (empty($this->cart)) {
            $this->addError('cart', 'Le panier est vide.');
            return;
        }

        $this->validate();
        $this->showConfirmModal = true;
    }

    public function submitOrder(): void
    {
        if (empty($this->cart)) {
            $this->addError('cart', 'Le panier est vide.');
            return;
        }

        $this->validate();

        $restaurant = $this->restaurant;

        try {
            \DB::transaction(function () use ($restaurant) {
                $order = Order::create([
                    'restaurant_id' => $restaurant->id,
                    'reference' => 'POS-' . now()->format('ymd') . '-' . strtoupper(Str::random(4)),
                    'tracking_token' => Str::random(32),
                    'customer_name' => $this->customerName,
                    'customer_email' => $this->customerEmail ?: null,
                    'customer_phone' => $this->customerPhone ?: null,
                    'type' => OrderType::from($this->orderType),
                    'status' => $this->paymentMethod === 'cash'
                        ? OrderStatus::CONFIRMED
                        : OrderStatus::PENDING_PAYMENT,
                    'subtotal' => $this->cartSubtotal,
                    'delivery_fee' => 0,
                    'discount_amount' => 0,
                    'tax_amount' => $this->cartTax,
                    'service_fee' => $this->cartServiceFee,
                    'total' => $this->cartTotal,
                    'table_number' => $this->orderType === 'dine_in' ? $this->tableNumber : null,
                    'customer_notes' => $this->customerNotes ?: null,
                    'internal_notes' => 'Commande créée via POS par ' . auth()->user()->name,
                    'payment_method' => $this->paymentMethod === 'cash' ? 'cash_on_delivery' : $this->paymentMethod,
                    'payment_status' => $this->paymentMethod === 'cash'
                        ? PaymentStatus::COMPLETED->value
                        : PaymentStatus::PENDING->value,
                    'paid_at' => $this->paymentMethod === 'cash' ? now() : null,
                    'confirmed_at' => $this->paymentMethod === 'cash' ? now() : null,
                    'estimated_prep_time' => $restaurant->estimated_prep_time ?? 30,
                ]);

                foreach ($this->cart as $item) {
                    $order->items()->create([
                        'dish_id' => $item['dish_id'],
                        'dish_name' => $item['dish_name'],
                        'unit_price' => $item['unit_price'],
                        'quantity' => $item['quantity'],
                        'total_price' => $item['unit_price'] * $item['quantity'],
                        'selected_options' => $item['selected_options'],
                        'options_price' => $item['options_price'],
                        'special_instructions' => $item['special_instructions'],
                    ]);
                }

                $this->lastOrder = $order;
            });

            // Send WhatsApp notification if enabled
            try {
                $whatsapp = app(\App\Services\WhatsAppService::class);
                if ($this->lastOrder->customer_phone) {
                    $whatsapp->sendOrderConfirmation($this->lastOrder);
                }
                $whatsapp->sendNewOrderToRestaurant($this->lastOrder);
            } catch (\Throwable $e) {
                // Don't fail the order if WhatsApp fails
                \Log::warning('POS WhatsApp notification failed: ' . $e->getMessage());
            }

            $this->showConfirmModal = false;
            $this->cart = [];
            $this->customerName = '';
            $this->customerPhone = '';
            $this->customerEmail = '';
            $this->tableNumber = '';
            $this->customerNotes = '';

            session()->flash('pos_success', 'Commande ' . $this->lastOrder->reference . ' créée avec succès !');

        } catch (\Throwable $e) {
            $this->addError('submit', 'Erreur lors de la création : ' . $e->getMessage());
            \Log::error('POS order creation failed', ['error' => $e->getMessage()]);
        }
    }

    public function selectCategory(?int $id): void
    {
        $this->selectedCategory = $this->selectedCategory === $id ? null : $id;
    }

    public function render()
    {
        return view('livewire.restaurant.pos')
            ->layout('components.layouts.admin-restaurant');
    }
}
