<?php

namespace App\Livewire\Crm;

use App\Enums\UserRole;
use App\Models\Crm\CommercialProfile;
use App\Models\Crm\Team;
use App\Models\Crm\TechnicianProfile;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Component;

class AdminTeams extends Component
{
    // Create / Edit team modal
    public bool $showTeamModal = false;
    public ?int $editingTeamId = null;
    public string $teamName = '';
    public string $teamZone = '';
    public string $teamLeaderId = '';
    public string $teamMonthlyTarget = '';
    public bool $teamIsActive = true;

    // Add member modal
    public bool $showMemberModal = false;
    public ?int $memberTeamId = null;
    public string $memberTeamName = '';
    public string $addMemberId = '';

    // Remove member confirm
    public bool $showRemoveMember = false;
    public ?int $removeMemberUserId = null;
    public ?int $removeMemberTeamId = null;

    // Delete team confirm
    public bool $showDeleteTeam = false;
    public ?int $deleteTeamId = null;

    public function mount(): void
    {
        if (!auth()->user()->isSuperAdmin()) {
            abort(403);
        }
    }

    #[Computed]
    public function teams()
    {
        return Team::query()
            ->with(['leader', 'members'])
            ->withCount([
                'leads as total_leads_count',
                'leads as converted_count' => fn ($q) => $q->where('status', 'actif')
                    ->where('converted_at', '>=', now()->startOfMonth()),
            ])
            ->orderByDesc('is_active')
            ->orderBy('name')
            ->get();
    }

    #[Computed]
    public function availableLeaders()
    {
        return User::whereIn('role', [UserRole::TEAM_LEADER, UserRole::COMMERCIAL])
            ->orderBy('name')
            ->get(['id', 'name', 'role']);
    }

    #[Computed]
    public function availableAgents()
    {
        if (!$this->memberTeamId) return collect();

        $existingMemberIds = DB::table('crm_team_members')
            ->where('team_id', $this->memberTeamId)
            ->pluck('user_id')
            ->toArray();

        return User::whereIn('role', [UserRole::COMMERCIAL, UserRole::TECHNICIAN, UserRole::TEAM_LEADER])
            ->whereNotIn('id', $existingMemberIds)
            ->orderBy('name')
            ->get(['id', 'name', 'role']);
    }

    public function openCreateTeam(): void
    {
        $this->editingTeamId = null;
        $this->teamName = '';
        $this->teamZone = '';
        $this->teamLeaderId = '';
        $this->teamMonthlyTarget = '10';
        $this->teamIsActive = true;
        $this->showTeamModal = true;
    }

    public function openEditTeam(int $teamId): void
    {
        $team = Team::findOrFail($teamId);
        $this->editingTeamId = $teamId;
        $this->teamName = $team->name;
        $this->teamZone = $team->zone ?? '';
        $this->teamLeaderId = (string) ($team->leader_id ?? '');
        $this->teamMonthlyTarget = (string) $team->monthly_target;
        $this->teamIsActive = $team->is_active;
        $this->showTeamModal = true;
    }

    public function saveTeam(): void
    {
        $this->validate([
            'teamName' => 'required|string|max:100',
            'teamZone' => 'nullable|string|max:100',
            'teamLeaderId' => 'nullable|exists:users,id',
            'teamMonthlyTarget' => 'required|integer|min:1|max:9999',
        ]);

        DB::transaction(function () {
            $data = [
                'name' => $this->teamName,
                'zone' => $this->teamZone ?: null,
                'leader_id' => $this->teamLeaderId ?: null,
                'monthly_target' => (int) $this->teamMonthlyTarget,
                'is_active' => $this->teamIsActive,
            ];

            if ($this->editingTeamId) {
                $team = Team::findOrFail($this->editingTeamId);
                $team->update($data);
            } else {
                $team = Team::create($data);
            }

            // If a leader is set, promote them to team_leader role and add to team
            if ($this->teamLeaderId) {
                $leader = User::find($this->teamLeaderId);
                if ($leader && $leader->role->value !== 'team_leader') {
                    $leader->update(['role' => 'team_leader']);
                }

                // Ensure leader is a member of this team
                DB::table('crm_team_members')->updateOrInsert(
                    ['team_id' => $team->id, 'user_id' => (int) $this->teamLeaderId],
                    ['role_in_team' => 'team_leader', 'joined_at' => now()]
                );

                // Sync CommercialProfile / TechnicianProfile team_id for leader
                CommercialProfile::where('user_id', $this->teamLeaderId)->update(['team_id' => $team->id]);
                TechnicianProfile::where('user_id', $this->teamLeaderId)->update(['team_id' => $team->id]);
            }
        });

        $this->showTeamModal = false;
        session()->flash('message', $this->editingTeamId ? "Équipe mise à jour." : "Équipe créée avec succès.");
        unset($this->teams);
    }

