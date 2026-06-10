<?php

namespace App\Livewire\Commando;

use App\Enums\AgentVerificationStatus;
use App\Models\CommandoAgent;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Component;

class RegisterStep1 extends Component
{
    public string $first_name = '';
    public string $last_name = '';
    public string $whatsapp = '';
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
                'regex:/^\+?[0-9\s\-]+$/',
                'unique:commando_agents,whatsapp',
            ],
            'city' => ['required', 'string', 'max:100'],
        ];
    }

    protected function messages(): array
    {
        return [
            'whatsapp.unique' => 'Ce numéro WhatsApp est déjà enregistré. Contactez l\'équipe MenuPro si c\'est le vôtre.',
        ];
    }

    protected function validationAttributes(): array
    {
        return [
            'first_name' => 'prénom',
            'last_name' => 'nom',
            'whatsapp' => 'numéro WhatsApp',
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

        $agent = CommandoAgent::create([
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'whatsapp' => preg_replace('/\s+/', '', $this->whatsapp),
            'city' => $this->city,
            'status_verification' => AgentVerificationStatus::PENDING_REVIEW,
        ]);

        return redirect()->route('commando.register.success');
    }

    public function render()
    {
        return view('livewire.commando.register-step1')
            ->layout('layouts.commando-public');
    }
}
