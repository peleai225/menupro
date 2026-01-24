<?php

namespace Database\Seeders;

use App\Enums\OrderStatus;
use App\Enums\OrderType;
use App\Enums\PaymentStatus;
use App\Enums\RestaurantStatus;
use App\Enums\SubscriptionStatus;
use App\Enums\Unit;
use App\Enums\UserRole;
use App\Models\Category;
use App\Models\Dish;
use App\Models\Ingredient;
use App\Models\IngredientCategory;
use App\Models\Order;
use App\Models\Plan;
use App\Models\Restaurant;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DemoRestaurantSeeder extends Seeder
{
    public function run(): void
    {
        // Get active plan (MenuPro or fallback to any active)
        $plan = Plan::where('slug', 'menupro')->first() 
            ?? Plan::where('is_active', true)->first();
        
        if (!$plan) {
            $this->command->error('Please run PlanSeeder first.');
            return;
        }

        // Create demo restaurant with slug "demo" for public demo access
        $restaurant = Restaurant::updateOrCreate(
            ['slug' => 'demo'],
            [
                'name' => 'Le Maquis d\'Abidjan',
                'slug' => 'demo',
                'email' => 'demo@menupro.ci',
                'phone' => '+2250506805382',
                'description' => '🎯 RESTAURANT DE DÉMONSTRATION — Découvrez toutes les fonctionnalités de MenuPro ! Spécialités ivoiriennes : poulet braisé, attiéké, alloco et bien plus.',
                'address' => 'Cocody, Angré 8ème Tranche',
                'city' => 'Abidjan',
                'status' => RestaurantStatus::ACTIVE,
                'validated_at' => now(),
                'current_plan_id' => $plan->id,
                'subscription_ends_at' => now()->addDays(30),
                'orders_blocked' => false,
                'primary_color' => '#f97316',
                'secondary_color' => '#1c1917',
                'currency' => 'XOF',
                'timezone' => 'Africa/Abidjan',
                'min_order_amount' => 2000,
                'delivery_fee' => 1000,
                'estimated_prep_time' => 30,
                'opening_hours' => [
                    'monday' => ['open' => '11:00', 'close' => '22:00', 'closed' => false],
                    'tuesday' => ['open' => '11:00', 'close' => '22:00', 'closed' => false],
                    'wednesday' => ['open' => '11:00', 'close' => '22:00', 'closed' => false],
                    'thursday' => ['open' => '11:00', 'close' => '22:00', 'closed' => false],
                    'friday' => ['open' => '11:00', 'close' => '23:00', 'closed' => false],
                    'saturday' => ['open' => '11:00', 'close' => '23:00', 'closed' => false],
                    'sunday' => ['open' => '12:00', 'close' => '21:00', 'closed' => false],
                ],
            ]
        );

        // Create subscription
        Subscription::updateOrCreate(
            ['restaurant_id' => $restaurant->id, 'status' => SubscriptionStatus::ACTIVE],
            [
                'restaurant_id' => $restaurant->id,
                'plan_id' => $plan->id,
                'status' => SubscriptionStatus::ACTIVE,
                'starts_at' => now(),
                'ends_at' => now()->addDays(30),
                'amount_paid' => $plan->price,
                'payment_reference' => 'DEMO-' . Str::random(8),
                'payment_method' => 'demo',
            ]
        );

        // Create restaurant admin
        $admin = User::updateOrCreate(
            ['email' => 'demo@menupro.ci'],
            [
                'name' => 'Jean Kouassi',
                'email' => 'demo@menupro.ci',
                'phone' => '+225 0712345678',
                'password' => Hash::make('password'),
                'role' => UserRole::RESTAURANT_ADMIN,
                'restaurant_id' => $restaurant->id,
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        // Create categories
        $categories = [
            ['name' => 'Entrées', 'description' => 'Pour bien commencer votre repas', 'sort_order' => 1],
            ['name' => 'Plats Principaux', 'description' => 'Nos spécialités du jour', 'sort_order' => 2],
            ['name' => 'Grillades', 'description' => 'Viandes et poissons grillés', 'sort_order' => 3],
            ['name' => 'Accompagnements', 'description' => 'Pour compléter votre plat', 'sort_order' => 4],
            ['name' => 'Boissons', 'description' => 'Rafraîchissez-vous', 'sort_order' => 5],
            ['name' => 'Desserts', 'description' => 'Une touche sucrée', 'sort_order' => 6],
        ];

        $categoryModels = [];
        foreach ($categories as $cat) {
            $categoryModels[$cat['name']] = Category::updateOrCreate(
                ['restaurant_id' => $restaurant->id, 'name' => $cat['name']],
                array_merge($cat, [
                    'restaurant_id' => $restaurant->id,
                    'slug' => Str::slug($cat['name']),
                    'is_active' => true,
                ])
            );
        }

        // Create dishes
        $dishes = [
            // Entrées
            ['category' => 'Entrées', 'name' => 'Salade Africaine', 'price' => 2500, 'description' => 'Mélange de légumes frais avec vinaigrette maison'],
            ['category' => 'Entrées', 'name' => 'Accras de Morue', 'price' => 3000, 'description' => 'Beignets de morue croustillants', 'is_spicy' => true],
            ['category' => 'Entrées', 'name' => 'Aloco', 'price' => 1500, 'description' => 'Bananes plantain frites'],
            
            // Plats Principaux
            ['category' => 'Plats Principaux', 'name' => 'Attiéké Poisson', 'price' => 4500, 'description' => 'Semoule de manioc avec poisson braisé et légumes', 'is_featured' => true],
            ['category' => 'Plats Principaux', 'name' => 'Foutou Sauce Graine', 'price' => 5000, 'description' => 'Foutou banane avec sauce graine traditionnelle'],
            ['category' => 'Plats Principaux', 'name' => 'Riz Gras', 'price' => 4000, 'description' => 'Riz cuit dans une sauce tomate épicée', 'is_spicy' => true],
            ['category' => 'Plats Principaux', 'name' => 'Placali Sauce Kopé', 'price' => 4500, 'description' => 'Pâte de manioc avec sauce kopé au poisson fumé'],
            ['category' => 'Plats Principaux', 'name' => 'Kedjenou de Poulet', 'price' => 6000, 'description' => 'Poulet mijoté aux épices africaines', 'is_featured' => true, 'is_new' => true],
            
            // Grillades
            ['category' => 'Grillades', 'name' => 'Poulet Braisé', 'price' => 5500, 'description' => 'Demi-poulet mariné et grillé', 'is_featured' => true],
            ['category' => 'Grillades', 'name' => 'Poisson Braisé', 'price' => 6500, 'description' => 'Poisson du jour grillé aux épices'],
            ['category' => 'Grillades', 'name' => 'Brochettes de Bœuf', 'price' => 4000, 'description' => '4 brochettes de bœuf marinées'],
            ['category' => 'Grillades', 'name' => 'Côtes de Porc', 'price' => 5000, 'description' => 'Côtes de porc grillées sauce BBQ'],
            
            // Accompagnements
            ['category' => 'Accompagnements', 'name' => 'Attiéké Simple', 'price' => 1000, 'description' => 'Portion de semoule de manioc'],
            ['category' => 'Accompagnements', 'name' => 'Riz Blanc', 'price' => 800, 'description' => 'Riz nature'],
            ['category' => 'Accompagnements', 'name' => 'Frites Maison', 'price' => 1500, 'description' => 'Frites de pommes de terre fraîches'],
            ['category' => 'Accompagnements', 'name' => 'Légumes Sautés', 'price' => 1200, 'description' => 'Mélange de légumes de saison', 'is_vegetarian' => true],
            
            // Boissons
            ['category' => 'Boissons', 'name' => 'Bissap', 'price' => 1000, 'description' => 'Jus d\'hibiscus fait maison', 'is_vegetarian' => true, 'is_vegan' => true],
            ['category' => 'Boissons', 'name' => 'Gingembre', 'price' => 1000, 'description' => 'Jus de gingembre frais', 'is_vegetarian' => true, 'is_vegan' => true],
            ['category' => 'Boissons', 'name' => 'Baobab', 'price' => 1200, 'description' => 'Jus de fruit de baobab', 'is_vegetarian' => true, 'is_vegan' => true],
            ['category' => 'Boissons', 'name' => 'Eau Minérale', 'price' => 500, 'description' => 'Bouteille 50cl'],
            ['category' => 'Boissons', 'name' => 'Coca-Cola', 'price' => 800, 'description' => 'Canette 33cl'],
            
            // Desserts
            ['category' => 'Desserts', 'name' => 'Dèguè', 'price' => 1500, 'description' => 'Yaourt au mil avec crème', 'is_vegetarian' => true],
            ['category' => 'Desserts', 'name' => 'Salade de Fruits', 'price' => 2000, 'description' => 'Fruits frais de saison', 'is_vegetarian' => true, 'is_vegan' => true],
            ['category' => 'Desserts', 'name' => 'Beignets au Miel', 'price' => 1800, 'description' => 'Beignets chauds nappés de miel', 'is_vegetarian' => true],
        ];

        foreach ($dishes as $dishData) {
            $category = $categoryModels[$dishData['category']] ?? null;
            unset($dishData['category']);
            
            Dish::updateOrCreate(
                ['restaurant_id' => $restaurant->id, 'name' => $dishData['name']],
                array_merge($dishData, [
                    'restaurant_id' => $restaurant->id,
                    'category_id' => $category?->id,
                    'slug' => Str::slug($dishData['name']),
                    'is_active' => true,
                    'is_featured' => $dishData['is_featured'] ?? false,
                    'is_new' => $dishData['is_new'] ?? false,
                    'is_spicy' => $dishData['is_spicy'] ?? false,
                    'is_vegetarian' => $dishData['is_vegetarian'] ?? false,
                    'is_vegan' => $dishData['is_vegan'] ?? false,
                ])
            );
        }

        // Create ingredient categories
        $ingredientCategories = [
            ['name' => 'Viandes', 'color' => '#ef4444'],
            ['name' => 'Poissons', 'color' => '#3b82f6'],
            ['name' => 'Légumes', 'color' => '#22c55e'],
            ['name' => 'Féculents', 'color' => '#f59e0b'],
            ['name' => 'Épices', 'color' => '#8b5cf6'],
            ['name' => 'Boissons', 'color' => '#06b6d4'],
        ];

        $ingredientCategoryModels = [];
        foreach ($ingredientCategories as $cat) {
            $ingredientCategoryModels[$cat['name']] = IngredientCategory::updateOrCreate(
                ['restaurant_id' => $restaurant->id, 'name' => $cat['name']],
                array_merge($cat, ['restaurant_id' => $restaurant->id])
            );
        }

        // Create ingredients
        $ingredients = [
            ['name' => 'Poulet', 'category' => 'Viandes', 'unit' => Unit::KILOGRAM, 'quantity' => 25, 'min' => 5, 'cost' => 3500],
            ['name' => 'Bœuf', 'category' => 'Viandes', 'unit' => Unit::KILOGRAM, 'quantity' => 15, 'min' => 3, 'cost' => 5000],
            ['name' => 'Porc', 'category' => 'Viandes', 'unit' => Unit::KILOGRAM, 'quantity' => 10, 'min' => 2, 'cost' => 4000],
            ['name' => 'Tilapia', 'category' => 'Poissons', 'unit' => Unit::KILOGRAM, 'quantity' => 20, 'min' => 5, 'cost' => 4500],
            ['name' => 'Morue', 'category' => 'Poissons', 'unit' => Unit::KILOGRAM, 'quantity' => 5, 'min' => 1, 'cost' => 8000],
            ['name' => 'Tomate', 'category' => 'Légumes', 'unit' => Unit::KILOGRAM, 'quantity' => 30, 'min' => 10, 'cost' => 800],
            ['name' => 'Oignon', 'category' => 'Légumes', 'unit' => Unit::KILOGRAM, 'quantity' => 20, 'min' => 5, 'cost' => 600],
            ['name' => 'Banane Plantain', 'category' => 'Légumes', 'unit' => Unit::KILOGRAM, 'quantity' => 40, 'min' => 10, 'cost' => 500],
            ['name' => 'Attiéké', 'category' => 'Féculents', 'unit' => Unit::KILOGRAM, 'quantity' => 50, 'min' => 15, 'cost' => 700],
            ['name' => 'Riz', 'category' => 'Féculents', 'unit' => Unit::KILOGRAM, 'quantity' => 100, 'min' => 25, 'cost' => 900],
            ['name' => 'Huile', 'category' => 'Féculents', 'unit' => Unit::LITER, 'quantity' => 25, 'min' => 5, 'cost' => 1200],
            ['name' => 'Piment', 'category' => 'Épices', 'unit' => Unit::KILOGRAM, 'quantity' => 2, 'min' => 0.5, 'cost' => 3000],
            ['name' => 'Sel', 'category' => 'Épices', 'unit' => Unit::KILOGRAM, 'quantity' => 5, 'min' => 1, 'cost' => 300],
            ['name' => 'Bissap', 'category' => 'Boissons', 'unit' => Unit::LITER, 'quantity' => 10, 'min' => 2, 'cost' => 500],
            ['name' => 'Gingembre', 'category' => 'Boissons', 'unit' => Unit::LITER, 'quantity' => 8, 'min' => 2, 'cost' => 600],
        ];

        foreach ($ingredients as $ing) {
            $category = $ingredientCategoryModels[$ing['category']] ?? null;
            
            Ingredient::updateOrCreate(
                ['restaurant_id' => $restaurant->id, 'name' => $ing['name']],
                [
                    'restaurant_id' => $restaurant->id,
                    'ingredient_category_id' => $category?->id,
                    'name' => $ing['name'],
                    'unit' => $ing['unit'],
                    'current_quantity' => $ing['quantity'],
                    'min_quantity' => $ing['min'],
                    'unit_cost' => $ing['cost'],
                    'is_active' => true,
                ]
            );
        }

        // Create sample orders
        $this->createSampleOrders($restaurant);

        $this->command->info('Demo restaurant "Le Maquis d\'Abidjan" created successfully.');
        $this->command->info('Public URL: /r/demo');
        $this->command->info('Login: demo@menupro.ci / password');
    }

    protected function createSampleOrders(Restaurant $restaurant): void
    {
        $dishes = $restaurant->dishes()->limit(5)->get();
        
        if ($dishes->isEmpty()) {
            return;
        }

        $statuses = [
            OrderStatus::COMPLETED,
            OrderStatus::COMPLETED,
            OrderStatus::COMPLETED,
            OrderStatus::PREPARING,
            OrderStatus::CONFIRMED,
            OrderStatus::PAID,
        ];

        $customers = [
            ['name' => 'Aminata Koné', 'email' => 'aminata@example.com', 'phone' => '+225 0701234567'],
            ['name' => 'Moussa Traoré', 'email' => 'moussa@example.com', 'phone' => '+225 0702345678'],
            ['name' => 'Fatou Diallo', 'email' => 'fatou@example.com', 'phone' => '+225 0703456789'],
            ['name' => 'Ibrahim Sanogo', 'email' => 'ibrahim@example.com', 'phone' => '+225 0704567890'],
            ['name' => 'Aïcha Bamba', 'email' => 'aicha@example.com', 'phone' => '+225 0705678901'],
        ];

        foreach ($statuses as $index => $status) {
            $customer = $customers[array_rand($customers)];
            $orderDishes = $dishes->random(rand(1, 3));
            
            $subtotal = $orderDishes->sum('price');
            $deliveryFee = rand(0, 1) ? 1000 : 0;
            $total = $subtotal + $deliveryFee;

            $order = Order::create([
                'restaurant_id' => $restaurant->id,
                'reference' => Order::generateReference(),
                'customer_name' => $customer['name'],
                'customer_email' => $customer['email'],
                'customer_phone' => $customer['phone'],
                'type' => $deliveryFee > 0 ? OrderType::DELIVERY : OrderType::TAKEAWAY,
                'status' => $status,
                'subtotal' => $subtotal,
                'delivery_fee' => $deliveryFee,
                'discount_amount' => 0,
                'total' => $total,
                'payment_status' => $status !== OrderStatus::PAID 
                    ? PaymentStatus::COMPLETED 
                    : PaymentStatus::PENDING,
                'paid_at' => $status !== OrderStatus::PAID ? now()->subMinutes(rand(5, 60)) : null,
                'confirmed_at' => in_array($status, [OrderStatus::CONFIRMED, OrderStatus::PREPARING, OrderStatus::COMPLETED]) 
                    ? now()->subMinutes(rand(5, 30)) : null,
                'preparing_at' => in_array($status, [OrderStatus::PREPARING, OrderStatus::COMPLETED]) 
                    ? now()->subMinutes(rand(1, 20)) : null,
                'completed_at' => $status === OrderStatus::COMPLETED 
                    ? now()->subMinutes(rand(1, 10)) : null,
                'created_at' => now()->subHours(rand(0, 48)),
            ]);

            foreach ($orderDishes as $dish) {
                $quantity = rand(1, 3);
                $order->items()->create([
                    'dish_id' => $dish->id,
                    'dish_name' => $dish->name,
                    'unit_price' => $dish->price,
                    'quantity' => $quantity,
                    'total_price' => $dish->price * $quantity,
                ]);
            }
        }
    }
}

