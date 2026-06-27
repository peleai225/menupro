<?php

namespace App\Http\Controllers\Auth;

use App\Enums\RestaurantStatus;
use App\Enums\SubscriptionStatus;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\Plan;
use App\Models\Restaurant;
use App\Models\Subscription;
use App\Models\SubscriptionAddon;
use App\Models\User;
use App\Services\JekoGateway;
use App\Services\MoneyFusionGateway;
use App\Services\MediaUploader;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;

class RegisterController extends Controller
{
    public function __construct(
        protected MediaUploader $mediaUploader,
        protected JekoGateway $jekoGateway,
        protected MoneyFusionGateway $moneyFusion,
    ) {}

    public function create(): View
    {
        $registrationsOpen = \App\Models\SystemSetting::get('registrations_open', true);
        if (!$registrationsOpen) {
            return view('pages.auth.registrations-closed');
        }

        $ref = request()->query('ref');
        if ($ref) {
            $agent = \App\Models\CommandoAgent::where('uuid', $ref)->valide()->first();
            if ($agent && !$agent->isBanni()) {
                session(['register_ref_agent' => $ref]);
            }
        }

        $planSlug = request()->query('plan', 'essentiel');

        $plan = Plan::where('slug', $planSlug)->where('is_active', true)->first();

        if (!$plan) {
            $plan = Plan::where('is_featured', true)->where('is_active', true)->first()
                ?? Plan::where('is_active', true)->orderBy('sort_order')->first();
        }

        $availablePlans = Plan::where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        return view('pages.auth.register', compact('plan', 'availablePlans'));
    }

    public function store(RegisterRequest $request): RedirectResponse
    {
        $planSlug = $request->input('plan', $request->query('plan', 'essentiel'));
        $plan = Plan::where('slug', $planSlug)->where('is_active', true)->first();
        if (!$plan) {
            $plan = Plan::where('is_featured', true)->where('is_active', true)->first()
                ?? Plan::where('is_active', true)->orderBy('sort_order')->first();
        }

        $billingPeriod = $request->billing_period ?? 'monthly';

        $priceCalculation = Subscription::calculatePriceWithDiscount($plan->price, $billingPeriod);

        $durationDays = match($billingPeriod) {
            'monthly' => 30,
            'quarterly' => 90,
            'semiannual' => 180,
            'annual' => 365,
            default => 30,
        };

        $addonsTotal = 0;
        if ($request->has('addons') && is_array($request->addons)) {
            $availableAddons = SubscriptionAddon::getAvailableAddons();
            $months = $durationDays / 30;
            foreach ($request->addons as $addonType) {
                if (isset($availableAddons[$addonType])) {
                    $addonData = $availableAddons[$addonType];
                    $addonsTotal += $addonData['price'] * $months;
                }
            }
        }

        $totalPrice = $priceCalculation['final_price'] + $addonsTotal;

        try {
            DB::beginTransaction();

            $trialDays = 7;
            $trialEndsAt = now()->addDays($trialDays);

            $referredByAgentId = null;
            if (session()->has('register_ref_agent')) {
                $refAgent = \App\Models\CommandoAgent::where('uuid', session('register_ref_agent'))->valide()->first();
                if ($refAgent && !$refAgent->isBanni()) {
                    $referredByAgentId = $refAgent->id;
                }
                session()->forget('register_ref_agent');
            }

            $restaurant = Restaurant::create([
                'name' => $request->restaurant_name,
                'type' => $request->restaurant_type ?? 'restaurant',
                'company_name' => $request->company_name,
                'rccm' => $request->rccm,
                'referred_by_agent_id' => $referredByAgentId,
                'slug' => Str::slug($request->restaurant_name),
                'email' => $request->email,
                'phone' => $request->phone,
                'description' => $request->restaurant_description,
                'address' => $request->restaurant_address,
                'city' => $request->restaurant_city,
                'status' => RestaurantStatus::ACTIVE,
                'current_plan_id' => $plan->id,
                'subscription_ends_at' => $trialEndsAt,
                'orders_blocked' => false,
            ]);

            if ($request->hasFile('logo')) {
                $restaurant->logo_path = $this->mediaUploader->uploadLogo(
                    $request->file('logo'),
                    $restaurant->id
                );
            }

            if ($request->hasFile('banner')) {
                $restaurant->banner_path = $this->mediaUploader->uploadBanner(
                    $request->file('banner'),
                    $restaurant->id
                );
            }

            if ($request->hasFile('rccm_document')) {
                $restaurant->rccm_document_path = $this->mediaUploader->upload(
                    $request->file('rccm_document'),
                    "restaurants/{$restaurant->id}/documents/rccm",
                    ['format' => $request->file('rccm_document')->getClientOriginalExtension()]
                );
            }

            $restaurant->save();

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'password' => Hash::make($request->password),
                'role' => UserRole::RESTAURANT_ADMIN,
                'restaurant_id' => $restaurant->id,
            ]);

