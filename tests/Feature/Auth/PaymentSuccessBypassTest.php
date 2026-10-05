<?php

namespace Tests\Feature\Auth;

use App\Enums\RestaurantStatus;
use App\Enums\SubscriptionStatus;
use App\Models\Plan;
use App\Models\Restaurant;
use App\Models\Subscription;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentSuccessBypassTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_cannot_self_activate_frozen_trial_via_payment_success(): void
    {
        Plan::firstOrCreate(
            ['slug' => 'stand'],
            ['name' => 'Stand', 'price' => 5000, 'duration_days' => 30, 'is_active' => true]
        );

        $this->post(route('register.post'), [
            'name' => 'Koné',
            'restaurant_name' => 'Chez Koné',
            'restaurant_type' => 'restaurant',
            'phone' => '0700000099',
            'email' => 'kone-bypass@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'restaurant_city' => 'Bouaké',
            'plan' => 'stand',
            'terms' => '1',
        ]);

        $restaurant = Restaurant::where('email', 'kone-bypass@example.com')->firstOrFail();
        $subscription = Subscription::where('restaurant_id', $restaurant->id)->firstOrFail();

        // L'abonnement d'essai gelé n'a aucune référence de paiement (payment_reference null)
        $this->assertNull($subscription->payment_reference);

        // L'owner, déjà authentifié par l'inscription, tente d'activer directement
        // le callback de paiement — ne doit PAS court-circuiter la validation admin.
        $this->get(route('register.payment.success', $subscription));

        $restaurant->refresh();
        $subscription->refresh();

        $this->assertSame(RestaurantStatus::PENDING, $restaurant->status);
        $this->assertNotSame(SubscriptionStatus::ACTIVE, $subscription->status);
    }
}
