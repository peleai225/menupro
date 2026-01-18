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
use App\Models\User;
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
        protected MediaUploader $mediaUploader
    ) {}

    /**
     * Display the registration view.
     */
    public function create(): View
    {
        $plans = Plan::active()->ordered()->get();

        return view('pages.auth.register', compact('plans'));
    }

    /**
     * Handle an incoming registration request.
     */
    public function store(RegisterRequest $request): RedirectResponse
    {
        $plan = Plan::where('slug', $request->plan)->firstOrFail();

        try {
            DB::beginTransaction();

            // Create restaurant
            $restaurant = Restaurant::create([
                'name' => $request->restaurant_name,
                'type' => $request->restaurant_type ?? 'restaurant',
                'company_name' => $request->company_name,
                'rccm' => $request->rccm,
                'slug' => Str::slug($request->restaurant_name),
                'email' => $request->email,
                'phone' => $request->phone,
                'description' => $request->restaurant_description,
                'address' => $request->restaurant_address,
                'city' => $request->restaurant_city,
                'status' => RestaurantStatus::PENDING,
                'current_plan_id' => $plan->id,
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

            // Upload RCCM document (required)
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

            // Create subscription (pending until payment or validation)
            Subscription::create([
                'restaurant_id' => $restaurant->id,
                'plan_id' => $plan->id,
                'status' => SubscriptionStatus::PENDING,
                'starts_at' => now(),
                'ends_at' => now()->addDays($plan->duration_days),
                'amount_paid' => 0, // Will be updated after payment
            ]);

            DB::commit();

            // Fire registered event (sends email verification)
            event(new Registered($user));

            return redirect()->route('login')
                ->with('success', 'Votre compte a été créé avec succès ! Un email de vérification a été envoyé. Veuillez vérifier votre email avant de vous connecter. Votre restaurant sera activé après validation par notre équipe.');

        } catch (\Exception $e) {
            DB::rollBack();
            
            return back()
                ->withInput()
                ->with('error', 'Une erreur est survenue lors de l\'inscription. Veuillez réessayer.');
        }
    }
}

