<?php
/**
 * Script de diagnostic Jeko — flux de paiement par restaurant
 *
 * Usage sur le serveur :
 *   php artisan tinker --execute="require base_path('scripts/check-jeko-payments.php');"
 *
 * Ou directement :
 *   php scripts/check-jeko-payments.php
 */

require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Restaurant;
use App\Models\Order;
use App\Models\JekoSubMerchant;
use App\Enums\JekoSubMerchantStatus;
use App\Enums\OrderStatus;

// ─── Couleurs terminal ────────────────────────────────────────────────────────
$G = "\033[32m"; // vert
$R = "\033[31m"; // rouge
$Y = "\033[33m"; // jaune
$B = "\033[36m"; // bleu
$W = "\033[37m"; // blanc
$X = "\033[0m";  // reset
$H = "\033[1m";  // gras

// ─── En-tête ─────────────────────────────────────────────────────────────────
echo "\n{$H}{$B}╔══════════════════════════════════════════════════════════════╗{$X}\n";
echo "{$H}{$B}║        DIAGNOSTIC JEKO — FLUX DE PAIEMENT RESTAURANTS        ║{$X}\n";
echo "{$H}{$B}╚══════════════════════════════════════════════════════════════╝{$X}\n\n";
echo "  Généré le : " . now()->format('d/m/Y H:i:s') . "\n\n";

// ─── 1. CONFIGURATION GLOBALE JEKO ───────────────────────────────────────────
echo "{$H}━━━ 1. CONFIGURATION JEKO GLOBALE ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━{$X}\n\n";

$settings = \App\Models\SystemPaymentSetting::whereIn('gateway', ['jeko_marketplace', 'jeko_normal'])->get();

if ($settings->isEmpty()) {
    echo "  {$R}❌ Aucune configuration Jeko trouvée en base de données !{$X}\n\n";
} else {
    foreach ($settings as $s) {
        $ok = $s->is_active && !empty($s->api_key);
        $icon = $ok ? "{$G}✅" : "{$R}❌";
        echo "  {$icon} {$H}{$s->gateway}{$X}";
        echo " — actif: " . ($s->is_active ? "{$G}OUI{$X}" : "{$R}NON{$X}");
        echo " — API Key: " . (!empty($s->api_key) ? "{$G}configurée{$X}" : "{$R}MANQUANTE{$X}");
        echo " — Store ID: " . (!empty($s->config['store_id'] ?? '') ? "{$G}OK{$X}" : "{$R}MANQUANT{$X}");
        echo "\n";
    }
}

// ─── 2. STATUT SOUS-MARCHANDS JEKO PAR RESTAURANT ────────────────────────────
echo "\n{$H}━━━ 2. STATUT INTÉGRATION JEKO PAR RESTAURANT ━━━━━━━━━━━━━━━━━{$X}\n\n";

$restaurants = Restaurant::with(['jekoSubMerchant'])->whereNull('deleted_at')->get();

$stats = ['integrated' => 0, 'pending' => 0, 'approved' => 0, 'rejected' => 0, 'none' => 0];

foreach ($restaurants as $restaurant) {
    $sub = $restaurant->jekoSubMerchant;

    if (!$sub) {
        $stats['none']++;
        echo "  {$Y}⚪ {$restaurant->name}{$X} — {$Y}Pas de demande Jeko{$X}\n";
        continue;
    }

    $status = $sub->status;
    $stats[$status->value]++;

    $icon = match($status) {
        JekoSubMerchantStatus::INTEGRATED => "{$G}✅",
        JekoSubMerchantStatus::APPROVED   => "{$B}🔵",
        JekoSubMerchantStatus::PENDING    => "{$Y}🟡",
        JekoSubMerchantStatus::REJECTED   => "{$R}❌",
    };

    $merchantId = $sub->jeko_merchant_id ? "{$G}OK ({$sub->jeko_merchant_id}){$X}" : "{$R}MANQUANT{$X}";
    $storeId    = $sub->jeko_store_id    ? "{$G}OK{$X}" : "{$R}MANQUANT{$X}";
    $walletId   = $sub->jeko_wallet_id   ? "{$G}OK{$X}" : "{$Y}—{$X}";
    $phone      = $sub->mobile_money     ? $sub->mobile_money : "{$R}NON RENSEIGNÉ{$X}";

    echo "  {$icon} {$H}{$restaurant->name}{$X} (ID: {$restaurant->id})\n";
    echo "       Statut      : {$H}" . $status->label() . "{$X}\n";
    echo "       Téléphone   : {$phone}\n";
    echo "       Merchant ID : {$merchantId}\n";
    echo "       Store ID    : {$storeId}\n";
    echo "       Wallet ID   : {$walletId}\n";

    if ($status === JekoSubMerchantStatus::REJECTED && $sub->rejected_reason) {
        echo "       Raison rejet: {$R}{$sub->rejected_reason}{$X}\n";
    }
    if ($status === JekoSubMerchantStatus::INTEGRATED && !$sub->jeko_merchant_id) {
        echo "       {$R}⚠️  PROBLÈME : statut INTEGRATED mais merchant_id manquant — paiements vers MenuPro !{$X}\n";
    }
    echo "\n";
}

