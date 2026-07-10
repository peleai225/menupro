<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Enums\UserRole;
use App\Models\ActivityLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function create(): View
    {
        return view('pages.auth.login');
    }

    public function adminCreate(): View
    {
        return view('pages.auth.admin-login');
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $request->user()->updateLastLogin();

        ActivityLog::log(
            'login',
            $request->user(),
            "Connexion de {$request->user()->name}",
            ['email' => $request->user()->email]
        );

        $isCrmAgent = in_array($request->user()->role, [
            UserRole::SUPER_ADMIN,
            UserRole::COMMERCIAL,
            UserRole::TECHNICIAN,
            UserRole::TEAM_LEADER,
            UserRole::COMMANDO_AGENT,
        ]);

        $route = $request->user()->getDashboardRoute();

        if ($isCrmAgent) {
            session()->flash('crm_login_success', true);
        }

        return redirect()->intended(route($route));
    }

    public function adminStore(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        if (! $request->user()->isSuperAdmin()) {
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()->withErrors(['login' => 'Accès refusé. Seul le super administrateur peut se connecter ici.'])->onlyInput('login');
        }

        $request->session()->regenerate();

        $request->user()->updateLastLogin();

        ActivityLog::log(
            'login',
            $request->user(),
            "Connexion super admin de {$request->user()->name}",
            ['email' => $request->user()->email, 'via' => 'admin_login']
        );

        return redirect()->intended(route('super-admin.dashboard'));
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

