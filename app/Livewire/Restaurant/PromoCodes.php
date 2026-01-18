<?php

namespace App\Livewire\Restaurant;

use App\Models\PromoCode;
use Livewire\Attributes\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class PromoCodes extends Component
{
    use WithPagination;

    public ?PromoCode $editingPromo = null;
    public bool $showModal = false;

    // Form fields
    #[Rule('required|string|max:50|regex:/^[A-Z0-9]+$/')]
    public string $code = '';

    #[Rule('nullable|string|max:500')]
    public ?string $description = null;

    #[Rule('required|in:percentage,fixed')]
    public string $discount_type = 'percentage';

    #[Rule('required|integer|min:1')]
    public int $discount_value = 0;

    #[Rule('nullable|integer|min:0')]
    public ?int $min_order_amount = null;

    #[Rule('nullable|integer|min:0')]
    public ?int $max_discount_amount = null;

    #[Rule('nullable|integer|min:1')]
    public ?int $max_uses = null;

    #[Rule('nullable|integer|min:1')]
    public ?int $max_uses_per_customer = null;

    #[Rule('nullable|date')]
    public ?string $starts_at = null;

    #[Rule('nullable|date|after:starts_at')]
    public ?string $expires_at = null;

    public bool $is_active = true;

    public string $search = '';
    public string $filter = 'all'; // all, active, expired, inactive

    public function mount(): void
    {
        //
    }

    public function openCreateModal(): void
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function openEditModal(PromoCode $promoCode): void
    {
        $this->editingPromo = $promoCode;
        $this->code = $promoCode->code;
        $this->description = $promoCode->description;
        $this->discount_type = $promoCode->discount_type;
        $this->discount_value = $promoCode->discount_value;
        $this->min_order_amount = $promoCode->min_order_amount;
        $this->max_discount_amount = $promoCode->max_discount_amount;
        $this->max_uses = $promoCode->max_uses;
        $this->max_uses_per_customer = $promoCode->max_uses_per_customer;
        $this->starts_at = $promoCode->starts_at?->format('Y-m-d\TH:i');
        $this->expires_at = $promoCode->expires_at?->format('Y-m-d\TH:i');
        $this->is_active = $promoCode->is_active;
        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
        $this->editingPromo = null;
    }

    public function resetForm(): void
    {
        $this->code = '';
        $this->description = null;
        $this->discount_type = 'percentage';
        $this->discount_value = 0;
        $this->min_order_amount = null;
        $this->max_discount_amount = null;
        $this->max_uses = null;
        $this->max_uses_per_customer = null;
        $this->starts_at = null;
        $this->expires_at = null;
        $this->is_active = true;
    }

    public function save(): void
    {
        $this->validate();

        $restaurant = auth()->user()->restaurant;

        if (!$restaurant) {
            session()->flash('error', 'Restaurant introuvable.');
            return;
        }

        try {
            $data = [
                'restaurant_id' => $restaurant->id,
                'code' => strtoupper($this->code),
                'description' => $this->description,
                'discount_type' => $this->discount_type,
                'discount_value' => $this->discount_value,
                'min_order_amount' => $this->min_order_amount,
                'max_discount_amount' => $this->max_discount_amount,
                'max_uses' => $this->max_uses,
                'max_uses_per_customer' => $this->max_uses_per_customer,
                'starts_at' => $this->starts_at ? date('Y-m-d H:i:s', strtotime($this->starts_at)) : null,
                'expires_at' => $this->expires_at ? date('Y-m-d H:i:s', strtotime($this->expires_at)) : null,
                'is_active' => $this->is_active,
            ];

            if ($this->editingPromo) {
                // Check if code is unique (except for current promo)
                $exists = PromoCode::where('restaurant_id', $restaurant->id)
                    ->where('code', strtoupper($this->code))
                    ->where('id', '!=', $this->editingPromo->id)
                    ->exists();

                if ($exists) {
                    session()->flash('error', 'Ce code promo existe déjà.');
                    return;
                }

                $this->editingPromo->update($data);
                session()->flash('success', 'Code promo mis à jour avec succès.');
            } else {
                // Check if code is unique
                $exists = PromoCode::where('restaurant_id', $restaurant->id)
                    ->where('code', strtoupper($this->code))
                    ->exists();

                if ($exists) {
                    session()->flash('error', 'Ce code promo existe déjà.');
                    return;
                }

                PromoCode::create($data);
                session()->flash('success', 'Code promo créé avec succès.');
            }

            $this->closeModal();
            $this->resetPage();
        } catch (\Exception $e) {
            session()->flash('error', 'Une erreur est survenue : ' . $e->getMessage());
        }
    }

    public function toggleActive(PromoCode $promoCode): void
    {
        try {
            $promoCode->update(['is_active' => !$promoCode->is_active]);
            session()->flash('success', 'Code promo ' . ($promoCode->is_active ? 'activé' : 'désactivé') . ' avec succès.');
        } catch (\Exception $e) {
            session()->flash('error', 'Une erreur est survenue : ' . $e->getMessage());
        }
    }

    public function delete(PromoCode $promoCode): void
    {
        try {
            if ($promoCode->current_uses > 0) {
                session()->flash('error', 'Impossible de supprimer un code promo qui a déjà été utilisé.');
                return;
            }

            $promoCode->delete();
            session()->flash('success', 'Code promo supprimé avec succès.');
            $this->resetPage();
        } catch (\Exception $e) {
            session()->flash('error', 'Une erreur est survenue : ' . $e->getMessage());
        }
    }

    public function render()
    {
        $restaurant = auth()->user()->restaurant;

        if (!$restaurant) {
            return view('livewire.restaurant.promo-codes')
                ->layout('components.layouts.admin-restaurant', [
                    'title' => 'Codes Promo',
                    'restaurant' => null,
                    'subscription' => null,
                ]);
        }

        $query = PromoCode::where('restaurant_id', $restaurant->id);

        // Search
        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('code', 'like', '%' . $this->search . '%')
                  ->orWhere('description', 'like', '%' . $this->search . '%');
            });
        }

        // Filter
        switch ($this->filter) {
            case 'active':
                $query->where('is_active', true)
                      ->where(function ($q) {
                          $q->whereNull('expires_at')
                            ->orWhere('expires_at', '>', now());
                      })
                      ->where(function ($q) {
                          $q->whereNull('max_uses')
                            ->orWhereColumn('current_uses', '<', 'max_uses');
                      });
                break;
            case 'expired':
                $query->where(function ($q) {
                    $q->where('expires_at', '<=', now())
                      ->orWhere(function ($q2) {
                          $q2->whereNotNull('max_uses')
                             ->whereColumn('current_uses', '>=', 'max_uses');
                      });
                });
                break;
            case 'inactive':
                $query->where('is_active', false);
                break;
        }

        $promoCodes = $query->latest()->paginate(15);

        $subscription = $restaurant->activeSubscription;

        return view('livewire.restaurant.promo-codes', [
            'promoCodes' => $promoCodes,
        ])
            ->layout('components.layouts.admin-restaurant', [
                'title' => 'Codes Promo',
                'restaurant' => $restaurant,
                'subscription' => $subscription,
            ]);
    }
}

