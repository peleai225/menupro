<?php

namespace Tests\Feature\Representatives;

use App\Models\Plan;
use App\Models\QrCampaign;
use App\Models\QrCampaignScan;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Campagnes QR : création, redirection suivie, comptage, téléchargements PNG/SVG,
 * attribution des inscriptions restaurant.
 */
class QrCampaignTest extends TestCase
{
    use RefreshDatabase;

    private const IPHONE = 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15 Mobile/15E148';

    private function admin(): User
    {
        return User::factory()->superAdmin()->create();
    }

    public function test_admin_creates_campaign_with_short_code(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->post(route('super-admin.qr-campaigns.store'), [
            'name' => 'Bâche gare Daloa',
            'target_type' => QrCampaign::TARGET_RESTAURANT_SIGNUP,
            'city' => 'Daloa',
            'location' => 'Gare routière',
        ])->assertRedirect();

        $campaign = QrCampaign::firstOrFail();
        $this->assertMatchesRegularExpression('/^[a-z0-9]{6}$/', $campaign->code);
        $this->assertSame($admin->id, $campaign->created_by);
        $this->assertStringEndsWith('/q/' . $campaign->code, $campaign->short_url);

        $this->actingAs($admin)->get(route('super-admin.qr-campaigns.index'))->assertOk()->assertSee('Bâche gare Daloa');
        $this->actingAs($admin)->get(route('super-admin.qr-campaigns.show', $campaign))->assertOk()->assertSee($campaign->short_url);
    }

    public function test_external_or_unsafe_internal_url_is_rejected(): void
    {
        $admin = $this->admin();

        foreach (['https://evil.example', '//evil.example', 'tarifs', 'javascript:alert(1)'] as $path) {
            $this->actingAs($admin)->post(route('super-admin.qr-campaigns.store'), [
                'name' => 'Test',
                'target_type' => QrCampaign::TARGET_INTERNAL_URL,
                'target_path' => $path,
            ])->assertSessionHasErrors('target_path');
        }

        $this->actingAs($admin)->post(route('super-admin.qr-campaigns.store'), [
            'name' => 'Affiche tarifs',
            'target_type' => QrCampaign::TARGET_INTERNAL_URL,
            'target_path' => '/tarifs',
        ])->assertSessionHasNoErrors();

        $this->assertSame(1, QrCampaign::count());
    }

    public function test_scan_is_counted_then_redirected(): void
    {
        $campaign = QrCampaign::create(['name' => 'Affiche marché', 'target_type' => QrCampaign::TARGET_REPRESENTATIVE_PAGE]);

        $response = $this->withHeader('User-Agent', self::IPHONE)->get('/q/' . $campaign->code);
        $response->assertRedirect();
        $location = $response->headers->get('Location');
        $this->assertStringStartsWith(route('representative.program'), $location);
        $this->assertStringContainsString('utm_source=qr', $location);
        $this->assertStringContainsString('utm_campaign=' . $campaign->code, $location);

        $this->withHeader('User-Agent', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)')->get('/q/' . strtoupper($campaign->code))->assertRedirect();

        $this->assertSame(2, $campaign->fresh()->scans_count);
        $this->assertSame(['desktop', 'mobile'], QrCampaignScan::orderBy('device')->pluck('device')->all());
        // Aucune donnée personnelle stockée
        $this->assertSame(['id', 'qr_campaign_id', 'device', 'scanned_at'], array_keys(QrCampaignScan::first()->getAttributes()));
    }

    public function test_client_app_and_internal_targets(): void
    {
        config(['apps.client_url' => 'https://app.menupro.ci']);
        $app = QrCampaign::create(['name' => 'App', 'target_type' => QrCampaign::TARGET_CLIENT_APP]);
        $internal = QrCampaign::create(['name' => 'Tarifs', 'target_type' => QrCampaign::TARGET_INTERNAL_URL, 'target_path' => '/tarifs']);

        $this->assertStringStartsWith('https://app.menupro.ci?utm_source=qr', $this->get('/q/' . $app->code)->headers->get('Location'));
        $this->assertStringStartsWith(url('/tarifs') . '?utm_source=qr', $this->get('/q/' . $internal->code)->headers->get('Location'));
    }

