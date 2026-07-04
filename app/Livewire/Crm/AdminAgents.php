<?php

namespace App\Livewire\Crm;

use App\Enums\Crm\Grade;
use App\Enums\UserRole;
use App\Models\Crm\CommercialProfile;
use App\Models\Crm\Lead;
use App\Models\Crm\Team;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class AdminAgents extends Component
{
    use WithPagination;

    public string $search = '';
    public string $roleFilter = '';
    public string $cityFilter = '';
    public string $gradeFilter = '';
    public string $verificationFilter = '';
    public string $teamFilter = '';
    public string $sortBy = 'created_at';
    public string $sortDirection = 'desc';

    public function mount(): void
    {
        // Verify user is super_admin
        if (!auth()->user()->isSuperAdmin()) {
            abort(403, 'Accès non autorisé');
        }
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingRoleFilter(): void
    {
        $this->resetPage();
    }

    public function updatingCityFilter(): void
    {
        $this->resetPage();
    }

    public function updatingGradeFilter(): void
    {
        $this->resetPage();
    }

    public function updatingVerificationFilter(): void
    {
        $this->resetPage();
    }

    public function updatingTeamFilter(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->reset(['search', 'roleFilter', 'cityFilter', 'gradeFilter', 'verificationFilter', 'teamFilter']);
        $this->resetPage();
    }

    #[Computed]
    public function stats(): array
    {
        $crmRoles = [UserRole::COMMERCIAL, UserRole::TECHNICIAN, UserRole::TEAM_LEADER];

        $totalAgents = User::whereIn('role', $crmRoles)->count();

        $verified = CommercialProfile::where('verification_status', 'verified')->count();

        $pending = CommercialProfile::where('verification_status', 'pending')->count();

        $activeThisWeek = User::whereIn('role', $crmRoles)
            ->where('last_login_at', '>=', now()->startOfWeek())
            ->count();

        return [
            'total_agents' => $totalAgents,
            'verified' => $verified,
            'pending_verification' => $pending,
            'active_this_week' => $activeThisWeek,
        ];
    }

    #[Computed]
    public function agents()
    {
        $crmRoles = [UserRole::COMMERCIAL, UserRole::TECHNICIAN, UserRole::TEAM_LEADER];

        $query = User::query()
            ->whereIn('role', $crmRoles)
            ->with([
                'commercialProfile.team',
                'technicianProfile.team',
                'crmGrade',
                'crmLeadsAssigned' => fn($q) => $q->where('status', 'actif'),
            ]);

        // Search filter
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('email', 'like', '%' . $this->search . '%')
                    ->orWhere('phone', 'like', '%' . $this->search . '%');
            });
        }

        // Role filter
        if ($this->roleFilter) {
            $query->where('role', $this->roleFilter);
        }

        // City filter
        if ($this->cityFilter) {
            $query->whereHas('commercialProfile', function ($q) {
                $q->where('city', $this->cityFilter);
            });
        }

        // Grade filter
        if ($this->gradeFilter) {
            $query->whereHas('crmGrade', function ($q) {
                $q->where('current_grade', $this->gradeFilter);
            });
        }

        // Verification status filter
        if ($this->verificationFilter) {
            $query->whereHas('commercialProfile', function ($q) {
                $q->where('verification_status', $this->verificationFilter);
            });
        }

        // Team filter
        if ($this->teamFilter) {
            $query->where(function ($q) {
                $q->whereHas('commercialProfile', function ($subQ) {
                    $subQ->where('team_id', $this->teamFilter);
                })->orWhereHas('technicianProfile', function ($subQ) {
                    $subQ->where('team_id', $this->teamFilter);
                });
            });
        }

        // Sorting
        $query->orderBy($this->sortBy, $this->sortDirection);

        return $query->paginate(15);
    }

    #[Computed]
    public function cities(): array
    {
        return CommercialProfile::query()
            ->whereNotNull('city')
            ->distinct()
            ->pluck('city')
            ->toArray();
    }

    #[Computed]
    public function teams()
    {
        return Team::query()
            ->select('id', 'name')
            ->orderBy('name')
            ->get();
    }

    public function verifyAgent(int $userId): void
    {
        $user = User::findOrFail($userId);

        if ($user->commercialProfile) {
            $user->commercialProfile->update([
                'verification_status' => 'verified',
                'approved_at' => now(),
                'rejection_reason' => null,
            ]);

            session()->flash('message', 'Agent vérifié avec succès.');
        }

        unset($this->agents);
        unset($this->stats);
    }

    public function rejectAgent(int $userId, string $reason = 'Documents invalides'): void
    {
        $user = User::findOrFail($userId);

        if ($user->commercialProfile) {
            $user->commercialProfile->update([
                'verification_status' => 'rejected',
                'rejection_reason' => $reason,
                'approved_at' => null,
            ]);

            session()->flash('message', 'Agent rejeté.');
        }

        unset($this->agents);
        unset($this->stats);
    }

    public function assignToTeam(int $userId, int $teamId): void
    {
        $user = User::findOrFail($userId);

        if ($user->commercialProfile) {
            $user->commercialProfile->update(['team_id' => $teamId]);
        }

        if ($user->technicianProfile) {
            $user->technicianProfile->update(['team_id' => $teamId]);
        }

        session()->flash('message', 'Agent assigné à l\'équipe.');

        unset($this->agents);
    }

    public function changeRole(int $userId, string $newRole): void
    {
        $user = User::findOrFail($userId);

        $allowedRoles = ['commercial', 'technician', 'team_leader'];

        if (!in_array($newRole, $allowedRoles)) {
            session()->flash('error', 'Rôle invalide.');
            return;
        }

        $user->update(['role' => $newRole]);

        session()->flash('message', 'Rôle modifié avec succès.');

        unset($this->agents);
    }

    public function toggleActiveStatus(int $userId): void
    {
        $user = User::findOrFail($userId);
        $user->update(['is_active' => !$user->is_active]);

        session()->flash('message', $user->is_active ? 'Agent activé.' : 'Agent désactivé.');

        unset($this->agents);
    }

    public function render()
    {
        return view('livewire.crm.admin-agents');
    }
}
