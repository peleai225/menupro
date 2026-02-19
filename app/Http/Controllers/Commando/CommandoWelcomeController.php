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
        if (!$user || !$user->isCommandoAgent()) {
            return redirect()->route('login')->with('error', 'Lien expiré ou invalide.');
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
        if (!$user || !$user->isCommandoAgent()) {
            return redirect()->route('login')->with('error', 'Lien expiré ou invalide.');
        }

        $user->update([
            'password' => Hash::make($request->password),
            'welcome_token' => null,
        ]);

        return redirect()->route('login')
            ->with('success', 'Mot de passe défini. Connectez-vous avec votre email : ' . $user->email);
    }
}