    public function test_unknown_or_inactive_code_returns_404(): void
    {
        $this->get('/q/inconnu')->assertNotFound();

        $campaign = QrCampaign::create(['name' => 'Ancienne bâche', 'target_type' => QrCampaign::TARGET_CLIENT_APP, 'is_active' => false]);
        $this->get('/q/' . $campaign->code)->assertNotFound();
        $this->assertSame(0, $campaign->fresh()->scans_count);
    }

    public function test_png_and_svg_downloads(): void
    {
        $admin = $this->admin();
        $campaign = QrCampaign::create(['name' => 'Bâche gare Daloa', 'target_type' => QrCampaign::TARGET_CLIENT_APP]);

        $png = $this->actingAs($admin)->get(route('super-admin.qr-campaigns.png', $campaign));
        $png->assertOk()->assertHeader('Content-Type', 'image/png');
        $this->assertStringContainsString('attachment; filename="qr-' . $campaign->code . '-bache-gare-daloa.png"', $png->headers->get('Content-Disposition'));
        [$width, $height] = getimagesizefromstring($png->getContent());
        $this->assertGreaterThanOrEqual(2000, $width);
        $this->assertSame($width, $height);
        // Marge blanche : le coin supérieur gauche est blanc
        $img = imagecreatefromstring($png->getContent());
        $this->assertSame(0xFFFFFF, imagecolorat($img, 5, 5) & 0xFFFFFF);

        $svg = $this->actingAs($admin)->get(route('super-admin.qr-campaigns.svg', $campaign));
        $svg->assertOk()->assertHeader('Content-Type', 'image/svg+xml');
        $this->assertStringContainsString('<svg', $svg->getContent());

        // Non-admin : refusé
        $owner = User::factory()->restaurantAdmin(Restaurant::factory()->create())->create();
        $this->actingAs($owner)->get(route('super-admin.qr-campaigns.png', $campaign))->assertForbidden();
    }

    public function test_restaurant_signup_from_qr_is_attributed_to_campaign(): void
    {
        Plan::firstOrCreate(['slug' => 'stand'], ['name' => 'Stand', 'price' => 5000, 'duration_days' => 30, 'is_active' => true]);
        $campaign = QrCampaign::create(['name' => 'Bâche gare Daloa', 'target_type' => QrCampaign::TARGET_RESTAURANT_SIGNUP]);

        $this->get('/q/' . $campaign->code)->assertRedirect()->assertSessionHas(QrCampaign::SESSION_KEY, $campaign->id);

        $this->post(route('register.post'), [
            'name' => 'Koné',
            'restaurant_name' => 'Maquis de la gare',
            'restaurant_type' => 'restaurant',
            'phone' => '0700000002',
            'email' => 'gare@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'restaurant_city' => 'Daloa',
            'plan' => 'stand',
            'terms' => '1',
        ])->assertRedirect(route('restaurant.pending'));

        $this->assertSame($campaign->id, Restaurant::where('name', 'Maquis de la gare')->value('qr_campaign_id'));

        $admin = $this->admin();
        $this->actingAs($admin)->get(route('super-admin.qr-campaigns.show', $campaign))
            ->assertOk()->assertSee('Maquis de la gare');
    }

    public function test_campaign_can_be_deactivated(): void
    {
        $admin = $this->admin();
        $campaign = QrCampaign::create(['name' => 'Affiche', 'target_type' => QrCampaign::TARGET_CLIENT_APP]);

        $this->actingAs($admin)->put(route('super-admin.qr-campaigns.update', $campaign), ['toggle' => 1])->assertRedirect();

        $this->assertFalse($campaign->fresh()->is_active);
        $this->get('/q/' . $campaign->code)->assertNotFound();
    }
}
