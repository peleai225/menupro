# Intégration API Jeko (Marketplace + Normal) Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Intégrer l'API Jeko pour permettre aux clients de payer leurs commandes via Mobile Money (Orange, MTN, Moov, Wave) avec reversement automatique aux restaurants (API Marketplace), et permettre aux restaurants de payer leurs abonnements MenuPro (API Normale).

**Architecture:** Architecture modulaire avec interface `PaymentGatewayInterface`, service `JekoGateway` supportant deux modes (Platform/Marketplace), onboarding restaurants avec validation admin, webhooks Jeko pour confirmations asynchrones, et backoffice super-admin pour configuration des gateways.

**Tech Stack:** Laravel 11, PHP 8.3, MySQL 8, Jeko API v1, Guzzle HTTP Client, Laravel Jobs/Queues

## Global Constraints

- PHP >= 8.3 (version actuelle du projet)
- Laravel 11 (version actuelle)
- Tous les montants stockés en **centimes** (int) dans la DB, convertis en FCFA pour les APIs
- Code PSR-12 compliant
- Tous les secrets (API keys) stockés chiffrés dans `system_payment_settings` table, jamais dans .env en production
- Tous les logs de paiement dans le channel `payments` (config/logging.php)
- Transactions atomiques avec locks pour éviter double-traitement des webhooks
- Tests avec PHPUnit (feature tests pour workflows complets)
- Commits fréquents (un par étape fonctionnelle)
- Messages de commit format: `type(scope): description` (ex: `feat(jeko): add payment gateway interface`)
- Pas de placeholders "TODO", "TBD" dans le code de production
- Vérification signature HMAC pour tous les webhooks entrants
- Idempotence garantie pour webhooks et payouts

---

## File Structure

### New Files (to create)

**Contracts:**
- `app/Contracts/PaymentGatewayInterface.php` — Interface commune pour tous les gateways

**Services:**
- `app/Services/JekoGateway.php` — Service principal Jeko (Marketplace + Normal modes)
- `app/Services/PaymentService.php` — Orchestrateur de paiements (factory pattern)

**Models:**
- `app/Models/JekoSubMerchant.php` — Restaurants intégrés comme sous-marchands Jeko
- `app/Models/SystemPaymentSetting.php` — Configuration des gateways (API keys chiffrées)

**Enums:**
- `app/Enums/PaymentGateway.php` — Liste des gateways disponibles (WAVE, JEKO, CINETPAY, CASH)
- `app/Enums/JekoSubMerchantStatus.php` — Statuts onboarding (PENDING, APPROVED, REJECTED, INTEGRATED)
- `app/Enums/MobileMoneyOperator.php` — Opérateurs (ORANGE, MTN, MOOV, WAVE)

**Controllers:**
- `app/Http/Controllers/Webhooks/JekoWebhookController.php` — Réception webhooks Jeko
- `app/Http/Controllers/Restaurant/JekoOnboardingController.php` — Onboarding resto (demande + statut)
- `app/Http/Controllers/Admin/JekoSubMerchantController.php` — Admin: validation demandes
- `app/Http/Controllers/Admin/PaymentSettingsController.php` — Admin: config gateways

**Jobs:**
- `app/Jobs/IntegrateJekoSubMerchantJob.php` — Intégration auto Jeko après validation admin
- `app/Jobs/ProcessJekoPayoutJob.php` — Reversement auto restaurant après paiement commande

**Migrations:**
- `database/migrations/YYYY_MM_DD_000001_create_jeko_sub_merchants_table.php`
- `database/migrations/YYYY_MM_DD_000002_create_system_payment_settings_table.php`
- `database/migrations/YYYY_MM_DD_000003_add_jeko_columns_to_payment_transactions.php`
- `database/migrations/YYYY_MM_DD_000004_add_gateway_enum_to_orders_and_subscriptions.php`

**Config:**
- `config/jeko.php` — Configuration Jeko (base URL, timeouts, etc.)

**Routes:**
- `routes/webhooks.php` (modifier) — Ajouter route POST /webhooks/jeko
- `routes/restaurant.php` (modifier) — Routes onboarding Jeko
- `routes/admin.php` (modifier) — Routes admin validation + settings

**Views:**
- `resources/views/restaurant/settings/jeko-onboarding.blade.php` — Formulaire demande Jeko
- `resources/views/admin/jeko/pending-requests.blade.php` — Liste demandes en attente
- `resources/views/admin/payment-settings/index.blade.php` — Config gateways (API keys)

**Tests:**
- `tests/Feature/Jeko/OnboardingWorkflowTest.php` — Test workflow onboarding complet
- `tests/Feature/Jeko/PaymentWorkflowTest.php` — Test paiement commande + payout
- `tests/Feature/Jeko/SubscriptionPaymentTest.php` — Test paiement abonnement
- `tests/Feature/Jeko/WebhookTest.php` — Test webhook handling + signature
- `tests/Unit/JekoGatewayTest.php` — Tests unitaires service Jeko

### Files to Modify

- `app/Services/WaveGateway.php` — Implémenter PaymentGatewayInterface
- `app/Models/PaymentTransaction.php` — Ajouter relations Jeko
- `app/Models/Restaurant.php` — Ajouter relation jekoSubMerchant()
- `app/Http/Controllers/Api/V1/Client/PaymentController.php` — Utiliser PaymentService au lieu de WaveGateway directement
- `.env.example` — Ajouter variables JEKO_*

---

## Task 1: Base Architecture — Payment Gateway Interface

**Files:**
- Create: `app/Contracts/PaymentGatewayInterface.php`
- Create: `app/Enums/PaymentGateway.php`
- Create: `app/Services/PaymentService.php`
- Test: `tests/Unit/PaymentServiceTest.php`

