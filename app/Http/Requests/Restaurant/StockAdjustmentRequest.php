<?php

namespace App\Http\Requests\Restaurant;

use Illuminate\Foundation\Http\FormRequest;

class StockAdjustmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $ingredient = $this->route('ingredient');
        return $this->user()->can('adjustStock', $ingredient);
    }

    public function rules(): array
    {
        return [
            'new_quantity' => ['required', 'numeric', 'min:0'],
            'reason' => ['required', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'new_quantity.required' => 'La nouvelle quantité est obligatoire.',
            'new_quantity.min' => 'La quantité ne peut pas être négative.',
            'reason.required' => 'Une raison est obligatoire pour un ajustement.',
            'reason.max' => 'La raison ne peut pas dépasser :max caractères.',
        ];
    }
}

