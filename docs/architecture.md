# MenuPro — Architecture Technique

## 1. Vue d'ensemble

MenuPro est un SaaS multi-restaurants permettant :
- Inscription rapide et mise en ligne d'un site de commande
- Gestion des menus (catégories, plats, images)
- Gestion des commandes et paiements (Lygos)
- Gestion des stocks et ingrédients
- Abonnements avec plans et quotas

## 2. Stack Technique

- **Framework** : Laravel 11
- **Frontend** : Blade + Livewire + Alpine.js
- **CSS** : TailwindCSS 4
- **Base de données** : MySQL/PostgreSQL
- **Paiement** : Lygos API
- **File d'attente** : Laravel Queue (database/redis)

## 3. Architecture Multi-Tenant

Architecture légère basée sur une **base de données unique** avec scoping par `restaurant_id`.

### Stratégie de Scoping

```php
// Trait BelongsToRestaurant (app/Models/Traits/)
- Ajoute un global scope automatique
- Set automatiquement restaurant_id à la création
- Méthodes: forRestaurant(), withoutRestaurantScope()
```

### Middlewares

| Middleware | Rôle |
|------------|------|
| `restaurant.scope` | Définit le contexte restaurant (session/auth) |
| `restaurant.active` | Vérifie que le restaurant est actif (public) |
| `restaurant.has` | Vérifie que l'utilisateur a un restaurant |
| `super-admin` | Réservé aux super administrateurs |
| `subscription` | Vérifie l'abonnement actif |
| `track-login` | Track la dernière connexion |

## 4. Modèle de Données

### Schéma Principal

```
plans (3 plans: Starter, Pro, Premium)
├── max_dishes, max_categories, max_employees
├── has_delivery, has_stock_management, has_analytics
└── price, duration_days

restaurants
├── name, slug, email, phone
├── status (enum: pending, active, suspended, expired)
├── current_plan_id → plans
├── subscription_ends_at
├── lygos_api_key (encrypted)
└── settings (JSON)

users
├── name, email, phone, password
├── role (enum: super_admin, restaurant_admin, employee)
└── restaurant_id → restaurants (nullable)

subscriptions
├── restaurant_id → restaurants
├── plan_id → plans
├── status, starts_at, ends_at
└── payment_reference

categories
├── restaurant_id → restaurants
└── name, slug, sort_order, is_active

dishes
├── restaurant_id → restaurants
├── category_id → categories
├── name, price, description, image_path
├── is_active, is_featured, track_stock
└── options (via dish_option_groups)

orders
├── restaurant_id → restaurants
├── reference (unique), customer_name/email/phone
├── status (enum: draft → paid → confirmed → preparing → ready → completed)
├── type (enum: dine_in, takeaway, delivery)
├── subtotal, delivery_fee, total
└── payment_status, payment_reference

order_items
├── order_id → orders
├── dish_id → dishes
└── dish_name (snapshot), unit_price, quantity, total_price

ingredients
├── restaurant_id → restaurants
├── name, unit (enum), current_quantity, min_quantity
└── unit_cost

stock_movements
├── ingredient_id → ingredients
├── type (enum: entry, exit_sale, exit_manual, adjustment)
└── quantity, quantity_before, quantity_after

suppliers
├── restaurant_id → restaurants
└── name, contact_name, email, phone

promo_codes
├── restaurant_id → restaurants
├── code, discount_type, discount_value
└── max_uses, expires_at
```

## 5. Enums PHP

| Enum | Valeurs |
|------|---------|
| `UserRole` | super_admin, restaurant_admin, employee |
| `RestaurantStatus` | pending, active, suspended, expired |
| `SubscriptionStatus` | pending, active, expired, cancelled |
| `OrderStatus` | draft, pending_payment, paid, confirmed, preparing, ready, delivering, completed, cancelled, refunded |
| `OrderType` | dine_in, takeaway, delivery |
| `PaymentStatus` | pending, processing, completed, failed, refunded |
| `StockMovementType` | entry, exit_sale, exit_manual, exit_waste, adjustment |
| `Unit` | kg, g, L, mL, piece, pack, dozen, bottle |

## 6. Services

### MediaUploader
```php
$uploader->uploadLogo($file, $restaurantId);
$uploader->uploadDishImage($file, $restaurantId);
$uploader->delete($path);
```

### LygosGateway
```php
$lygos->forRestaurant($restaurant)
      ->createPayment($order, $returnUrl, $cancelUrl);
$lygos->verifyPayment($paymentId);
$lygos->verifyWebhookSignature($payload, $signature);
```

### PlanLimiter
```php
$limiter->forRestaurant($restaurant)
        ->canCreate('dishes'); // bool
$limiter->getRemainingQuota('categories');
$limiter->validateOrFail('employees'); // throws QuotaExceededException
```

### StockManager
```php
$stock->forRestaurant($restaurant)
      ->entry($ingredient, 10, 500); // +10 units at 500 FCFA/unit
$stock->exit($ingredient, 5, 'Consommation interne');
$stock->deductForOrder($order);
$stock->getLowStock();
```