    public function openAddMember(int $teamId): void
    {
        $this->memberTeamId = $teamId;
        $this->memberTeamName = Team::find($teamId)?->name ?? '';
        $this->addMemberId = '';
        $this->showMemberModal = true;
        unset($this->availableAgents);
    }

    public function addMember(): void
    {
        $this->validate([
            'addMemberId' => 'required|exists:users,id',
        ]);

        $user = User::findOrFail($this->addMemberId);
        $team = Team::findOrFail($this->memberTeamId);

        DB::transaction(function () use ($user, $team) {
            $roleInTeam = match ($user->role->value) {
                'team_leader' => 'team_leader',
                'technician' => 'technician',
                default => 'commercial',
            };

            DB::table('crm_team_members')->updateOrInsert(
                ['team_id' => $team->id, 'user_id' => $user->id],
                ['role_in_team' => $roleInTeam, 'joined_at' => now()]
            );

            // Sync profile team_id
            if ($user->commercialProfile) {
                $user->commercialProfile->update(['team_id' => $team->id]);
            }
            if ($user->technicianProfile) {
                $user->technicianProfile->update(['team_id' => $team->id]);
            }
        });

        $this->addMemberId = '';
        session()->flash('message', "{$user->name} ajouté à {$team->name}.");
        unset($this->teams);
        unset($this->availableAgents);
    }

    public function confirmRemoveMember(int $userId, int $teamId): void
    {
        $this->removeMemberUserId = $userId;
        $this->removeMemberTeamId = $teamId;
        $this->showRemoveMember = true;
    }

    public function removeMember(): void
    {
        $user = User::findOrFail($this->removeMemberUserId);
        $team = Team::findOrFail($this->removeMemberTeamId);

        DB::transaction(function () use ($user, $team) {
            DB::table('crm_team_members')
                ->where('team_id', $team->id)
                ->where('user_id', $user->id)
                ->delete();

            if ($user->commercialProfile?->team_id === $team->id) {
                $user->commercialProfile->update(['team_id' => null]);
            }
            if ($user->technicianProfile?->team_id === $team->id) {
                $user->technicianProfile->update(['team_id' => null]);
            }

            // If leader removed, clear leader from team
            if ($team->leader_id === $user->id) {
                $team->update(['leader_id' => null]);
            }
        });

        $this->showRemoveMember = false;
        session()->flash('message', "{$user->name} retiré de l'équipe.");
        unset($this->teams);
    }

    public function confirmDeleteTeam(int $teamId): void
    {
        $this->deleteTeamId = $teamId;
        $this->showDeleteTeam = true;
    }

    public function deleteTeam(): void
    {
        $team = Team::findOrFail($this->deleteTeamId);

        DB::transaction(function () use ($team) {
            // Clear team_id from profiles
            CommercialProfile::where('team_id', $team->id)->update(['team_id' => null]);
            TechnicianProfile::where('team_id', $team->id)->update(['team_id' => null]);

            // Remove all members
            DB::table('crm_team_members')->where('team_id', $team->id)->delete();

            $team->delete();
        });

        $this->showDeleteTeam = false;
        session()->flash('message', "Équipe supprimée.");
        unset($this->teams);
    }

    public function render()
    {
        return view('livewire.crm.admin-teams');
    }
}
