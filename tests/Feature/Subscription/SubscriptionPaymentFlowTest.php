<?php

namespace Tests\Feature\Subscription;

use App\Enums\RestaurantStatus;
use App\Enums\SubscriptionStatus;
use App\Models\LedgerEntry;
use App\Models\Restaurant;
use App\Models\Subscription;
use App\Models\SystemPaymentSetting;
use App\Models\User;
use Illuminate\Http\Client\Request as HttpRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use Tests\Feature\Plans\PricingGridTestCase;

/**
 * Parcours complets de paiement d'abonnement (plans Stand et Restaurant) avec l'API Jeko
 * simulée : choix plan/période/opérateur → initiation Jeko → redirection → retour
 * (succès / échec, avec ou sans session) → webhook signé → abonnement actif.
 */
class SubscriptionPaymentFlowTest extends PricingGridTestCase
{
    private const SECRET = 'whsec-test-jeko';

    private string $paymentsLog;

    protected function setUp(): void
    {
        parent::setUp();

        $setting = new SystemPaymentSetting([
            'gateway' => 'jeko_marketplace',
            'is_active' => true,
            'mode' => 'production',
            'merchant_id' => 'key-id-test',
            'config' => ['store_id' => 'store-test'],
        ]);
        $setting->setEncryptedApiKey('api-key-test');
        $setting->setEncryptedWebhookSecret(self::SECRET);
        $setting->save();

        $this->paymentsLog = storage_path('logs/test-payments-' . getmypid() . '.log');
        @unlink($this->paymentsLog);
        config(['logging.channels.payments' => ['driver' => 'single', 'path' => $this->paymentsLog]]);
    }

    protected function tearDown(): void
    {
        @unlink($this->paymentsLog);
        parent::tearDown();
    }

    // ─── Aides ──────────────────────────────────────────────────────────────

    private function fakeJekoOk(string $id = 'pr_123'): void
    {
        Http::fake([
            'api.jeko.africa/partner_api/payment_requests' => Http::response([
                'id' => $id,
                'redirectUrl' => "https://pay.jeko.africa/{$id}",
            ], 201),
        ]);
    }

    private function expiredRestaurant(string $slug, array $attributes = []): Restaurant
    {
        return $this->subscribedRestaurant($slug, array_merge([
            'status' => RestaurantStatus::EXPIRED,
            'orders_blocked' => true,
            'subscription_ends_at' => now()->subDays(3),
        ], $attributes), [
            'status' => SubscriptionStatus::EXPIRED,
            'starts_at' => now()->subDays(33),
            'ends_at' => now()->subDays(3),
        ]);
    }

    private function trialRestaurant(string $slug): Restaurant
    {
        return $this->subscribedRestaurant($slug, ['subscription_ends_at' => now()->addDays(12)], [
            'status' => SubscriptionStatus::TRIAL,
            'is_trial' => true,
            'amount_paid' => 0,
            'ends_at' => now()->addDays(12),
        ]);
    }

    private function pending(Restaurant $restaurant): ?Subscription
    {
        return Subscription::withoutGlobalScopes()->where('restaurant_id', $restaurant->id)
            ->where('status', SubscriptionStatus::PENDING)->latest('id')->first();
    }

    private function signedWebhook(array $payload): \Illuminate\Testing\TestResponse
    {
        $raw = json_encode($payload);

        return $this->call('POST', '/webhooks/jeko', [], [], [], [
            'HTTP_JEKO_SIGNATURE' => hash_hmac('sha256', $raw, self::SECRET),
            'CONTENT_TYPE' => 'application/json',
        ], $raw);
    }

    private function jekoPaymentWebhook(Subscription $subscription, string $status = 'success'): \Illuminate\Testing\TestResponse
    {
        return $this->signedWebhook([
            'event' => 'transaction.completed',
            'data' => [
                'id' => 'tx_' . $subscription->id,
                'status' => $status,
                'transactionType' => 'payment',
                'transactionDetails' => ['reference' => $subscription->payment_metadata['jeko_reference']],
            ],
        ]);
    }

    private function returnPath(Subscription $subscription, string $issue): string
    {
        return "/abonnement/paiement/{$subscription->id}/{$subscription->payment_metadata['return_token']}/{$issue}";
    }

    // ─── (a) Stand : paiement depuis l'espace restaurant ────────────────────

