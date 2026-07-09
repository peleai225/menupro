<?php

namespace App\Livewire\Commando;

use App\Enums\AgentVerificationStatus;
use App\Enums\UserRole;
use App\Models\CommandoAgent;
use App\Models\Crm\CommercialProfile;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rules\Password;
use Livewire\Component;

class RegisterStep1 extends Component
{
    public string $first_name = '';
    public string $last_name = '';
    public string $whatsapp = '';
    public string $password = '';
    public string $password_confirmation = '';
    public string $city = '';

    protected function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'whatsapp' => [
                'required',
                'string',
                'max:20',
                // Numéros ivoiriens : 07/05/01/04/... avec ou sans +225, espaces acceptés
                'regex:/^(\+?225[\s\-]?)?0[0-9][\s\-]?[0-9]{2}[\s\-]?[0-9]{2}[\s\-]?[0-9]{2}[\s\-]?[0-9]{2}$/',
                'unique:commando_agents,whatsapp',
                'unique:users,phone',
            ],
            'password' => ['required', 'confirmed', Password::min(8)],
            'city' => ['required', 'string', 'max:100'],
        ];
    }

    protected function messages(): array
    {
        return [
            'whatsapp.unique'  => 'Ce numéro WhatsApp est déjà enregistré. Contactez l\'équipe MenuPro si c\'est le vôtre.',
            'whatsapp.regex'   => 'Numéro invalide. Format attendu : 07 00 00 00 00 ou +225 07 00 00 00 00.',
            'password.min'     => 'Le mot de passe doit contenir au moins 8 caractères.',
        ];
    }

    protected function validationAttributes(): array
    {
        return [
            'first_name' => 'prénom',
            'last_name' => 'nom',
            'whatsapp' => 'numéro WhatsApp',
            'password' => 'mot de passe',
            'city' => 'ville',
        ];
    }

    public function submit(): mixed
    {
        $this->validate();

        $key = 'commando-register:' . request()->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $this->addError('whatsapp', 'Trop de tentatives. Réessayez dans ' . RateLimiter::availableIn($key) . ' secondes.');
            return null;
        }
        RateLimiter::hit($key, 60);

        $whatsapp = preg_replace('/\s+/', '', $this->whatsapp);
        $hashedPassword = Hash::make($this->password);

        $user = DB::transaction(function () use ($whatsapp, $hashedPassword) {
            // 1. Créer le User unifié
            // `role` est dans $guarded → assignation explicite après create()
            $user = User::create([
                'name'              => trim($this->first_name . ' ' . $this->last_name),
                'email'             => $whatsapp . '@ambassadeur.menupro.ci',
                'phone'             => $whatsapp,
                'password'          => $hashedPassword,
                'is_active'         => false,
                'email_verified_at' => null,
            ]);
            $user->role = UserRole::COMMERCIAL;
            $user->save();

            // 2. Créer le CommercialProfile lié
            CommercialProfile::create([
                'user_id'             => $user->id,
                'city'                => $this->city,
                'verification_status' => AgentVerificationStatus::PENDING_REVIEW->value,
            ]);

            // 3. Créer le CommandoAgent pour rétrocompatibilité (QR card, etc.)
            CommandoAgent::create([
                'first_name'          => $this->first_name,
                'last_name'           => $this->last_name,
                'whatsapp'            => $whatsapp,
                'password'            => $hashedPassword,
                'city'                => $this->city,
                'status_verification' => AgentVerificationStatus::PENDING_REVIEW,
                'user_id'             => $user->id,
            ]);

            return $user;
        });

        // 4. Connecter automatiquement l'utilisateur
        Auth::login($user);

        // 5. Rediriger vers la page de confirmation
        return redirect()->route('commando.register.success');
    }

    public function render()
    {
        return view('livewire.commando.register-step1')
            ->layout('layouts.commando-public');
    }
}
