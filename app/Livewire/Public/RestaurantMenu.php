<?php

namespace App\Livewire\Public;

use App\Enums\RestaurantStatus;
use App\Models\Category;
use App\Models\Dish;
use App\Models\Restaurant;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

class RestaurantMenu extends Component
{
    public Restaurant $restaurant;
    public ?int $activeCategory = null;
    public ?int $selectedDishId = null;
    public string $searchQuery = '';
    
    // Cart state
    public array $cart = [];
    public bool $showCart = false;

    public function mount(string $slug): void
    {
        try {
            $restaurant = Restaurant::where('slug', $slug)->first();
            
            if (!$restaurant) {
                abort(404, "Restaurant avec le slug '{$slug}' introuvable.");
            }
            
            // In production, only allow ACTIVE restaurants
            // In development/local, allow access to all restaurants (for testing)
            // Allow access if not in production OR if status is ACTIVE
            $isProduction = app()->environment('production');
            if ($isProduction && $restaurant->status !== RestaurantStatus::ACTIVE) {
                abort(404, 'Ce restaurant n\'est pas disponible pour le moment. Statut: ' . $restaurant->status->label());
            }
            
            // Note: In development, we allow access to all restaurants without warning
            // to facilitate testing. In production, only ACTIVE restaurants are accessible.
            
            $this->restaurant = $restaurant;

            // Set first category as active
            $firstCategory = $this->categories->first();
            if ($firstCategory) {
                $this->activeCategory = $firstCategory->id;
            }

            // Load cart from session
            $this->cart = session()->get("cart.{$this->restaurant->id}", []);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            abort(404, "Restaurant introuvable.");
        } catch (\Exception $e) {
            abort(500, "Erreur lors du chargement du restaurant: " . $e->getMessage());
        }
    }

    /**
     * Refresh restaurant data to get latest opening hours
     */
    public function refreshRestaurant(): void
    {
        $this->restaurant->refresh();
    }

    #[Computed]
    public function isRestaurantOpen(): bool
    {
        return $this->restaurant->isOpenNow();
    }

    #[Computed]
    public function categories()
    {
        return Category::where('restaurant_id', $this->restaurant->id)
            ->active()
            ->ordered()
            ->withCount(['dishes' => fn($q) => $q->active()])
            ->having('dishes_count', '>', 0)
            ->get();
    }

    #[Computed]
    public function dishes()
    {
        return Dish::where('restaurant_id', $this->restaurant->id)
            ->active()
            ->when($this->activeCategory, fn($q) => $q->where('category_id', $this->activeCategory))
            ->when($this->searchQuery, fn($q) => $q->where('name', 'like', "%{$this->searchQuery}%"))
            ->ordered()
            ->get();
    }

    #[Computed]
    public function featuredDishes()
    {
        return Dish::where('restaurant_id', $this->restaurant->id)
            ->active()
            ->featured()
            ->limit(6)
            ->get();
    }

    #[Computed]
    public function reviews()
    {
        return \App\Models\Review::where('restaurant_id', $this->restaurant->id)
            ->where('is_approved', true)
            ->where('is_visible', true)
            ->latest()
            ->limit(10)
            ->get();
    }

    #[Computed]
    public function averageRating(): float
    {
        return \App\Models\Review::where('restaurant_id', $this->restaurant->id)
            ->where('is_approved', true)
            ->where('is_visible', true)
            ->avg('rating') ?? 0;
    }

    #[Computed]
    public function reviewsCount(): int
    {
        return \App\Models\Review::where('restaurant_id', $this->restaurant->id)
            ->where('is_approved', true)
            ->where('is_visible', true)
            ->count();
    }

    #[Computed]
    public function selectedDish(): ?Dish
    {
        if (!$this->selectedDishId) {
            return null;
        }

        return Dish::with('optionGroups.activeOptions')
            ->find($this->selectedDishId);
    }

    #[Computed]
    public function cartTotal(): int
    {
        return collect($this->cart)->sum(fn($item) => $item['total']);
    }

    #[Computed]
    public function cartItemsCount(): int
    {
        return collect($this->cart)->sum('quantity');
    }

