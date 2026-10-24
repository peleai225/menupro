<?php

namespace Tests\Feature\Notifications;

use App\Enums\DeliveryStatus;
use App\Enums\OrderStatus;
use App\Models\Customer;
use App\Models\Delivery;
use App\Models\DeliveryDriver;
use App\Models\Order;
use App\Models\PushToken;
use App\Models\Restaurant;
use App\Models\ServiceRequest;
use App\Models\SystemSetting;
use App\Models\User;
use App\Notifications\Channels\PushChannel;
use App\Notifications\ServiceRequestNotification;
use App\Services\DriverAssignmentService;
use App\Support\PushBadge;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class PushNotificationsTest extends TestCase
{
    use RefreshDatabase;

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
    }

    /** Messages FCM envoyés, indexés par token */
    private function sentMessages(): array
    {
        return collect(Http::recorded())
            ->map(fn ($pair) => $pair[0])
            ->filter(fn (Request $request) => str_contains($request->url(), 'messages:send'))
            ->mapWithKeys(fn (Request $request) => [$request['message']['token'] => $request['message']])
            ->all();
    }

    public function test_push_channel_reaches_every_device_of_a_user_with_a_badge(): void
    {
        $user = User::factory()->superAdmin()->create();
        PushToken::create(['user_id' => $user->id, 'token' => 'token-pc']);
        PushToken::create(['user_id' => $user->id, 'token' => 'token-telephone']);

        $user->notify(new TestPushNotification());

        $messages = $this->sentMessages();
        $this->assertSame(['token-pc', 'token-telephone'], array_keys($messages));
        $this->assertSame('Titre de test', $messages['token-pc']['notification']['title']);
        $this->assertSame('https://www.menupro.ci/lien', $messages['token-pc']['webpush']['fcm_options']['link']);
        // Admin : badge = notifications non lues (celle-ci vient d'être enregistrée en base)
        $this->assertSame('1', $messages['token-pc']['data']['badge']);
        $this->assertSame('test', $messages['token-pc']['data']['type']);
    }

    public function test_push_channel_reaches_customer_app_and_web_devices(): void
    {
        $user = User::factory()->create();
        PushToken::create(['user_id' => $user->id, 'token' => 'token-site-web']);
        $customer = Customer::create(['user_id' => $user->id, 'phone' => '+2250700000001', 'fcm_token' => 'token-app-client']);

        $restaurant = Restaurant::factory()->create();
        Order::factory()->create(['restaurant_id' => $restaurant->id, 'customer_id' => $customer->id, 'status' => OrderStatus::PREPARING]);
        Order::factory()->create(['restaurant_id' => $restaurant->id, 'customer_id' => $customer->id, 'status' => OrderStatus::COMPLETED]);

        $customer->notify(new TestPushNotification(database: false));

        $messages = $this->sentMessages();
        $this->assertEqualsCanonicalizing(['token-app-client', 'token-site-web'], array_keys($messages));
        // Client : commandes en cours uniquement
        $this->assertSame('1', $messages['token-app-client']['data']['badge']);
    }

    public function test_push_channel_is_silent_without_devices_or_firebase(): void
    {
        $user = User::factory()->superAdmin()->create();
        $user->notify(new TestPushNotification());

        SystemSetting::set('firebase_project_id', '');
        PushToken::create(['user_id' => $user->id, 'token' => 'token-pc']);
        $user->notify(new TestPushNotification());

        Http::assertNothingSent();
    }

    public function test_service_request_rings_restaurant_staff_on_every_device(): void
    {
        $restaurant = Restaurant::factory()->create();
        $owner = User::factory()->restaurantAdmin($restaurant)->create();
        PushToken::create(['user_id' => $owner->id, 'token' => 'token-gerant']);
        Order::factory()->create(['restaurant_id' => $restaurant->id, 'status' => OrderStatus::PAID]);
        Order::factory()->create(['restaurant_id' => $restaurant->id, 'status' => OrderStatus::PREPARING]);

        $this->postJson(route('r.service-request.store', $restaurant->slug), [
            'table_number' => 'Chambre 12',
            'type'         => 'assistance',
        ])->assertOk();

        $request = ServiceRequest::firstOrFail();
        $this->assertSame(1, $owner->notifications()->where('type', ServiceRequestNotification::class)->count());

        $message = $this->sentMessages()['token-gerant'];
        $this->assertSame('🔔 Chambre 12 : Appel du personnel', $message['notification']['title']);
        $this->assertSame('service_request', $message['data']['type']);
        $this->assertTrue($message['webpush']['notification']['requireInteraction']);
        $this->assertSame('service-request-' . $request->id, $message['webpush']['notification']['tag']);
        // 1 commande payée à accepter + 1 appel en attente (la commande en préparation ne compte pas)
        $this->assertSame('2', $message['data']['badge']);
    }

    public function test_numbered_table_is_labelled_as_table(): void
    {
        $restaurant = Restaurant::factory()->create();
        $owner = User::factory()->restaurantAdmin($restaurant)->create();
        PushToken::create(['user_id' => $owner->id, 'token' => 'token-gerant']);

        $this->postJson(route('r.service-request.store', $restaurant->slug), [
            'table_number' => '4',
            'type'         => 'checkout',
        ])->assertOk();

        $this->assertSame('💳 Table 4 : Addition', $this->sentMessages()['token-gerant']['notification']['title']);
    }

    public function test_driver_is_told_when_admin_cancels_his_delivery(): void
    {
        $driver = DeliveryDriver::factory()->create(['is_available' => false, 'fcm_token' => 'token-livreur']);
        $delivery = Delivery::factory()->create([
            'driver_id' => $driver->id,
            'status'    => DeliveryStatus::HEADING_TO_RESTAURANT,
        ]);

        app(DriverAssignmentService::class)->cancelDelivery($delivery, 'Course bloquée', 'admin');

        $message = $this->sentMessages()['token-livreur'];
        $this->assertSame('delivery_cancelled', $message['data']['type']);
        $this->assertSame((string) $delivery->id, $message['data']['delivery_id']);
        $this->assertArrayHasKey('badge', $message['data']);
        $this->assertSame(rtrim(config('apps.driver_url'), '/') . '/?open=dashboard', $message['webpush']['fcm_options']['link']);
        $this->assertTrue($message['webpush']['notification']['requireInteraction']);
    }

    public function test_driver_cancelling_himself_is_not_notified(): void
    {
        $driver = DeliveryDriver::factory()->create(['is_available' => false, 'fcm_token' => 'token-livreur']);
        $delivery = Delivery::factory()->create([
            'driver_id' => $driver->id,
            'status'    => DeliveryStatus::ASSIGNED,
        ]);

        app(DriverAssignmentService::class)->cancelDelivery($delivery, 'Panne de moto', 'driver');

        Http::assertNothingSent();
    }

    public function test_driver_is_told_when_the_order_is_cancelled(): void
    {
        $driver = DeliveryDriver::factory()->create(['is_available' => false, 'fcm_token' => 'token-livreur']);
        $order = Order::factory()->create(['status' => OrderStatus::CONFIRMED]);
        $delivery = Delivery::factory()->create([
            'order_id'      => $order->id,
            'restaurant_id' => $order->restaurant_id,
            'driver_id'     => $driver->id,
            'status'        => DeliveryStatus::ASSIGNED,
        ]);

        $this->assertTrue($order->cancel('Rupture de stock'));

        $this->assertSame(DeliveryStatus::CANCELLED, $delivery->fresh()->status);
        $message = $this->sentMessages()['token-livreur'];
        $this->assertSame('delivery_cancelled', $message['data']['type']);
        $this->assertStringContainsString('Rupture de stock', $message['notification']['body']);
    }

    public function test_restaurant_staff_is_told_when_driver_picks_up_the_order(): void
    {
        $restaurant = Restaurant::factory()->create();
        $owner = User::factory()->restaurantAdmin($restaurant)->create();
        PushToken::create(['user_id' => $owner->id, 'token' => 'token-gerant']);

        $driverUser = User::factory()->create();
        $driverUser->role = \App\Enums\UserRole::DELIVERY_DRIVER;
        $driverUser->save();
        $driver = DeliveryDriver::factory()->create(['user_id' => $driverUser->id, 'name' => 'Koffi']);
        $order = Order::factory()->create(['restaurant_id' => $restaurant->id, 'status' => OrderStatus::READY]);
        $delivery = Delivery::factory()->create([
            'order_id'      => $order->id,
            'restaurant_id' => $restaurant->id,
            'driver_id'     => $driver->id,
            'status'        => DeliveryStatus::HEADING_TO_RESTAURANT,
        ]);

        \Laravel\Sanctum\Sanctum::actingAs($driverUser);
        $this->patchJson("/api/v1/driver/deliveries/{$delivery->id}/status", ['status' => 'picked_up'])->assertOk();

        $message = $this->sentMessages()['token-gerant'] ?? null;
        $this->assertNotNull($message, 'Le restaurant doit recevoir un push');
        $this->assertSame('driver_picked_up', $message['data']['type']);
        $this->assertSame('driver-order-' . $order->id, $message['webpush']['notification']['tag']);
        $this->assertArrayNotHasKey('requireInteraction', $message['webpush']['notification']);
    }

    public function test_restaurant_badge_counts_orders_to_handle_and_pending_calls(): void
    {
        $restaurant = Restaurant::factory()->create();
        Order::factory()->create(['restaurant_id' => $restaurant->id, 'status' => OrderStatus::PAID]);
        Order::factory()->create(['restaurant_id' => $restaurant->id, 'status' => OrderStatus::CONFIRMED]);
        Order::factory()->create(['restaurant_id' => $restaurant->id, 'status' => OrderStatus::READY]);
        Order::factory()->create(['restaurant_id' => $restaurant->id, 'status' => OrderStatus::PENDING_PAYMENT]);
        ServiceRequest::create(['restaurant_id' => $restaurant->id, 'table_number' => '3', 'type' => 'checkout', 'status' => 'pending']);
        ServiceRequest::create(['restaurant_id' => $restaurant->id, 'table_number' => '5', 'type' => 'checkout', 'status' => 'done']);

        $this->assertSame(3, PushBadge::forRestaurant($restaurant->id));
    }
}

/**
 * Notification de test : base + push.
 */
class TestPushNotification extends Notification
{
    public function __construct(private bool $database = true) {}

    public function via(object $notifiable): array
    {
        return $this->database ? ['database', PushChannel::class] : [PushChannel::class];
    }

    public function toPush(object $notifiable): array
    {
        return [
            'title' => 'Titre de test',
            'body'  => 'Corps',
            'data'  => ['type' => 'test'],
            'link'  => 'https://www.menupro.ci/lien',
        ];
    }

    public function toArray(object $notifiable): array
    {
        return ['type' => 'test', 'message' => 'Test'];
    }
}
