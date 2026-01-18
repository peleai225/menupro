<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class PasswordResetController extends Controller
{
    /**
     * Display the password reset link request view.
     */
    public function requestForm(): View
    {
        return view('pages.auth.forgot-password');
    }

    /**
     * Handle an incoming password reset link request.
     */
    public function sendResetLink(Request $request): RedirectResponse
    {
        // Rate limiting: max 3 requests per 15 minutes per IP
        $key = 'password-reset:' . $request->ip();
        $maxAttempts = 3;
        $decayMinutes = 15;

        if (\Illuminate\Support\Facades\RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            $seconds = \Illuminate\Support\Facades\RateLimiter::availableIn($key);
            return back()->withErrors([
                'email' => "Trop de tentatives. Veuillez réessayer dans " . ceil($seconds / 60) . " minute(s)."
            ]);
        }

        $request->validate([
            'email' => ['required', 'email'],
        ], [
            'email.required' => 'L\'adresse email est obligatoire.',
            'email.email' => 'L\'adresse email n\'est pas valide.',
        ]);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        // Increment rate limiter
        \Illuminate\Support\Facades\RateLimiter::hit($key, $decayMinutes * 60);

        // Translate status messages to French
        $messages = [
            Password::RESET_LINK_SENT => 'Un lien de réinitialisation a été envoyé à votre adresse email.',
            Password::INVALID_USER => 'Aucun compte n\'est associé à cette adresse email.',
        ];

        $message = $messages[$status] ?? 'Une erreur est survenue. Veuillez réessayer.';

        return $status == Password::RESET_LINK_SENT
            ? back()->with('status', $message)
            : back()->withInput($request->only('email'))
                ->withErrors(['email' => $message]);
    }

    /**
     * Display the password reset view.
     */
    public function resetForm(Request $request): View
    {
        return view('pages.auth.reset-password', ['request' => $request]);
    }

    /**
     * Handle an incoming new password request.
     */
    public function reset(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ], [
            'token.required' => 'Le token de réinitialisation est requis.',
            'email.required' => 'L\'adresse email est obligatoire.',
            'email.email' => 'L\'adresse email n\'est pas valide.',
            'password.required' => 'Le mot de passe est obligatoire.',
            'password.confirmed' => 'Les mots de passe ne correspondent pas.',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user) use ($request) {
                $user->forceFill([
                    'password' => Hash::make($request->password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        // Translate status messages to French
        $messages = [
            Password::PASSWORD_RESET => 'Votre mot de passe a été réinitialisé avec succès. Vous pouvez maintenant vous connecter.',
            Password::INVALID_TOKEN => 'Le lien de réinitialisation est invalide ou a expiré. Veuillez en demander un nouveau.',
            Password::INVALID_USER => 'Aucun compte n\'est associé à cette adresse email.',
        ];

        $message = $messages[$status] ?? 'Une erreur est survenue. Veuillez réessayer.';

        return $status == Password::PASSWORD_RESET
            ? redirect()->route('login')->with('success', $message)
            : back()->withInput($request->only('email'))
                ->withErrors(['email' => $message]);
    }
}

