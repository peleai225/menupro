<?php

namespace App\Console\Commands;

use App\Models\Ingredient;
use App\Models\IngredientCategory;
use App\Models\Plan;
use App\Models\Restaurant;
use Illuminate\Console\Command;

class FixStockData extends Command
{
    protected $signature = 'stock:fix-data {--dry-run : Afficher les corrections sans les appliquer}';

    protected $description = 'Corrige les données pour le module stock : restaurants sans plan, ingrédients orphelins';

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->warn('Mode simulation (--dry-run) : aucune modification ne sera effectuée.');
            $this->newLine();
        }

        $fixed = 0;

        // 1. Plan MenuPro et has_stock_management
        $plan = Plan::where('slug', 'menupro')->first();
        if (!$plan) {
            $this->error('Plan MenuPro introuvable. Exécutez : php artisan db:seed --class=PlanSeeder');
            return self::FAILURE;
        }

        if (!$plan->has_stock_management) {
            $this->line('Plan MenuPro : activation de has_stock_management...');
            if (!$dryRun) {
                $plan->update(['has_stock_management' => true]);
            }
            $fixed++;
        }

        // 2. Restaurants sans plan
        $restaurantsWithoutPlan = Restaurant::whereNull('current_plan_id')->get();
        if ($restaurantsWithoutPlan->isNotEmpty()) {
            $this->line('Restaurants sans plan : ' . $restaurantsWithoutPlan->count());
            foreach ($restaurantsWithoutPlan as $r) {
                $this->line("  - {$r->name} (id={$r->id})");
            }
            if (!$dryRun) {
                Restaurant::whereNull('current_plan_id')->update(['current_plan_id' => $plan->id]);
            }
            $fixed += $restaurantsWithoutPlan->count();
        }

        // 3. Ingrédients orphelins (restaurant_id null)
        $orphanIngredients = Ingredient::withoutGlobalScope('restaurant')
            ->whereNull('restaurant_id')
            ->get();

        if ($orphanIngredients->isNotEmpty()) {
            $this->line('Ingrédients orphelins (restaurant_id null) : ' . $orphanIngredients->count());

            $defaultRestaurant = Restaurant::whereNotNull('current_plan_id')->first();
            if (!$defaultRestaurant) {
                $this->warn('  Aucun restaurant avec plan trouvé. Assignez d\'abord un plan aux restaurants.');
            } else {
                foreach ($orphanIngredients as $ing) {
                    $targetRestaurantId = null;
                    if ($ing->ingredient_category_id) {
                        $category = IngredientCategory::withoutGlobalScope('restaurant')
                            ->find($ing->ingredient_category_id);
                        $targetRestaurantId = $category?->restaurant_id;
                    }
                    $targetRestaurantId ??= $defaultRestaurant->id;

                    $this->line("  - {$ing->name} (id={$ing->id}) → restaurant_id={$targetRestaurantId}");
                    if (!$dryRun) {
                        $ing->update(['restaurant_id' => $targetRestaurantId]);
                    }
                    $fixed++;
                }
            }
        }

        $this->newLine();
        if ($fixed > 0) {
            $this->info($dryRun
                ? "Corrections à appliquer : {$fixed}. Relancez sans --dry-run pour exécuter."
                : "Corrections appliquées : {$fixed}."
            );
        } else {
            $this->info('Aucune correction nécessaire.');
        }

        return self::SUCCESS;
    }
}
