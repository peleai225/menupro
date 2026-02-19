<?php

namespace App\Http\Controllers\Commando;

use App\Http\Controllers\Controller;
use App\Models\CommandoAgent;
use Illuminate\View\View;

class AgentVerificationController extends Controller
{
    /**
     * Page publique de vérification d'un agent par UUID (scan QR).
     */
    public function show(string $uuid): View
    {
        $agent = CommandoAgent::where('uuid', $uuid)->first();

        if (!$agent) {
            return view('pages.commando.verify-agent', [
                'valid' => false,
                'message' => 'Agent introuvable.',
            ]);
        }

        // Log du scan QR (anti-fraude / statistiques)
        $agent->verifyScans()->create([
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        $valid = $agent->isValide();

        return view('pages.commando.verify-agent', [
            'valid' => $valid,
            'agent' => $valid ? $agent : null,
            'message' => $valid
                ? 'Cet agent MenuPro Commando est authentique.'
                : 'Agent invalide ou révoqué.',
        ]);
    }
}
