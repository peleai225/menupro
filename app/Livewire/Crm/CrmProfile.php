<?php

namespace App\Livewire\Crm;

use App\Models\Crm\CommercialProfile;
use App\Services\MediaUploader;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Livewire\Component;
use Livewire\WithFileUploads;

class CrmProfile extends Component
{
    use WithFileUploads;

    // Info perso
    public string $name = '';
    public string $phone = '';
    public string $city = '';
    public string $statut_metier = '';

    // Mot de passe
    public string $current_password = '';
    public string $new_password = '';
    public string $new_password_confirmation = '';

    // Photo
    public $photo = null;

    public bool $profileSaved = false;
    public bool $passwordSaved = false;

    public function mount(): void
    {
        $user = auth()->user();
        $this->name = $user->name;
        $this->phone = $user->phone ?? '';

        $profile = CommercialProfile::where('user_id', $user->id)->first();
        $this->city = $profile?->city ?? '';
        $this->statut_metier = $profile?->statut_metier ?? '';
    }

    public function saveProfile(): void
    {
        $this->validate([
            'name'          => ['required', 'string', 'max:150'],
            'phone'         => ['nullable', 'string', 'max:30'],
            'city'          => ['nullable', 'string', 'max:100'],
            'statut_metier' => ['nullable', 'string', 'max:100'],
        ], [], [
            'name'          => 'nom complet',
            'phone'         => 'téléphone',
            'city'          => 'ville',
            'statut_metier' => 'statut métier',
        ]);

        $user = auth()->user();
        $user->update([
            'name'  => trim($this->name),
            'phone' => trim($this->phone) ?: null,
        ]);

        CommercialProfile::where('user_id', $user->id)->update([
            'city'          => trim($this->city) ?: null,
            'statut_metier' => trim($this->statut_metier) ?: null,
        ]);

        $this->profileSaved = true;
        $this->dispatch('toast', message: 'Profil mis à jour.', type: 'success');
    }

    public function savePassword(): void
    {
        $this->validate([
            'current_password'      => ['required', 'string'],
            'new_password'          => ['required', 'confirmed', Password::min(6)],
        ], [], [
            'current_password' => 'mot de passe actuel',
            'new_password'     => 'nouveau mot de passe',
        ]);

        $user = auth()->user();

        if (!Hash::check($this->current_password, $user->password)) {
            $this->addError('current_password', 'Mot de passe actuel incorrect.');
            return;
        }

        $user->update(['password' => Hash::make($this->new_password)]);

        $this->reset('current_password', 'new_password', 'new_password_confirmation');
        $this->passwordSaved = true;
        $this->dispatch('toast', message: 'Mot de passe modifié.', type: 'success');
    }

    public function uploadPhoto(MediaUploader $uploader): void
    {
        $this->validate([
            'photo' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:3072'],
        ]);

        $user = auth()->user();
        $path = $uploader->upload($this->photo, 'crm/agents/' . $user->id . '/photo', [
            'width'           => 400,
            'height'          => 400,
            'maintain_aspect' => false,
        ]);

        $user->update(['avatar' => $path]);
        $this->photo = null;
        $this->dispatch('toast', message: 'Photo mise à jour.', type: 'success');
        $this->dispatch('$refresh');
    }

    public function render()
    {
        return view('livewire.crm.crm-profile', [
            'profile' => CommercialProfile::where('user_id', auth()->id())->first(),
        ]);
    }
}
