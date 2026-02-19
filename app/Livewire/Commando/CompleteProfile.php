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

    public function submit(MediaUploader $uploader): mixed
    {
        $agent = auth()->user()->commandoAgent;
        if (!$agent || $agent->status_verification !== AgentVerificationStatus::SHADOW) {
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

        $agent->update([
            'statut_metier' => $this->statut_metier,
            'id_document_path' => $path,
            'status_verification' => AgentVerificationStatus::PENDING_REVIEW,
        ]);

        $message = 'Profil complété. L\'équipe va vérifier votre pièce d\'identité. Vous aurez accès à votre carte digitale après validation.';
        $this->dispatch('profileCompleted', $message);
    }

    public function render()
    {
        return view('livewire.commando.complete-profile');
    }
}
