<?php

namespace App\Http\Requests\Restaurant;

use Illuminate\Foundation\Http\FormRequest;

class StockEntryRequest extends FormRequest
{
    public function authorize(): bool
    {
        $ingredient = $this->route('ingredient');
        return $this->user()->can('adjustStock', $ingredient);
    }

    public function rules(): array
    {
        return [
            'quantity' => ['required', 'numeric', 'min:0.001'],
            'unit_cost' => ['nullable', 'integer', 'min:0'],
            'supplier_id' => ['nullable', 'exists:suppliers,id'],
            'expiry_date' => ['nullable', 'date', 'after:today'],
            'batch_number' => ['nullable', 'string', 'max:50'],
            'reason' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'quantity.required' => 'La quantité est obligatoire.',
            'quantity.min' => 'La quantité doit être supérieure à 0.',
            'unit_cost.min' => 'Le coût ne peut pas être négatif.',
            'supplier_id.exists' => 'Le fournisseur sélectionné n\'existe pas.',
            'expiry_date.after' => 'La date de péremption doit être dans le futur.',
            'batch_number.max' => 'Le numéro de lot ne peut pas dépasser :max caractères.',
            'reason.max' => 'La raison ne peut pas dépasser :max caractères.',
        ];
    }
}