    public function test_stand_page_offers_every_mobile_money_operator_and_weekly_price(): void
    {
        $restaurant = $this->expiredRestaurant('stand');

        $response = $this->actingAs($this->adminOf($restaurant))->get(route('restaurant.subscription'));

        $response->assertOk()
            ->assertSee('data-payment-form', false)
            ->assertSee('name="billing_period" value="weekly"', false)
            ->assertSee('1 500 F');
        foreach (['wave', 'orange', 'mtn', 'moov'] as $operator) {
            $response->assertSee('name="jeko_operator" value="' . $operator . '"', false);
        }
    }

    public function test_stand_weekly_payment_with_orange_money_end_to_end(): void
    {
        $this->fakeJekoOk('pr_stand');
        $restaurant = $this->expiredRestaurant('stand');
        $owner = $this->adminOf($restaurant);

        $this->actingAs($owner)
            ->post(route('restaurant.subscription.change'), [
                'plan' => 'stand', 'billing_period' => 'weekly', 'jeko_operator' => 'orange',
            ])
            ->assertRedirect('https://pay.jeko.africa/pr_stand');

        $subscription = $this->pending($restaurant);
        $this->assertNotNull($subscription);
        $this->assertSame(1500, (int) $subscription->amount_paid);
        $this->assertSame('weekly', $subscription->billing_period);
        $this->assertSame('pr_stand', $subscription->payment_reference);

        Http::assertSent(function (HttpRequest $request) use ($subscription) {
            $data = $request->data();

            return $data['amountCents'] === 150000
                && $data['paymentDetails']['data']['paymentMethod'] === 'orange'
                && str_contains($data['paymentDetails']['data']['successUrl'], "/abonnement/paiement/{$subscription->id}/")
                && str_ends_with($data['paymentDetails']['data']['errorUrl'], '/echec');
        });

        // Retour Jeko AVANT le webhook, dans un navigateur sans session (appli Orange Money)
        auth()->logout();
        $this->get($this->returnPath($subscription, 'succes'))
            ->assertOk()->assertSee('Confirmation du paiement');

        // Webhook signé → abonnement actif, restaurant débloqué
        $this->jekoPaymentWebhook($subscription)->assertOk();

        $subscription->refresh();
        $restaurant->refresh();
        $this->assertSame(SubscriptionStatus::ACTIVE, $subscription->status);
        $this->assertSame(RestaurantStatus::ACTIVE, $restaurant->status);
        $this->assertFalse((bool) $restaurant->orders_blocked);
        $this->assertTrue($subscription->ends_at->between(now()->addDays(7)->subMinute(), now()->addDays(7)->addMinute()));

        $this->getJson("/abonnement/paiement/{$subscription->id}/{$subscription->payment_metadata['return_token']}/statut")
            ->assertOk()->assertJson(['state' => 'active']);
        $this->get($this->returnPath($subscription, 'succes'))->assertSee('Paiement confirmé');

        $this->assertDatabaseHas('ledger_entries', [
            'subscription_id' => $subscription->id,
            'type' => LedgerEntry::TYPE_SUBSCRIPTION_STAND,
            'gross_amount' => 1500,
        ]);
    }

    public function test_missing_operator_without_javascript_falls_back_to_wave_instead_of_crashing(): void
    {
        $this->fakeJekoOk();
        $restaurant = $this->expiredRestaurant('stand');

        // Alpine non chargé : champs cachés vides (avant : TypeError → erreur 500)
        $this->actingAs($this->adminOf($restaurant))
            ->post(route('restaurant.subscription.change'), [
                'plan' => 'stand', 'billing_period' => '', 'jeko_operator' => '', 'payment_gateway' => '',
            ])
            ->assertRedirect('https://pay.jeko.africa/pr_123');

        Http::assertSent(fn (HttpRequest $r) => $r->data()['paymentDetails']['data']['paymentMethod'] === 'wave'
            && $r->data()['amountCents'] === 500000);
    }

    public function test_double_tap_reuses_the_same_pending_subscription(): void
    {
        $this->fakeJekoOk();
        $restaurant = $this->expiredRestaurant('stand');
        $owner = $this->adminOf($restaurant);
        $payload = ['plan' => 'stand', 'billing_period' => 'monthly', 'jeko_operator' => 'wave'];

        $this->actingAs($owner)->post(route('restaurant.subscription.change'), $payload);
        $this->actingAs($owner)->post(route('restaurant.subscription.change'), array_merge($payload, ['jeko_operator' => 'moov']));

        $this->assertSame(1, Subscription::withoutGlobalScopes()->where('restaurant_id', $restaurant->id)
            ->where('status', SubscriptionStatus::PENDING)->count());
        $this->assertSame('moov', $this->pending($restaurant)->payment_metadata['jeko_operator']);
    }

