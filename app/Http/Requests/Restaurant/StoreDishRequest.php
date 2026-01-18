<?php

namespace App\Http\Requests\Restaurant;

use Illuminate\Foundation\Http\FormRequest;

class StoreDishRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\Dish::class);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:1000'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'price' => ['required', 'integer', 'min:0'],
            'compare_price' => ['nullable', 'integer', 'min:0', 'gt:price'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,webp', 'max:2048'],
            'gallery.*' => ['image', 'mimes:jpeg,png,webp', 'max:2048'],
            
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
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Le nom du plat est obligatoire.',
            'name.max' => 'Le nom ne peut pas dépasser :max caractères.',
            'description.max' => 'La description ne peut pas dépasser :max caractères.',
            'category_id.exists' => 'La catégorie sélectionnée n\'existe pas.',
            'price.required' => 'Le prix est obligatoire.',
            'price.min' => 'Le prix ne peut pas être négatif.',
            'compare_price.gt' => 'Le prix barré doit être supérieur au prix actuel.',
            'image.image' => 'Le fichier doit être une image.',
            'image.mimes' => 'L\'image doit être au format JPEG, PNG ou WEBP.',
            'image.max' => 'L\'image ne doit pas dépasser 2 Mo.',
            'gallery.*.image' => 'Les fichiers de la galerie doivent être des images.',
            'gallery.*.mimes' => 'Les images de la galerie doivent être au format JPEG, PNG ou WEBP.',
            'gallery.*.max' => 'Chaque image de la galerie ne doit pas dépasser 2 Mo.',
            'stock_quantity.min' => 'La quantité en stock ne peut pas être négative.',
            'prep_time.min' => 'Le temps de préparation doit être d\'au moins 1 minute.',
            'calories.min' => 'Les calories ne peuvent pas être négatives.',
        ];
    }
}

