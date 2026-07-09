<?php

namespace App\Livewire\Crm;

use App\Enums\RestaurantStatus;
use App\Enums\SubscriptionStatus;
use App\Enums\UserRole;
use App\Enums\Crm\LeadStatus;
use App\Models\Crm\Lead;
use App\Models\Plan;
use App\Models\Restaurant;
use App\Models\Subscription;
use App\Models\User;
use App\Services\Crm\LeadPipelineService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Livewire\Attributes\On;
use Livewire\Component;

class LeadRegistration extends Component
{
    public bool $show = false;
    public ?int $leadId = null;

    // Champs restaurant
    public string $restaurant_name = '';
    public string $restaurant_type = 'restaurant';
    public string $phone = '';
    public string $email = '';
    public string $city = '';
    public string $address = '';

    // Champs compte
    public string $owner_name = '';
    public string $password = '';

    public bool $success = false;
    public ?string $registrationLink = null;

    #[On('open-lead-registration')]
    public function open(int $leadId): void
    {
        $lead = Lead::find($leadId);
        if (!$lead) return;

        $this->leadId = $leadId;

        // Pré-remplir depuis les infos du lead
        $this->restaurant_name = $lead->restaurant_name ?? '';
        $this->phone           = $lead->phone ?? '';
        $this->email           = $lead->email ?? '';
        $this->city            = $lead->city ?? '';
        $this->owner_name      = $lead->manager_name ?? '';
        $this->password        = '';
        $this->success         = false;
        $this->registrationLink = null;
        $this->show            = true;
    }

    public function close(): void
    {
        $this->reset();
        $this->show = false;
    }

    public function register(): void
    {
        $this->validate([
            'restaurant_name' => 'required|string|max:255',
            'restaurant_type' => 'required|in:restaurant,bar,brasserie,maquis,traiteur,cafe,food_truck,brunch,evenementiel',
            'phone'           => 'required|string|max:20',
            'email'           => 'required|email|unique:users,email|unique:restaurants,email',
            'owner_name'      => 'required|string|max:255',
            'password'        => 'required|string|min:6',
            'city'            => 'nullable|string|max:100',
        ]);

        $lead = Lead::findOrFail($this->leadId);

        // Vérifier que le lead n'a pas déjà un restaurant lié
        if ($lead->restaurant_id) {
            $this->addError('restaurant_name', 'Ce lead a déjà un restaurant inscrit.');
            return;
        }

        $plan = Plan::where('slug', $lead->subscription_plan?->value ?? 'essentiel')
            ->where('is_active', true)
            ->first()
            ?? Plan::where('is_active', true)->orderBy('sort_order')->first();

        DB::transaction(function () use ($lead, $plan) {
            $trialEndsAt = now()->addDays(7);

            $restaurant = Restaurant::create([
                'name'               => $this->restaurant_name,
                'type'               => $this->restaurant_type,
                'slug'               => Str::slug($this->restaurant_name) . '-' . Str::random(4),
                'email'              => $this->email,
                'phone'              => $this->phone,
                'address'            => $this->address,
                'city'               => $this->city,
                'status'             => RestaurantStatus::ACTIVE,
                'current_plan_id'    => $plan->id,
                'subscription_ends_at' => $trialEndsAt,
                'orders_blocked'     => false,
                'referred_by_user_id' => $lead->assigned_to,
            ]);

            $user = User::create([
                'name'       => $this->owner_name,
                'email'      => $this->email,
                'phone'      => $this->phone,
                'password'   => Hash::make($this->password),
                'role'       => UserRole::RESTAURANT_ADMIN,
                'restaurant_id' => $restaurant->id,
            ]);

            Subscription::create([
                'restaurant_id'      => $restaurant->id,
                'plan_id'            => $plan->id,
                'status'             => SubscriptionStatus::TRIAL,
                'is_trial'           => true,
                'trial_days'         => 7,
                'starts_at'          => now(),
                'ends_at'            => $trialEndsAt,
                'amount_paid'        => 0,
                'billing_period'     => 'monthly',
                'discount_percentage' => 0,
            ]);

            // Lier le restaurant au lead et avancer → SIGNATURE
            $lead->update(['restaurant_id' => $restaurant->id]);

            if ($lead->status->canTransitionTo(LeadStatus::SIGNATURE)) {
                app(LeadPipelineService::class)->changeStatus($lead, LeadStatus::SIGNATURE);
            }
        });

        $this->success = true;
        $this->dispatch('lead-updated');
    }

    public function generateLink(): void
    {
        $lead = Lead::findOrFail($this->leadId);

        if (!$lead->registration_token) {
            $token = Str::random(48);
            $lead->update(['registration_token' => $token]);
            $lead->refresh();
        }

        $this->registrationLink = route('register') . '?lead=' . $lead->registration_token;
    }

    public function viewingLeadPhone(): ?string
    {
        return $this->leadId ? Lead::find($this->leadId)?->phone : null;
    }

    public function render()
    {
        return view('livewire.crm.lead-registration');
    }
}
