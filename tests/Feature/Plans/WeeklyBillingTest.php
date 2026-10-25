<?php

namespace Tests\Feature\Plans;

use App\Enums\SubscriptionStatus;
use App\Jobs\SendSubscriptionReminders;
use App\Models\Subscription;
use App\Notifications\SubscriptionExpiringNotification;
use App\Services\JekoGateway;
use Illuminate\Support\Facades\Notification;

class WeeklyBillingTest extends PricingGridTestCase
{
    private function fakeJeko(): void
    {
        $this->mock(JekoGateway::class, function ($mock) {
            $mock->shouldReceive('forMarketplacePlatform')->andReturnSelf();
            $mock->shouldReceive('isConfigured')->andReturnTrue();
            $mock->shouldReceive('createPayment')->andReturn([
                'success' => true,
                'payment_id' => 'JEKO-TEST',
                'payment_url' => 'https://pay.test/jeko',
            ]);
        });
    }

    public function test_stand_weekly_quote_is_1500_for_7_days_without_discount(): void
    {
        $quote = Subscription::quote($this->plan('stand'), 'weekly');

        $this->assertSame(1500, $quote['final_price']);
        $this->assertSame(7, $quote['duration_days']);
        $this->assertSame(0, $quote['discount_percentage']);
    }

    public function test_weekly_is_refused_for_another_plan(): void
    {
        $this->assertFalse($this->plan('pro')->supportsWeeklyBilling());
        $this->assertNotContains('weekly', $this->plan('essentiel')->availableBillingPeriods());

        $this->expectException(\InvalidArgumentException::class);
        Subscription::quote($this->plan('pro'), 'weekly');
    }

    public function test_monthly_periods_keep_their_discounts(): void
    {
        $this->assertSame(15000, Subscription::quote($this->plan('essentiel'), 'monthly')['final_price']);
        $this->assertSame(81000, Subscription::quote($this->plan('pro'), 'quarterly')['final_price']); // 90 000 - 10 %
        $this->assertSame(288000, Subscription::quote($this->plan('pro'), 'annual')['final_price']);   // 360 000 - 20 %
    }

    public function test_stand_restaurant_can_pay_weekly(): void
    {
        $this->fakeJeko();
        $restaurant = $this->subscribedRestaurant('stand', ['subscription_ends_at' => now()->addDay()], ['ends_at' => now()->addDay()]);

        $this->actingAs($this->adminOf($restaurant))
            ->post(route('restaurant.subscription.change'), ['plan' => 'stand', 'billing_period' => 'weekly'])
            ->assertRedirect('https://pay.test/jeko');

        $pending = Subscription::where('restaurant_id', $restaurant->id)
            ->where('status', SubscriptionStatus::PENDING)->firstOrFail();

        $this->assertSame(1500, $pending->amount_paid);
        $this->assertSame('weekly', $pending->billing_period);
        $this->assertSame(7, (int) round($pending->starts_at->diffInDays($pending->ends_at)));
    }

    public function test_weekly_payment_is_refused_for_pro_plan(): void
    {
        $this->fakeJeko();
        $restaurant = $this->subscribedRestaurant('essentiel');

        $this->actingAs($this->adminOf($restaurant))
            ->post(route('restaurant.subscription.change'), ['plan' => 'pro', 'billing_period' => 'weekly'])
            ->assertSessionHas('error');

        $this->assertFalse(Subscription::where('restaurant_id', $restaurant->id)
            ->where('status', SubscriptionStatus::PENDING)->exists());
    }

    public function test_registration_refuses_weekly_for_non_stand_plan(): void
    {
        $this->post(route('register.post'), [
            'name' => 'Koné',
            'restaurant_name' => 'Chez Koné',
            'restaurant_type' => 'restaurant',
            'phone' => '0700000001',
            'password' => 'password123',
            'plan' => 'pro',
            'billing_period' => 'weekly',
            'terms' => '1',
        ])->assertSessionHasErrors('billing_period');

        $this->assertDatabaseCount('subscriptions', 0);
    }

    public function test_weekly_subscription_reminder_is_sent_the_day_before_not_a_week_before(): void
    {
        Notification::fake();

        $weeklySoon = $this->subscribedRestaurant('stand', [], [
            'billing_period' => 'weekly', 'starts_at' => now()->subDays(6), 'ends_at' => now()->addHours(20),
        ]);
        $weeklyFresh = $this->subscribedRestaurant('stand', [], [
            'billing_period' => 'weekly', 'starts_at' => now()->subDays(2), 'ends_at' => now()->addDays(5),
        ]);
        $monthly = $this->subscribedRestaurant('essentiel', [], [
            'billing_period' => 'monthly', 'ends_at' => now()->addDays(5),
        ]);
        foreach ([$weeklySoon, $weeklyFresh, $monthly] as $restaurant) {
            $this->adminOf($restaurant);
        }

        (new SendSubscriptionReminders(7))->handle();

        $sent = fn ($restaurant) => Subscription::where('restaurant_id', $restaurant->id)->first()->reminder_sent_at !== null;
        $this->assertTrue($sent($weeklySoon), 'Hebdo à J-1 : rappel attendu');
        $this->assertFalse($sent($weeklyFresh), 'Hebdo à J-5 : pas de rappel (sinon envoyé dès le paiement)');
        $this->assertTrue($sent($monthly), 'Mensuel à J-5 : rappel J-7 conservé');
        Notification::assertSentTimes(SubscriptionExpiringNotification::class, 2);
    }
}
