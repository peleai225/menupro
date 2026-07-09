<?php

namespace App\Http\Controllers\Crm;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

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

    public function resetPassword(User $agent)
    {
        $newPassword = Str::random(8);

        $agent->update(['password' => Hash::make($newPassword)]);

        return back()->with('success', "Nouveau mot de passe de {$agent->name} : {$newPassword} — Communiquez-le lui via WhatsApp.");
    }
}
