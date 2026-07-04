<?php

namespace App\Http\Controllers\Crm;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class AgentController extends Controller
{
    public function show(User $agent)
    {
        // Load relationships
        $agent->load([
            'commercialProfile.team',
            'technicianProfile.team',
            'crmGrade',
            'crmLeadsAssigned',
            'crmInstallations',
            'crmCommissions',
            'crmWallet',
        ]);

        return view('pages.crm.agent-show', compact('agent'));
    }
}
