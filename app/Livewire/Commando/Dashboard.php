<?php

namespace App\Livewire\Commando;

use App\Enums\AgentVerificationStatus;
use App\Enums\CommissionTransactionType;
use App\Enums\CommissionTransactionStatus;
use App\Models\CommandoAgent;
use App\Models\CommandoCommissionTransaction;
use App\Models\User;
use App\Notifications\CommandoWithdrawalRequestNotification;
use App\Services\MediaUploader;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithFileUploads;

class Dashboard extends Component
{
    use WithFileUploads;

    public $agent;

    public ?string $successMessage = null;

    public string $withdrawalAmount = '';
    public string $withdrawalPhone = '';
    public string $withdrawalMethod = 'wave';
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
        $this->validate([
            'withdrawalAmount' => ['required'],
            'withdrawalPhone' => ['required', 'string', 'min:8', 'max:20'],
            'withdrawalMethod' => ['required', 'in:wave,orange_money,mtn_money'],
        ], [], [
            'withdrawalAmount' => 'montant',
            'withdrawalPhone' => 'numéro de paiement',
            'withdrawalMethod' => 'mode de paiement',
        ]);

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
            'meta' => [
                'payment_method' => $this->withdrawalMethod,
                'phone' => $this->withdrawalPhone,
            ],
        ]);

        $admins = User::superAdmins()->get();
        foreach ($admins as $admin) {
            $admin->notify(new CommandoWithdrawalRequestNotification($this->agent, $amountCents));
        }

        $this->showWithdrawalModal = false;
        $this->withdrawalAmount = '';
        $this->withdrawalPhone = '';
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

    #[Computed]
    public function leaderboard()
    {
        return CommandoAgent::with('user')
            ->where('status_verification', AgentVerificationStatus::VALIDE)
            ->whereNull('banned_at')
            ->withCount('referredRestaurants')
            ->orderByDesc('referred_restaurants_count')
            ->limit(10)
            ->get()
            ->map(fn($agent) => [
                'id'       => $agent->id,
                'name'     => $agent->full_name ?? trim(($agent->first_name ?? '') . ' ' . ($agent->last_name ?? '')),
                'badge_id' => $agent->badge_id,
                'city'     => $agent->city,
                'count'    => $agent->referred_restaurants_count,
                'grade'    => $agent->grade->label(),
                'is_me'    => $agent->id === $this->agent?->id,
            ]);
    }

    #[Computed]
    public function myRank(): int
    {
        if (!$this->agent) {
            return 0;
        }
        return CommandoAgent::where('status_verification', AgentVerificationStatus::VALIDE)
            ->whereNull('banned_at')
            ->withCount('referredRestaurants')
            ->havingRaw('referred_restaurants_count > ?', [$this->agent->referredRestaurants()->count()])
            ->count() + 1;
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
            ->with(['activeSubscription', 'currentPlan'])
            ->orderByDesc('created_at')
            ->limit(50)
            ->get()
            ->map(function ($restaurant) {
                $sub = $restaurant->activeSubscription;
                $isTrial = $sub && $sub->is_trial;
                $isTrulyActive = $restaurant->status->value === 'active' && $sub && $sub->isActive();

                return [
                    'id'                     => $restaurant->id,
                    'name'                   => $restaurant->name,
                    'status'                 => $restaurant->status->value,
                    'plan'                   => $restaurant->currentPlan?->name ?? 'Aucun plan',
                    'subscription_expires_at'=> $sub?->ends_at?->format('d/m/Y'),
                    'created_at'             => $restaurant->created_at->format('d/m/Y'),
                    'is_truly_active'        => $isTrulyActive,
                    'is_trial'               => $isTrial,
                ];
            });
    }

    public function getUnreadNotificationsCountProperty(): int
    {
        if (!auth()->user()) {
            return 0;
        }
        return auth()->user()->unreadNotifications()->count();
    }

    public function getRecentNotificationsProperty()
    {
        if (!auth()->user()) {
            return collect();
        }
        return auth()->user()->notifications()->latest()->limit(10)->get();
    }

    public function markAllNotificationsRead(): void
    {
        auth()->user()->unreadNotifications->markAsRead();
    }

    public function markNotificationRead(string $id): void
    {
        $notification = auth()->user()->notifications()->find($id);
        if ($notification) {
            $notification->markAsRead();
        }
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
