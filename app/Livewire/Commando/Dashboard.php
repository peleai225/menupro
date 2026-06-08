<?php

namespace App\Livewire\Commando;

use App\Enums\CommissionTransactionType;
use App\Enums\CommissionTransactionStatus;
use App\Models\CommandoAgent;
use App\Models\CommandoCommissionTransaction;
use App\Models\User;
use App\Notifications\CommandoWithdrawalRequestNotification;
use App\Services\MediaUploader;
use Livewire\Component;
use Livewire\WithFileUploads;

class Dashboard extends Component
{
    use WithFileUploads;

    public $agent;

    public ?string $successMessage = null;

    public string $withdrawalAmount = '';
    public bool $showWithdrawalModal = false;

    public $photo = null;

    public string $profile_city = '';
    public string $profile_whatsapp = '';

    public function mount(): void
    {
        $user = auth()->user();
        $this->agent = $user->commandoAgent;

        if (!$this->agent) {
            abort(404, 'Profil agent introuvable.');
        }

        $this->profile_city = $this->agent->city ?? '';
        $this->profile_whatsapp = $this->agent->whatsapp ?? '';
    }

    public function refreshAgent(): void
    {
        $this->agent = $this->agent->fresh();
        $this->profile_city = $this->agent->city ?? '';
        $this->profile_whatsapp = $this->agent->whatsapp ?? '';
    }

    public function updateProfile(): void
    {
        $this->validate([
            'profile_city' => ['nullable', 'string', 'max:120'],
            'profile_whatsapp' => ['required', 'string', 'max:30'],
        ], [], [
            'profile_city' => 'ville',
            'profile_whatsapp' => 'WhatsApp',
        ]);

        $this->agent->update([
            'city' => trim($this->profile_city) ?: null,
            'whatsapp' => trim($this->profile_whatsapp),
        ]);
        $this->successMessage = 'Coordonnées mises à jour.';
        $this->refreshAgent();
    }

    public function requestWithdrawal(): void
    {
        $amount = (float) str_replace(',', '.', $this->withdrawalAmount);
        if ($amount < 1) {
            $this->addError('withdrawalAmount', 'Montant invalide.');
            return;
        }
        $amountCents = (int) round($amount * 100);
        if ($amountCents > $this->agent->balance_cents) {
            $this->addError('withdrawalAmount', 'Solde insuffisant.');
            return;
        }

        CommandoCommissionTransaction::create([
            'commando_agent_id' => $this->agent->id,
            'type' => CommissionTransactionType::WITHDRAWAL_REQUEST,
            'status' => CommissionTransactionStatus::PENDING,
            'amount_cents' => $amountCents,
            'description' => 'Demande de retrait',
        ]);

        $admins = User::superAdmins()->get();
        foreach ($admins as $admin) {
            $admin->notify(new CommandoWithdrawalRequestNotification($this->agent, $amountCents));
        }

        $this->showWithdrawalModal = false;
        $this->withdrawalAmount = '';
        $this->successMessage = 'Votre demande de retrait a bien été envoyée.';
        $this->refreshAgent();
    }

    public function uploadPhoto(MediaUploader $uploader): void
    {
        $this->validate([
            'photo' => ['required', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
        ]);

        $folder = 'commando/agents/' . $this->agent->id . '/photo';
        $path = $uploader->upload($this->photo, $folder, [
            'width' => 400,
            'height' => 400,
            'maintain_aspect' => false,
        ]);

        $this->agent->update(['photo_path' => $path]);
        $this->photo = null;
        $this->successMessage = 'Photo mise à jour.';
        $this->refreshAgent();
        $this->dispatch('photoUploaded');
    }

    public function getWalletHistoryProperty()
    {
        if (!$this->agent) {
            return collect();
        }
        return $this->agent->commissionTransactions()
            ->orderByDesc('created_at')
            ->limit(20)
            ->get();
    }

    public function getReferredRestaurantsProperty()
    {
        if (!$this->agent) {
            return collect();
        }
        return $this->agent->referredRestaurants()
            ->orderByDesc('created_at')
            ->limit(50)
            ->get();
    }

    public function render()
    {
        return view('livewire.commando.dashboard', [
            'walletHistory' => $this->walletHistory,
            'referredRestaurants' => $this->referredRestaurants,
        ])->layout('components.layouts.admin-commando', [
            'title' => 'Mon espace',
            'agent' => $this->agent,
        ]);
    }
}
