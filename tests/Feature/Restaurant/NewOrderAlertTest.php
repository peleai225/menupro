<?php

namespace Tests\Feature\Restaurant;

use App\Enums\OrderStatus;
use App\Jobs\AlertRestaurantNewOrder;
use App\Models\Order;
use App\Models\PushToken;
use App\Models\Restaurant;
use App\Models\SystemSetting;
use App\Models\User;
use App\Notifications\NewOrderNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class NewOrderAlertTest extends TestCase
{
    use RefreshDatabase;

    private function configureFirebase(): void
    {
        SystemSetting::set('firebase_project_id', 'menupro-test');
        SystemSetting::set('firebase_service_account_json', json_encode([
            'client_email' => 'fcm@menupro-test.iam.gserviceaccount.com',
            'private_key'  => 'inutilisée : le jeton OAuth est en cache',
        ]));
        Cache::put('fcm:access_token', 'fake-oauth-token', 3600);
    }

    private function restaurantWithOwner(): array
    {
        $restaurant = Restaurant::factory()->create();
        $owner = User::factory()->restaurantAdmin($restaurant)->create();

        return [$restaurant, $owner];
    }

    public function test_marking_an_order_paid_queues_the_restaurant_alert(): void
    {
        Queue::fake();
        [$restaurant] = $this->restaurantWithOwner();
        $order = Order::factory()->create(['restaurant_id' => $restaurant->id, 'status' => OrderStatus::PENDING_PAYMENT]);

        $order->markAsPaid(['method' => 'wave']);

        Queue::assertPushed(AlertRestaurantNewOrder::class, fn ($job) => $job->orderId === $order->id);
    }

    public function test_restaurant_confirming_payment_itself_is_not_alerted(): void
    {
        Queue::fake();
        [$restaurant] = $this->restaurantWithOwner();
        $order = Order::factory()->create(['restaurant_id' => $restaurant->id, 'status' => OrderStatus::PENDING_PAYMENT]);

        $order->markAsPaid(['method' => 'on_site'], announce: false);

        Queue::assertNotPushed(AlertRestaurantNewOrder::class);
    }

    public function test_alert_notifies_staff_once_and_pushes_to_every_device(): void
    {
        $this->configureFirebase();
        Http::fake(['fcm.googleapis.com/*' => Http::response(['name' => 'projects/x/messages/1'])]);
        // Firebase n'accepte que des liens https (comme en production)
        \Illuminate\Support\Facades\URL::forceRootUrl('https://www.menupro.ci');
        \Illuminate\Support\Facades\URL::forceScheme('https');

        [$restaurant, $owner] = $this->restaurantWithOwner();
        PushToken::create(['user_id' => $owner->id, 'token' => 'token-telephone']);
        PushToken::create(['user_id' => $owner->id, 'token' => 'token-tablette']);
        $order = Order::factory()->create(['restaurant_id' => $restaurant->id, 'status' => OrderStatus::CONFIRMED]);

        (new AlertRestaurantNewOrder($order->id))->handle(app(\App\Services\FcmService::class), app(\App\Services\WhatsAppService::class));
        // Rejoué (ex. retry de la file) : pas de doublon
        (new AlertRestaurantNewOrder($order->id))->handle(app(\App\Services\FcmService::class), app(\App\Services\WhatsAppService::class));

        $this->assertSame(1, $owner->notifications()->where('type', NewOrderNotification::class)->count());

        Http::assertSentCount(2);
        Http::assertSent(fn ($request) => str_contains($request->url(), 'messages:send')
            && $request['message']['webpush']['fcm_options']['link'] === route('restaurant.orders')
            && $request['message']['webpush']['headers']['Urgency'] === 'high');
    }

    public function test_token_rejected_by_firebase_is_deleted(): void
    {
        $this->configureFirebase();
        Http::fake(['fcm.googleapis.com/*' => Http::response([
            'error' => [
                'code'    => 404,
                'message' => 'Requested entity was not found.',
                'details' => [['errorCode' => 'UNREGISTERED']],
            ],
        ], 404)]);

        [, $owner] = $this->restaurantWithOwner();
        PushToken::create(['user_id' => $owner->id, 'token' => 'token-desinstalle']);

        $sent = app(\App\Services\FcmService::class)->sendToToken('token-desinstalle', 'Titre', 'Corps');

        $this->assertFalse($sent);
        $this->assertDatabaseMissing('push_tokens', ['token' => 'token-desinstalle']);
    }

    public function test_logged_in_user_can_register_a_device(): void
    {
        [, $owner] = $this->restaurantWithOwner();

        $this->actingAs($owner)
            ->postJson(route('push-token.store'), ['token' => 'nouveau-token'])
            ->assertOk();

        $this->assertDatabaseHas('push_tokens', ['user_id' => $owner->id, 'token' => 'nouveau-token']);
    }
}