**Interfaces:**
- Consumes: Nothing (foundation layer)
- Produces: 
  - `PaymentGatewayInterface` with methods: `isConfigured()`, `forPlatform()`, `createPayment()`, `getPaymentStatus()`, `payout()`, `verifyWebhookSignature()`
  - `PaymentService::gateway(string $gatewayName): PaymentGatewayInterface`
  - `PaymentGateway` enum with cases: WAVE, JEKO, CINETPAY, CASH

- [ ] **Step 1: Create PaymentGateway enum**

```php
<?php

namespace App\Enums;

enum PaymentGateway: string
{
    case WAVE = 'wave';
    case JEKO = 'jeko';
    case CINETPAY = 'cinetpay';
    case CASH = 'cash';

    public function label(): string
    {
        return match($this) {
            self::WAVE => 'Wave CI',
            self::JEKO => 'Jeko Mobile Money',
            self::CINETPAY => 'CinetPay',
            self::CASH => 'Espèces',
        };
    }

    public function supportsOnlinePayment(): bool
    {
        return $this !== self::CASH;
    }
}
```

- [ ] **Step 2: Create PaymentGatewayInterface**

```php
<?php

namespace App\Contracts;

use App\Models\Order;
use App\Models\Subscription;

interface PaymentGatewayInterface
{
    /**
     * Vérifie si le gateway est configuré (API keys présentes).
     */
    public function isConfigured(): bool;

    /**
     * Configure le gateway en mode plateforme (paiements vers MenuPro).
     */
    public function forPlatform(): static;

    /**
     * Crée une demande de paiement (Pay-in).
     * 
     * @param Order|Subscription $entity L'entité à payer
     * @param string $successUrl URL de retour succès
     * @param string $errorUrl URL de retour erreur
     * @return array ['success' => bool, 'payment_id' => string, 'payment_url' => string, 'error' => string]
     */
    public function createPayment(Order|Subscription $entity, string $successUrl, string $errorUrl): array;

    /**
     * Récupère le statut d'un paiement.
     * 
     * @param string $paymentId ID du paiement côté gateway
     * @return array ['success' => bool, 'status' => string, 'data' => array, 'error' => string]
     */
    public function getPaymentStatus(string $paymentId): array;

    /**
     * Effectue un payout (Pay-out) vers un bénéficiaire.
     * 
     * @param string $recipient Numéro mobile ou identifiant bénéficiaire
     * @param int $amount Montant en centimes
     * @param string $reference Référence unique de la transaction
     * @return array ['success' => bool, 'transfer_id' => string, 'status' => string, 'error' => string]
     */
    public function payout(string $recipient, int $amount, string $reference): array;

    /**
     * Vérifie la signature HMAC d'un webhook.
     * 
     * @param string $rawPayload Payload brut du webhook
     * @param string $signatureHeader Header de signature
     * @return bool True si signature valide
     */
    public function verifyWebhookSignature(string $rawPayload, string $signatureHeader): bool;
}
```

- [ ] **Step 3: Create PaymentService orchestrator**

```php
<?php

namespace App\Services;

use App\Contracts\PaymentGatewayInterface;
use App\Enums\PaymentGateway;
use InvalidArgumentException;

class PaymentService
{
    /**
     * Retourne une instance de gateway selon le nom.
     *
     * @throws InvalidArgumentException Si le gateway est inconnu
     */
    public static function gateway(string $gatewayName): PaymentGatewayInterface
    {
        $gateway = PaymentGateway::tryFrom($gatewayName);

        if (!$gateway) {
            throw new InvalidArgumentException("Gateway inconnu: {$gatewayName}");
        }

        return match($gateway) {
            PaymentGateway::WAVE => app(WaveGateway::class)->forPlatform(),
            PaymentGateway::JEKO => app(JekoGateway::class)->forPlatform(),
            PaymentGateway::CINETPAY => throw new InvalidArgumentException('CinetPay pas encore implémenté'),
            PaymentGateway::CASH => throw new InvalidArgumentException('Cash ne supporte pas les paiements en ligne'),
        };
    }

    /**
     * Vérifie si un gateway est disponible et configuré.
     */
    public static function isGatewayAvailable(string $gatewayName): bool
    {
        try {
            $gateway = self::gateway($gatewayName);
            return $gateway->isConfigured();
        } catch (\Exception $e) {
            return false;
        }
    }
}
```

- [ ] **Step 4: Write test for PaymentService**

```php
<?php

namespace Tests\Unit;

use App\Enums\PaymentGateway;
use App\Services\PaymentService;
use App\Services\WaveGateway;
use Tests\TestCase;

class PaymentServiceTest extends TestCase
{
    public function test_gateway_returns_wave_instance()
    {
        $gateway = PaymentService::gateway('wave');

        $this->assertInstanceOf(WaveGateway::class, $gateway);
    }

    public function test_gateway_throws_for_unknown_gateway()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Gateway inconnu: fake_gateway');

        PaymentService::gateway('fake_gateway');
    }

    public function test_gateway_throws_for_cash()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Cash ne supporte pas les paiements en ligne');

        PaymentService::gateway('cash');
    }
}
```

- [ ] **Step 5: Run test**

Run: `php artisan test --filter=PaymentServiceTest`  
Expected: PASS (3 tests)

- [ ] **Step 6: Commit**

```bash
git add app/Contracts/PaymentGatewayInterface.php app/Enums/PaymentGateway.php app/Services/PaymentService.php tests/Unit/PaymentServiceTest.php
git commit -m "feat(payments): add PaymentGatewayInterface and PaymentService orchestrator

- PaymentGatewayInterface: contrat commun pour tous les gateways
- PaymentGateway enum: WAVE, JEKO, CINETPAY, CASH
- PaymentService: factory pattern pour instancier les gateways
- Tests unitaires PaymentService (3 tests)"
```

---

## Task 2: System Payment Settings — Secure Gateway Configuration

