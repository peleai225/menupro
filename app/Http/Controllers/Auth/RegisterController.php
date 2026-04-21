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
use App\Services\LygosGateway;
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
        protected LygosGateway $lygosGateway
    ) {}

    /**
     * Display the registration view.
     */
    public function create(): View
    {
        // Check if registrations are open
        $registrationsOpen = \App\Models\SystemSetting::get('registrations_open', true);
        if (!$registrationsOpen) {
            return view('pages.auth.registrations-closed');
        }

        // Parrainage Commando : stocker ref en session si UUID agent valide
        $ref = request()->query('ref');
        if ($ref) {
            $agent = \App\Models\CommandoAgent::where('uuid', $ref)->valide()->first();
            if ($agent && !$agent->isBanni()) {
                session(['register_ref_agent' => $ref]);
            }
        }

        // Only get the MenuPro plan (unique plan)
        $plan = Plan::where('slug', 'menupro')->where('is_active', true)->first();

        return view('pages.auth.register', compact('plan'));
    }

    /**
     * Handle an incoming registration request.
     * Create account IMMEDIATELY with PENDING status, then redirect to payment.
     * Account will be ACTIVATED after successful payment.
     */
    public function store(RegisterRequest $request): RedirectResponse
    {
        // Get the MenuPro plan (unique plan)
        $plan = Plan::where('slug', 'menupro')->where('is_active', true)->firstOrFail();

        // Get billing period (default: monthly)
        $billingPeriod = $request->billing_period ?? 'monthly';
        
        // Calculate price with discount based on billing period
        $priceCalculation = Subscription::calculatePriceWithDiscount($plan->price, $billingPeriod);
        
        // Calculate duration based on billing period
        $durationDays = match($billingPeriod) {
            'monthly' => 30,
            'quarterly' => 90,
            'semiannual' => 180,
            'annual' => 365,
            default => 30,
        };

        // Calculate add-ons total price
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

        // Total price = base price (with discount) + add-ons
        $totalPrice = $priceCalculation['final_price'] + $addonsTotal;

        try {
            DB::beginTransaction();

            // CREATE ACCOUNT WITH 14-DAY FREE TRIAL
            $trialDays = 14;
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
                'status' => RestaurantStatus::ACTIVE, // ACTIVATED immediately for trial
                'current_plan_id' => $plan->id,
                'subscription_ends_at' => $trialEndsAt,
                'orders_blocked' => false, // Allow orders during trial
            ]);

            // Upload logo if provided
            if ($request->hasFile('logo')) {
                $restaurant->logo_path = $this->mediaUploader->uploadLogo(
                    $request->file('logo'),
                    $restaurant->id
                );
            }

            // Upload banner if provided
            if ($request->hasFile('banner')) {
                $restaurant->banner_path = $this->mediaUploader->uploadBanner(
                    $request->file('banner'),
                    $restaurant->id
                );
            }

            // Upload RCCM document (optional)
            if ($request->hasFile('rccm_document')) {
                $restaurant->rccm_document_path = $this->mediaUploader->upload(
                    $request->file('rccm_document'),
                    "restaurants/{$restaurant->id}/documents/rccm",
                    ['format' => $request->file('rccm_document')->getClientOriginalExtension()]
                );
            }

            $restaurant->save();

            // Create user
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'password' => Hash::make($request->password),
                'role' => UserRole::RESTAURANT_ADMIN,
                'restaurant_id' => $restaurant->id,
            ]);

            // Create FREE TRIAL subscription (14 days)
            $subscription = Subscription::create([
                'restaurant_id' => $restaurant->id,
                'plan_id' => $plan->id,
                'status' => SubscriptionStatus::TRIAL,
                'is_trial' => true,
                'trial_days' => $trialDays,
                'starts_at' => now(),
                'ends_at' => $trialEndsAt,
                'amount_paid' => 0, // Free trial
                'billing_period' => $billingPeriod,
                'discount_percentage' => 0,
            ]);

            // No add-ons during trial (can be added when converting to paid)

            DB::commit();

            // Fire registered event (sends email verification)
            event(new Registered($user));

            // Auto-login user
            auth()->login($user);

            // Send welcome email with trial information
            $user->notify(new \App\Notifications\TrialStartedNotification($subscription));

            // Redirect to dashboard with success message
            return redirect()->route('restaurant.dashboard')
                ->with('success', "🎉 Bienvenue ! Votre essai gratuit de {$trialDays} jours a commencé. Profitez de toutes les fonctionnalités de MenuPro !");

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

    /**
     * Handle successful payment callback - ACTIVATE ACCOUNT AFTER PAYMENT
     */
    public function paymentSuccess(Subscription $subscription): RedirectResponse
    {
        // Ensure the authenticated user owns this subscription (multi-tenant safety)
        $user = auth()->user();
        if (!$user || !$user->belongsToRestaurant($subscription->restaurant_id)) {
            abort(403, 'Accès non autorisé à cet abonnement.');
        }

        // Verify payment using subscription reference (order_id in Lygos)
        $subscriptionReference = 'SUB-' . $subscription->id . '-' . $subscription->created_at->format('Ymd');
        $platformApiKey = \App\Models\SystemSetting::get('lygos_api_key', '');
        
        if ($platformApiKey) {
            $result = $this->lygosGateway
                ->forPlatform()
                ->verifyPayment($subscriptionReference);

            if (!$result['success'] || !$result['paid']) {
                return redirect()->route('restaurant.dashboard')
                    ->with('error', 'Le paiement n\'a pas été confirmé. Veuillez réessayer depuis votre tableau de bord.');
            }
        }

        try {
            DB::beginTransaction();

            // Activate subscription
            $subscription->update([
                'status' => SubscriptionStatus::ACTIVE,
                'payment_method' => 'lygos',
            ]);

            // Activate restaurant
            $restaurant = $subscription->restaurant;
            $restaurant->update([
                'status' => RestaurantStatus::ACTIVE,
                'current_plan_id' => $subscription->plan_id,
                'subscription_ends_at' => $subscription->ends_at,
                'orders_blocked' => false,
            ]);

            DB::commit();

            return redirect()->route('restaurant.dashboard')
                ->with('success', 'Félicitations ! Votre abonnement a été activé avec succès ! Votre restaurant est maintenant opérationnel.');

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

    /**
     * Handle cancelled payment - KEEP ACCOUNT, USER CAN RETRY
     */
    public function paymentCancel(Subscription $subscription): RedirectResponse
    {
        // Ensure the authenticated user owns this subscription (multi-tenant safety)
        $user = auth()->user();
        if (!$user || !$user->belongsToRestaurant($subscription->restaurant_id)) {
            abort(403, 'Accès non autorisé à cet abonnement.');
        }

        // Keep account and subscription as PENDING
        // User can retry payment from dashboard
        return redirect()->route('restaurant.dashboard')
            ->with('info', 'Le paiement a été annulé. Votre compte est créé mais en attente de paiement. Vous pouvez compléter votre abonnement depuis votre tableau de bord.');
    }
}

