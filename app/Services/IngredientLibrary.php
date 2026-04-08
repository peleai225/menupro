<?php

namespace App\Services;

use App\Enums\Unit;
use App\Models\Ingredient;
use App\Models\IngredientCategory;
use App\Models\Restaurant;
use Illuminate\Support\Facades\DB;

class IngredientLibrary
{
    /**
     * Bibliothèque d'ingrédients communs pour maquis et restaurants ivoiriens.
     * Organisés par catégorie avec unités et seuils par défaut.
     */
    public static function getLibrary(): array
    {
        return [
            'Viandes & Poissons' => [
                ['name' => 'Poulet', 'unit' => 'kg', 'min' => 5],
                ['name' => 'Poisson (maquereau)', 'unit' => 'kg', 'min' => 3],
                ['name' => 'Poisson (thon)', 'unit' => 'kg', 'min' => 3],
                ['name' => 'Poisson (carpe)', 'unit' => 'kg', 'min' => 2],
                ['name' => 'Viande de bœuf', 'unit' => 'kg', 'min' => 5],
                ['name' => 'Viande de mouton', 'unit' => 'kg', 'min' => 3],
                ['name' => 'Brochettes (bœuf)', 'unit' => 'piece', 'min' => 20],
                ['name' => 'Crevettes', 'unit' => 'kg', 'min' => 2],
                ['name' => 'Escargots', 'unit' => 'kg', 'min' => 2],
                ['name' => 'Œufs', 'unit' => 'piece', 'min' => 30],
            ],
            'Féculents & Céréales' => [
                ['name' => 'Riz', 'unit' => 'kg', 'min' => 25],
                ['name' => 'Attiéké', 'unit' => 'kg', 'min' => 10],
                ['name' => 'Semoule de manioc', 'unit' => 'kg', 'min' => 10],
                ['name' => 'Foutou (banane plantain)', 'unit' => 'kg', 'min' => 10],
                ['name' => 'Igname', 'unit' => 'kg', 'min' => 10],
                ['name' => 'Banane plantain', 'unit' => 'kg', 'min' => 10],
                ['name' => 'Placali', 'unit' => 'kg', 'min' => 5],
                ['name' => 'Pain', 'unit' => 'piece', 'min' => 20],
                ['name' => 'Farine de blé', 'unit' => 'kg', 'min' => 5],
                ['name' => 'Pâtes alimentaires', 'unit' => 'kg', 'min' => 5],
            ],
            'Légumes & Condiments' => [
                ['name' => 'Tomate fraîche', 'unit' => 'kg', 'min' => 5],
                ['name' => 'Concentré de tomate', 'unit' => 'piece', 'min' => 10],
                ['name' => 'Oignon', 'unit' => 'kg', 'min' => 5],
                ['name' => 'Piment frais', 'unit' => 'kg', 'min' => 2],
                ['name' => 'Ail', 'unit' => 'kg', 'min' => 1],
                ['name' => 'Gingembre', 'unit' => 'kg', 'min' => 1],
                ['name' => 'Aubergine', 'unit' => 'kg', 'min' => 3],
                ['name' => 'Gombo', 'unit' => 'kg', 'min' => 3],
                ['name' => 'Épinard / Dah', 'unit' => 'kg', 'min' => 3],
                ['name' => 'Feuille de manioc', 'unit' => 'kg', 'min' => 2],
                ['name' => 'Laitue / Salade', 'unit' => 'piece', 'min' => 10],
                ['name' => 'Concombre', 'unit' => 'kg', 'min' => 2],
                ['name' => 'Carotte', 'unit' => 'kg', 'min' => 2],
                ['name' => 'Chou', 'unit' => 'piece', 'min' => 3],
            ],
            'Huiles & Assaisonnements' => [
                ['name' => 'Huile de palme (huile rouge)', 'unit' => 'L', 'min' => 5],
                ['name' => 'Huile végétale', 'unit' => 'L', 'min' => 10],
                ['name' => 'Sel', 'unit' => 'kg', 'min' => 5],
                ['name' => 'Cube Maggi', 'unit' => 'piece', 'min' => 50],
                ['name' => 'Poivre', 'unit' => 'g', 'min' => 200],
                ['name' => 'Soumbara', 'unit' => 'g', 'min' => 200],
                ['name' => 'Vinaigre', 'unit' => 'L', 'min' => 1],
                ['name' => 'Moutarde', 'unit' => 'piece', 'min' => 3],
                ['name' => 'Mayonnaise', 'unit' => 'piece', 'min' => 3],
                ['name' => 'Pâte d\'arachide', 'unit' => 'kg', 'min' => 3],
                ['name' => 'Noix de coco râpée', 'unit' => 'kg', 'min' => 1],
            ],
            'Boissons' => [
                ['name' => 'Eau minérale (pack)', 'unit' => 'pack', 'min' => 5],
                ['name' => 'Jus de bissap', 'unit' => 'L', 'min' => 5],
                ['name' => 'Jus de gingembre', 'unit' => 'L', 'min' => 5],
                ['name' => 'Jus de baobab', 'unit' => 'L', 'min' => 3],
                ['name' => 'Coca-Cola / Soda', 'unit' => 'piece', 'min' => 24],
                ['name' => 'Bière (Flag/Ivoire)', 'unit' => 'piece', 'min' => 24],
                ['name' => 'Vin rouge', 'unit' => 'bottle', 'min' => 3],
                ['name' => 'Lait', 'unit' => 'L', 'min' => 5],
                ['name' => 'Café', 'unit' => 'kg', 'min' => 1],
                ['name' => 'Sucre', 'unit' => 'kg', 'min' => 5],
            ],
        ];
    }