**Files:**
- Create: `app/Models/SystemPaymentSetting.php`
- Create: `database/migrations/YYYY_MM_DD_000002_create_system_payment_settings_table.php`
- Create: `database/seeders/SystemPaymentSettingsSeeder.php`
- Test: `tests/Unit/SystemPaymentSettingTest.php`

**Interfaces:**
- Consumes: Nothing
- Produces:
  - `SystemPaymentSetting` model with methods: `getDecryptedApiKey()`, `setEncryptedApiKey(string $key)`, `isActive()`, `isSandbox()`
  - Migration creating `system_payment_settings` table

- [ ] **Step 1: Create migration**

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('system_payment_settings', function (Blueprint $table) {
            $table->id();
            $table->string('gateway', 50)->unique();
            $table->boolean('is_active')->default(false);
            $table->enum('mode', ['sandbox', 'production'])->default('sandbox');
            
            // Credentials chiffrés
            $table->text('api_key')->nullable();
            $table->text('webhook_secret')->nullable();
            $table->string('merchant_id')->nullable();
            
            // Config JSON (timeouts, URLs custom, etc.)
            $table->json('config')->nullable();
            
            $table->timestamps();
            
            $table->index('gateway');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('system_payment_settings');
    }
};
```

- [ ] **Step 2: Run migration**

Run: `php artisan migrate`  
Expected: Migration successful, table created

- [ ] **Step 3: Create SystemPaymentSetting model**

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class SystemPaymentSetting extends Model
{
    protected $fillable = [
        'gateway',
        'is_active',
        'mode',
        'api_key',
        'webhook_secret',
        'merchant_id',
        'config',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'config' => 'array',
    ];

    /**
     * Récupère la clé API déchiffrée.
     */
    public function getDecryptedApiKey(): ?string
    {
        if (empty($this->api_key)) {
            return null;
        }

        try {
            return Crypt::decryptString($this->api_key);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Stocke la clé API chiffrée.
     */
    public function setEncryptedApiKey(string $key): void
    {
        $this->api_key = Crypt::encryptString($key);
    }

    /**
     * Récupère le webhook secret déchiffré.
     */
    public function getDecryptedWebhookSecret(): ?string
    {
        if (empty($this->webhook_secret)) {
            return null;
        }

        try {
            return Crypt::decryptString($this->webhook_secret);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Stocke le webhook secret chiffré.
     */
    public function setEncryptedWebhookSecret(string $secret): void
    {
        $this->webhook_secret = Crypt::encryptString($secret);
    }

    /**
     * Vérifie si le gateway est actif.
     */
    public function isActive(): bool
    {
        return $this->is_active && !empty($this->api_key);
    }

    /**
     * Vérifie si le mode sandbox est actif.
     */
    public function isSandbox(): bool
    {
        return $this->mode === 'sandbox';
    }
}
```

- [ ] **Step 4: Create seeder**

```php
<?php

namespace Database\Seeders;

use App\Models\SystemPaymentSetting;
use Illuminate\Database\Seeder;

class SystemPaymentSettingsSeeder extends Seeder
{
    public function run(): void
    {
        $gateways = [
            ['gateway' => 'jeko_marketplace', 'is_active' => false, 'mode' => 'production'],
            ['gateway' => 'jeko_normal', 'is_active' => false, 'mode' => 'production'],
            ['gateway' => 'wave', 'is_active' => false, 'mode' => 'production'],
            ['gateway' => 'cinetpay', 'is_active' => false, 'mode' => 'production'],
        ];

        foreach ($gateways as $gateway) {
            SystemPaymentSetting::firstOrCreate(
                ['gateway' => $gateway['gateway']],
                $gateway
            );
        }
    }
}
```

- [ ] **Step 5: Run seeder**

Run: `php artisan db:seed --class=SystemPaymentSettingsSeeder`  
Expected: 4 gateways created

- [ ] **Step 6: Write test**

```php
<?php

namespace Tests\Unit;

use App\Models\SystemPaymentSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SystemPaymentSettingTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_key_is_encrypted_and_decrypted()
    {
        $setting = SystemPaymentSetting::create([
            'gateway' => 'test_gateway',
            'is_active' => true,
            'mode' => 'sandbox',
        ]);

        $setting->setEncryptedApiKey('secret_key_123');
        $setting->save();

        $this->assertNotEquals('secret_key_123', $setting->api_key);
        $this->assertEquals('secret_key_123', $setting->getDecryptedApiKey());
    }

    public function test_is_active_returns_false_if_no_api_key()
    {
        $setting = SystemPaymentSetting::create([
            'gateway' => 'test_gateway',
            'is_active' => true,
            'mode' => 'sandbox',
        ]);

        $this->assertFalse($setting->isActive());
    }

    public function test_is_active_returns_true_if_api_key_present()
    {
        $setting = SystemPaymentSetting::create([
            'gateway' => 'test_gateway',
            'is_active' => true,
            'mode' => 'sandbox',
        ]);

        $setting->setEncryptedApiKey('secret_key');
        $setting->save();

        $this->assertTrue($setting->isActive());
    }

    public function test_is_sandbox_returns_correct_mode()
    {
        $setting = SystemPaymentSetting::create([
            'gateway' => 'test_gateway',
            'mode' => 'sandbox',
        ]);

        $this->assertTrue($setting->isSandbox());

        $setting->mode = 'production';
        $this->assertFalse($setting->isSandbox());
    }
}
```

- [ ] **Step 7: Run test**

Run: `php artisan test --filter=SystemPaymentSettingTest`  
Expected: PASS (4 tests)

- [ ] **Step 8: Commit**

```bash
git add app/Models/SystemPaymentSetting.php database/migrations/*create_system_payment_settings* database/seeders/SystemPaymentSettingsSeeder.php tests/Unit/SystemPaymentSettingTest.php
git commit -m "feat(payments): add SystemPaymentSetting model for secure gateway config

- Credentials chiffrés (api_key, webhook_secret) avec Crypt
- Mode sandbox/production
- Seeder pour initialiser les 4 gateways
- Tests unitaires (4 tests)"
```

