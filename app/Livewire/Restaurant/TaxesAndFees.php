<?php

namespace App\Livewire\Restaurant;

use Livewire\Attributes\Rule;
use Livewire\Component;

class TaxesAndFees extends Component
{
    // Tax settings
    #[Rule('nullable|numeric|min:0|max:100')]
    public ?float $tax_rate = null;

    #[Rule('boolean')]
    public bool $tax_included = false;

    #[Rule('nullable|string|max:50')]
    public ?string $tax_name = 'TVA';

    // Service fee settings
    #[Rule('nullable|numeric|min:0|max:100')]
    public ?float $service_fee_rate = null;

    #[Rule('nullable|integer|min:0')]
    public ?int $service_fee_fixed = null;

    #[Rule('boolean')]
    public bool $service_fee_enabled = false;

    public function mount(): void
    {
        $restaurant = auth()->user()->restaurant;

        if (!$restaurant) {
            return;
        }

        $this->tax_rate = $restaurant->tax_rate ? (float) $restaurant->tax_rate : null;
        $this->tax_included = $restaurant->tax_included ?? false;
        $this->tax_name = $restaurant->tax_name ?? 'TVA';
        $this->service_fee_rate = $restaurant->service_fee_rate ? (float) $restaurant->service_fee_rate : null;
        $this->service_fee_fixed = $restaurant->service_fee_fixed ?? null;
        $this->service_fee_enabled = $restaurant->service_fee_enabled ?? false;
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
            $restaurant->update([
                'tax_rate' => $this->tax_rate ?? 0,
                'tax_included' => $this->tax_included,
                'tax_name' => $this->tax_name ?? 'TVA',
                'service_fee_rate' => $this->service_fee_rate ?? 0,
                'service_fee_fixed' => $this->service_fee_fixed ?? 0,
                'service_fee_enabled' => $this->service_fee_enabled,
            ]);

            session()->flash('success', 'Paramètres de taxes et frais enregistrés avec succès.');
        } catch (\Exception $e) {
            session()->flash('error', 'Une erreur est survenue : ' . $e->getMessage());
        }
    }

    public function getExampleCalculationProperty(): array
    {
        $subtotal = 10000; // 100 FCFA en centimes
        $deliveryFee = 2000; // 20 FCFA
        $discount = 0;

        $baseAmount = $subtotal + $deliveryFee - $discount;

        // Tax calculation
        $taxAmount = 0;
        if ($this->tax_rate && $this->tax_rate > 0) {
            if ($this->tax_included) {
                $taxAmount = (int) round($baseAmount * ($this->tax_rate / (100 + $this->tax_rate)));
            } else {
                $taxAmount = (int) round($baseAmount * ($this->tax_rate / 100));
            }
        }

        // Service fee calculation
        $serviceFee = 0;
        if ($this->service_fee_enabled) {
            if ($this->service_fee_rate && $this->service_fee_rate > 0) {
                $serviceFee += (int) round($baseAmount * ($this->service_fee_rate / 100));
            }
            if ($this->service_fee_fixed && $this->service_fee_fixed > 0) {
                $serviceFee += $this->service_fee_fixed;
            }
        }

        $total = $baseAmount + ($this->tax_included ? 0 : $taxAmount) + $serviceFee;

        return [
            'subtotal' => $subtotal,
            'delivery_fee' => $deliveryFee,
            'discount' => $discount,
            'tax_amount' => $taxAmount,
            'service_fee' => $serviceFee,
            'total' => $total,
        ];
    }

    public function render()
    {
        $restaurant = auth()->user()->restaurant;
        $subscription = $restaurant?->activeSubscription;
        $example = $this->exampleCalculation;

        return view('livewire.restaurant.taxes-and-fees', [
            'example' => $example,
        ])
            ->layout('components.layouts.admin-restaurant', [
                'title' => 'Taxes & Frais',
                'restaurant' => $restaurant,
                'subscription' => $subscription,
            ]);
    }
}

