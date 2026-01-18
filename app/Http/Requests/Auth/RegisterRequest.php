<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // User info
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'phone' => ['required', 'string', 'max:20'],
            'password' => ['required', Password::min(8)],
            
            // Restaurant info
            'restaurant_name' => ['required', 'string', 'max:255'],
            'restaurant_type' => ['required', 'string', 'in:restaurant,bar,brasserie,maquis,traiteur,cafe'],
            'company_name' => ['required', 'string', 'max:255'],
            'rccm' => ['required', 'string', 'max:50', 'unique:restaurants,rccm'],
            'restaurant_description' => ['nullable', 'string', 'max:1000'],
            'restaurant_address' => ['nullable', 'string', 'max:500'],
            'restaurant_city' => ['nullable', 'string', 'max:100'],
            
            // Files
            'logo' => ['nullable', 'image', 'mimes:jpeg,png,webp', 'max:2048'],
            'banner' => ['nullable', 'image', 'mimes:jpeg,png,webp', 'max:5120'],
            'rccm_document' => ['required', 'file', 'mimes:pdf,jpeg,png,jpg', 'max:5120'],
            
            // Plan
            'plan' => ['required', 'exists:plans,slug'],
            
            // Terms
            'terms' => ['required', 'accepted'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Le nom est obligatoire.',
            'email.required' => 'L\'adresse email est obligatoire.',
            'email.email' => 'L\'adresse email n\'est pas valide.',
            'email.unique' => 'Cette adresse email est déjà utilisée.',
            'phone.required' => 'Le numéro de téléphone est obligatoire.',
            'password.required' => 'Le mot de passe est obligatoire.',
            'password.confirmed' => 'Les mots de passe ne correspondent pas.',
            'password.min' => 'Le mot de passe doit contenir au moins :min caractères.',
            'restaurant_name.required' => 'Le nom du restaurant est obligatoire.',
            'company_name.required' => 'Le nom de l\'entreprise est obligatoire.',
            'rccm.required' => 'Le numéro RCCM est obligatoire.',
            'rccm.unique' => 'Ce numéro RCCM est déjà enregistré.',
            'rccm_document.required' => 'L\'extrait RCCM est obligatoire.',
            'rccm_document.mimes' => 'L\'extrait RCCM doit être au format PDF, JPEG ou PNG.',
            'rccm_document.max' => 'L\'extrait RCCM ne doit pas dépasser 5 Mo.',
            'logo.image' => 'Le logo doit être une image.',
            'logo.mimes' => 'Le logo doit être au format JPEG, PNG ou WEBP.',
            'logo.max' => 'Le logo ne doit pas dépasser 2 Mo.',
            'banner.image' => 'La bannière doit être une image.',
            'banner.mimes' => 'La bannière doit être au format JPEG, PNG ou WEBP.',
            'banner.max' => 'La bannière ne doit pas dépasser 5 Mo.',
            'plan.required' => 'Veuillez sélectionner un plan.',
            'plan.exists' => 'Le plan sélectionné n\'existe pas.',
            'terms.required' => 'Vous devez accepter les conditions d\'utilisation.',
            'terms.accepted' => 'Vous devez accepter les conditions d\'utilisation.',
        ];
    }
}