---

## Task 3: Jeko Sub-Merchant Model and Enums

**Files:**
- Create: `app/Models/JekoSubMerchant.php`
- Create: `app/Enums/JekoSubMerchantStatus.php`
- Create: `app/Enums/MobileMoneyOperator.php`
- Create: `database/migrations/YYYY_MM_DD_000001_create_jeko_sub_merchants_table.php`
- Modify: `app/Models/Restaurant.php` (add relation)
- Test: `tests/Unit/JekoSubMerchantTest.php`

**Interfaces:**
- Consumes: Nothing
- Produces:
  - `JekoSubMerchant` model with methods: `isPending()`, `isIntegrated()`, `canReceivePayments()`
  - `JekoSubMerchantStatus` enum: PENDING, APPROVED, REJECTED, INTEGRATED
  - `MobileMoneyOperator` enum: ORANGE, MTN, MOOV, WAVE
  - `Restaurant::jekoSubMerchant()` relation

- [ ] **Step 1: Create JekoSubMerchantStatus enum**

```php
<?php

namespace App\Enums;

enum JekoSubMerchantStatus: string
{
    case PENDING = 'pending';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';
    case INTEGRATED = 'integrated';

    public function label(): string
    {
        return match($this) {
            self::PENDING => 'En attente',
            self::APPROVED => 'Approuvé',
            self::REJECTED => 'Rejeté',
            self::INTEGRATED => 'Intégré',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::PENDING => 'yellow',
            self::APPROVED => 'blue',
            self::REJECTED => 'red',
            self::INTEGRATED => 'green',
        };
    }
}
```

- [ ] **Step 2: Create MobileMoneyOperator enum**

```php
<?php

namespace App\Enums;

enum MobileMoneyOperator: string
{
    case ORANGE = 'orange';
    case MTN = 'mtn';
    case MOOV = 'moov';
    case WAVE = 'wave';

    public function label(): string
    {
        return match($this) {
            self::ORANGE => 'Orange Money',
            self::MTN => 'MTN Mobile Money',
            self::MOOV => 'Moov Money',
            self::WAVE => 'Wave',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::ORANGE => '#FF6600',
            self::MTN => '#FFCC00',
            self::MOOV => '#009DDB',
            self::WAVE => '#1B4D89',
        };
    }
}
```

- [ ] **Step 3: Create migration**

```php
<?php

use App\Enums\JekoSubMerchantStatus;
use App\Enums\MobileMoneyOperator;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jeko_sub_merchants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')->unique()->constrained()->cascadeOnDelete();
            
            // Statut onboarding
            $table->string('status')->default(JekoSubMerchantStatus::PENDING->value);
            
            // Infos KYC
            $table->string('legal_name');
            $table->string('business_type', 100)->nullable();
            $table->string('mobile_money', 20);
            $table->string('mobile_money_operator');
            $table->string('email')->nullable();
            
            // Données Jeko retournées après intégration
            $table->string('jeko_merchant_id')->unique()->nullable();
            $table->string('jeko_store_id')->nullable();
            $table->string('jeko_wallet_id')->nullable();
            $table->text('jeko_api_key')->nullable(); // Chiffré
            
            // Validation admin
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('rejected_reason')->nullable();
            
            // Métadonnées
            $table->json('integration_metadata')->nullable();
            
            $table->timestamps();
            
            // Index
            $table->index('status');
            $table->index('jeko_merchant_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jeko_sub_merchants');
    }
};
```

- [ ] **Step 4: Run migration**

Run: `php artisan migrate`  
Expected: Table created successfully

- [ ] **Step 5: Create JekoSubMerchant model**

```php
<?php

namespace App\Models;

use App\Enums\JekoSubMerchantStatus;
use App\Enums\MobileMoneyOperator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Crypt;

class JekoSubMerchant extends Model
{
    protected $fillable = [
        'restaurant_id',
        'status',
        'legal_name',
        'business_type',
        'mobile_money',
        'mobile_money_operator',
        'email',
        'jeko_merchant_id',
        'jeko_store_id',
        'jeko_wallet_id',
        'jeko_api_key',
        'approved_by',
        'approved_at',
        'rejected_reason',
        'integration_metadata',
    ];

    protected $casts = [
        'status' => JekoSubMerchantStatus::class,
        'mobile_money_operator' => MobileMoneyOperator::class,
        'approved_at' => 'datetime',
        'integration_metadata' => 'array',
    ];

    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function isPending(): bool
    {
        return $this->status === JekoSubMerchantStatus::PENDING;
    }

    public function isApproved(): bool
    {
        return $this->status === JekoSubMerchantStatus::APPROVED;
    }

    public function isRejected(): bool
    {
        return $this->status === JekoSubMerchantStatus::REJECTED;
    }

    public function isIntegrated(): bool
    {
        return $this->status === JekoSubMerchantStatus::INTEGRATED && !empty($this->jeko_merchant_id);
    }

    public function canReceivePayments(): bool
    {
        return $this->isIntegrated();
    }

    public function getDecryptedApiKey(): ?string
    {
        if (empty($this->jeko_api_key)) {
            return null;
        }

        try {
            return Crypt::decryptString($this->jeko_api_key);
        } catch (\Exception $e) {
            return null;
        }
    }

    public function setEncryptedApiKey(string $key): void
    {
        $this->jeko_api_key = Crypt::encryptString($key);
    }
}
```

- [ ] **Step 6: Add relation to Restaurant model**

Modify `app/Models/Restaurant.php`:

```php
use App\Models\JekoSubMerchant;
use Illuminate\Database\Eloquent\Relations\HasOne;

// Dans la classe Restaurant, ajouter:

public function jekoSubMerchant(): HasOne
{
    return $this->hasOne(JekoSubMerchant::class);
}

public function hasJekoIntegrated(): bool
{
    return $this->jekoSubMerchant?->isIntegrated() ?? false;
}
```

- [ ] **Step 7: Write test**

```php
<?php

namespace Tests\Unit;

use App\Enums\JekoSubMerchantStatus;
use App\Enums\MobileMoneyOperator;
use App\Models\JekoSubMerchant;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JekoSubMerchantTest extends TestCase
{
    use RefreshDatabase;

    public function test_is_pending_returns_true_for_pending_status()
    {
        $restaurant = Restaurant::factory()->create();
        $subMerchant = JekoSubMerchant::create([
            'restaurant_id' => $restaurant->id,
            'status' => JekoSubMerchantStatus::PENDING,
            'legal_name' => 'Test Restaurant SARL',
            'mobile_money' => '0700000000',
            'mobile_money_operator' => MobileMoneyOperator::ORANGE,
        ]);

        $this->assertTrue($subMerchant->isPending());
        $this->assertFalse($subMerchant->isIntegrated());
    }

    public function test_is_integrated_returns_true_when_has_merchant_id()
    {
        $restaurant = Restaurant::factory()->create();
        $subMerchant = JekoSubMerchant::create([
            'restaurant_id' => $restaurant->id,
            'status' => JekoSubMerchantStatus::INTEGRATED,
            'legal_name' => 'Test Restaurant',
            'mobile_money' => '0700000000',
            'mobile_money_operator' => MobileMoneyOperator::MTN,
            'jeko_merchant_id' => 'jeko_merchant_123',
            'jeko_store_id' => 'store_123',
        ]);

        $this->assertTrue($subMerchant->isIntegrated());
        $this->assertTrue($subMerchant->canReceivePayments());
    }

    public function test_api_key_encryption()
    {
        $restaurant = Restaurant::factory()->create();
        $subMerchant = JekoSubMerchant::create([
            'restaurant_id' => $restaurant->id,
            'legal_name' => 'Test',
            'mobile_money' => '0700000000',
            'mobile_money_operator' => MobileMoneyOperator::WAVE,
        ]);

        $subMerchant->setEncryptedApiKey('secret_api_key_xyz');
        $subMerchant->save();

        $this->assertNotEquals('secret_api_key_xyz', $subMerchant->jeko_api_key);
        $this->assertEquals('secret_api_key_xyz', $subMerchant->getDecryptedApiKey());
    }

    public function test_restaurant_relation()
    {
        $restaurant = Restaurant::factory()->create();
        $subMerchant = JekoSubMerchant::create([
            'restaurant_id' => $restaurant->id,
            'legal_name' => 'Test',
            'mobile_money' => '0700000000',
            'mobile_money_operator' => MobileMoneyOperator::ORANGE,
        ]);

        $this->assertEquals($restaurant->id, $subMerchant->restaurant->id);
        $this->assertEquals($subMerchant->id, $restaurant->jekoSubMerchant->id);
    }
}
```

- [ ] **Step 8: Run test**

Run: `php artisan test --filter=JekoSubMerchantTest`  
Expected: PASS (4 tests)

- [ ] **Step 9: Commit**

```bash
git add app/Models/JekoSubMerchant.php app/Enums/JekoSubMerchantStatus.php app/Enums/MobileMoneyOperator.php database/migrations/*create_jeko_sub_merchants* app/Models/Restaurant.php tests/Unit/JekoSubMerchantTest.php
git commit -m "feat(jeko): add JekoSubMerchant model and enums

- JekoSubMerchant model: onboarding restaurants comme sous-marchands Jeko
- JekoSubMerchantStatus enum: PENDING, APPROVED, REJECTED, INTEGRATED
- MobileMoneyOperator enum: ORANGE, MTN, MOOV, WAVE
- Relation Restaurant::jekoSubMerchant()
- API key chiffrée pour chaque sous-marchand
- Tests unitaires (4 tests)"
```

---

## Task 4: Jeko Gateway Service — Core Implementation

**Files:**
- Create: `app/Services/JekoGateway.php`
- Create: `config/jeko.php`
- Modify: `.env.example` (add JEKO_* variables)
- Test: `tests/Unit/JekoGatewayTest.php`

**Interfaces:**
- Consumes:
  - `PaymentGatewayInterface` from Task 1
  - `SystemPaymentSetting` from Task 2
  - `JekoSubMerchant` from Task 3
- Produces:
  - `JekoGateway` service with methods: `forPlatform()`, `forMarketplace(Restaurant $restaurant)`, `createPayment()`, `payout()`, `integrateSubMerchant()`, `createContact()`, `getPaymentStatus()`, `verifyWebhookSignature()`

- [ ] **Step 1: Create config/jeko.php**

```php
<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Jeko API Configuration
    |--------------------------------------------------------------------------
    |
    | Base URL et timeouts pour l'API Jeko.
    | Les credentials (API keys) sont gérés dans SystemPaymentSetting.
    |
    */

    'base_url' => env('JEKO_API_URL', 'https://api.jeko.africa/v1'),

    'timeout' => env('JEKO_TIMEOUT', 30), // Timeout HTTP en secondes

    'payout_timeout' => env('JEKO_PAYOUT_TIMEOUT', 60), // Timeout pour payouts (plus long)

    'currency' => env('JEKO_CURRENCY', 'XOF'),

    /*
    |--------------------------------------------------------------------------
    | Marketplace Mode
    |--------------------------------------------------------------------------
    |
    | Configuration spécifique au mode Marketplace (sous-marchands).
    |
    */

    'marketplace' => [
        'enabled' => env('JEKO_MARKETPLACE_ENABLED', true),
    ],
];
```

- [ ] **Step 2: Add Jeko variables to .env.example**

Append to `.env.example`:

```bash
# ═══════════════════════════════════════════════════════════════════
# Jeko — Paiements Mobile Money (Orange, MTN, Moov, Wave)
# ⚠️  JEKO_API_KEY et JEKO_WEBHOOK_SECRET sont gérés depuis le backoffice
#     super-admin (Paramètres système → Paiements). Ne pas renseigner ici.
# ═══════════════════════════════════════════════════════════════════
JEKO_API_URL=https://api.jeko.africa/v1
JEKO_TIMEOUT=30
JEKO_PAYOUT_TIMEOUT=60
JEKO_CURRENCY=XOF
JEKO_MARKETPLACE_ENABLED=true
```

- [ ] **Step 3: Create JekoGateway service**

```php
<?php

namespace App\Services;

use App\Contracts\PaymentGatewayInterface;
use App\Models\JekoSubMerchant;
use App\Models\Order;
use App\Models\Restaurant;
use App\Models\Subscription;
use App\Models\SystemPaymentSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class JekoGateway implements PaymentGatewayInterface
{
    protected string $baseUrl;
    protected string $apiKey;
    protected string $webhookSecret;
    protected string $currency;
    protected int $timeout;
    protected int $payoutTimeout;
    
    protected bool $isMarketplaceMode = false;
    protected ?Restaurant $restaurant = null;

    public function __construct()
    {
        $this->baseUrl = config('jeko.base_url');
        $this->currency = config('jeko.currency');
        $this->timeout = config('jeko.timeout');
        $this->payoutTimeout = config('jeko.payout_timeout');
        
        // Credentials chargés via loadConfig()
        $this->apiKey = '';
        $this->webhookSecret = '';
    }

    /**
     * Configure le gateway en mode Marketplace (paiements restaurants).
     */
    public function forMarketplace(Restaurant $restaurant): static
    {
        $this->isMarketplaceMode = true;
        $this->restaurant = $restaurant;
        $this->loadConfig();
        
        return $this;
    }

    /**
     * Configure le gateway en mode Normal (paiements plateforme).
     */
    public function forPlatform(): static
    {
        $this->isMarketplaceMode = false;
        $this->restaurant = null;
        $this->loadConfig();
        
        return $this;
    }

    /**
     * Charge les credentials depuis SystemPaymentSetting.
     */
    protected function loadConfig(): void
    {
        $gateway = $this->isMarketplaceMode ? 'jeko_marketplace' : 'jeko_normal';
        
        $setting = SystemPaymentSetting::where('gateway', $gateway)->first();
        
        if ($setting) {
            $this->apiKey = $setting->getDecryptedApiKey() ?? '';
            $this->webhookSecret = $setting->getDecryptedWebhookSecret() ?? '';
        }
    }

    public function isConfigured(): bool
    {
        if (empty($this->apiKey)) {
            $this->loadConfig();
        }
        
        return !empty($this->apiKey);
    }

    /**
     * Crée une demande de paiement Jeko (Pay-in).
     */
    public function createPayment(Order|Subscription $entity, string $successUrl, string $errorUrl): array
    {
        if (!$this->isConfigured()) {
            return ['success' => false, 'error' => 'Jeko API key not configured'];
        }

        $amount = $entity instanceof Order ? $entity->total : $entity->amount_paid;
        $amountFcfa = (int) ($amount / 100); // Centimes → FCFA
        
        $reference = $entity instanceof Order 
            ? "ORDER-{$entity->id}-{$entity->reference}" 
            : "SUB-{$entity->id}-" . now()->timestamp;

        try {
            $response = Http::withToken($this->apiKey)
                ->timeout($this->timeout)
                ->post("{$this->baseUrl}/demandes-de-paiement", [
                    'montant' => $amountFcfa,
                    'devise' => $this->currency,
                    'reference_client' => $reference,
                    'url_succes' => $successUrl,
                    'url_erreur' => $errorUrl,
                    'type' => 'redirect',
                ]);

            if ($response->successful()) {
                $data = $response->json();

                Log::channel('payments')->info('Jeko payment request created', [
                    'entity_type' => $entity instanceof Order ? 'order' : 'subscription',
                    'entity_id' => $entity->id,
                    'payment_id' => $data['id'] ?? null,
                    'amount' => $amountFcfa,
                ]);

                return [
                    'success' => true,
                    'payment_id' => $data['id'],
                    'payment_url' => $data['url_redirection'] ?? $data['url'],
                    'status' => $data['statut'] ?? 'pending',
                ];
            }

            Log::channel('payments')->error('Jeko payment request failed', [
                'entity_type' => $entity instanceof Order ? 'order' : 'subscription',
                'entity_id' => $entity->id,
                'status' => $response->status(),
                'body' => $response->json(),
            ]);

            return [
                'success' => false,
                'error' => $response->json('message') ?? 'Erreur Jeko payment request',
            ];
        } catch (\Exception $e) {
            Log::channel('payments')->error('Jeko payment request exception', [
                'entity_type' => $entity instanceof Order ? 'order' : 'subscription',
                'entity_id' => $entity->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => 'Impossible de contacter Jeko',
            ];
        }
    }

    /**
     * Récupère le statut d'un paiement Jeko.
     */
    public function getPaymentStatus(string $paymentId): array
    {
        if (!$this->isConfigured()) {
            return ['success' => false, 'error' => 'Jeko API key not configured'];
        }

        try {
            $response = Http::withToken($this->apiKey)
                ->timeout(15)
                ->get("{$this->baseUrl}/demandes-de-paiement/{$paymentId}");

            if ($response->successful()) {
                return [
                    'success' => true,
                    'status' => $response->json('statut'),
                    'data' => $response->json(),
                ];
            }

            return [
                'success' => false,
                'error' => 'Paiement introuvable',
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Effectue un payout (virement) vers un restaurant.
     */
    public function payout(string $recipient, int $amount, string $reference): array
    {
        if (!$this->isConfigured()) {
            return ['success' => false, 'error' => 'Jeko API key not configured'];
        }

        if ($amount < 100) {
            return ['success' => false, 'error' => 'Montant trop faible (min 100 FCFA)'];
        }

        $amountFcfa = (int) ($amount / 100); // Centimes → FCFA

        // 1. Créer le contact bénéficiaire (si pas existant)
        $contact = $this->createContact($recipient);

        if (!$contact['success']) {
            return $contact;
        }

        // 2. Créer le virement
        try {
            $response = Http::withToken($this->apiKey)
                ->timeout($this->payoutTimeout)
                ->post("{$this->baseUrl}/virements", [
                    'contact_id' => $contact['contact_id'],
                    'montant' => $amountFcfa,
                    'devise' => $this->currency,
                    'reference_client' => $reference,
                    'raison' => 'Reversement commande MenuPro',
                ]);

            if ($response->successful()) {
                $data = $response->json();

                Log::channel('payments')->info('Jeko payout succeeded', [
                    'transfer_id' => $data['id'] ?? null,
                    'recipient' => $recipient,
                    'amount' => $amountFcfa,
                    'reference' => $reference,
                ]);

                return [
                    'success' => true,
                    'transfer_id' => $data['id'],
                    'status' => $data['statut'] ?? 'processing',
                    'data' => $data,
                ];
            }

            $errorData = $response->json();
            Log::channel('payments')->error('Jeko payout failed', [
                'status' => $response->status(),
                'recipient' => $recipient,
                'amount' => $amountFcfa,
                'error' => $errorData,
            ]);

            return [
                'success' => false,
                'error' => $errorData['message'] ?? "Erreur Jeko payout (HTTP {$response->status()})",
                'data' => $errorData,
            ];
        } catch (\Exception $e) {
            Log::channel('payments')->error('Jeko payout exception', [
                'recipient' => $recipient,
                'amount' => $amountFcfa,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => 'Impossible de contacter Jeko pour le payout',
            ];
        }
    }

    /**
     * Crée un contact bénéficiaire Jeko (pour payouts).
     */
    public function createContact(string $mobile): array
    {
        try {
            $response = Http::withToken($this->apiKey)
                ->timeout($this->timeout)
                ->post("{$this->baseUrl}/contacts", [
                    'type' => 'mobile_money',
                    'mobile' => $mobile,
                ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'contact_id' => $response->json('id'),
                ];
            }

            // Si le contact existe déjà, Jeko peut retourner une erreur
            // On tente de le récupérer via GET
            if ($response->status() === 409 || $response->status() === 400) {
                return $this->getExistingContact($mobile);
            }

            return [
                'success' => false,
                'error' => $response->json('message') ?? 'Contact création échouée',
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Récupère un contact existant par numéro mobile.
     */
    protected function getExistingContact(string $mobile): array
    {
        try {
            $response = Http::withToken($this->apiKey)
                ->timeout($this->timeout)
                ->get("{$this->baseUrl}/contacts", [
                    'mobile' => $mobile,
                ]);

            if ($response->successful()) {
                $contacts = $response->json('data') ?? [];
                
                if (!empty($contacts)) {
                    return [
                        'success' => true,
                        'contact_id' => $contacts[0]['id'],
                    ];
                }
            }

            return [
                'success' => false,
                'error' => 'Contact introuvable',
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Intègre un restaurant comme sous-marchand Jeko (Marketplace).
     */
    public function integrateSubMerchant(JekoSubMerchant $subMerchant): array
    {
        if (!$this->isConfigured()) {
            return ['success' => false, 'error' => 'Jeko Marketplace API key not configured'];
        }

        try {
            $response = Http::withToken($this->apiKey)
                ->timeout($this->timeout)
                ->post("{$this->baseUrl}/fournisseurs-de-services/integrer-entreprise", [
                    'nom_entreprise' => $subMerchant->legal_name,
                    'email' => $subMerchant->email,
                    'telephone' => $subMerchant->mobile_money,
                    'type_activite' => $subMerchant->business_type ?? 'restaurant',
                ]);

            if ($response->successful()) {
                $data = $response->json();

                Log::channel('payments')->info('Jeko sub-merchant integrated', [
                    'restaurant_id' => $subMerchant->restaurant_id,
                    'merchant_id' => $data['entreprise_id'] ?? null,
                    'store_id' => $data['magasin_id'] ?? null,
                ]);

                return [
                    'success' => true,
                    'merchant_id' => $data['entreprise_id'],
                    'store_id' => $data['magasin_id'],
                    'wallet_id' => $data['portefeuille_id'] ?? null,
                ];
            }

            Log::channel('payments')->error('Jeko sub-merchant integration failed', [
                'restaurant_id' => $subMerchant->restaurant_id,
                'status' => $response->status(),
                'error' => $response->json(),
            ]);

            return [
                'success' => false,
                'error' => $response->json('message') ?? 'Intégration Jeko échouée',
            ];
        } catch (\Exception $e) {
            Log::channel('payments')->error('Jeko sub-merchant integration exception', [
                'restaurant_id' => $subMerchant->restaurant_id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => 'Impossible de contacter Jeko',
            ];
        }
    }

    /**
     * Vérifie la signature HMAC d'un webhook Jeko.
     */
    public function verifyWebhookSignature(string $rawPayload, string $signatureHeader): bool
    {
        if (empty($this->webhookSecret) || empty($signatureHeader)) {
            return false;
        }

        // Jeko utilise HMAC-SHA256
        $expected = hash_hmac('sha256', $rawPayload, $this->webhookSecret);

        return hash_equals($expected, $signatureHeader);
    }
}
```