    /**
     * Get flat list of all ingredient names for display.
     */
    public static function getCategoryNames(): array
    {
        return array_keys(static::getLibrary());
    }

    /**
     * Import selected ingredients into a restaurant.
     * Returns the count of ingredients created.
     */
    public static function importForRestaurant(Restaurant $restaurant, array $selectedIngredients): int
    {
        $library = static::getLibrary();
        $count = 0;

        // Get existing ingredient names to avoid duplicates
        $existingNames = Ingredient::where('restaurant_id', $restaurant->id)
            ->pluck('name')
            ->map(fn($n) => mb_strtolower($n))
            ->toArray();

        DB::transaction(function () use ($restaurant, $library, $selectedIngredients, $existingNames, &$count) {
            // Cache categories by name
            $categories = [];

            foreach ($library as $categoryName => $ingredients) {
                foreach ($ingredients as $ingredient) {
                    $key = $categoryName . '::' . $ingredient['name'];

                    if (!in_array($key, $selectedIngredients)) {
                        continue;
                    }

                    // Skip if already exists
                    if (in_array(mb_strtolower($ingredient['name']), $existingNames)) {
                        continue;
                    }

                    // Get or create category
                    if (!isset($categories[$categoryName])) {
                        $categories[$categoryName] = IngredientCategory::firstOrCreate(
                            ['restaurant_id' => $restaurant->id, 'name' => $categoryName],
                            ['color' => static::getCategoryColor($categoryName), 'sort_order' => count($categories)]
                        );
                    }

                    Ingredient::create([
                        'restaurant_id' => $restaurant->id,
                        'ingredient_category_id' => $categories[$categoryName]->id,
                        'name' => $ingredient['name'],
                        'unit' => $ingredient['unit'],
                        'current_quantity' => 0,
                        'min_quantity' => $ingredient['min'],
                        'unit_cost' => 0,
                        'is_active' => true,
                    ]);

                    $count++;
                }
            }
        });

        return $count;
    }

    /**
     * Import ALL ingredients from the library.
     */
    public static function importAllForRestaurant(Restaurant $restaurant): int
    {
        $library = static::getLibrary();
        $all = [];

        foreach ($library as $categoryName => $ingredients) {
            foreach ($ingredients as $ingredient) {
                $all[] = $categoryName . '::' . $ingredient['name'];
            }
        }

        return static::importForRestaurant($restaurant, $all);
    }

    private static function getCategoryColor(string $category): string
    {
        return match ($category) {
            'Viandes & Poissons' => '#ef4444',
            'Féculents & Céréales' => '#f59e0b',
            'Légumes & Condiments' => '#22c55e',
            'Huiles & Assaisonnements' => '#f97316',
            'Boissons' => '#3b82f6',
            default => '#6b7280',
        };
    }
}