    // ─── (a) Restaurant : essai → paiement ──────────────────────────────────

    public function test_restaurant_plan_trial_conversion_with_mtn_end_to_end(): void
    {
        $this->fakeJekoOk('pr_resto');
        $restaurant = $this->trialRestaurant('essentiel');
        $owner = $this->adminOf($restaurant);

        $this->actingAs($owner)->get(route('restaurant.subscription'))
            ->assertOk()->assertSee('name="jeko_operator" value="mtn"', false);

        $this->actingAs($owner)
            ->post(route('restaurant.subscription.convertTrial'), [
                'plan' => 'essentiel', 'billing_period' => 'monthly', 'jeko_operator' => 'mtn',
            ])
            ->assertRedirect('https://pay.jeko.africa/pr_resto');

        $subscription = $this->pending($restaurant);
        $this->assertSame(15000, (int) $subscription->amount_paid);

        $this->jekoPaymentWebhook($subscription)->assertOk();

        $this->assertSame(SubscriptionStatus::ACTIVE, $subscription->fresh()->status);
        $this->assertSame('essentiel', $restaurant->fresh()->currentPlan->slug);
        $this->assertFalse($restaurant->fresh()->isOnTrial());
        $this->assertDatabaseHas('ledger_entries', [
            'subscription_id' => $subscription->id,
            'type' => LedgerEntry::TYPE_SUBSCRIPTION_RESTAURANT,
            'gross_amount' => 15000,
        ]);

        // Ancien lien de retour authentifié : renvoie vers la page de retour publique
        $this->actingAs($owner)->get(route('restaurant.subscription.success', $subscription))
            ->assertRedirect(route('restaurant.subscription'));
    }

    public function test_failed_payment_return_explains_in_french_and_is_logged(): void
    {
        $this->fakeJekoOk();
        $restaurant = $this->trialRestaurant('essentiel');

        $this->actingAs($this->adminOf($restaurant))->post(route('restaurant.subscription.convertTrial'), [
            'plan' => 'essentiel', 'billing_period' => 'monthly', 'jeko_operator' => 'wave',
        ]);
        $subscription = $this->pending($restaurant);

        $this->get($this->returnPath($subscription, 'echec'))
            ->assertOk()
            ->assertSee('Le paiement n\'a pas abouti', false)
            ->assertSee('Réessayer le paiement');
        $this->assertStringContainsString('retour Jeko en échec', file_get_contents($this->paymentsLog));

        // Webhook d'échec → annulé, le restaurant peut relancer
        $this->jekoPaymentWebhook($subscription, 'failed')->assertOk();
        $this->assertSame(SubscriptionStatus::CANCELLED, $subscription->fresh()->status);
    }

    public function test_failure_of_an_older_payment_request_does_not_cancel_the_retry(): void
    {
        $this->fakeJekoOk();
        $restaurant = $this->expiredRestaurant('essentiel');
        $owner = $this->adminOf($restaurant);
        $this->actingAs($owner)->post(route('restaurant.subscription.change'), ['plan' => 'essentiel', 'jeko_operator' => 'wave']);
        $subscription = $this->pending($restaurant);
        $oldReference = $subscription->payment_metadata['jeko_reference'];

        $this->travel(2)->seconds();
        $this->actingAs($owner)->post(route('restaurant.subscription.retry', $subscription), ['jeko_operator' => 'orange']);
        $this->assertNotSame($oldReference, $subscription->fresh()->payment_metadata['jeko_reference']);

        $this->signedWebhook(['event' => 'transaction.completed', 'data' => [
            'id' => 'tx_old', 'status' => 'failed', 'transactionType' => 'payment',
            'transactionDetails' => ['reference' => $oldReference],
        ]])->assertOk();

        $this->assertSame(SubscriptionStatus::PENDING, $subscription->fresh()->status);
    }

