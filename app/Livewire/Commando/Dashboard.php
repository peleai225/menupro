<?php

namespace App\Livewire\Commando;

use App\Enums\CommissionTransactionType;
use App\Enums\DeploymentStatus;
use App\Models\CommandoAgent;
use App\Models\CommandoDeployment;
use App\Models\CommandoCommissionTransaction;
use App\Models\User;
use App\Notifications\CommandoWithdrawalRequestNotification;
use App\Services\MediaUploader;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class Dashboard extends Component
{
    use WithFileUploads, WithPagination;

    /** @var CommandoAgent */
    public $agent;

    public ?string $successMessage = null;

    /** Demande de retrait */
    public string $withdrawalAmount = '';
    public bool $showWithdrawalModal = false;

    /** Objectif mensuel (signatures) */
    public int $monthlyTarget = 10;

    /** CRM - Nouveau prospect */
    public string $deploy_restaurant_name = '';
    public string $deploy_manager_name = '';
    public string $deploy_phone = '';
    public ?float $deploy_lat = null;
    public ?float $deploy_lng = null;

    /** Photo de profil (upload) */
    public $photo = null;

    /** Profil modifiable (ville, WhatsApp) */
    public string $profile_city = '';
    public string $profile_whatsapp = '';

    protected $listeners = ['profileCompleted' => 'onProfileCompleted'];
    protected $queryString = [];

    protected function rules(): array
    {
        return [
            'withdrawalAmount' => ['nullable', 'numeric', 'min:1', 'max:10000000'],
            'deploy_restaurant_name' => ['required_with:deploy_restaurant_name', 'string', 'max:255'],
            'deploy_manager_name' => ['nullable', 'string', 'max:255'],
            'deploy_phone' => ['nullable', 'string', 'max:30'],
        ];
    }

    public function mount(): void
    {
        $user = auth()->user();
        $this->agent = $user->commandoAgent;

        if (!$this->agent) {
            abort(404, 'Profil agent introuvable.');
        }

        $this->monthlyTarget = (int) config('commando.monthly_target', 10);
    }

    public function onProfileCompleted(?string $message = null): void
    {
        $this->successMessage = $message ?? 'Profil complété. L\'équipe va vérifier votre pièce d\'identité.';
        $this->refreshAgent();
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
            'status' => \App\Enums\CommissionTransactionStatus::PENDING,
            'amount_cents' => $amountCents,
            'description' => 'Demande de retrait',
        ]);

        $admins = User::superAdmins()->get();
        foreach ($admins as $admin) {
            $admin->notify(new CommandoWithdrawalRequestNotification($this->agent, $amountCents));
        }

        $this->showWithdrawalModal = false;
        $this->withdrawalAmount = '';
        $this->successMessage = 'Votre demande de retrait a bien été envoyée. L\'équipe vous recontactera.';
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
        $this->successMessage = 'Photo de profil mise à jour.';
        $this->refreshAgent();
        $this->dispatch('photoUploaded');
    }

    public function addDeployment(): void
    {
        $this->validate([
            'deploy_restaurant_name' => ['required', 'string', 'max:255'],
            'deploy_manager_name' => ['nullable', 'string', 'max:255'],
            'deploy_phone' => ['nullable', 'string', 'max:30'],
        ]);

        CommandoDeployment::create([
            'commando_agent_id' => $this->agent->id,
            'restaurant_name' => $this->deploy_restaurant_name,
            'manager_name' => $this->deploy_manager_name ?: null,
            'phone' => $this->deploy_phone ?: null,
            'latitude' => $this->deploy_lat,
            'longitude' => $this->deploy_lng,
            'status' => DeploymentStatus::EN_NEGOCIATION,
        ]);

        $this->deploy_restaurant_name = '';
        $this->deploy_manager_name = '';
        $this->deploy_phone = '';
        $this->deploy_lat = null;
        $this->deploy_lng = null;
        $this->successMessage = 'Prospect enregistré.';
        $this->refreshAgent();
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

    public function getMonthlySignaturesCountProperty(): int
    {
        if (!$this->agent) {
            return 0;
        }
        return $this->agent->referredRestaurants()
            ->whereMonth('restaurants.created_at', now()->month)
            ->whereYear('restaurants.created_at', now()->year)
            ->count();
    }

    /**
     * Liste fusionnée : restaurants inscrits via le lien de parrainage (ACTIF) + prospects enregistrés à la main.
     * Ainsi, quand un resto s'inscrit via le lien, il apparaît automatiquement dans le déploiement opérationnel.
     */
    public function getDeploymentItemsProperty(): \Illuminate\Support\Collection
    {
        if (!$this->agent) {
            return collect();
        }
        $items = collect();

        // 1. Restaurants inscrits via le lien de parrainage (affichés en premier, statut ACTIF)
        foreach ($this->agent->referredRestaurants()->orderByDesc('created_at')->limit(50)->get() as $restaurant) {
            $items->push((object)[
                'name' => $restaurant->name,
                'subtitle' => $restaurant->email ?: $restaurant->phone ?: '—',
                'status' => 'actif',
                'is_referral' => true,
            ]);
        }

        // 2. Prospects enregistrés manuellement (déploiements) — on évite les doublons si un resto parrainé a aussi été saisi à la main
        $referralNames = $items->pluck('name')->map(fn ($n) => \Illuminate\Support\Str::lower($n))->flip();
        foreach ($this->agent->deployments()->orderByDesc('created_at')->limit(50)->get() as $d) {
            if ($referralNames->has(\Illuminate\Support\Str::lower($d->restaurant_name))) {
                continue; // déjà affiché comme parrainé
            }
            $items->push((object)[
                'name' => $d->restaurant_name,
                'subtitle' => $d->manager_name ?: $d->phone ?: '—',
                'subtitle_extra' => $d->phone && $d->manager_name ? $d->phone : null,
                'status' => $d->status->value,
                'is_referral' => false,
            ]);
        }

        return $items;
    }

    public function render()
    {
        return view('livewire.commando.dashboard', [
            'walletHistory' => $this->walletHistory,
            'monthlySignatures' => $this->monthlySignaturesCount,
            'deployments' => $this->agent
                ? $this->agent->deployments()->orderByDesc('created_at')->limit(50)->get()
                : collect(),
            'deploymentItems' => $this->deploymentItems,
        ])->layout('components.layouts.admin-commando', [
            'title' => 'Centre d\'opérations',
            'agent' => $this->agent,
        ]);
    }
}
