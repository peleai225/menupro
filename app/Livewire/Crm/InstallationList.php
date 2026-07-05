<?php

namespace App\Livewire\Crm;

use App\Enums\Crm\InstallationStatus;
use App\Models\Crm\Installation;
use App\Models\User;
use App\Services\Crm\InstallationService;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

class InstallationList extends Component
{
    public string $statusFilter = '';
    public string $technicianFilter = '';
    public string $dateFilter = 'all';

    public ?int $activeInstallationId = null;
    public ?int $ratingValue = null;
    public string $notes = '';

    public ?int $reassignInstallationId = null;
    public ?int $reassignTechnicianId = null;

    #[On('installation-updated')]
    public function refresh(): void
    {
        unset($this->installations, $this->stats);
    }

    public function setStatusFilter(string $status): void
    {
        $this->statusFilter = $status;
        unset($this->installations);
    }

    #[Computed]
    public function stats(): array
    {
        $baseQuery = $this->baseQuery();

        return [
            'planifiee' => (clone $baseQuery)->where('status', InstallationStatus::PLANIFIEE)->count(),
            'en_cours' => (clone $baseQuery)->where('status', InstallationStatus::EN_COURS)->count(),
            'terminee' => (clone $baseQuery)->where('status', InstallationStatus::TERMINEE)->count(),
            'probleme' => (clone $baseQuery)->where('status', InstallationStatus::PROBLEME)->count(),
            'today' => (clone $baseQuery)->whereDate('scheduled_at', today())->count(),
        ];
    }

    #[Computed]
    public function installations()
    {
        $query = $this->baseQuery()->with(['lead', 'technician', 'restaurant']);

        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }

        if ($this->technicianFilter) {
            $query->where('technician_id', $this->technicianFilter);
        }

        if ($this->dateFilter === 'today') {
            $query->whereDate('scheduled_at', today());
        } elseif ($this->dateFilter === 'week') {
            $query->whereBetween('scheduled_at', [now()->startOfWeek(), now()->endOfWeek()]);
        } elseif ($this->dateFilter === 'overdue') {
            $query->where('status', InstallationStatus::PLANIFIEE)
                ->where('scheduled_at', '<', now());
        }

        return $query->orderByRaw("FIELD(status, 'en_cours', 'planifiee', 'probleme', 'terminee', 'annulee')")
            ->orderBy('scheduled_at')
            ->limit(50)
            ->get();
    }

    #[Computed]
    public function technicians()
    {
        return User::where('role', 'technician')
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    public function startInstallation(int $id): void
    {
        $installation = $this->findAuthorized($id);
        if (!$installation) return;

        if ($installation->status !== InstallationStatus::PLANIFIEE) {
            $this->dispatch('toast', message: 'Seules les installations planifiées peuvent être démarrées', type: 'error');
            return;
        }

        $installation->start();
        $this->dispatch('toast', message: 'Installation démarrée', type: 'success');
        $this->dispatch('installation-updated');
    }

    public function openCompleteModal(int $id): void
    {
        $this->activeInstallationId = $id;
        $this->ratingValue = null;
        $this->notes = '';
    }

    public function completeInstallation(InstallationService $service): void
    {
        if (!$this->activeInstallationId) return;

        $installation = $this->findAuthorized($this->activeInstallationId);
        if (!$installation) return;

        if ($installation->status !== InstallationStatus::EN_COURS) {
            $this->dispatch('toast', message: 'Seules les installations en cours peuvent être terminées', type: 'error');
            return;
        }

        $notes = $this->notes ?: null;
        $service->complete($installation, $this->ratingValue);

        if ($notes) {
            $installation->update(['notes' => trim(($installation->notes ?? '') . "\n" . $notes)]);
        }

        $this->activeInstallationId = null;
        $this->dispatch('toast', message: 'Installation terminée avec succès !', type: 'success');
        $this->dispatch('installation-updated');
    }

    public function openReassignModal(int $id): void
    {
        $user = auth()->user();
        if (!in_array($user->role->value, ['super_admin', 'team_leader'])) {
            $this->dispatch('toast', message: 'Action non autorisée', type: 'error');
            return;
        }
        $this->reassignInstallationId = $id;
        $this->reassignTechnicianId = null;
    }

    public function confirmReassign(InstallationService $service): void
    {
        if (!$this->reassignInstallationId || !$this->reassignTechnicianId) return;

        $user = auth()->user();
        if (!in_array($user->role->value, ['super_admin', 'team_leader'])) {
            $this->dispatch('toast', message: 'Action non autorisée', type: 'error');
            return;
        }

        $installation = Installation::findOrFail($this->reassignInstallationId);
        $technician = User::findOrFail($this->reassignTechnicianId);

        $service->assignTechnician($installation, $technician);

        $this->reassignInstallationId = null;
        $this->reassignTechnicianId = null;
        $this->dispatch('toast', message: "Réassigné à {$technician->name}", type: 'success');
        $this->dispatch('installation-updated');
    }

    public function reportProblem(int $id): void
    {
        $installation = $this->findAuthorized($id);
        if (!$installation) return;

        $installation->update(['status' => InstallationStatus::PROBLEME]);
        $this->dispatch('toast', message: 'Problème signalé', type: 'warning');
        $this->dispatch('installation-updated');
    }

    public function cancelInstallation(int $id): void
    {
        $user = auth()->user();
        if ($user->role->value !== 'super_admin' && $user->role->value !== 'team_leader') {
            $this->dispatch('toast', message: 'Action non autorisée', type: 'error');
            return;
        }

        $installation = Installation::findOrFail($id);
        $installation->update(['status' => InstallationStatus::ANNULEE]);
        $this->dispatch('toast', message: 'Installation annulée', type: 'success');
        $this->dispatch('installation-updated');
    }

    public function reschedule(int $id): void
    {
        $user = auth()->user();
        if (!in_array($user->role->value, ['super_admin', 'team_leader'])) {
            $this->dispatch('toast', message: 'Action non autorisée', type: 'error');
            return;
        }

        $installation = Installation::findOrFail($id);
        if ($installation->status === InstallationStatus::PROBLEME) {
            $installation->update(['status' => InstallationStatus::PLANIFIEE]);
            $this->dispatch('toast', message: 'Installation replanifiée', type: 'success');
            $this->dispatch('installation-updated');
        }
    }

    private function baseQuery()
    {
        $user = auth()->user();
        $query = Installation::query();

        if ($user->role->value === 'technician') {
            $query->forTechnician($user->id);
        } elseif ($user->role->value === 'team_leader') {
            $teamIds = $user->ledTeams()->pluck('id');
            $query->whereHas('lead', fn ($q) => $q->whereIn('team_id', $teamIds));
        }

        return $query;
    }

    private function findAuthorized(int $id): ?Installation
    {
        $installation = Installation::find($id);
        if (!$installation) {
            $this->dispatch('toast', message: 'Installation introuvable', type: 'error');
            return null;
        }

        $user = auth()->user();
        $authorized = match ($user->role->value) {
            'super_admin' => true,
            'team_leader' => $user->ledTeams()->whereHas('leads', fn ($q) => $q->where('id', $installation->lead_id))->exists(),
            'technician' => $installation->technician_id === $user->id,
            default => false,
        };

        if (!$authorized) {
            $this->dispatch('toast', message: 'Action non autorisée', type: 'error');
            return null;
        }

        return $installation;
    }

    public function render()
    {
        return view('livewire.crm.installation-list');
    }
}