## 7. Jobs & Scheduled Tasks

| Job | Schedule | Rôle |
|-----|----------|------|
| `ProcessSubscriptionExpiration` | Daily 00:00 | Marque les abonnements expirés |
| `SendSubscriptionReminders` | Daily 09:00 | Envoie rappels J-7 |
| `CheckLowStock` | Daily 08:00 | Alerte stock bas |

## 8. Policies & Autorisation

Chaque modèle a sa Policy :
- `RestaurantPolicy`
- `CategoryPolicy`
- `DishPolicy`
- `OrderPolicy`
- `IngredientPolicy`
- `UserPolicy`

**Gate Super Admin** : Les super admins bypassent toutes les policies.

## 9. Structure des Fichiers

```
app/
├── Enums/
│   ├── UserRole.php
│   ├── RestaurantStatus.php
│   ├── OrderStatus.php
│   └── ...
├── Models/
│   ├── Traits/
│   │   ├── BelongsToRestaurant.php
│   │   └── HasSlug.php
│   ├── Restaurant.php
│   ├── User.php
│   ├── Dish.php
│   └── ...
├── Http/
│   ├── Middleware/
│   │   ├── SetRestaurantScope.php
│   │   ├── EnsureRestaurantActive.php
│   │   └── ...
│   └── Requests/
│       ├── Auth/
│       └── Restaurant/
├── Policies/
├── Services/
│   ├── MediaUploader.php
│   ├── LygosGateway.php
│   ├── PlanLimiter.php
│   └── StockManager.php
├── Jobs/
├── Notifications/
└── Exceptions/
    └── QuotaExceededException.php
```

## 10. Identifiants de Test

| Rôle | Email | Mot de passe |
|------|-------|--------------|
| Super Admin | admin@menupro.ci | password |
| Restaurant Admin | demo@menupro.ci | password |

Restaurant de démo : **Le Délice** (slug: `le-delice`)

## 11. Plans Tarifaires

| Plan | Prix | Plats | Catégories | Employés | Features |
|------|------|-------|------------|----------|----------|
| Starter | 15,000 FCFA | 20 | 5 | 1 | Base |
| Pro | 35,000 FCFA | 50 | 15 | 3 | +Livraison, +Stock, +Analytics |
| Premium | 75,000 FCFA | 200 | 50 | 10 | +Domaine custom, +Support prioritaire |

## 12. Controllers

### Auth Controllers (`app/Http/Controllers/Auth/`)
- `LoginController` : Connexion/déconnexion
- `RegisterController` : Inscription restaurant
- `PasswordResetController` : Réinitialisation mot de passe
- `EmailVerificationController` : Vérification email

### Restaurant Controllers (`app/Http/Controllers/Restaurant/`)
- `DashboardController` : Dashboard + gestion équipe
- `CategoryController` : CRUD catégories
- `DishController` : CRUD plats + toggle disponibilité
- `OrderController` : Liste, détail, status, impression
- `CustomerController` : Liste clients + export
- `SettingsController` : Paramètres restaurant
- `SubscriptionController` : Gestion abonnement
- `IngredientController` : Stock + alertes + rapports
- `IngredientCategoryController` : Catégories d'ingrédients
- `SupplierController` : CRUD fournisseurs

### Super Admin Controllers (`app/Http/Controllers/SuperAdmin/`)
- `DashboardController` : Dashboard + paramètres système
- `RestaurantController` : CRUD restaurants + approve/suspend
- `PlanController` : CRUD plans tarifaires
- `UserController` : CRUD utilisateurs + suspend/reset password
- `ActivityController` : Logs d'activité
- `StatsController` : Statistiques globales

### Public Controllers (`app/Http/Controllers/Public/`)
- `MenuController` : Affichage menu restaurant
- `CheckoutController` : Commande + paiement Lygos
- `OrderStatusController` : Suivi commande client

### Webhook Controllers (`app/Http/Controllers/Webhook/`)
- `LygosWebhookController` : Gestion webhooks paiement

## 13. Routes (145 routes configurées)

| Préfixe | Domaine | Middlewares |
|---------|---------|-------------|
| `/` | Pages publiques marketing | - |
| `/connexion`, `/inscription` | Auth | guest |
| `/dashboard/*` | Admin Restaurant | auth, verified, has.restaurant, restaurant.active |
| `/admin/*` | Super Admin | auth, verified, super.admin |
| `/r/{slug}/*` | Site public restaurant | - |
| `/webhooks/*` | Webhooks externes | sans CSRF |

## 14. Prochaines Étapes

1. ✅ Migrations & Modèles
2. ✅ Middlewares & Policies
3. ✅ Services (Media, Paiement, Stock)
4. ✅ Jobs & Notifications
5. ✅ Seeders
6. ✅ Controllers (145 routes)
7. ⏳ Intégration Livewire (composants dynamiques)
8. ⏳ Tests (Unit + Feature)
9. ⏳ Intégration Lygos réelle
10. ⏳ Déploiement