    public function test_return_page_rejects_a_wrong_token(): void
    {
        $this->fakeJekoOk();
        $restaurant = $this->expiredRestaurant('stand');
        $this->actingAs($this->adminOf($restaurant))->post(route('restaurant.subscription.change'), ['plan' => 'stand']);
        $subscription = $this->pending($restaurant);

        $this->get("/abonnement/paiement/{$subscription->id}/mauvais-jeton/succes")->assertNotFound();
        $this->getJson("/abonnement/paiement/{$subscription->id}/mauvais-jeton/statut")->assertNotFound();
    }

    // ─── Échecs d'initiation : jamais muets ─────────────────────────────────

    public function test_jeko_refusal_shows_a_clear_message_logs_and_leaves_no_pending_subscription(): void
    {
        Http::fake(['api.jeko.africa/*' => Http::response(['message' => 'Invalid payment method'], 422)]);
        $restaurant = $this->expiredRestaurant('stand');

        $this->actingAs($this->adminOf($restaurant))
            ->post(route('restaurant.subscription.change'), ['plan' => 'stand', 'billing_period' => 'weekly', 'jeko_operator' => 'orange'])
            ->assertRedirect(route('restaurant.subscription'))
            ->assertSessionHas('error', fn ($message) => str_contains($message, 'Jeko n\'a pas pu préparer le paiement Orange Money')
                && str_contains($message, 'Invalid payment method'));

        $this->assertNull($this->pending($restaurant));
        $log = file_get_contents($this->paymentsLog);
        $this->assertStringContainsString("Échec d'initiation du paiement d'abonnement [jeko_refused]", $log);
        $this->assertStringContainsString('"operator":"orange"', $log);

        $this->followRedirects($this->actingAs($restaurant->users()->first())
            ->post(route('restaurant.subscription.change'), ['plan' => 'stand', 'jeko_operator' => 'orange']))
            ->assertSee('Jeko n&#039;a pas pu préparer le paiement', false);
    }

    public function test_jeko_unreachable_and_not_configured_are_explained(): void
    {
        $restaurant = $this->expiredRestaurant('stand');
        $owner = $this->adminOf($restaurant);

        Http::fake(fn () => throw new \Illuminate\Http\Client\ConnectionException('timeout'));
        $this->actingAs($owner)->post(route('restaurant.subscription.change'), ['plan' => 'stand'])
            ->assertSessionHas('error', fn ($m) => str_contains($m, 'ne répond pas'));

        SystemPaymentSetting::query()->update(['is_active' => false]);
        $this->actingAs($owner)->post(route('restaurant.subscription.change'), ['plan' => 'stand'])
            ->assertSessionHas('error', fn ($m) => str_contains($m, 'momentanément indisponible'));
        $this->assertStringContainsString('[jeko_not_configured]', file_get_contents($this->paymentsLog));
    }

    public function test_invalid_operator_is_rejected_with_a_french_message(): void
    {
        $restaurant = $this->expiredRestaurant('stand');

        $this->actingAs($this->adminOf($restaurant))
            ->post(route('restaurant.subscription.change'), ['plan' => 'stand', 'jeko_operator' => 'paypal'])
            ->assertRedirect(route('restaurant.subscription'))
            ->assertSessionHas('error', fn ($m) => str_contains($m, 'Choisissez votre opérateur Mobile Money'));
    }

    public function test_expired_csrf_token_redirects_back_to_subscription_with_a_message(): void
    {
        Route::middleware('web')->post('/_test/abonnement-419', fn () => throw new \Illuminate\Session\TokenMismatchException('CSRF token mismatch.'))
            ->name('restaurant.subscription.test419');
        $restaurant = $this->expiredRestaurant('stand');

        $this->actingAs($this->adminOf($restaurant))->post('/_test/abonnement-419')
            ->assertRedirect(route('restaurant.subscription'))
            ->assertSessionHas('error', fn ($m) => str_contains($m, 'session avait expiré'));
        $this->assertStringContainsString('session expirée (419)', file_get_contents($this->paymentsLog));
    }

    // ─── (c) Renouvellement : expiré, et anticipé ───────────────────────────