echo "  {$H}Résumé : {$G}{$stats['integrated']} intégrés{$X}, {$B}{$stats['approved']} approuvés{$X}, {$Y}{$stats['pending']} en attente{$X}, {$R}{$stats['rejected']} rejetés{$X}, {$Y}{$stats['none']} sans demande{$X}\n\n";

// ─── 3. PAIEMENTS JEKO/WAVE DES 7 DERNIERS JOURS ─────────────────────────────
echo "{$H}━━━ 3. PAIEMENTS JEKO/WAVE (7 DERNIERS JOURS) ━━━━━━━━━━━━━━━━━{$X}\n\n";

$recentOrders = Order::with('restaurant')
    ->whereIn('payment_method', ['jeko', 'wave'])
    ->whereNotNull('paid_at')
    ->where('paid_at', '>=', now()->subDays(7))
    ->whereNotIn('status', [
        OrderStatus::CANCELLED->value,
        OrderStatus::REFUNDED->value,
        OrderStatus::DRAFT->value,
    ])
    ->orderByDesc('paid_at')
    ->limit(100)
    ->get();

if ($recentOrders->isEmpty()) {
    echo "  {$Y}Aucun paiement Jeko/Wave trouvé sur les 7 derniers jours.{$X}\n\n";
} else {
    // Grouper par restaurant
    $byRestaurant = $recentOrders->groupBy('restaurant_id');

    foreach ($byRestaurant as $restaurantId => $orders) {
        $restaurant = $orders->first()->restaurant;
        $sub = $restaurant?->jekoSubMerchant;
        $isIntegrated = $sub?->isIntegrated() ?? false;

        $totalAmount = $orders->sum('total');
        $waveCount   = $orders->where('payment_method', 'wave')->count();
        $jekoCount   = $orders->where('payment_method', 'jeko')->count();

        $routingIcon = $isIntegrated ? "{$G}→ compte restaurant{$X}" : "{$R}→ compte MenuPro (à reverser !){$X}";

        $restaurantName = $restaurant ? $restaurant->name : 'Restaurant #' . $restaurantId;
        echo "  {$H}{$restaurantName}{$X}\n";
        echo "    Commandes   : {$orders->count()} ({$waveCount} Wave, {$jekoCount} Jeko)\n";
        echo "    Total       : {$H}" . number_format($totalAmount, 0, ',', ' ') . " F{$X}\n";
        echo "    Intégration : " . ($isIntegrated ? "{$G}✅ Sous-marchand actif{$X}" : "{$R}❌ Non intégré{$X}") . "\n";
        echo "    Argent      : {$routingIcon}\n";

        // Liste des commandes non reversées si pas intégré
        if (!$isIntegrated && $orders->count() > 0) {
            echo "    {$Y}Commandes à reverser manuellement :{$X}\n";
            foreach ($orders->take(10) as $order) {
                $method = strtoupper($order->payment_method ?? '?');
                $operator = $order->payment_metadata['jeko_operator'] ?? $order->payment_metadata['operator'] ?? '';
                $operatorStr = $operator ? " ({$operator})" : '';
                echo "      • #{$order->reference} — " . number_format($order->total, 0, ',', ' ') . " F — {$method}{$operatorStr} — payé le " . $order->paid_at->format('d/m H:i') . "\n";
            }
            if ($orders->count() > 10) {
                echo "      ... et " . ($orders->count() - 10) . " autres\n";
            }
        }
        echo "\n";
    }
}

// ─── 4. DIAGNOSTIC SPÉCIFIQUE RESTAURANT (optionnel) ─────────────────────────
// Décommente et remplace "prunel" par le nom du restaurant à vérifier
$nomRecherche = getenv('RESTAURANT') ?: 'prunel';

echo "{$H}━━━ 4. DIAGNOSTIC SPÉCIFIQUE : « {$nomRecherche} » ━━━━━━━━━━━━━━━━━{$X}\n\n";

$target = Restaurant::with(['jekoSubMerchant'])
    ->where('name', 'like', "%{$nomRecherche}%")
    ->orWhere('slug', 'like', "%{$nomRecherche}%")
    ->first();

