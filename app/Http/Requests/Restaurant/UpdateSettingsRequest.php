<?php

namespace App\Http\Requests\Restaurant;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        $restaurant = $this->user()->restaurant;
        return $this->user()->can('manageSettings', $restaurant);
    }

    public function rules(): array
    {
        return [
            // General
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'email' => ['sometimes', 'required', 'email', 'max:255'],
            'phone' => ['sometimes', 'required', 'string', 'max:20'],
            
            // Address
            'address' => ['nullable', 'string', 'max:500'],
            'city' => ['nullable', 'string', 'max:100'],
            'postal_code' => ['nullable', 'string', 'max:10'],
            
            // Branding
            'logo' => ['nullable', 'image', 'mimes:jpeg,png,webp', 'max:2048'],
            'banner' => ['nullable', 'image', 'mimes:jpeg,png,webp', 'max:5120'],
            'primary_color' => ['nullable', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'secondary_color' => ['nullable', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'remove_logo' => ['boolean'],
            'remove_banner' => ['boolean'],
            
            // Operations
            'min_order_amount' => ['nullable', 'integer', 'min:0'],
            'delivery_fee' => ['nullable', 'integer', 'min:0'],
            'delivery_radius_km' => ['nullable', 'integer', 'min:1'],
            'estimated_prep_time' => ['nullable', 'integer', 'min:5', 'max:180'],
            'currency' => ['nullable', 'string', 'size:3'],
            'timezone' => ['nullable', 'string', 'timezone'],
            
            // Opening hours
            'opening_hours' => ['nullable', 'array'],
            'opening_hours.*.open' => ['nullable', 'date_format:H:i'],
            'opening_hours.*.close' => ['nullable', 'date_format:H:i', 'after:opening_hours.*.open'],
            'opening_hours.*.closed' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Le nom du restaurant est obligatoire.',
            'email.required' => 'L\'email est obligatoire.',
            'email.email' => 'L\'email n\'est pas valide.',
            'phone.required' => 'Le téléphone est obligatoire.',
            'logo.image' => 'Le logo doit être une image.',
            'logo.mimes' => 'Le logo doit être au format JPEG, PNG ou WEBP.',
            'logo.max' => 'Le logo ne doit pas dépasser 2 Mo.',
            'banner.image' => 'La bannière doit être une image.',
            'banner.mimes' => 'La bannière doit être au format JPEG, PNG ou WEBP.',
            'banner.max' => 'La bannière ne doit pas dépasser 5 Mo.',
            'primary_color.regex' => 'La couleur primaire n\'est pas valide.',
            'secondary_color.regex' => 'La couleur secondaire n\'est pas valide.',
            'min_order_amount.min' => 'Le montant minimum ne peut pas être négatif.',
            'delivery_fee.min' => 'Les frais de livraison ne peuvent pas être négatifs.',
            'estimated_prep_time.min' => 'Le temps de préparation doit être d\'au moins 5 minutes.',
            'estimated_prep_time.max' => 'Le temps de préparation ne peut pas dépasser 3 heures.',
            'timezone.timezone' => 'Le fuseau horaire n\'est pas valide.',
            'opening_hours.*.close.after' => 'L\'heure de fermeture doit être après l\'heure d\'ouverture.',
        ];
    }
}

