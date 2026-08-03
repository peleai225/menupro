<?php

namespace App\Http\Controllers\Restaurant;

use App\Enums\JekoSubMerchantStatus;
use App\Enums\MobileMoneyOperator;
use App\Http\Controllers\Controller;
use App\Models\JekoSubMerchant;
use App\Models\User;
use App\Notifications\JekoOnboardingRequestNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class JekoOnboardingController extends Controller
{
    public function show(Request $request): View
    {
        $restaurant  = $request->user()->restaurant;
        $subMerchant = $restaurant->jekoSubMerchant;
        $operators   = MobileMoneyOperator::cases();

        return view('restaurant.jeko-onboarding', compact('restaurant', 'subMerchant', 'operators'));
    }

    public function submit(Request $request): RedirectResponse
    {
        $restaurant = $request->user()->restaurant;

        // Prevent duplicate requests
        if ($restaurant->jekoSubMerchant) {
            return back()->with('error', 'Vous avez déjà soumis une demande d\'intégration Jeko.');
        }

        $validated = $request->validate([
            'legal_name'            => ['required', 'string', 'max:255'],
            'business_type'         => ['nullable', 'string', 'max:100'],
            'mobile_money'          => ['required', 'string', 'max:20', 'regex:/^\+?[0-9]{8,15}$/'],
            'mobile_money_operator' => ['required', 'string', 'in:' . implode(',', array_column(MobileMoneyOperator::cases(), 'value'))],
            'email'                 => ['nullable', 'email', 'max:255'],
        ]);

        try {
            JekoSubMerchant::create([
                'restaurant_id'         => $restaurant->id,
                'status'                => JekoSubMerchantStatus::PENDING,
                'legal_name'            => $validated['legal_name'],
                'business_type'         => $validated['business_type'] ?? 'restaurant',
                'mobile_money'          => $this->normalizePhone($validated['mobile_money']),
                'mobile_money_operator' => $validated['mobile_money_operator'],
                'email'                 => $validated['email'] ?? $restaurant->email,
            ]);
        } catch (\Illuminate\Database\UniqueConstraintViolationException) {
            return back()->with('error', 'Vous avez déjà soumis une demande d\'intégration Jeko.');
        }

        // Notify all super admins
        User::superAdmins()->get()->each(
            fn (User $admin) => $admin->notify(new JekoOnboardingRequestNotification($restaurant))
        );

        return redirect()->route('restaurant.jeko.onboarding')
            ->with('success', 'Votre demande d\'intégration Jeko a été soumise. L\'équipe MenuPro va examiner votre dossier.');
    }

    // Normalise vers +225XXXXXXXXXX requis par l'API Jeko (10 chiffres après +225)
    private function normalizePhone(string $phone): string
    {
        $digits = preg_replace('/\D/', '', $phone);

        // Déjà avec indicatif 225 et 10 chiffres (format correct)
        if (str_starts_with($digits, '225') && strlen($digits) === 13) {
            return '+' . $digits;
        }

        // Indicatif 225 mais seulement 9 chiffres → ajouter 0
        if (str_starts_with($digits, '225') && strlen($digits) === 12) {
            return '+225' . '0' . substr($digits, 3);
        }

        // Numéro local avec 0 initial (10 chiffres)
        if (str_starts_with($digits, '0') && strlen($digits) === 10) {
            return '+225' . $digits;
        }

        // Numéro local sans 0 (9 chiffres) → ajouter 0
        if (strlen($digits) === 9) {
            return '+225' . '0' . $digits;
        }

        // Numéro local avec 0 déjà présent (10 chiffres)
        if (strlen($digits) === 10) {
            return '+225' . $digits;
        }

        // Fallback : ajouter +225 tel quel
        return '+225' . $digits;
    }
}