- [ ] **Step 4: Write unit test**

```php
<?php

namespace Tests\Unit;

use App\Models\SystemPaymentSetting;
use App\Services\JekoGateway;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class JekoGatewayTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Mock system payment setting
        $setting = SystemPaymentSetting::create([
            'gateway' => 'jeko_normal',
            'is_active' => true,
            'mode' => 'production',
        ]);
        $setting->setEncryptedApiKey('test_jeko_api_key');
        $setting->setEncryptedWebhookSecret('test_webhook_secret');
        $setting->save();
    }

    public function test_is_configured_returns_true_when_api_key_present()
    {
        $gateway = new JekoGateway();
        $gateway->forPlatform();

        $this->assertTrue($gateway->isConfigured());
    }

    public function test_is_configured_returns_false_when_no_api_key()
    {
        SystemPaymentSetting::where('gateway', 'jeko_normal')->delete();
        
        $gateway = new JekoGateway();
        $gateway->forPlatform();

        $this->assertFalse($gateway->isConfigured());
    }

    public function test_verify_webhook_signature_validates_correct_signature()
    {
        $gateway = new JekoGateway();
        $gateway->forPlatform();

        $payload = '{"event":"payment.success","id":"123"}';
        $signature = hash_hmac('sha256', $payload, 'test_webhook_secret');

        $this->assertTrue($gateway->verifyWebhookSignature($payload, $signature));
    }

    public function test_verify_webhook_signature_rejects_invalid_signature()
    {
        $gateway = new JekoGateway();
        $gateway->forPlatform();

        $payload = '{"event":"payment.success","id":"123"}';
        $wrongSignature = 'invalid_signature';

        $this->assertFalse($gateway->verifyWebhookSignature($payload, $wrongSignature));
    }
}
```

