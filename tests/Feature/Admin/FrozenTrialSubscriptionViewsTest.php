<?php

namespace Tests\Feature\Admin;

use App\Enums\SubscriptionStatus;
use App\Models\Plan;
use App\Models\Restaurant;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FrozenTrialSubscriptionViewsTest extends TestCase
{
    use RefreshDatabase;

    private function frozenSubscription(): Subscription
    {
        $restaurant = Restaurant::factory()->create();
        $plan = Plan::factory()->create();

        return Subscription::factory()->create([
            'restaurant_id' => $restaurant->id,
            'plan_id' => $plan->id,
            'status' => SubscriptionStatus::TRIAL,
            'is_trial' => true,
            'starts_at' => null,
            'ends_at' => null,
        ]);
    }

    public function test_model_accessors_are_null_safe_on_frozen_trial(): void
    {
        $sub = $this->frozenSubscription();

        $this->assertFalse($sub->is_active);
        $this->assertFalse($sub->is_expired);
        $this->assertSame(0, $sub->days_remaining);
        $this->assertSame(0, $sub->duration_days);
    }

    // Note : super-admin.subscriptions.index et .export exécutent aussi
    // AVG(DATEDIFF(ends_at, starts_at)) (SubscriptionController.php:110),
    // une requête MySQL-only qui échoue sur SQLite indépendamment de tout
    // abonnement gelé (même un abonnement ACTIVE normal la fait planter en
    // test). C'est le même type de dette pré-existante que FinanceController
    // (déjà écarté dans ce travail) — pas testable end-to-end ici. Le
    // null-safety sur starts_at/ends_at est donc vérifié au niveau modèle
    // (ci-dessus) et appliqué par relecture directe dans la vue et l'export.
}
