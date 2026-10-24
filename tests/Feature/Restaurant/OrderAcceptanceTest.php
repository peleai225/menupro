<?php

namespace Tests\Feature\Restaurant;

use App\Contracts\SmsSender;
use App\Enums\DeliveryStatus;
use App\Enums\OrderRejectionReason;
use App\Enums\OrderStatus;
use App\Enums\OrderType;
use App\Enums\PaymentStatus;
use App\Jobs\AlertRestaurantNewOrder;
use App\Jobs\AssignDriverJob;
use App\Jobs\RemindRestaurantPendingOrder;
use App\Jobs\SendPushMessage;
use App\Listeners\NotifyCustomerOnOrderStatusChange;
use App\Livewire\Restaurant\Orders;
use App\Models\Customer;
use App\Models\Delivery;
use App\Models\Order;
use App\Models\PushToken;
use App\Models\Restaurant;
use App\Models\SystemSetting;
use App\Models\User;
use App\Notifications\Admin\RefundRequiredNotification;
use App\Notifications\Admin\RestaurantUnresponsiveNotification;
use App\Services\WalletService;
use App\Support\OrderActionLink;
use Illuminate\Events\CallQueuedListener;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request as HttpRequest;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\URL;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Phase 1 « plus jamais besoin d'appeler un restaurant » : acceptation obligatoire des commandes,
 * livreur appelé à l'acceptation, relances automatiques et liens signés des notifications.
 */
class OrderAcceptanceTest extends TestCase
{
    use RefreshDatabase;

    private Restaurant $restaurant;
    private User $owner;
    private User $admin;
    private Customer $customer;

    /** SMS « envoyés » (fournisseur factice) */
    private array $sms = [];

