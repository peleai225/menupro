<?php

namespace Tests\Feature\Representatives;

use App\Enums\UserRole;
use App\Models\Crm\RepresentativeProfile;
use App\Models\Crm\Wallet;
use App\Models\PartnerApplication;
use App\Models\Restaurant;
use App\Models\User;
use App\Notifications\Admin\NewPartnerApplicationNotification;
use App\Notifications\Channels\PushChannel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Programme « Représentant de ville » : page publique, candidature (validation,
 * anti-spam, notification), back-office et création du représentant.
 */
class PartnerApplicationTest extends TestCase
{
    use RefreshDatabase;

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'full_name' => 'Awa Traoré',
            'phone' => '+225 07 08 09 10 11',
            'email' => 'awa.traore@example.com',
            'city' => 'Daloa',
            'legal_status' => 'auto_entrepreneur',
            'experience' => 'Dix ans dans la distribution de boissons auprès des maquis de Daloa.',
            'availability' => 'temps_plein',
            'motivation' => 'Je connais tous les restaurateurs du centre-ville et je veux les digitaliser.',
            'accept_terms' => '1',
        ], $overrides);
    }

    private function acceptedApplication(array $attributes = []): PartnerApplication
    {
        return PartnerApplication::create(array_merge($this->payload(), [
            'status' => PartnerApplication::STATUS_ACCEPTED,
        ], $attributes));
    }

    // =========================================================================
    // PAGE PUBLIQUE + CANDIDATURE
    // =========================================================================

    public function test_public_page_is_reachable_and_linked_from_footer(): void
    {
        $this->get(route('representative.program'))
            ->assertOk()
            ->assertSee('Devenir représentant')
            ->assertSee('20 %')
            ->assertSee('name="website"', false)
            ->assertSee(route('representative.apply'), false);

        $this->get(route('devenir-livreur'))->assertOk()->assertSee(route('representative.program'), false);
    }

    public function test_valid_application_is_stored_and_notifies_admins(): void
    {
        Notification::fake();
        Storage::fake('local');
        $admin = User::factory()->superAdmin()->create();
        $restrictedOther = User::factory()->superAdmin()->create(['admin_permissions' => ['orders']]);

        $this->post(route('representative.apply'), $this->payload([
            'attachment' => UploadedFile::fake()->create('cv.pdf', 120, 'application/pdf'),
        ]))->assertRedirect(route('representative.program'))->assertSessionHas('application_sent');

        $application = PartnerApplication::firstOrFail();
        $this->assertSame('Awa Traoré', $application->full_name);
        $this->assertSame('Daloa', $application->city);
        $this->assertSame(PartnerApplication::STATUS_NEW, $application->status);
        $this->assertSame('cv.pdf', $application->attachment_name);
        Storage::disk('local')->assertExists($application->attachment_path);

        Notification::assertSentTo($admin, NewPartnerApplicationNotification::class, function ($n, $channels) use ($admin) {
            $push = $n->toPush($admin);
            return in_array('database', $channels, true)
                && in_array(PushChannel::class, $channels, true)
                && str_contains($push['body'], 'Awa Traoré');
        });
        Notification::assertNotSentTo($restrictedOther, NewPartnerApplicationNotification::class);
    }

    public function test_application_is_validated(): void
    {
        Notification::fake();

        $this->post(route('representative.apply'), $this->payload([
            'full_name' => '',
            'phone' => 'abc',
            'legal_status' => 'pirate',
            'motivation' => 'court',
            'accept_terms' => null,
        ]))->assertSessionHasErrors(['full_name', 'phone', 'legal_status', 'motivation', 'accept_terms']);

        $this->assertSame(0, PartnerApplication::count());
        Notification::assertNothingSent();
    }

    public function test_honeypot_silently_drops_bots(): void
    {
        Notification::fake();
        User::factory()->superAdmin()->create();

        $this->post(route('representative.apply'), $this->payload(['website' => 'http://spam.example']))
            ->assertRedirect(route('representative.program'))
            ->assertSessionHas('application_sent');

        $this->assertSame(0, PartnerApplication::count());
        Notification::assertNothingSent();
    }

    public function test_application_endpoint_is_throttled(): void
    {
        Notification::fake();

        for ($i = 0; $i < 5; $i++) {
            $this->post(route('representative.apply'), $this->payload(['phone' => '070000000' . $i]))->assertRedirect();
        }

        $this->post(route('representative.apply'), $this->payload())->assertStatus(429);
        $this->assertSame(5, PartnerApplication::count());
    }

    // =========================================================================
    // BACK-OFFICE
    // =========================================================================

    public function test_admin_lists_reviews_and_annotates_applications(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $application = PartnerApplication::create(array_merge($this->payload(), ['status' => PartnerApplication::STATUS_NEW]));

        $this->actingAs($admin)->get(route('super-admin.partner-applications.index'))->assertOk()->assertSee('Awa Traoré');
        $this->actingAs($admin)->get(route('super-admin.partner-applications.show', $application))->assertOk()->assertSee('Dix ans');

        $this->actingAs($admin)->put(route('super-admin.partner-applications.update', $application), [
            'status' => PartnerApplication::STATUS_INTERVIEW,
            'internal_notes' => 'Entretien prévu mardi',
        ])->assertRedirect();

        $application->refresh();
        $this->assertSame(PartnerApplication::STATUS_INTERVIEW, $application->status);
        $this->assertSame('Entretien prévu mardi', $application->internal_notes);
        $this->assertSame($admin->id, $application->reviewed_by);
    }

    public function test_non_admins_cannot_reach_back_office(): void
    {
        $application = $this->acceptedApplication();
        $restaurant = Restaurant::factory()->create();
        $owner = User::factory()->restaurantAdmin($restaurant)->create();
        $employee = User::factory()->superAdmin()->create(['admin_permissions' => ['orders']]);
        $rep = User::factory()->create();
        $rep->role = UserRole::REPRESENTATIVE;
        $rep->save();

        foreach ([$owner, $employee, $rep] as $user) {
            $this->actingAs($user)->get(route('super-admin.partner-applications.index'))->assertForbidden();
            $this->actingAs($user)->get(route('super-admin.representatives.index'))->assertForbidden();
            $this->actingAs($user)->get(route('super-admin.qr-campaigns.index'))->assertForbidden();
            $this->actingAs($user)->post(route('super-admin.partner-applications.create-representative', $application), [])->assertForbidden();
        }

        auth()->logout();
        $this->get(route('super-admin.partner-applications.index'))->assertRedirect(route('login'));

        $crmEmployee = User::factory()->superAdmin()->create(['admin_permissions' => ['crm']]);
        $this->actingAs($crmEmployee)->get(route('super-admin.partner-applications.index'))->assertOk();
    }

    // =========================================================================
    // CRÉATION DU REPRÉSENTANT
    // =========================================================================

    public function test_admin_creates_representative_from_accepted_application(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $application = $this->acceptedApplication();

        $response = $this->actingAs($admin)->post(route('super-admin.partner-applications.create-representative', $application), [
            'city' => 'Daloa',
            'subscription_rate' => 20,
            'delivery_rate' => 20,
            'trial_ends_at' => now()->addMonths(3)->toDateString(),
            'quarterly_target' => 12,
            'exclusivity_active' => '1',
        ]);

        $profile = RepresentativeProfile::firstOrFail();
        $response->assertRedirect(route('super-admin.representatives.show', $profile))
            ->assertSessionHas('success', fn ($msg) => str_contains($msg, 'mot de passe'));

        $user = $profile->user;
        $this->assertSame(UserRole::REPRESENTATIVE, $user->role);
        $this->assertSame('awa.traore@example.com', $user->email);
        $this->assertSame('Daloa', $profile->city);
        $this->assertSame(20.0, $profile->subscription_rate);
        $this->assertSame(20.0, $profile->delivery_rate);
        $this->assertSame(12, $profile->quarterly_target);
        $this->assertTrue($profile->exclusivity_active);
        $this->assertNotNull($profile->exclusivity_granted_at);
        $this->assertSame(now()->addMonths(3)->toDateString(), $profile->trial_ends_at->toDateString());
        $this->assertNotNull($profile->uuid);
        $this->assertTrue(Wallet::where('user_id', $user->id)->exists());
        $this->assertSame($user->id, $application->fresh()->user_id);
        $this->assertSame('crm.dashboard', $user->getDashboardRoute());

        // Une deuxième création est refusée
        $this->actingAs($admin)->post(route('super-admin.partner-applications.create-representative', $application), [
            'city' => 'Daloa', 'subscription_rate' => 20, 'delivery_rate' => 20,
            'trial_ends_at' => now()->addMonths(3)->toDateString(), 'quarterly_target' => 12,
        ])->assertSessionHasErrors('application');
        $this->assertSame(1, RepresentativeProfile::count());

        // L'espace représentant (CRM) lui est ouvert ; les outils terrain non
        $this->actingAs($user)->get(route('crm.dashboard'))->assertOk()->assertSee('Daloa')->assertSee($profile->referral_url);
        $this->actingAs($user)->get(route('crm.wallet'))->assertOk();
        $this->actingAs($user)->get(route('crm.leads.index'))->assertForbidden();
    }

    public function test_default_rates_and_trial_come_from_config(): void
    {
        $application = $this->acceptedApplication();
        $result = app(\App\Services\Crm\RepresentativeService::class)->createFromApplication($application);

        $this->assertSame(20.0, $result['profile']->subscription_rate);
        $this->assertSame(20.0, $result['profile']->delivery_rate);
        $this->assertSame(now()->addMonthsNoOverflow(3)->toDateString(), $result['profile']->trial_ends_at->toDateString());
        $this->assertFalse($result['profile']->exclusivity_active);
    }

    public function test_representative_cannot_be_created_from_non_accepted_application(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $application = $this->acceptedApplication(['status' => PartnerApplication::STATUS_INTERVIEW]);

        $this->actingAs($admin)->post(route('super-admin.partner-applications.create-representative', $application), [
            'city' => 'Daloa', 'subscription_rate' => 20, 'delivery_rate' => 20,
            'trial_ends_at' => now()->addMonths(3)->toDateString(), 'quarterly_target' => 10,
        ])->assertSessionHasErrors('application');

        $this->assertSame(0, RepresentativeProfile::count());
    }

    public function test_city_exclusivity_is_unique(): void
    {
        $service = app(\App\Services\Crm\RepresentativeService::class);
        $service->createFromApplication($this->acceptedApplication(), ['exclusivity_active' => true]);

        $this->expectException(\Illuminate\Validation\ValidationException::class);
        $service->createFromApplication(
            $this->acceptedApplication(['email' => 'autre@example.com', 'city' => ' daloa ']),
            ['exclusivity_active' => true]
        );
    }
}
