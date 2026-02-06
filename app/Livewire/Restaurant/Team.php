<?php

namespace App\Livewire\Restaurant;

use App\Enums\UserRole;
use App\Models\User;
use App\Notifications\TeamInvitationNotification;
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

        // Note: Plan unique - pas de restriction sur le nombre de membres d'équipe

        try {
            // Combiner prénom et nom en un seul champ 'name'
            $fullName = trim($this->first_name . ' ' . $this->last_name);

            if ($this->editingUser) {
                // Update existing user
                $this->editingUser->update([
                    'name' => $fullName,
                    'email' => $this->email,
                    'phone' => $this->phone,
                    'role' => UserRole::from($this->role),
                ]);

                session()->flash('success', 'Membre d\'équipe mis à jour avec succès.');
            } else {
                // Create new user with temporary password
                $tempPassword = Str::random(12);

                $user = User::create([
                    'name' => $fullName,
                    'email' => $this->email,
                    'phone' => $this->phone,
                    'password' => Hash::make($tempPassword),
                    'role' => UserRole::from($this->role),
                    'restaurant_id' => $restaurant->id,
                    'email_verified_at' => now(), // Auto-vérifier pour permettre la connexion immédiate
                ]);

                // Envoyer l'email d'invitation avec le mot de passe temporaire
                $user->notify(new TeamInvitationNotification($restaurant, $tempPassword));

                session()->flash('success', "Membre ajouté avec succès ! Un email d'invitation a été envoyé à {$user->email}.");
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
        
        // Séparer le nom complet en prénom et nom
        $nameParts = explode(' ', $user->name, 2);
        $this->first_name = $nameParts[0] ?? '';
        $this->last_name = $nameParts[1] ?? '';
        
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

    public function resendInvitation(User $user): void
    {
        // Prevent resending to self
        if ($user->id === auth()->id()) {
            session()->flash('error', 'Vous ne pouvez pas vous renvoyer une invitation.');
            return;
        }

        $restaurant = auth()->user()->restaurant;

        if (!$restaurant) {
            session()->flash('error', 'Restaurant introuvable.');
            return;
        }

        try {
            // Générer un nouveau mot de passe temporaire
            $tempPassword = Str::random(12);
            $user->update(['password' => Hash::make($tempPassword)]);

            // Renvoyer l'email d'invitation
            $user->notify(new TeamInvitationNotification($restaurant, $tempPassword));

            session()->flash('success', "Invitation renvoyée à {$user->email} avec un nouveau mot de passe.");
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
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('email', 'like', '%' . $this->search . '%');
            });
        }

        $teamMembers = $query->latest()->paginate(15);

        $currentTeamCount = $restaurant->users()->count();

        $subscription = $restaurant->activeSubscription;

        return view('livewire.restaurant.team', [
            'teamMembers' => $teamMembers,
            'currentTeamCount' => $currentTeamCount,
        ])
            ->layout('components.layouts.admin-restaurant', [
                'title' => 'Équipe',
                'restaurant' => $restaurant,
                'subscription' => $subscription,
            ]);
    }
}

