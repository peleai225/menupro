<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\WhatsAppService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\View\View;

class OtpPasswordResetController extends Controller
{
    public function requestForm(): View
    {
        return view('pages.auth.forgot-password-otp');
    }

    public function sendOtp(Request $request): RedirectResponse
    {
        $request->validate([
            'phone' => ['required', 'string'],
        ], [
            'phone.required' => 'Le numéro WhatsApp est obligatoire.',
        ]);

        $key = 'otp-reset:' . $request->ip();
        if (RateLimiter::tooManyAttempts($key, 3)) {
            $seconds = RateLimiter::availableIn($key);
            return back()->withErrors([
                'phone' => 'Trop de tentatives. Réessayez dans ' . ceil($seconds / 60) . ' minute(s).',
            ]);
        }

        $phone = preg_replace('/\s+/', '', $request->phone);
        $user = User::where('phone', $phone)->first();

        // On ne révèle pas si le numéro existe ou non (sécurité)
        if ($user) {
            $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

            DB::table('password_reset_otps')->where('phone', $phone)->delete();
            DB::table('password_reset_otps')->insert([
                'phone'      => $phone,
                'otp'        => $otp,
                'expires_at' => now()->addMinutes(10),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            try {
                app(WhatsAppService::class)->sendOtp($phone, $otp);
            } catch (\Throwable $e) {
                \Log::error('OTP WhatsApp send failed: ' . $e->getMessage());
            }
        }

        RateLimiter::hit($key, 15 * 60);

        return redirect()->route('password.otp.verify.form')
            ->with('otp_phone', $phone)
            ->with('status', 'Un code a été envoyé sur votre WhatsApp si ce numéro est enregistré.');
    }

    public function verifyForm(Request $request): View
    {
        return view('pages.auth.otp-verify', [
            'phone' => session('otp_phone', $request->query('phone', '')),
        ]);
    }

    public function verifyOtp(Request $request): RedirectResponse
    {
        $request->validate([
            'phone'    => ['required', 'string'],
            'otp'      => ['required', 'string', 'size:6'],
            'password' => ['required', 'confirmed', 'min:8'],
        ], [
            'otp.required'        => 'Le code OTP est obligatoire.',
            'otp.size'            => 'Le code doit contenir 6 chiffres.',
            'password.required'   => 'Le mot de passe est obligatoire.',
            'password.confirmed'  => 'Les mots de passe ne correspondent pas.',
            'password.min'        => 'Le mot de passe doit contenir au moins 8 caractères.',
        ]);

        $phone = preg_replace('/\s+/', '', $request->phone);

        $record = DB::table('password_reset_otps')
            ->where('phone', $phone)
            ->where('otp', $request->otp)
            ->where('expires_at', '>', now())
            ->first();

        if (!$record) {
            return back()->withInput($request->only('phone'))->withErrors([
                'otp' => 'Code invalide ou expiré. Recommencez.',
            ]);
        }

        $user = User::where('phone', $phone)->first();
        if (!$user) {
            return back()->withErrors(['phone' => 'Numéro introuvable.']);
        }

        $user->forceFill([
            'password'       => Hash::make($request->password),
            'remember_token' => \Illuminate\Support\Str::random(60),
        ])->save();

        DB::table('password_reset_otps')->where('phone', $phone)->delete();

        return redirect()->route('login')
            ->with('success', 'Mot de passe réinitialisé avec succès. Connectez-vous.');
    }
}