            $subscription = Subscription::create([
                'restaurant_id' => $restaurant->id,
                'plan_id' => $plan->id,
                'status' => SubscriptionStatus::TRIAL,
                'is_trial' => true,
                'trial_days' => $trialDays,
                'starts_at' => now(),
                'ends_at' => $trialEndsAt,
                'amount_paid' => 0,
                'billing_period' => $billingPeriod,
                'discount_percentage' => 0,
            ]);

            DB::commit();

            event(new Registered($user));

            auth()->login($user);

            $user->notify(new \App\Notifications\TrialStartedNotification($subscription));

            return redirect()->route('restaurant.dashboard')
                ->with('success', "Bienvenue ! Votre essai gratuit de {$trialDays} jours a commencé. Profitez de toutes les fonctionnalités de MenuPro !");

        } catch (\Exception $e) {
            DB::rollBack();

            \Log::error('Registration error: ' . $e->getMessage(), [
                'exception' => $e,
                'request' => $request->except(['password']),
            ]);

            return back()
                ->withInput()
                ->with('error', 'Une erreur est survenue lors de l\'inscription. Veuillez réessayer.');
        }
    }

    public function paymentSuccess(Subscription $subscription): RedirectResponse
    {
        $user = auth()->user();
        if (!$user || !$user->belongsToRestaurant($subscription->restaurant_id)) {
            abort(403, 'Accès non autorisé à cet abonnement.');
        }

        $ref = $subscription->payment_reference;
        $gateway = $subscription->payment_metadata['gateway'] ?? 'jeko';

        if ($ref) {
            $paid = false;

            if ($gateway === 'moneyfusion' && $this->moneyFusion->isConfigured()) {
                $result = $this->moneyFusion->verifyPayment($ref);
                $paid = $result['success'] && ($result['paid'] ?? false);
            } else {
                $jeko = $this->jekoGateway->forPlatform();
                if ($jeko->isConfigured()) {
                    $result = $jeko->verifyPaymentLink($ref);
                    $paid = $result['success'] && ($result['paid'] ?? false);
                }
            }

            if (!$paid) {
                return redirect()->route('restaurant.dashboard')
                    ->with('error', 'Le paiement n\'a pas été confirmé. Veuillez réessayer depuis votre tableau de bord.');
            }
        }

        try {
            DB::beginTransaction();

            $subscription->update([
                'status' => SubscriptionStatus::ACTIVE,
                'payment_method' => $gateway,
            ]);

            $restaurant = $subscription->restaurant;
            $restaurant->update([
                'status' => RestaurantStatus::ACTIVE,
                'current_plan_id' => $subscription->plan_id,
                'subscription_ends_at' => $subscription->ends_at,
                'orders_blocked' => false,
            ]);

            DB::commit();

            return redirect()->route('restaurant.dashboard')
                ->with('success', 'Félicitations ! Votre abonnement a été activé avec succès !');

        } catch (\Exception $e) {
            DB::rollBack();

            \Log::error('Subscription activation error: ' . $e->getMessage(), [
                'exception' => $e,
                'subscription_id' => $subscription->id,
            ]);

            return redirect()->route('restaurant.dashboard')
                ->with('error', 'Une erreur est survenue lors de l\'activation. Veuillez contacter le support.');
        }
    }

    public function paymentCancel(Subscription $subscription): RedirectResponse
    {
        $user = auth()->user();
        if (!$user || !$user->belongsToRestaurant($subscription->restaurant_id)) {
            abort(403, 'Accès non autorisé à cet abonnement.');
        }

        return redirect()->route('restaurant.dashboard')
            ->with('info', 'Le paiement a été annulé. Votre compte est créé mais en attente de paiement. Vous pouvez compléter votre abonnement depuis votre tableau de bord.');
    }
}
