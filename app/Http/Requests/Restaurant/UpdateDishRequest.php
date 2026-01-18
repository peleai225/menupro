<?php

namespace App\Http\Requests\Restaurant;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDishRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('dish'));
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:1000'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'price' => ['sometimes', 'required', 'integer', 'min:0'],
            'compare_price' => ['nullable', 'integer', 'min:0'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,webp', 'max:2048'],
            'gallery.*' => ['image', 'mimes:jpeg,png,webp', 'max:2048'],
            'remove_image' => ['boolean'],
            
            // Options
            'is_active' => ['boolean'],
            'is_featured' => ['boolean'],
            'is_new' => ['boolean'],
            'is_spicy' => ['boolean'],
            'is_vegetarian' => ['boolean'],
            'is_vegan' => ['boolean'],
            'is_gluten_free' => ['boolean'],
            
            // Stock
            'track_stock' => ['boolean'],
            'stock_quantity' => ['nullable', 'integer', 'min:0'],
            'allow_out_of_stock_orders' => ['boolean'],
            
            // Meta
            'prep_time' => ['nullable', 'integer', 'min:1'],
            'calories' => ['nullable', 'integer', 'min:0'],
            'allergens' => ['nullable', 'array'],
            'allergens.*' => ['string', 'max:50'],
            
            // Option groups
            'option_groups' => ['nullable', 'array'],
            'option_groups.*' => ['exists:dish_option_groups,id'],
            
            // Sort
            'sort_order' => ['integer', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Le nom du plat est obligatoire.',
            'name.max' => 'Le nom ne peut pas dépasser :max caractères.',
            'price.required' => 'Le prix est obligatoire.',
            'price.min' => 'Le prix ne peut pas être négatif.',
            'image.image' => 'Le fichier doit être une image.',
            'image.mimes' => 'L\'image doit être au format JPEG, PNG ou WEBP.',
            'image.max' => 'L\'image ne doit pas dépasser 2 Mo.',
        ];
    }
}

