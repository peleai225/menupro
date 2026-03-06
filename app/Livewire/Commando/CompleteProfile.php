<?php

namespace App\Livewire\Commando;

use App\Enums\AgentVerificationStatus;
use App\Models\CommandoAgent;
use App\Services\MediaUploader;
use Livewire\Component;
use Livewire\WithFileUploads;

class CompleteProfile extends Component
{
    use WithFileUploads;

    public string $statut_metier = '';
    public $id_document = null;

    /** true si l'agent a été rejeté et resoumet une nouvelle pièce */
    public bool $isResubmit = false;

    protected function rules(): array
    {
        return [
            'statut_metier' => ['required', 'string', 'in:etudiant,auto_entrepreneur,salarie,autre'],
            'id_document' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ];
    }

    protected function validationAttributes(): array
    {
        return [
            'statut_metier' => 'statut',
            'id_document' => 'pièce d\'identité ou justificatif de domicile',
        ];
    }

    protected function messages(): array
    {
        return [
            'id_document.required' => 'Veuillez sélectionner un fichier (image ou PDF) et attendre la fin du chargement avant d\'envoyer.',
            'id_document.file' => 'Le fichier n\'a pas été reçu. Attendez la fin du chargement puis réessayez.',
            'id_document.max' => 'Le fichier est trop lourd. Taille maximum : 5 Mo.',
            'id_document.mimes' => 'Format non accepté. Utilisez une image (JPG, PNG) ou un PDF.',
        ];
    }

    public function mount(): void
    {
        $agent = auth()->user()->commandoAgent;
        if ($agent) {
            if ($agent->statut_metier) {
                $this->statut_metier = $agent->statut_metier;
            }
            $this->isResubmit = $agent->status_verification === AgentVerificationStatus::REJETE;
        }
    }

    public function submit(MediaUploader $uploader): mixed
    {
        $agent = auth()->user()->commandoAgent;
        if (!$agent) {
            return null;
        }

        $allowed = [AgentVerificationStatus::SHADOW, AgentVerificationStatus::REJETE];
        if (!in_array($agent->status_verification, $allowed, true)) {
            return null;
        }

        $this->validate();

        $folder = 'commando/agents/' . $agent->id . '/id';
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

        $wasRejected = $agent->status_verification === AgentVerificationStatus::REJETE;

        $agent->update([
            'statut_metier' => $this->statut_metier,
            'id_document_path' => $path,
            'status_verification' => AgentVerificationStatus::PENDING_REVIEW,
            'rejection_reason' => null,
        ]);

        $message = $wasRejected
            ? 'Nouvelle pièce envoyée. L\'équipe va la vérifier sous peu.'
            : 'Profil complété. L\'équipe va vérifier votre pièce d\'identité. Vous aurez accès à votre carte digitale après validation.';

        $this->dispatch('profileCompleted', $message);

        return null;
    }

    public function render()
    {
        return view('livewire.commando.complete-profile');
    }
}
