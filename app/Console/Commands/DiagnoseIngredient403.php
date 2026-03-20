<?php

namespace App\Console\Commands;

use App\Models\Ingredient;
use App\Models\User;
use Illuminate\Console\Command;

class DiagnoseIngredient403 extends Command
{
    protected $signature = 'stock:diagnose-403 {ingredient_id} {email}';

    protected $description = 'Diagnostique pourquoi un utilisateur reçoit 403 sur une page ingrédient';

    public function handle(): int
    {
        $ingredientId = (int) $this->argument('ingredient_id');
        $email = trim($this->argument('email'));

        $ingredient = Ingredient::withoutGlobalScope('restaurant')->find($ingredientId);
        $user = User::where('email', $email)->first();

        if (!$ingredient) {
            $this->error("Ingrédient {$ingredientId} introuvable.");
            return self::FAILURE;
        }
        if (!$user) {
            $this->error("Utilisateur {$email} introuvable.");
            return self::FAILURE;
        }

        $this->info("=== Diagnostic 403 pour ingrédient {$ingredientId} / {$email} ===");
        $this->newLine();

        $this->table(
            ['Élément', 'Valeur'],
            [
                ['Ingrédient', "id={$ingredient->id}, name={$ingredient->name}, restaurant_id=" . ($ingredient->restaurant_id ?? 'NULL')],
                ['Utilisateur', "id={$user->id}, role={$user->role->value}, restaurant_id=" . ($user->restaurant_id ?? 'NULL')],
            ]
        );

        $this->newLine();
        $this->line('Vérifications de la politique :');

        $hasStockFeature = $user->isSuperAdmin() || ($user->restaurant?->hasFeature('stock') ?? false);
        $this->line($hasStockFeature ? '  ✓ hasStockFeature: OUI' : '  ✗ hasStockFeature: NON');

        $isRestaurantAdmin = $user->isRestaurantAdmin();
        $this->line($isRestaurantAdmin ? '  ✓ isRestaurantAdmin: OUI' : '  ✗ isRestaurantAdmin: NON (role=' . $user->role->value . ')');

        $effectiveRestaurantId = session('current_restaurant_id') ?? $user->restaurant_id;
        $canAccess = $ingredient->restaurant_id === null
            ? ($user->restaurant_id !== null || session()->has('current_restaurant_id'))
            : ($effectiveRestaurantId !== null && (int) $ingredient->restaurant_id === (int) $effectiveRestaurantId);
        $this->line($canAccess ? '  ✓ canAccessIngredient: OUI' : '  ✗ canAccessIngredient: NON');
        $this->line("    (ingredient.restaurant_id=" . json_encode($ingredient->restaurant_id) . ", effective=" . json_encode($effectiveRestaurantId) . ")");

        if ($user->restaurant) {
            $this->newLine();
            $this->line('Restaurant de l\'utilisateur :');
            $this->line('  - current_plan_id: ' . ($user->restaurant->current_plan_id ?? 'NULL'));
            $this->line('  - hasFeature(stock): ' . ($user->restaurant->hasFeature('stock') ? 'OUI' : 'NON'));
        }

        $this->newLine();
        $wouldPass = $hasStockFeature && $isRestaurantAdmin && $canAccess;
        $this->info($wouldPass ? '→ La politique devrait AUTORISER l\'accès.' : '→ La politique REFUSE l\'accès (403).');

        return self::SUCCESS;
    }
}
