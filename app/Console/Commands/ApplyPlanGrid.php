<?php

namespace App\Console\Commands;

use App\Models\Plan;
use App\Models\Restaurant;
use Database\Seeders\PlanSeeder;
use Illuminate\Console\Command;

/**
 * Applique la grille d'abonnements (PlanSeeder, source de vérité) en production.
 *
 * Idempotent : updateOrCreate par slug. Ne touche NI aux abonnements en cours NI à leurs
 * montants : seuls les plans (prix catalogue, limites, fonctions, visibilité) changent.
 */
class ApplyPlanGrid extends Command
{
    protected $signature = 'plans:appliquer-grille
                            {--dry-run : Afficher les changements sans les appliquer}';

    protected $description = 'Applique la nouvelle grille d\'abonnements (Stand, Restaurant, Pro, Gold) sans toucher aux abonnements';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $changes = 0;

        foreach (PlanSeeder::definitions() as $slug => $data) {
            $plan = Plan::where('slug', $slug)->first();

            if (!$plan) {
                $this->line("  <info>Création</info> plan {$slug} ({$data['name']}, {$data['price']} F)");
                $changes++;
                continue;
            }

            $diff = [];
            foreach ($data as $field => $value) {
                $current = $plan->getAttribute($field);
                if (is_bool($value)) {
                    $current = (bool) $current;
                }
                if ($this->show($current) !== $this->show($value)) {
                    $diff[] = sprintf('%s: %s → %s', $field, $this->show($current), $this->show($value));
                }
            }

            if ($diff) {
                $changes++;
                $this->line("  <comment>Mise à jour</comment> plan {$slug} (" . $plan->restaurants()->count() . ' restaurant(s))');
                foreach ($diff as $line) {
                    $this->line("      {$line}");
                }
            } else {
                $this->line("  <fg=gray>Inchangé</> plan {$slug}");
            }
        }

        $toDeactivate = Plan::whereNotIn('slug', PlanSeeder::ACTIVE_SLUGS)->where('is_active', true)->pluck('slug');
        foreach ($toDeactivate as $slug) {
            $changes++;
            $this->line("  <comment>Désactivation</comment> plan {$slug} (retiré de la vente)");
        }

        // Restaurants encore rattachés à un plan retiré de la vente (ex. ancien « menupro »)
        $legacy = Restaurant::query()
            ->whereHas('currentPlan', fn ($q) => $q->whereNotIn('slug', PlanSeeder::ACTIVE_SLUGS))
            ->with('currentPlan:id,slug')
            ->get(['id', 'current_plan_id'])
            ->groupBy(fn ($r) => $r->currentPlan->slug)
            ->map->count();

        foreach ($legacy as $slug => $count) {
            $this->warn("  {$count} restaurant(s) encore sur le plan retiré « {$slug} » → php artisan plans:migrer-anciens --dry-run");
        }

        if ($dryRun) {
            $this->info("Simulation : {$changes} changement(s) à appliquer. Aucun abonnement ne sera modifié.");
            return self::SUCCESS;
        }

        if ($changes === 0) {
            $this->info('Grille déjà à jour, rien à faire.');
            return self::SUCCESS;
        }

        $this->call('db:seed', ['--class' => PlanSeeder::class, '--force' => true]);
        $this->info("Grille appliquée ({$changes} changement(s)). Abonnements en cours inchangés.");

        return self::SUCCESS;
    }

    private function show(mixed $value): string
    {
        return match (true) {
            $value === null => 'null',
            is_bool($value) => $value ? 'oui' : 'non',
            default => (string) $value,
        };
    }
}
