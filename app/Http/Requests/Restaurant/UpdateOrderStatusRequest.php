<?php

namespace App\Http\Requests\Restaurant;

use App\Enums\OrderStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateOrderStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('updateStatus', $this->route('order'));
    }

    public function rules(): array
    {
        $order = $this->route('order');
        $allowedStatuses = $order->status->allowedTransitions();
        $allowedValues = array_map(fn($s) => $s->value, $allowedStatuses);

        return [
            'status' => ['required', Rule::in($allowedValues)],
            'estimated_prep_time' => ['nullable', 'integer', 'min:1', 'max:180'],
            'internal_notes' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'status.required' => 'Le statut est obligatoire.',
            'status.in' => 'Ce changement de statut n\'est pas autorisé.',
            'estimated_prep_time.min' => 'Le temps de préparation doit être d\'au moins 1 minute.',
            'estimated_prep_time.max' => 'Le temps de préparation ne peut pas dépasser 180 minutes.',
            'internal_notes.max' => 'Les notes ne peuvent pas dépasser :max caractères.',
        ];
    }
}

