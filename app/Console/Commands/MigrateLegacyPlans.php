<?php

namespace App\Console\Commands;

use App\Enums\SubscriptionStatus;
use App\Models\Plan;
use App\Models\Restaurant;
use App\Models\Subscription;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Rattache les restaurants de l'ancien plan « menupro » (retiré de la vente) au plan Pro,
 * en CONSERVANT leur tarif : restaurants.locked_price = 25 000 F, utilisé en priorité au
 * renouvellement (Subscription::quote()) tant qu'ils restent abonnés.
 *
 * Verrouille aussi le tarif des abonnés Pro actuels (ancien prix Pro 25 000 F, la grille passe
 * Pro à 30 000 F) — désactivable avec --sans-pro.
 *
 * N'altère ni la durée ni le montant des abonnements en cours (seul plan_id des abonnements
 * actifs / en essai / en attente passe de menupro à pro). Idempotent.
 */
class MigrateLegacyPlans extends Command
{
    protected $signature = 'plans:migrer-anciens
                            {--dry-run : Lister les restaurants concernés sans rien modifier}
                            {--depuis=menupro : Slug de l\'ancien plan à migrer}
                            {--vers=pro : Slug du plan cible}
                            {--prix=25000 : Prix mensuel conservé (F) pour les restaurants migrés}
                            {--prix-pro=25000 : Ancien prix Pro (F) conservé pour les abonnés Pro actuels}
                            {--sans-pro : Ne pas verrouiller le tarif des abonnés Pro actuels}';

    protected $description = 'Migre les restaurants de l\'ancien plan menupro vers Pro en conservant leur tarif (prix verrouillé)';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $target = Plan::where('slug', $this->option('vers'))->first();

        if (!$target) {
            $this->error("Plan cible « {$this->option('vers')} » introuvable. Lancez d'abord php artisan plans:appliquer-grille.");
            return self::FAILURE;
        }

        $lockedPrice = (int) $this->option('prix');
        $legacy = Plan::where('slug', $this->option('depuis'))->first();

        $migrated = $legacy ? $this->migrateLegacy($legacy, $target, $lockedPrice, $dryRun) : 0;
        if (!$legacy) {
            $this->line("  Plan « {$this->option('depuis')} » absent : rien à migrer.");
        }

        $proLocked = $this->option('sans-pro')
            ? 0
            : $this->lockCurrentProSubscribers($target, (int) $this->option('prix-pro'), $dryRun);

        $verb = $dryRun ? 'à migrer' : 'migré(s)';
        $this->info("{$migrated} restaurant(s) {$verb} vers {$target->name}, {$proLocked} abonné(s) Pro avec tarif conservé."
            . ($dryRun ? ' Simulation : aucune modification.' : ''));

        return self::SUCCESS;
    }

    private function migrateLegacy(Plan $legacy, Plan $target, int $lockedPrice, bool $dryRun): int
    {
        $restaurants = Restaurant::where('current_plan_id', $legacy->id)
            ->with(['activeSubscription'])
            ->orderBy('id')
            ->get();

        if ($restaurants->isEmpty()) {
            $this->line("  Aucun restaurant sur le plan « {$legacy->slug} » (déjà migré).");
            return 0;
        }

        $this->table(
            ['ID', 'Restaurant', 'Abonnement', 'Fin', 'Montant payé', 'Action'],
            $restaurants->map(fn (Restaurant $r) => [
                $r->id,
                $r->name,
                $r->activeSubscription?->status?->value ?? 'aucun',
                $r->subscription_ends_at?->format('d/m/Y') ?? '-',
                $r->activeSubscription ? number_format($r->activeSubscription->amount_paid, 0, ',', ' ') . ' F' : '-',
                "→ {$target->slug}, prix verrouillé " . number_format($r->locked_price ?? $lockedPrice, 0, ',', ' ') . ' F',
            ])->all()
        );

        if ($dryRun) {
            return $restaurants->count();
        }

        foreach ($restaurants as $restaurant) {
            DB::transaction(function () use ($restaurant, $legacy, $target, $lockedPrice) {
                // Abonnements en cours (actif, essai, en attente de paiement) : seul le plan change,
                // jamais les dates ni le montant.
                Subscription::withoutGlobalScopes()
                    ->where('restaurant_id', $restaurant->id)
                    ->where('plan_id', $legacy->id)
                    ->whereIn('status', [
                        SubscriptionStatus::ACTIVE->value,
                        SubscriptionStatus::TRIAL->value,
                        SubscriptionStatus::PENDING->value,
                    ])
                    ->update(['plan_id' => $target->id, 'updated_at' => now()]);

                $restaurant->forceFill([
                    'current_plan_id' => $target->id,
                    'locked_price' => $restaurant->locked_price ?? $lockedPrice,
                ])->save();
            });

            $this->line("  <info>Migré</info> #{$restaurant->id} {$restaurant->name}");
        }

        return $restaurants->count();
    }

    /**
     * Abonnés Pro payants actuels : conservent l'ancien prix Pro au renouvellement.
     */
    private function lockCurrentProSubscribers(Plan $pro, int $oldProPrice, bool $dryRun): int
    {
        if ($oldProPrice <= 0 || $oldProPrice >= (int) $pro->price) {
            return 0; // pas de hausse de prix : rien à protéger
        }

        $restaurants = Restaurant::where('current_plan_id', $pro->id)
            ->whereNull('locked_price')
            ->whereHas('subscriptions', fn ($q) => $q->withoutGlobalScopes()
                ->where('status', SubscriptionStatus::ACTIVE->value)
                ->where('is_trial', false)
                ->where('plan_id', $pro->id)
                ->where('ends_at', '>', now()))
            ->orderBy('id')
            ->get();

        foreach ($restaurants as $restaurant) {
            $this->line(($dryRun ? '  <comment>À verrouiller</comment>' : '  <info>Verrouillé</info>')
                . " #{$restaurant->id} {$restaurant->name} (abonné Pro) : " . number_format($oldProPrice, 0, ',', ' ') . ' F/mois');

            if (!$dryRun) {
                $restaurant->forceFill(['locked_price' => $oldProPrice])->save();
            }
        }

        return $restaurants->count();
    }
}
