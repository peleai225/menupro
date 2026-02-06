<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\ActivityLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LoginController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('pages.auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        // Update last login
        $request->user()->updateLastLogin();

        // Log login activity
        ActivityLog::log(
            'login',
            $request->user(),
            "Connexion de {$request->user()->name}",
            ['email' => $request->user()->email]
        );

        // Check if email is verified
        if (!$request->user()->hasVerifiedEmail()) {
            $request->user()->sendEmailVerificationNotification();
            return redirect()->route('verification.notice')
                ->with('warning', 'Veuillez vérifier votre adresse email avant de continuer. Un nouveau lien de vérification a été envoyé.');
        }

        // Redirect based on role
        $route = $request->user()->getDashboardRoute();

        return redirect()->intended(route($route));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $user = $request->user();

        // Log logout activity before destroying session
        if ($user) {
            ActivityLog::log(
                'logout',
                $user,
                "Déconnexion de {$user->name}",
                ['email' => $user->email]
            );
        }

        auth()->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}

