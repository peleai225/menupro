<?php

namespace App\Livewire\Commando;

use App\Models\Crm\CommercialProfile;
use App\Services\MediaUploader;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithFileUploads;

class RegisterStep2 extends Component
{
    use WithFileUploads;

    public string $statut_metier = '';
    public $id_document = null;

    protected function rules(): array
    {
        return [
            'statut_metier' => ['required', 'string', 'in:etudiant,auto_entrepreneur,salarie,autre'],
            'id_document'   => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ];
    }

    protected function validationAttributes(): array
    {
        return [
            'statut_metier' => 'statut professionnel',
            'id_document'   => 'pièce d\'identité',
        ];
    }

    protected function messages(): array
    {
        return [
            'id_document.required' => 'Veuillez sélectionner un fichier et attendre la fin du chargement avant d\'envoyer.',
            'id_document.mimes'    => 'Format non accepté. Utilisez une image (JPG, PNG) ou un PDF.',
            'id_document.max'      => 'Le fichier est trop lourd. Taille maximum : 5 Mo.',
        ];
    }

    public function mount(): void
    {
        $user = auth()->user();

        // Accessible uniquement pour les agents en attente de vérification
        $profile = $user?->commercialProfile;
        if (!$profile || !in_array($profile->verification_status, ['pending', 'rejected'])) {
            abort(403, 'Cette étape n\'est pas disponible pour votre compte.');
        }

        if ($profile->statut_metier) {
            $this->statut_metier = $profile->statut_metier;
        }
    }

    #[Computed]
    public function profile(): CommercialProfile
    {
        return auth()->user()->commercialProfile;
    }

    public function submit(MediaUploader $uploader): mixed
    {
        $this->validate();

        $profile = auth()->user()->commercialProfile;
        if (!$profile) return null;

        $userId = auth()->id();
        $folder = "crm/agents/{$userId}/id";
        $ext = strtolower($this->id_document->getClientOriginalExtension());

        if ($ext === 'pdf') {
            $path = $this->id_document->store($folder, 'public');
        } else {
            $path = $uploader->upload(
                $this->id_document,
                $folder,
                ['width' => 1200, 'height' => 1200]
            );
        }

        $profile->update([
            'statut_metier'       => $this->statut_metier,
            'id_document_path'    => $path,
            'verification_status' => 'pending',
            'rejection_reason'    => null,
        ]);

        return redirect()->route('commando.register.success');
    }

    public function render()
    {
        return view('livewire.commando.register-step2')
            ->layout('layouts.commando-public');
    }
}
