<?php

namespace App\Http\Controllers\Commando;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class CommandoWelcomeController extends Controller
{
    /**
     * Page "Bienvenue" pour que l'agent définisse son mot de passe (lien envoyé après approbation).
     */
    public function show(Request $request): View|RedirectResponse
    {
        $token = $request->query('token');
        if (!$token) {
            return redirect()->route('login')->with('error', 'Lien invalide.');
        }

        $user = User::where('welcome_token', $token)->first();
        if (!$user || !$this->isCrmAgent($user)) {
            return redirect()->route('login')->with('error', 'Lien expiré ou invalide.');
        }

        if ($user->welcome_token_expires_at && $user->welcome_token_expires_at->isPast()) {
            return redirect()->route('login')->with('error', 'Ce lien de bienvenue a expiré. Contactez votre administrateur.');
        }

        return view('pages.commando.welcome', ['user' => $user, 'token' => $token]);
    }

    /**
     * Enregistrer le mot de passe et rediriger vers la connexion.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => ['required', 'string'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user = User::where('welcome_token', $request->token)->first();
        if (!$user || !$this->isCrmAgent($user)) {
            return redirect()->route('login')->with('error', 'Lien expiré ou invalide.');
        }

        if ($user->welcome_token_expires_at && $user->welcome_token_expires_at->isPast()) {
            return redirect()->route('login')->with('error', 'Ce lien de bienvenue a expiré. Contactez votre administrateur.');
        }

        $user->update([
            'password' => Hash::make($request->password),
            'welcome_token' => null,
            'welcome_token_expires_at' => null,
        ]);

        $loginHint = $user->phone ?: $user->email;

        return redirect()->route('login')
            ->with('success', 'Mot de passe défini ! Connectez-vous avec : ' . $loginHint);
    }

    private function isCrmAgent(User $user): bool
    {
        $crmRoles = ['commercial', 'technician', 'team_leader', 'commando_agent'];
        return in_array($user->role->value, $crmRoles);
    }
}
