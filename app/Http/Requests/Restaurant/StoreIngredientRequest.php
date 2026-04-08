<?php

namespace App\Http\Requests\Restaurant;

use App\Enums\Unit;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreIngredientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\Ingredient::class);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'sku' => ['nullable', 'string', 'max:50', 'unique:ingredients,sku'],
            'ingredient_category_id' => ['nullable', 'exists:ingredient_categories,id'],
            'unit' => ['required', Rule::enum(Unit::class)],
            'current_quantity' => ['nullable', 'numeric', 'min:0'],
            'min_quantity' => ['nullable', 'numeric', 'min:0'],
            'max_quantity' => ['nullable', 'numeric', 'min:0'],
            'unit_cost' => ['nullable', 'integer', 'min:0'],
            'track_expiry' => ['boolean'],
            'default_expiry_days' => ['nullable', 'integer', 'min:1'],
            'notes' => ['nullable', 'string', 'max:500'],
            'is_active' => ['boolean'],
            'image' => ['nullable', 'image', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Le nom de l\'ingrédient est obligatoire.',
            'name.max' => 'Le nom ne peut pas dépasser :max caractères.',
            'sku.unique' => 'Ce code article existe déjà.',
            'unit.required' => 'L\'unité de mesure est obligatoire.',
            'unit.enum' => 'L\'unité de mesure n\'est pas valide.',
            'current_quantity.min' => 'La quantité ne peut pas être négative.',
            'min_quantity.min' => 'Le seuil ne peut pas être négatif.',
            'unit_cost.min' => 'Le coût ne peut pas être négatif.',
            'default_expiry_days.min' => 'La durée de péremption doit être d\'au moins 1 jour.',
        ];
    }
}

