<?php

namespace App\Livewire\Restaurant;

use Illuminate\Support\Facades\Hash;
use Livewire\Component;

class Profile extends Component
{
    public string $current_password = '';
    public string $new_password = '';
    public string $new_password_confirmation = '';

    public function changePassword(): void
    {
        $this->validate([
            'current_password'          => ['required', 'string'],
            'new_password'              => ['required', 'string', 'min:8', 'confirmed'],
            'new_password_confirmation' => ['required', 'string'],
        ], [
            'new_password.min'       => 'Le nouveau mot de passe doit contenir au moins 8 caractères.',
            'new_password.confirmed' => 'Les mots de passe ne correspondent pas.',
        ]);

        $user = auth()->user();

        if (!Hash::check($this->current_password, $user->password)) {
            $this->addError('current_password', 'Le mot de passe actuel est incorrect.');
            return;
        }

        $user->update(['password' => Hash::make($this->new_password)]);

        $this->reset(['current_password', 'new_password', 'new_password_confirmation']);
        session()->flash('success', 'Mot de passe modifié avec succès.');
    }

    public function render()
    {
        $restaurant = auth()->user()->restaurant;
        $subscription = $restaurant?->activeSubscription;

        return view('livewire.restaurant.profile')
            ->layout('components.layouts.admin-restaurant', [
                'title'        => 'Mon profil',
                'restaurant'   => $restaurant,
                'subscription' => $subscription,
            ]);
    }
}
