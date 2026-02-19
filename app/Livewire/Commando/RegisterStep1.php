<?php

namespace App\Livewire\Commando;

use App\Enums\AgentVerificationStatus;
use App\Enums\UserRole;
use App\Models\CommandoAgent;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Component;

class RegisterStep1 extends Component
{
    public string $first_name = '';
    public string $last_name = '';
    public string $whatsapp = '';
    public string $city = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';

    protected function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'whatsapp' => [
                'required',
                'string',
                'max:20',
                'regex:/^\+?[0-9\s\-]+$/',
                'unique:commando_agents,whatsapp',
            ],
            'city' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }

    protected function validationAttributes(): array
    {
        return [
            'first_name' => 'prénom',
            'last_name' => 'nom',
            'whatsapp' => 'numéro WhatsApp',
            'city' => 'ville',
            'email' => 'adresse email (Gmail ou autre)',
            'password' => 'mot de passe',
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

        $user = User::create([
            'name' => trim($this->first_name . ' ' . $this->last_name),
            'email' => $this->email,
            'email_verified_at' => now(), // Pas de vérification email pour les agents Commando
            'password' => Hash::make($this->password),
            'role' => UserRole::COMMANDO_AGENT,
            'phone' => preg_replace('/\s+/', '', $this->whatsapp),
            'is_active' => true,
        ]);

        $agent = CommandoAgent::create([
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'whatsapp' => preg_replace('/\s+/', '', $this->whatsapp),
            'city' => $this->city,
            'status_verification' => AgentVerificationStatus::SHADOW,
            'user_id' => $user->id,
        ]);

        Auth::login($user);

        return redirect()->route('commando.dashboard')
            ->with('success', 'Compte créé. Complétez votre profil (pièce d\'identité) pour que l\'équipe valide votre accès à la carte digitale.');
    }

    public function render()
    {
        return view('livewire.commando.register-step1')
            ->layout('layouts.commando-public');
    }
}