    protected function setUp(): void
    {
        parent::setUp();

        SystemSetting::set('firebase_project_id', 'menupro-test');
        SystemSetting::set('firebase_service_account_json', json_encode([
            'client_email' => 'fcm@menupro-test.iam.gserviceaccount.com',
            'private_key'  => 'inutilisée : le jeton OAuth est en cache',
        ]));
        Cache::put('fcm:access_token', 'fake-oauth-token', 3600);
        Http::fake(['fcm.googleapis.com/*' => Http::response(['name' => 'projects/x/messages/1'])]);

        // Firebase n'accepte que des liens https (comme en production)
        URL::forceRootUrl('https://www.menupro.ci');
        URL::forceScheme('https');

        $this->app->instance(SmsSender::class, new class($this->sms) implements SmsSender {
            public function __construct(private array &$sent) {}

            public function send(string $phone, string $message): bool
            {
                $this->sent[] = compact('phone', 'message');
                return true;
            }
        });

        Carbon::setTestNow('2026-10-24 12:00:00');

        $this->restaurant = Restaurant::factory()->create(['phone' => '+2250701020304']);
        $this->owner = User::factory()->restaurantAdmin($this->restaurant)->create();
        $this->admin = User::factory()->superAdmin()->create();
        PushToken::create(['user_id' => $this->owner->id, 'token' => 'token-gerant']);
        PushToken::create(['user_id' => $this->admin->id, 'token' => 'token-admin']);

        $clientUser = User::factory()->create();
        $this->customer = Customer::create(['user_id' => $clientUser->id, 'phone' => '+2250700000001', 'fcm_token' => 'token-client']);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    /**
     * Commande plateforme en livraison (comme Api\V1\Client\OrderController::store), avec sa course.
     */
    private function platformOrder(array $attributes = []): Order
    {
        $order = Order::factory()->create(array_merge([
            'restaurant_id'  => $this->restaurant->id,
            'customer_id'    => $this->customer->id,
            'source'         => 'platform_web',
            'type'           => OrderType::DELIVERY,
            'status'         => OrderStatus::PENDING_PAYMENT,
            'payment_method' => 'jeko',
            'delivery_address' => 'Quartier Commerce, Bouaké',
        ], $attributes));

        Delivery::factory()->create([
            'order_id'      => $order->id,
            'restaurant_id' => $this->restaurant->id,
            'status'        => DeliveryStatus::PENDING,
        ]);

        return $order->fresh();
    }

    /** Commande payée en ligne, donc « à accepter » */
    private function paidOrder(): Order
    {
        $order = $this->platformOrder();
        $order->markAsPaid(['method' => 'jeko', 'reference' => 'TX-1']);

        return $order->fresh();
    }

    /** Push FCM envoyés, par token */
    private function pushesTo(string $token): array
    {
        return collect(Http::recorded())
            ->map(fn ($pair) => $pair[0])
            ->filter(fn (HttpRequest $request) => str_contains($request->url(), 'messages:send')
                && $request['message']['token'] === $token)
            ->map(fn (HttpRequest $request) => $request['message'])
            ->values()
            ->all();
    }

    private function runReminder(Order $order, int $step): void
    {
        app()->call([new RemindRestaurantPendingOrder($order->id, $step), 'handle']);
    }

    // =========================================================================
    // Réception
    // =========================================================================

    public function test_paid_order_waits_for_acceptance_and_no_driver_is_called_yet(): void
    {
        Queue::fake();

        $order = $this->paidOrder();

        $this->assertSame(OrderStatus::PAID, $order->status);
        $this->assertTrue($order->isAwaitingAcceptance());
        Queue::assertPushed(AlertRestaurantNewOrder::class, fn ($job) => $job->orderId === $order->id);
        Queue::assertNotPushed(AssignDriverJob::class);
        Queue::assertPushed(RemindRestaurantPendingOrder::class, fn ($job) => $job->orderId === $order->id
            && $job->step === RemindRestaurantPendingOrder::STEP_PUSH
            && $job->delay->equalTo(now()->addMinutes(2)));
    }

    public function test_drivers_do_not_see_a_delivery_before_the_restaurant_accepts(): void
    {
        Queue::fake();
        $order = $this->paidOrder();

        $this->assertSame(0, Order::withoutGlobalScope('restaurant')->openToDrivers()->whereKey($order->id)->count());

        $order->accept(10);
        $this->travel(1)->minutes();

        $this->assertSame(1, Order::withoutGlobalScope('restaurant')->openToDrivers()->whereKey($order->id)->count());
    }

    // =========================================================================
    // Acceptation
    // =========================================================================

    public function test_accepting_confirms_the_order_and_calls_the_driver_near_the_end_of_preparation(): void
    {
        Queue::fake();
        $order = $this->paidOrder();

        $this->assertTrue($order->accept(30));

        $order->refresh();
        $this->assertSame(OrderStatus::CONFIRMED, $order->status);
        $this->assertSame(30, $order->prep_minutes);
        $this->assertTrue($order->accepted_at->equalTo(now()));
        $this->assertTrue($order->driver_search_at->equalTo(now()->addMinutes(20)));

        // 30 min de préparation − 10 min d'approche = livreurs appelés dans 20 min
        Queue::assertPushed(AssignDriverJob::class, fn ($job) => $job->deliveryId === $order->delivery->id
            && $job->delay->equalTo(now()->addMinutes(20)));

        // Client prévenu (push « Commande acceptée »)
        Queue::assertPushed(CallQueuedListener::class, fn ($job) => $job->class === NotifyCustomerOnOrderStatusChange::class);
    }

    public function test_default_preparation_is_15_minutes_and_driver_delay_is_at_least_one_minute(): void
    {
        Queue::fake();

        $default = $this->paidOrder();
        $default->accept();
        $this->assertSame(15, $default->fresh()->prep_minutes);
        Queue::assertPushed(AssignDriverJob::class, fn ($job) => $job->deliveryId === $default->delivery->id
            && $job->delay->equalTo(now()->addMinutes(5)));

        $quick = $this->paidOrder();
        $quick->accept(10);
        Queue::assertPushed(AssignDriverJob::class, fn ($job) => $job->deliveryId === $quick->delivery->id
            && $job->delay->equalTo(now()->addMinute()));
    }

    public function test_customer_receives_a_push_when_the_order_is_accepted(): void
    {
        // Le reste de la file tourne (écouteur « client prévenu ») ; la recherche de livreurs est hors sujet
        Queue::fake([AssignDriverJob::class]);
        $order = $this->paidOrder();
        Http::fake(['fcm.googleapis.com/*' => Http::response(['name' => 'projects/x/messages/1'])]);

        $order->accept(20);

        $pushes = $this->pushesTo('token-client');
        $this->assertNotEmpty($pushes);
        $last = end($pushes);
        $this->assertSame('Commande acceptée ✅', $last['notification']['title']);
        $this->assertStringContainsString('~20 min', $last['notification']['body']);
    }

    public function test_confirming_from_kanban_or_kitchen_counts_as_acceptance_with_default_time(): void
    {
        Queue::fake();
        $order = $this->paidOrder();

        $order->transitionTo(OrderStatus::CONFIRMED);

        $order->refresh();
        $this->assertNotNull($order->accepted_at);
        $this->assertSame(15, $order->prep_minutes);
        Queue::assertPushed(AssignDriverJob::class);
    }

    public function test_an_order_can_only_be_decided_once(): void
    {
        Queue::fake();
        $order = $this->paidOrder();

        $this->assertTrue($order->accept(15));
        $this->assertFalse(Order::find($order->id)->reject(OrderRejectionReason::TOO_BUSY));
        $this->assertFalse(Order::find($order->id)->accept(20));

        $this->assertSame(OrderStatus::CONFIRMED, $order->fresh()->status);
        $this->assertSame(15, $order->fresh()->prep_minutes);
    }

    public function test_restaurant_accepts_from_the_orders_screen(): void
    {
        Queue::fake();
        $order = $this->paidOrder();

        Livewire::actingAs($this->owner)
            ->test(Orders::class)
            ->assertSee('À accepter')
            ->call('acceptOrder', $order->id, 45);

        $this->assertSame(OrderStatus::CONFIRMED, $order->fresh()->status);
        $this->assertSame(45, $order->fresh()->prep_minutes);
    }

    // =========================================================================
    // Refus
    // =========================================================================

    public function test_rejecting_an_online_paid_order_cancels_it_flags_a_refund_and_alerts_admins(): void
    {
        Queue::fake();
        $order = $this->paidOrder();

        $this->assertTrue($order->reject(OrderRejectionReason::OUT_OF_STOCK, 'Plus de poulet'));

        $order->refresh();
        $this->assertSame(OrderStatus::CANCELLED, $order->status);
        $this->assertSame(OrderRejectionReason::OUT_OF_STOCK, $order->rejection_reason);
        $this->assertSame('Rupture de stock : Plus de poulet', $order->cancellation_reason);
        $this->assertNotNull($order->rejected_at);
        $this->assertSame('pending', $order->refund_status);
        // Pas de remboursement automatique
        $this->assertSame(PaymentStatus::COMPLETED, $order->payment_status);

        Queue::assertNotPushed(AssignDriverJob::class);
        $this->assertSame(DeliveryStatus::PENDING, $order->delivery->status);

        $this->assertSame(1, $this->admin->notifications()->where('type', RefundRequiredNotification::class)->count());
        Queue::assertPushed(SendPushMessage::class, fn ($job) => $job->notifiable->is($this->admin)
            && str_contains($job->message['title'], 'Remboursement à effectuer'));

        Queue::assertPushed(CallQueuedListener::class, fn ($job) => $job->class === NotifyCustomerOnOrderStatusChange::class);
    }

    public function test_customer_is_told_why_the_order_was_rejected(): void
    {
        $order = $this->paidOrder();
        Http::fake(['fcm.googleapis.com/*' => Http::response(['name' => 'projects/x/messages/1'])]);

        $order->reject(OrderRejectionReason::CLOSED);

        $pushes = $this->pushesTo('token-client');
        $last = end($pushes);
        $this->assertSame('Commande refusée ❌', $last['notification']['title']);
        $this->assertStringContainsString('le restaurant est fermé', $last['notification']['body']);
        $this->assertStringContainsString('remboursé', $last['notification']['body']);
    }

    public function test_rejecting_a_cash_order_needs_no_refund(): void
    {
        Queue::fake();
        $order = $this->platformOrder(['status' => OrderStatus::PAID, 'payment_method' => 'cash_on_delivery']);

        $this->assertTrue($order->reject(OrderRejectionReason::TOO_BUSY));

        $this->assertNull($order->fresh()->refund_status);
        $this->assertSame(0, $this->admin->notifications()->count());
        Queue::assertNotPushed(AssignDriverJob::class);
    }

    public function test_rejection_from_the_orders_screen_requires_a_reason(): void
    {
        Queue::fake();
        $order = $this->paidOrder();

        Livewire::actingAs($this->owner)
            ->test(Orders::class)
            ->call('openRejectModal', $order->id)
            ->call('rejectOrder')
            ->assertHasErrors('rejectReason')
            ->set('rejectReason', 'other')
            ->call('rejectOrder')
            ->assertHasErrors('rejectNote')
            ->set('rejectNote', 'Gaz coupé')
            ->call('rejectOrder')
            ->assertHasNoErrors();

        $this->assertSame('Autre motif : Gaz coupé', $order->fresh()->cancellation_reason);
    }

    // =========================================================================
    // Relances
    // =========================================================================

    public function test_reminders_escalate_at_2_3_and_5_minutes(): void
    {
        Queue::fake();
        $order = $this->paidOrder();

        // Trop tôt (file synchrone, rejeu…) : rien
        $this->travel(1)->minutes();
        $this->runReminder($order, RemindRestaurantPendingOrder::STEP_PUSH);
        Queue::assertNotPushed(SendPushMessage::class);

        // T+2 : push au personnel, relance suivante programmée à T+3
        $this->travelTo(Carbon::parse('2026-10-24 12:02:00'));
        $this->runReminder($order, RemindRestaurantPendingOrder::STEP_PUSH);
        Queue::assertPushed(SendPushMessage::class, fn ($job) => $job->notifiable->is($this->owner)
            && $job->message['title'] === "⏰ Commande #{$order->reference} en attente depuis 2 min"
            && $job->message['require_interaction'] === true
            && str_contains($job->message['link'], 'signature='));
        Queue::assertPushed(RemindRestaurantPendingOrder::class, fn ($job) => $job->step === RemindRestaurantPendingOrder::STEP_SMS
            && $job->delay->equalTo(Carbon::parse('2026-10-24 12:03:00')));

        // Rejoué : pas de second push
        $this->runReminder($order, RemindRestaurantPendingOrder::STEP_PUSH);
        Queue::assertPushed(SendPushMessage::class, 1);

        // T+3 : SMS au téléphone du restaurant
        $this->travelTo(Carbon::parse('2026-10-24 12:03:00'));
        $this->runReminder($order, RemindRestaurantPendingOrder::STEP_SMS);
        $this->assertCount(1, $this->sms);
        $this->assertSame('+2250701020304', $this->sms[0]['phone']);
        $this->assertStringContainsString($order->reference, $this->sms[0]['message']);

        // T+5 : super-admins alertés (cloche + push) et client rassuré
        $this->travelTo(Carbon::parse('2026-10-24 12:05:00'));
        $this->runReminder($order, RemindRestaurantPendingOrder::STEP_ADMIN);

        $alert = $this->admin->notifications()->where('type', RestaurantUnresponsiveNotification::class)->first();
        $this->assertNotNull($alert);
        $this->assertStringContainsString("ne répond pas à la commande #{$order->reference}", $alert->data['message']);
        $this->assertSame(route('super-admin.orders.show', $order->id), $alert->data['url']);
        Queue::assertPushed(SendPushMessage::class, fn ($job) => $job->notifiable->is($this->admin));

        $customerPushes = $this->pushesTo('token-client');
        $this->assertSame('Encore un instant ⏳', end($customerPushes)['notification']['title']);

        // Pas d'annulation automatique
        $this->assertSame(OrderStatus::PAID, $order->fresh()->status);
        $this->assertSame(RemindRestaurantPendingOrder::STEP_ADMIN, $order->fresh()->acceptance_reminder_level);
    }

    public function test_no_reminder_once_the_order_is_accepted(): void
    {
        Queue::fake();
        $order = $this->paidOrder();
        $order->accept(15);

        foreach ([2 => RemindRestaurantPendingOrder::STEP_PUSH, 3 => RemindRestaurantPendingOrder::STEP_SMS, 5 => RemindRestaurantPendingOrder::STEP_ADMIN] as $minute => $step) {
            $this->travelTo(Carbon::parse('2026-10-24 12:00:00')->addMinutes($minute));
            $this->runReminder($order, $step);
        }

        Queue::assertNotPushed(SendPushMessage::class);
        Queue::assertPushed(RemindRestaurantPendingOrder::class, 1); // seulement la première, programmée à la réception
        $this->assertSame([], $this->sms);
        $this->assertSame(0, $this->admin->notifications()->count());
        $this->assertSame(0, $order->fresh()->acceptance_reminder_level);
    }

    // =========================================================================
    // Liens signés (notifications)
    // =========================================================================

    public function test_signed_link_shows_the_order_and_accepts_it(): void
    {
        Queue::fake();
        $order = $this->paidOrder();
        $url = OrderActionLink::for($order);

        $this->get($url)
            ->assertOk()
            ->assertSee($order->reference)
            ->assertSee('Accepter la commande')
            ->assertSee('Refuser la commande');

        $this->post($url, ['decision' => 'accept', 'prep_minutes' => 20])
            ->assertRedirect($url);

        $this->assertSame(OrderStatus::CONFIRMED, $order->fresh()->status);
        $this->assertSame(20, $order->fresh()->prep_minutes);
    }

    public function test_signed_link_rejects_with_mandatory_reason(): void
    {
        Queue::fake();
        $order = $this->paidOrder();
        $url = OrderActionLink::for($order);

        $this->from($url)->post($url, ['decision' => 'reject'])->assertSessionHasErrors('reason');
        $this->assertSame(OrderStatus::PAID, $order->fresh()->status);

        $this->post($url, ['decision' => 'reject', 'reason' => 'too_busy'])->assertRedirect($url);
        $this->assertSame(OrderRejectionReason::TOO_BUSY, $order->fresh()->rejection_reason);
    }

    public function test_expired_signed_link_is_refused(): void
    {
        Queue::fake();
        $order = $this->paidOrder();
        $url = OrderActionLink::for($order);

        $this->travel(31)->minutes();

        $this->get($url)->assertForbidden()->assertSee('Ce lien a expiré');
        $this->post($url, ['decision' => 'accept', 'prep_minutes' => 15])->assertForbidden();
        $this->assertSame(OrderStatus::PAID, $order->fresh()->status);
    }

    public function test_tampered_signed_link_is_refused(): void
    {
        Queue::fake();
        $order = $this->paidOrder();
        $other = $this->paidOrder();
        $url = OrderActionLink::for($order);

        // Autre commande avec la signature de la première
        $tampered = str_replace("/commande-a-traiter/{$order->id}?", "/commande-a-traiter/{$other->id}?", $url);
        $this->get($tampered)->assertForbidden()->assertSee("Ce lien n'est pas valide");
        $this->post($tampered, ['decision' => 'reject', 'reason' => 'closed'])->assertForbidden();

        // Sans signature
        $this->get(route('order-action.show', $order->id))->assertForbidden();

        $this->assertSame(OrderStatus::PAID, $other->fresh()->status);
    }

    public function test_new_order_push_links_to_the_signed_accept_page(): void
    {
        Queue::fake();
        $order = $this->paidOrder();

        (new AlertRestaurantNewOrder($order->id))->handle(app(\App\Services\FcmService::class), app(\App\Services\WhatsAppService::class));

        $link = $this->pushesTo('token-gerant')[0]['webpush']['fcm_options']['link'];
        $this->assertStringContainsString("/commande-a-traiter/{$order->id}", $link);
        $this->assertStringContainsString('signature=', $link);
        $this->get($link)->assertOk();
    }

    // =========================================================================
    // Hors périmètre / non-régression
    // =========================================================================

    public function test_pos_orders_confirmed_by_the_restaurant_skip_acceptance(): void
    {
        Queue::fake();
        $order = Order::factory()->create([
            'restaurant_id' => $this->restaurant->id,
            'source'        => 'pos',
            'status'        => OrderStatus::PENDING_PAYMENT,
        ]);

        // Comme Livewire\Restaurant\Orders::updateStatus() : le restaurant confirme le paiement lui-même
        $order->markAsPaid(['method' => 'on_site'], announce: false);
        $order->transitionTo(OrderStatus::CONFIRMED);

        $this->assertSame(OrderStatus::CONFIRMED, $order->fresh()->status);
        Queue::assertNotPushed(AlertRestaurantNewOrder::class);
        Queue::assertNotPushed(RemindRestaurantPendingOrder::class);
        Queue::assertNotPushed(AssignDriverJob::class);
    }

    public function test_wave_webhook_still_marks_the_order_paid_and_alerts_the_restaurant(): void
    {
        Queue::fake();
        $this->mock(WalletService::class)->shouldReceive('creditWallet')->once();
        SystemSetting::set('wave_api_key', 'wave-key');
        SystemSetting::set('wave_webhook_secret', 'wave-secret');

        $order = $this->platformOrder(['payment_method' => 'wave', 'payment_reference' => 'cos-123']);

        $payload = json_encode([
            'type' => 'checkout.session.completed',
            'data' => ['id' => 'cos-123', 'transaction_id' => 'TX-WAVE', 'amount' => (string) $order->total],
        ]);
        $timestamp = time();
        $signature = hash_hmac('sha256', $timestamp . $payload, 'wave-secret');

        $this->call('POST', route('webhooks.wave'), [], [], [], [
            'CONTENT_TYPE'          => 'application/json',
            'HTTP_WAVE_SIGNATURE'   => "t={$timestamp},v1={$signature}",
        ], $payload)->assertOk();

        $order->refresh();
        $this->assertSame(OrderStatus::PAID, $order->status);
        $this->assertSame(PaymentStatus::COMPLETED, $order->payment_status);
        Queue::assertPushed(AlertRestaurantNewOrder::class, fn ($job) => $job->orderId === $order->id);
        Queue::assertPushed(RemindRestaurantPendingOrder::class, fn ($job) => $job->orderId === $order->id);
        Queue::assertNotPushed(AssignDriverJob::class);
    }
}
