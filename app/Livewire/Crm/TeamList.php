<?php

namespace App\Livewire\Crm;

use App\Models\Crm\Team;
use Livewire\Attributes\Computed;
use Livewire\Component;

class TeamList extends Component
{
    #[Computed]
    public function teams()
    {
        $user = auth()->user();

        $query = Team::active()
            ->with(['leader', 'members'])
            ->withCount([
                'leads as total_leads_count',
                'leads as active_leads_count' => fn ($q) => $q->active(),
                'leads as converted_count' => fn ($q) => $q->where('status', 'actif')->where('converted_at', '>=', now()->startOfMonth()),
            ]);

        if ($user->role->value === 'team_leader') {
            $query->where('leader_id', $user->id);
        }

        return $query->orderBy('name')->get();
    }

    public function render()
    {
        return view('livewire.crm.team-list');
    }
}