    public function setCategory(?int $categoryId): void
    {
        $this->activeCategory = $categoryId;
        $this->searchQuery = '';
    }

    public function openDish(int $dishId): void
    {
        $this->selectedDishId = $dishId;
    }

    public function closeDish(): void
    {
        $this->selectedDishId = null;
    }

    public function addToCart(int $dishId, int $quantity = 1, array $options = [], ?string $instructions = null): void
    {
        // Check if restaurant is open
        if (!$this->restaurant->isOpenNow()) {
            $nextOpening = $this->restaurant->getNextOpeningTime();
            $message = 'Le restaurant est actuellement fermé.';
            if ($nextOpening) {
                $message .= " Réouverture : {$nextOpening}";
            }
            session()->flash('error', $message);
            return;
        }

        $dish = Dish::with('optionGroups.options')->findOrFail($dishId);

        if (!$dish->is_available) {
            session()->flash('error', 'Ce plat n\'est plus disponible.');
            return;
        }

        // Calculate options price
        $optionsPrice = 0;
        $selectedOptions = [];
        
        foreach ($options as $optionId) {
            $option = $dish->optionGroups()
                ->join('dish_options', 'dish_option_groups.id', '=', 'dish_options.option_group_id')
                ->where('dish_options.id', $optionId)
                ->select('dish_options.*')
                ->first();

            if ($option) {
                $optionsPrice += $option->price_adjustment;
                $selectedOptions[] = [
                    'id' => $option->id,
                    'name' => $option->name,
                    'price' => $option->price_adjustment,
                ];
            }
        }

        $unitPrice = $dish->price + $optionsPrice;
        $cartKey = $dishId . '-' . md5(json_encode($options) . $instructions);

        // Check if item already exists
        if (isset($this->cart[$cartKey])) {
            $this->cart[$cartKey]['quantity'] += $quantity;
            $this->cart[$cartKey]['total'] = $this->cart[$cartKey]['quantity'] * $unitPrice;
        } else {
            $this->cart[$cartKey] = [
                'dish_id' => $dishId,
                'name' => $dish->name,
                'image' => $dish->image_path,
                'unit_price' => $unitPrice,
                'quantity' => $quantity,
                'total' => $unitPrice * $quantity,
                'options' => $selectedOptions,
                'options_price' => $optionsPrice,
                'instructions' => $instructions,
            ];
        }

        // Save to session
        session()->put("cart.{$this->restaurant->id}", $this->cart);
        
        // Open cart automatically when adding an item
        $this->showCart = true;
        
        $this->closeDish();
        $this->dispatch('cart-updated');
    }

    public function updateCartQuantity(string $cartKey, int $quantity): void
    {
        if (!isset($this->cart[$cartKey])) {
            return;
        }

        if ($quantity <= 0) {
            $this->removeFromCart($cartKey);
            return;
        }

        $this->cart[$cartKey]['quantity'] = $quantity;
        $this->cart[$cartKey]['total'] = $quantity * $this->cart[$cartKey]['unit_price'];

        session()->put("cart.{$this->restaurant->id}", $this->cart);
        $this->dispatch('cart-updated');
    }

    public function removeFromCart(string $cartKey): void
    {
        unset($this->cart[$cartKey]);
        session()->put("cart.{$this->restaurant->id}", $this->cart);
        $this->dispatch('cart-updated');
    }

    public function clearCart(): void
    {
        $this->cart = [];
        session()->forget("cart.{$this->restaurant->id}");
        $this->dispatch('cart-updated');
    }

    public function toggleCart(): void
    {
        $this->showCart = !$this->showCart;
    }

    public function proceedToCheckout(): void
    {
        if (empty($this->cart)) {
            session()->flash('error', 'Votre panier est vide.');
            return;
        }

        // Store cart in session for checkout
        session()->put("checkout.{$this->restaurant->id}", $this->cart);

        $this->redirect(route('r.checkout', $this->restaurant->slug));
    }

    public function render()
    {
        return view('livewire.public.restaurant-menu')
            ->layout('layouts.restaurant-public', [
                'restaurant' => $this->restaurant,
            ]);
    }
}

