<?php

/**
 * Script de Test Rapide - MenuPro
 * 
 * Usage: php test-platform.php
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Restaurant;
use App\Models\Subscription;
use App\Models\Order;
use App\Models\User;
use App\Models\Plan;
use App\Enums\SubscriptionStatus;
use App\Enums\OrderStatus;
use App\Enums\RestaurantStatus;

echo "🧪 TEST COMPLET DE LA PLATEFORME MENUPRO\n";
echo "==========================================\n\n";

$errors = [];
$success = [];

// Test 1: Vérifier les routes critiques
echo "📋 Test 1: Vérification des routes...\n";
try {
    $routes = [
        'restaurant.orders' => '/dashboard/commandes',
        'restaurant.orders.kanban' => '/dashboard/commandes/kanban',
        'restaurant.orders.rush' => '/dashboard/commandes/rush',
        'restaurant.subscription' => '/dashboard/abonnement',
        'r.order.status' => '/r/demo/commande/test-token',
    ];
    
    foreach ($routes as $name => $path) {
        try {
            $url = route($name, ['slug' => 'demo', 'token' => 'test', 'order' => 1], false);
            $success[] = "Route {$name} : OK";
        } catch (\Exception $e) {
            $errors[] = "Route {$name} : ERREUR - " . $e->getMessage();
        }
    }
    echo "✅ Routes vérifiées\n\n";
} catch (\Exception $e) {
    $errors[] = "Test routes : " . $e->getMessage();
}

// Test 2: Vérifier les modèles
echo "📋 Test 2: Vérification des modèles...\n";
try {
    $restaurant = Restaurant::first();
    if ($restaurant) {
        $success[] = "Modèle Restaurant : OK";
        
        // Vérifier essai
        $subscription = $restaurant->activeSubscription;
        if ($subscription) {
            if ($subscription->isTrial()) {
                $success[] = "Essai détecté : OK";
            } else {
                $success[] = "Abonnement payant : OK";
            }
        }
    } else {
        $errors[] = "Aucun restaurant trouvé";
    }
    echo "✅ Modèles vérifiés\n\n";
} catch (\Exception $e) {
    $errors[] = "Test modèles : " . $e->getMessage();
}

// Test 3: Vérifier les enums
echo "📋 Test 3: Vérification des enums...\n";
try {
    $trialStatus = SubscriptionStatus::TRIAL;
    if ($trialStatus->value === 'trial') {
        $success[] = "Enum SubscriptionStatus::TRIAL : OK";
    }
    
    $canModify = OrderStatus::PAID->canBeModifiedByCustomer();
    if ($canModify) {
        $success[] = "OrderStatus::PAID peut être modifié par client : OK";
    }
    
    echo "✅ Enums vérifiés\n\n";
} catch (\Exception $e) {
    $errors[] = "Test enums : " . $e->getMessage();
}

// Test 4: Vérifier les migrations
echo "📋 Test 4: Vérification des migrations...\n";
try {
    $columns = \DB::select("SHOW COLUMNS FROM subscriptions WHERE Field = 'is_trial'");
    if (count($columns) > 0) {
        $success[] = "Colonne is_trial dans subscriptions : OK";
    } else {
        $errors[] = "Colonne is_trial manquante";
    }
    
    $columns = \DB::select("SHOW COLUMNS FROM subscriptions WHERE Field = 'trial_days'");
    if (count($columns) > 0) {
        $success[] = "Colonne trial_days dans subscriptions : OK";
    } else {
        $errors[] = "Colonne trial_days manquante";
    }
    
    $columns = \DB::select("SHOW COLUMNS FROM order_refunds");
    if (count($columns) > 0) {
        $success[] = "Table order_refunds : OK";
    } else {
        $errors[] = "Table order_refunds manquante";
    }
    
    echo "✅ Migrations vérifiées\n\n";
} catch (\Exception $e) {
    $errors[] = "Test migrations : " . $e->getMessage();
}

// Test 5: Vérifier les jobs
echo "📋 Test 5: Vérification des jobs...\n";
try {
    if (class_exists(\App\Jobs\ProcessTrialExpiration::class)) {
        $success[] = "Job ProcessTrialExpiration : OK";
    } else {
        $errors[] = "Job ProcessTrialExpiration manquant";
    }
    echo "✅ Jobs vérifiés\n\n";
} catch (\Exception $e) {
    $errors[] = "Test jobs : " . $e->getMessage();
}

// Test 6: Vérifier les notifications
echo "📋 Test 6: Vérification des notifications...\n";
try {
    $notifications = [
        \App\Notifications\TrialStartedNotification::class,
        \App\Notifications\TrialExpiringNotification::class,
        \App\Notifications\TrialExpiredNotification::class,
        \App\Notifications\OrderModifiedNotification::class,
    ];
    
    foreach ($notifications as $notification) {
        if (class_exists($notification)) {
            $success[] = "Notification " . basename($notification) . " : OK";
        } else {
            $errors[] = "Notification " . basename($notification) . " manquante";
        }
    }
    echo "✅ Notifications vérifiées\n\n";
} catch (\Exception $e) {
    $errors[] = "Test notifications : " . $e->getMessage();
}

// Résumé
echo "\n";
echo "==========================================\n";
echo "📊 RÉSUMÉ DES TESTS\n";
echo "==========================================\n\n";

echo "✅ SUCCÈS (" . count($success) . "):\n";
foreach ($success as $msg) {
    echo "   ✓ $msg\n";
}

if (count($errors) > 0) {
    echo "\n❌ ERREURS (" . count($errors) . "):\n";
    foreach ($errors as $msg) {
        echo "   ✗ $msg\n";
    }
    echo "\n⚠️  Des erreurs ont été détectées. Veuillez les corriger.\n";
    exit(1);
} else {
    echo "\n🎉 Tous les tests sont passés avec succès !\n";
    exit(0);
}