if (!$target) {
    echo "  {$Y}Restaurant « {$nomRecherche} » non trouvé. Passe RESTAURANT=nom php scripts/check-jeko-payments.php{$X}\n\n";
} else {
    $sub = $target->jekoSubMerchant;
    echo "  Restaurant : {$H}{$target->name}{$X} (ID: {$target->id})\n";
    echo "  Statut     : {$target->status->value}\n\n";

    if (!$sub) {
        echo "  {$R}❌ Aucune intégration Jeko — les paiements vont sur le compte MenuPro !{$X}\n";
        echo "  {$Y}➡ Action : le restaurant doit faire une demande d'intégration Jeko dans les paramètres.{$X}\n\n";
    } else {
        $statusColor = match($sub->status) {
            JekoSubMerchantStatus::INTEGRATED => $G,
            JekoSubMerchantStatus::APPROVED   => $B,
            JekoSubMerchantStatus::PENDING    => $Y,
            JekoSubMerchantStatus::REJECTED   => $R,
        };

        echo "  Intégration Jeko :\n";
        echo "    Statut       : {$statusColor}{$H}" . $sub->status->label() . "{$X}\n";
        echo "    Merchant ID  : " . ($sub->jeko_merchant_id ?: "{$R}MANQUANT{$X}") . "\n";
        echo "    Store ID     : " . ($sub->jeko_store_id    ?: "{$R}MANQUANT{$X}") . "\n";
        echo "    Wallet ID    : " . ($sub->jeko_wallet_id   ?: "{$Y}non renseigné{$X}") . "\n";
        echo "    Téléphone    : " . ($sub->mobile_money     ?: "{$R}MANQUANT{$X}") . "\n";
        echo "    Approuvé le  : " . ($sub->approved_at?->format('d/m/Y H:i') ?: "{$Y}—{$X}") . "\n";

        if ($sub->status === JekoSubMerchantStatus::REJECTED) {
            echo "\n  {$R}❌ Raison du rejet : {$sub->rejected_reason}{$X}\n";
            echo "  {$Y}➡ Action : corriger les informations et soumettre à nouveau.{$X}\n";
        } elseif ($sub->status === JekoSubMerchantStatus::PENDING) {
            echo "\n  {$Y}⏳ Demande en attente d'approbation dans le Super Admin.{$X}\n";
            echo "  {$Y}➡ Action : approuver la demande dans le dashboard Super Admin > Sous-marchands Jeko.{$X}\n";
        } elseif ($sub->status === JekoSubMerchantStatus::APPROVED) {
            echo "\n  {$B}🔵 Demande approuvée mais pas encore intégrée côté Jeko API.{$X}\n";
            echo "  {$Y}➡ Action : lancer l'intégration Jeko via Super Admin > Sous-marchands Jeko > Intégrer.{$X}\n";
        } elseif ($sub->status === JekoSubMerchantStatus::INTEGRATED && !$sub->jeko_merchant_id) {
            echo "\n  {$R}⚠️  Statut INTEGRATED mais merchant_id manquant !{$X}\n";
            echo "  {$R}   Les paiements vont sur le compte MenuPro, pas au restaurant.{$X}\n";
            echo "  {$Y}➡ Action : relancer l'intégration Jeko via Super Admin.{$X}\n";
        } else {
            echo "\n  {$G}✅ Intégration complète — les paiements arrivent directement au restaurant.{$X}\n";
        }

        // Paiements récents de ce restaurant
        echo "\n  Paiements Jeko/Wave des 30 derniers jours :\n";
        $targetOrders = Order::where('restaurant_id', $target->id)
            ->whereIn('payment_method', ['jeko', 'wave'])
            ->whereNotNull('paid_at')
            ->where('paid_at', '>=', now()->subDays(30))
            ->whereNotIn('status', ['cancelled', 'refunded', 'draft'])
            ->orderByDesc('paid_at')
            ->limit(20)
            ->get();

        if ($targetOrders->isEmpty()) {
            echo "    {$Y}Aucun paiement Jeko/Wave sur les 30 derniers jours.{$X}\n";
        } else {
            $totalTarget = $targetOrders->sum('total');
            echo "    Total : {$H}" . number_format($totalTarget, 0, ',', ' ') . " F{$X} sur {$targetOrders->count()} commandes\n\n";
            foreach ($targetOrders as $order) {
                $method   = strtoupper($order->payment_method ?? '?');
                $operator = $order->payment_metadata['jeko_operator'] ?? $order->payment_metadata['operator'] ?? '';
                $opStr    = $operator ? " [{$operator}]" : '';
                $dest     = $sub?->isIntegrated() ? "{$G}→ restaurant{$X}" : "{$R}→ MenuPro{$X}";
                echo "    • #{$order->reference}  " . number_format($order->total, 0, ',', ' ') . " F  {$method}{$opStr}  payé " . $order->paid_at->format('d/m H:i') . "  {$dest}\n";
            }
        }
    }
}

echo "\n{$H}{$B}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━{$X}\n";
echo "  Fin du diagnostic. Pour un autre restaurant : RESTAURANT=nom php scripts/check-jeko-payments.php\n\n";
