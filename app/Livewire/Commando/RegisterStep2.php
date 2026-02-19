<?php

namespace App\Livewire\Commando;

use App\Enums\AgentVerificationStatus;
use App\Models\CommandoAgent;
use App\Services\MediaUploader;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class RegisterStep2 extends Component
{
    use WithFileUploads;

    public ?CommandoAgent $agent = null;

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
            'id_document' => 'pièce d\'identité',
        ];
    }

    public function mount(CommandoAgent $agent): void
    {
        if ($agent->status_verification !== AgentVerificationStatus::SHADOW) {
            abort(403, 'Cette étape d\'inscription n\'est plus disponible.');
        }
        $this->agent = $agent;
    }

    public function submit(MediaUploader $uploader): mixed
    {
        $this->validate();

        $folder = 'commando/agents/' . $this->agent->id . '/id';
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

        $this->agent->update([
            'statut_metier' => $this->statut_metier,
            'id_document_path' => $path,
            'status_verification' => AgentVerificationStatus::PENDING_REVIEW,
        ]);

        return redirect()->route('commando.register.success');
    }

    public function render()
    {
        return view('livewire.commando.register-step2')
            ->layout('layouts.commando-public');
    }
}