    public function test_blocked_restaurant_can_always_reach_the_payment_page_and_pay(): void
    {
        $this->fakeJekoOk('pr_renew');
        $restaurant = $this->expiredRestaurant('essentiel');
        $owner = $this->adminOf($restaurant);

        $this->actingAs($owner)->get(route('restaurant.dashboard'))->assertRedirect(route('restaurant.subscription'));
        $this->actingAs($owner)->get(route('restaurant.subscription'))->assertOk()->assertSee('Renouvelez pour réactiver');
        $this->actingAs($owner)->get(route('restaurant.subscription.plans'))->assertOk();

        $this->actingAs($owner)->post(route('restaurant.subscription.change'), [
            'plan' => 'essentiel', 'billing_period' => 'quarterly', 'jeko_operator' => 'moov',
        ])->assertRedirect('https://pay.jeko.africa/pr_renew');

        $this->jekoPaymentWebhook($this->pending($restaurant))->assertOk();

        $restaurant->refresh();
        $this->assertSame(RestaurantStatus::ACTIVE, $restaurant->status);
        $this->assertFalse((bool) $restaurant->orders_blocked);
        // Plus de renvoi vers l'abonnement (le tableau de bord utilise HOUR(), absent de SQLite)
        $this->assertNotSame(302, $this->actingAs($owner->fresh())->get(route('restaurant.dashboard'))->status());
    }

    public function test_early_renewal_keeps_the_remaining_days(): void
    {
        $this->fakeJekoOk();
        $restaurant = $this->subscribedRestaurant('stand', ['subscription_ends_at' => now()->addDays(5)], [
            'ends_at' => now()->addDays(5), 'payment_reference' => 'old', 'billing_period' => 'monthly',
        ]);
        $oldEnd = $restaurant->activeSubscription->ends_at;

        $this->actingAs($this->adminOf($restaurant))
            ->post(route('restaurant.subscription.change'), ['plan' => 'stand', 'billing_period' => 'monthly', 'jeko_operator' => 'wave']);
        $subscription = $this->pending($restaurant);
        $this->jekoPaymentWebhook($subscription)->assertOk();

        $this->assertEqualsWithDelta($oldEnd->copy()->addDays(30)->getTimestamp(), $subscription->fresh()->ends_at->getTimestamp(), 5);
        $this->assertEqualsWithDelta($oldEnd->copy()->addDays(30)->getTimestamp(), $restaurant->fresh()->subscription_ends_at->getTimestamp(), 5);
    }

    // ─── (b) Inscription → validation → paiement ────────────────────────────

    public function test_stand_registered_then_approved_can_pay_weekly_from_its_space(): void
    {
        $this->fakeJekoOk('pr_new');

        $this->post(route('register.post'), [
            'name' => 'Awa', 'restaurant_name' => 'Stand Awa', 'restaurant_type' => 'stand',
            'phone' => '0700000099', 'password' => 'password123', 'plan' => 'stand',
            'billing_period' => 'weekly', 'terms' => '1',
        ])->assertRedirect(route('restaurant.pending'));
        // Rien n'est payé à l'inscription : essai gelé jusqu'à la validation
        $restaurant = Restaurant::where('name', 'Stand Awa')->firstOrFail();
        $this->assertSame(0, Subscription::where('restaurant_id', $restaurant->id)->where('amount_paid', '>', 0)->count());
        auth()->logout();

        $admin = User::factory()->superAdmin()->create();
        $this->actingAs($admin)->post(route('super-admin.restaurants.approve', $restaurant));
        $owner = $restaurant->users()->first();
        $this->assertTrue($restaurant->fresh()->isOnTrial());

        $this->actingAs($owner)->post(route('restaurant.subscription.convertTrial'), [
            'plan' => 'stand', 'billing_period' => 'weekly', 'jeko_operator' => 'wave',
        ])->assertRedirect('https://pay.jeko.africa/pr_new');

        $this->jekoPaymentWebhook($this->pending($restaurant))->assertOk();
        $this->assertSame('stand', $restaurant->fresh()->currentPlan->slug);
        $this->assertFalse($restaurant->fresh()->isOnTrial());
    }

    // ─── Journal des erreurs navigateur ─────────────────────────────────────

    public function test_client_error_endpoint_logs_without_csrf_token(): void
    {
        $this->postJson(route('client-errors.store'), [
            'type' => 'js_error', 'message' => 'Alpine is not defined', 'page' => '/dashboard/abonnement',
            'context' => ['standalone' => true, 'width' => 360],
        ])->assertOk();

        $log = file_get_contents($this->paymentsLog);
        $this->assertStringContainsString('Erreur côté navigateur', $log);
        $this->assertStringContainsString('Alpine is not defined', $log);
    }
}
