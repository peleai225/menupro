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
            'mobile_money'          => ['required', 'string', 'max:20', 'regex:/^[0-9+]{8,15}$/'],
            'mobile_money_operator' => ['required', 'string', 'in:' . implode(',', array_column(MobileMoneyOperator::cases(), 'value'))],
            'email'                 => ['nullable', 'email', 'max:255'],
        ]);

        JekoSubMerchant::create([
            'restaurant_id'         => $restaurant->id,
            'status'                => JekoSubMerchantStatus::PENDING,
            'legal_name'            => $validated['legal_name'],
            'business_type'         => $validated['business_type'] ?? 'restaurant',
            'mobile_money'          => $validated['mobile_money'],
            'mobile_money_operator' => $validated['mobile_money_operator'],
            'email'                 => $validated['email'] ?? $restaurant->email,
        ]);

        // Notify all super admins
        User::superAdmins()->get()->each(
            fn (User $admin) => $admin->notify(new JekoOnboardingRequestNotification($restaurant))
        );

        return redirect()->route('restaurant.jeko.onboarding')
            ->with('success', 'Votre demande d\'intégration Jeko a été soumise. L\'équipe MenuPro va examiner votre dossier.');
    }
}
