<?php

namespace Tests\Feature\Plans;

use App\Models\Plan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlanSlugStabilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_renaming_a_plan_keeps_its_slug(): void
    {
        $plan = Plan::factory()->create(['name' => 'Essentiel', 'slug' => 'essentiel']);

        $plan->update(['name' => 'Restaurant']);

        $this->assertSame('essentiel', $plan->fresh()->slug);
    }
}
