<?php

namespace App\Livewire\Restaurant;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Livewire\Attributes\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class Team extends Component
{
    use WithPagination;

    public bool $showInviteModal = false;
    public ?User $editingUser = null;

    // Invite form
    #[Rule('required|string|max:100')]
    public string $first_name = '';

    #[Rule('required|string|max:100')]
    public string $last_name = '';

    #[Rule('required|email|max:255|unique:users,email')]
    public string $email = '';

    #[Rule('nullable|string|max:20')]
    public ?string $phone = null;

    #[Rule('required|in:restaurant_admin,employee')]
    public string $role = 'employee';

    public string $search = '';

    public function mount(): void
    {
        //
    }

    public function openInviteModal(): void
    {
        $this->resetForm();
        $this->showInviteModal = true;
    }

    public function closeInviteModal(): void
    {
        $this->showInviteModal = false;
        $this->resetForm();
        $this->editingUser = null;
    }

    public function resetForm(): void
    {
        $this->first_name = '';
        $this->last_name = '';
        $this->email = '';
        $this->phone = null;
        $this->role = 'employee';
    }

    public function invite(): void
    {
        $this->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'required|email|max:255|unique:users,email' . ($this->editingUser ? ',' . $this->editingUser->id : ''),
            'phone' => 'nullable|string|max:20',
            'role' => 'required|in:restaurant_admin,employee',
        ]);

        $restaurant = auth()->user()->restaurant;

        if (!$restaurant) {
            session()->flash('error', 'Restaurant introuvable.');
            return;
        }

        // Check quota
        $currentPlan = $restaurant->currentPlan;
        $maxTeam = $currentPlan?->team_members ?? 1;
        $currentTeamCount = $restaurant->users()->count();

        if ($currentTeamCount >= $maxTeam && !$this->editingUser) {
            session()->flash('error', "Vous avez atteint la limite de {$maxTeam} membre(s) d'équipe pour votre plan.");
            return;
        }

        try {
            if ($this->editingUser) {
                // Update existing user
                $this->editingUser->update([
                    'first_name' => $this->first_name,
                    'last_name' => $this->last_name,
                    'email' => $this->email,
                    'phone' => $this->phone,
                    'role' => UserRole::from($this->role),
                ]);

                session()->flash('success', 'Membre d\'équipe mis à jour avec succès.');
            } else {
                // Create new user with temporary password
                $tempPassword = Str::random(12);

                $user = User::create([
                    'first_name' => $this->first_name,
                    'last_name' => $this->last_name,
                    'email' => $this->email,
                    'phone' => $this->phone,
                    'password' => Hash::make($tempPassword),
                    'role' => UserRole::from($this->role),
                    'restaurant_id' => $restaurant->id,
                    'email_verified_at' => null, // Require email verification
                ]);

                // TODO: Send invitation email with temp password
                // Mail::to($user->email)->send(new TeamInvitation($user, $tempPassword));

                session()->flash('success', "Invitation envoyée à {$user->email}. Un email avec les instructions de connexion a été envoyé.");
            }

            $this->closeInviteModal();
            $this->resetPage();
        } catch (\Exception $e) {
            session()->flash('error', 'Une erreur est survenue : ' . $e->getMessage());
        }
    }

    public function editUser(User $user): void
    {
        // Only allow editing employees, not the owner
        if ($user->id === auth()->id()) {
            session()->flash('error', 'Vous ne pouvez pas modifier votre propre compte depuis cette page.');
            return;
        }

        $this->editingUser = $user;
        $this->first_name = $user->first_name;
        $this->last_name = $user->last_name;
        $this->email = $user->email;
        $this->phone = $user->phone;
        $this->role = $user->role->value;
        $this->showInviteModal = true;
    }

    public function removeUser(User $user): void
    {
        // Prevent removing self
        if ($user->id === auth()->id()) {
            session()->flash('error', 'Vous ne pouvez pas supprimer votre propre compte.');
            return;
        }

        // Prevent removing the owner
        if ($user->isRestaurantAdmin() && $user->id === $user->restaurant->owner?->id) {
            session()->flash('error', 'Vous ne pouvez pas supprimer le propriétaire du restaurant.');
            return;
        }

        try {
            $user->delete();
            session()->flash('success', 'Membre d\'équipe supprimé avec succès.');
            $this->resetPage();
        } catch (\Exception $e) {
            session()->flash('error', 'Une erreur est survenue : ' . $e->getMessage());
        }
    }

    public function toggleActive(User $user): void
    {
        // Prevent deactivating self
        if ($user->id === auth()->id()) {
            session()->flash('error', 'Vous ne pouvez pas désactiver votre propre compte.');
            return;
        }

        try {
            $user->update(['is_active' => !$user->is_active]);
            session()->flash('success', 'Membre d\'équipe ' . ($user->is_active ? 'activé' : 'désactivé') . ' avec succès.');
        } catch (\Exception $e) {
            session()->flash('error', 'Une erreur est survenue : ' . $e->getMessage());
        }
    }

    public function render()
    {
        $restaurant = auth()->user()->restaurant;

        if (!$restaurant) {
            return view('livewire.restaurant.team')
                ->layout('components.layouts.admin-restaurant', [
                    'title' => 'Équipe',
                    'restaurant' => null,
                    'subscription' => null,
                ]);
        }

        $query = $restaurant->users();

        // Search
        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('first_name', 'like', '%' . $this->search . '%')
                  ->orWhere('last_name', 'like', '%' . $this->search . '%')
                  ->orWhere('email', 'like', '%' . $this->search . '%');
            });
        }

        $teamMembers = $query->latest()->paginate(15);

        $currentPlan = $restaurant->currentPlan;
        $maxTeam = $currentPlan?->team_members ?? 1;
        $currentTeamCount = $restaurant->users()->count();
        $canAddMore = $currentTeamCount < $maxTeam;

        $subscription = $restaurant->activeSubscription;

        return view('livewire.restaurant.team', [
            'teamMembers' => $teamMembers,
            'maxTeam' => $maxTeam,
            'currentTeamCount' => $currentTeamCount,
            'canAddMore' => $canAddMore,
        ])
            ->layout('components.layouts.admin-restaurant', [
                'title' => 'Équipe',
                'restaurant' => $restaurant,
                'subscription' => $subscription,
            ]);
    }
}