- [ ] **Step 5: Run test**

Run: `php artisan test --filter=JekoGatewayTest`  
Expected: PASS (4 tests)

- [ ] **Step 6: Commit**

```bash
git add app/Services/JekoGateway.php config/jeko.php .env.example tests/Unit/JekoGatewayTest.php
git commit -m "feat(jeko): implement JekoGateway service (core)

- JekoGateway implements PaymentGatewayInterface
- Modes: forPlatform() (abonnements) et forMarketplace() (commandes)
- Méthodes: createPayment(), payout(), integrateSubMerchant(), getPaymentStatus()
- Credentials chargés depuis SystemPaymentSetting (chiffrés)
- Webhook signature verification (HMAC-SHA256)
- Config Jeko avec base_url, timeouts, currency
- Tests unitaires (4 tests)"
```

---

**Note:** Le plan est volumineux. Je vais créer les tâches restantes de manière plus concise. Voici les tâches suivantes :

---

## Task 5: Jeko Webhook Controller

**Files:**
- Create: `app/Http/Controllers/Webhooks/JekoWebhookController.php`
- Modify: `routes/webhooks.php`
- Create: `app/Jobs/ProcessJekoPayoutJob.php`
- Test: `tests/Feature/Jeko/WebhookTest.php`

**Implementation:** Créer le controller qui reçoit les webhooks Jeko, vérifie la signature, traite les événements `payment.success` et `payment.failed`, met à jour les Order/Subscription, et déclenche le payout auto pour les commandes.

---

## Task 6: Jeko Onboarding — Restaurant Request

**Files:**
- Create: `app/Http/Controllers/Restaurant/JekoOnboardingController.php`
- Modify: `routes/restaurant.php`
- Create: `resources/views/restaurant/settings/jeko-onboarding.blade.php`
- Test: `tests/Feature/Jeko/OnboardingRequestTest.php`

**Implementation:** Interface restaurant pour demander l'activation Jeko (formulaire KYC), validation des données, création `JekoSubMerchant` avec status PENDING, notification admin.

---

## Task 7: Admin Validation & Auto-Integration

**Files:**
- Create: `app/Http/Controllers/Admin/JekoSubMerchantController.php`
- Modify: `routes/admin.php`
- Create: `resources/views/admin/jeko/pending-requests.blade.php`
- Create: `app/Jobs/IntegrateJekoSubMerchantJob.php`
- Test: `tests/Feature/Jeko/AdminApprovalTest.php`

**Implementation:** Interface admin pour voir les demandes PENDING, approuver/rejeter, job auto qui appelle `JekoGateway::integrateSubMerchant()` après approbation, mise à jour status INTEGRATED.

---

## Task 8: Payment Flow Integration

**Files:**
- Modify: `app/Http/Controllers/Api/V1/Client/PaymentController.php`
- Modify: `app/Models/PaymentTransaction.php`
- Create: `database/migrations/YYYY_MM_DD_000003_add_jeko_columns_to_payment_transactions.php`
- Test: `tests/Feature/Jeko/PaymentFlowTest.php`

**Implementation:** Intégrer PaymentService dans PaymentController, supporter `payment_method=jeko`, créer PaymentTransaction avec colonnes Jeko, workflow complet commande → paiement → webhook → payout.

---

## Task 9: Backoffice Payment Settings

**Files:**
- Create: `app/Http/Controllers/Admin/PaymentSettingsController.php`
- Modify: `routes/admin.php`
- Create: `resources/views/admin/payment-settings/index.blade.php`
- Test: `tests/Feature/Admin/PaymentSettingsTest.php`

**Implementation:** Interface super-admin pour activer/désactiver gateways, saisir API keys (chiffrées), tester la connexion, voir les logs.

---

## Task 10: Integration Tests & Final Validation

**Files:**
- Test: `tests/Feature/Jeko/FullWorkflowTest.php`
- Test: `tests/Feature/Jeko/SubscriptionPaymentTest.php`
- Modify: `README.md` (add Jeko setup instructions)

**Implementation:** Tests end-to-end complets (onboarding → paiement → payout), documentation, vérification coverage.

---

**Voulez-vous que je développe en détail les tâches 5 à 10 avec le même niveau de précision que les tâches 1-4 ?**

Ou cette structure vous suffit pour démarrer l'implémentation ?