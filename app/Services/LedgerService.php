<?php

namespace App\Services;

use App\Enums\OrderType;
use App\Enums\PaymentStatus;
use App\Enums\SubscriptionStatus;
use App\Models\DriverEarning;
use App\Models\LedgerEntry;
use App\Models\Order;
use App\Models\OrderRefund;
use App\Models\PaymentTransaction;
use App\Models\PayoutTransaction;
use App\Models\Restaurant;
use App\Models\Subscription;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Alimentation du journal des transactions (table ledger_entries).
 *
 * Une ligne par mouvement d'argent, identifiée par une clé d'idempotence
 * « source:id:nature » (ex. order:42:payment). Les mêmes constructeurs servent à
 * l'alimentation automatique (observers) et à la reconstitution (ledger:reconstruire) :
 * une ligne écrite en direct puis reconstituée garde donc la même clé et n'est jamais
 * dupliquée.
 *
 * FRAIS JEKO — choix documenté :
 *   Le propriétaire indique que Jeko prélève 1,5 % « à chaque transfert ». On distingue :
 *   - jeko_fee_collection_percent (défaut 1,5) : appliqué à chaque ENCAISSEMENT Jeko
 *     (commande ou abonnement payé par Jeko) ;
 *   - jeko_fee_payout_percent (défaut 1,5) : appliqué à chaque SORTIE Jeko
 *     (reversement au restaurant, remboursement client payé via Jeko).
 *   Les deux taux sont modifiables depuis le journal (mettre 0 si l'un ne s'applique
 *   pas). Le taux est figé sur la ligne au moment de l'écriture : changer un taux ne
 *   réécrit pas l'historique. Les espèces n'ont jamais de frais. Les frais Wave des
 *   retraits de wallet sont repris tels que renvoyés par Wave (PayoutTransaction.fee).
 *   Les frais sont supposés supportés par MenuPro : ils diminuent le « net MenuPro ».
 *
 * Toutes les écritures sont protégées (try/catch + log) : le journal ne doit jamais
 * bloquer un paiement.
 */
class LedgerService
{
    public const SETTING_COLLECTION_FEE = 'jeko_fee_collection_percent';
    public const SETTING_PAYOUT_FEE = 'jeko_fee_payout_percent';
    public const DEFAULT_FEE_PERCENT = 1.5;

    /** Valeurs brutes de payment_method considérées comme des espèces. */
    public const CASH_METHODS = ['cash', 'cash_on_delivery', 'on_site'];

    /**
     * Moyens d'un paiement d'abonnement reçu HORS plateforme et saisi par le super-admin
     * (prolongation manuelle) : libellé et moyen normalisé du journal.
     */
    public const MANUAL_PAYMENT_METHODS = [
        'cash'          => ['label' => 'Espèces',           'method' => 'cash'],
        'jeko_deposit'  => ['label' => 'Dépôt Jeko',        'method' => 'jeko'],
        'wave'          => ['label' => 'Wave',              'method' => 'wave'],
        'orange_money'  => ['label' => 'Orange Money',      'method' => 'other'],
        'mtn_momo'      => ['label' => 'MTN MoMo',          'method' => 'other'],
        'moov_money'    => ['label' => 'Moov Money',        'method' => 'other'],
        'bank_transfer' => ['label' => 'Virement bancaire', 'method' => 'other'],
        'other'         => ['label' => 'Autre',             'method' => 'other'],
    ];

    /** Statuts de reversement de commande considérés comme effectués. */
    public const PAYOUT_DONE = ['completed', 'manual'];

    /** Cache local (durée d'une requête / d'un job) des restaurants. */
    private array $restaurants = [];

    // =========================================================================
    // FRAIS
    // =========================================================================

    public function collectionFeePercent(): float
    {
        return $this->percentSetting(self::SETTING_COLLECTION_FEE);
    }

    public function payoutFeePercent(): float
    {
        return $this->percentSetting(self::SETTING_PAYOUT_FEE);
    }

    private function percentSetting(string $key): float
    {
        $value = SystemSetting::get($key);

        if ($value === null || $value === '') {
            return self::DEFAULT_FEE_PERCENT;
        }

        return max(0.0, (float) str_replace(',', '.', (string) $value));
    }

    public function fee(int $amount, float $percent): int
    {
        return $amount > 0 && $percent > 0 ? (int) round($amount * $percent / 100) : 0;
    }

    // =========================================================================
    // ÉCRITURE
    // =========================================================================

    /**
     * Écrit une ligne si sa clé n'existe pas encore. Jamais d'exception.
     */
    public function write(?array $entry): ?LedgerEntry
    {
        if (!$entry) {
            return null;
        }

        try {
            return LedgerEntry::createOrFirst(
                ['idempotency_key' => $entry['idempotency_key']],
                $entry
            );
        } catch (\Throwable $e) {
            Log::channel('payments')->error('Journal des transactions : écriture impossible', [
                'key' => $entry['idempotency_key'] ?? null,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Construit puis écrit une ligne APRÈS la validation de la transaction SQL en cours
     * (immédiatement s'il n'y en a pas). La construction est faite tout de suite pour
     * figer l'état du modèle au moment de l'événement.
     */
    public function writeAfterCommit(callable $builder): void
    {
        try {
            $entry = $builder();
        } catch (\Throwable $e) {
            Log::channel('payments')->error('Journal des transactions : construction impossible', [
                'error' => $e->getMessage(),
            ]);

            return;
        }

        if (!$entry) {
            return;
        }

        DB::afterCommit(fn () => $this->write($entry));
    }

    /**
     * Fait évoluer le statut d'une ligne existante (les montants restent figés).
     * Un statut « reversé » ou « remboursé » n'est jamais ramené à « réussi ».
     */
    public function updateStatus(string $key, string $status): void
    {
        DB::afterCommit(function () use ($key, $status) {
            try {
                $query = LedgerEntry::where('idempotency_key', $key)->where('status', '!=', $status);

                if ($status === LedgerEntry::STATUS_PAID_OUT) {
                    // Une commande remboursée ne devient pas « reversée »
                    $query->whereIn('status', [LedgerEntry::STATUS_SUCCEEDED, LedgerEntry::STATUS_PENDING]);
                } elseif ($status === LedgerEntry::STATUS_SUCCEEDED) {
                    $query->where('status', LedgerEntry::STATUS_PENDING);
                }

                $query->update(['status' => $status, 'updated_at' => now()]);
            } catch (\Throwable $e) {
                Log::channel('payments')->error('Journal des transactions : mise à jour de statut impossible', [
                    'key' => $key,
                    'status' => $status,
                    'error' => $e->getMessage(),
                ]);
            }
        });
    }

    // =========================================================================
    // CLÉS
    // =========================================================================

    public static function orderPaymentKey(int $orderId): string
    {
        return "order:{$orderId}:payment";
    }

    public static function orderPaymentFailedKey(int $orderId): string
    {
        return "order:{$orderId}:payment_failed";
    }

    public static function orderPayoutKey(int $orderId): string
    {
        return "order:{$orderId}:payout";
    }

    public static function orderPayoutFailedKey(int $orderId): string
    {
        return "order:{$orderId}:payout_failed";
    }

    // =========================================================================
    // CONSTRUCTEURS — COMMANDES
    // =========================================================================

    /**
     * Encaissement d'une commande (en ligne ou espèces).
     *
     * @param bool $reconstruct Reconstitution : le statut reflète l'état actuel
     *                          (reversé / remboursé) au lieu de « réussi ».
     */
    public function orderPaymentEntry(Order $order, ?string $via = null, bool $reconstruct = false): ?array
    {
        $gross = (int) $order->total;
        if ($gross <= 0) {
            return null;
        }

        $method = $this->normalizeMethod($order->payment_method);
        $isCash = $method === 'cash';
        $rate = $method === 'jeko' ? $this->collectionFeePercent() : 0.0;
        $fee = $this->fee($gross, $rate);
        $commission = max(0, (int) $order->platform_commission);
        $deliveryFee = max(0, (int) $order->delivery_fee);
        $driverShare = $this->isPlatformDelivery($order) ? $deliveryFee : 0;

        if ($method === 'jeko') {
            // Même calcul que ProcessJekoPayoutJob : total - commission - frais de livraison
            $restaurantShare = max(0, $gross - $commission - $deliveryFee);
        } else {
            $restaurantShare = max(0, $gross - $commission - $driverShare);
        }

        // Espèces : MenuPro ne détient pas l'argent, seule la commission lui revient (à
        // recouvrer). En ligne : ce que MenuPro garde = brut - frais - restaurant - livreur.
        $net = $isCash ? $commission : $gross - $fee - $restaurantShare - $driverShare;

        $status = LedgerEntry::STATUS_SUCCEEDED;
        if ($reconstruct) {
            if ($order->payment_status === PaymentStatus::REFUNDED) {
                $status = LedgerEntry::STATUS_REFUNDED;
            } elseif (!$isCash && in_array($order->payout_status, self::PAYOUT_DONE, true)) {
                $status = LedgerEntry::STATUS_PAID_OUT;
            }
        }

        [$ref, $refAlt] = $this->orderGatewayReferences($order);

        return array_merge($this->baseOrderEntry($order, $via), [
            'idempotency_key' => self::orderPaymentKey($order->id),
            'occurred_at' => $order->paid_at ?? $order->completed_at ?? $order->updated_at ?? now(),
            'type' => $this->orderType($order),
            'direction' => 'in',
            'status' => $status,
            'method' => $method,
            'method_detail' => $order->payment_method,
            'payer_type' => 'customer',
            'payer_name' => $order->customer_name,
            'payer_phone' => $order->customer_phone ?: null,
            'beneficiary_type' => 'restaurant',
            'beneficiary_name' => $this->restaurant($order->restaurant_id)?->name,
            'gateway_reference' => $ref,
            'gateway_reference_alt' => $refAlt,
            'gross_amount' => $gross,
            'gateway_fee' => $fee,
            'gateway_fee_rate' => $rate,
            'menupro_commission' => $commission,
            'restaurant_share' => $restaurantShare,
            'driver_share' => $driverShare,
            'net_menupro' => $net,
            'description' => 'Commande #' . $order->reference,
            'metadata' => [
                'order_type' => $order->type?->value,
                'source' => $order->source,
                'delivery_fee' => $deliveryFee,
            ],
        ]);
    }

    /** Tentative de paiement en ligne échouée (aucun montant compté). */
    public function orderPaymentFailedEntry(Order $order, ?string $via = null): ?array
    {
        $method = $this->normalizeMethod($order->payment_method);
        if ($method === 'cash' || (int) $order->total <= 0) {
            return null;
        }

        [$ref, $refAlt] = $this->orderGatewayReferences($order);

        return array_merge($this->baseOrderEntry($order, $via), [
            'idempotency_key' => self::orderPaymentFailedKey($order->id),
            'occurred_at' => $order->updated_at ?? now(),
            'type' => $this->orderType($order),
            'direction' => 'in',
            'status' => LedgerEntry::STATUS_FAILED,
            'method' => $method,
            'method_detail' => $order->payment_method,
            'payer_type' => 'customer',
            'payer_name' => $order->customer_name,
            'payer_phone' => $order->customer_phone ?: null,
            'beneficiary_type' => 'restaurant',
            'beneficiary_name' => $this->restaurant($order->restaurant_id)?->name,
            'gateway_reference' => $ref,
            'gateway_reference_alt' => $refAlt,
            'gross_amount' => (int) $order->total,
            'description' => 'Paiement échoué — commande #' . $order->reference,
        ]);
    }

    /** Montant reversé au restaurant pour une commande (formule de ProcessJekoPayoutJob). */
    public function orderPayoutAmount(Order $order): int
    {
        return max(0, (int) $order->total - (int) ($order->platform_commission ?? 0) - (int) ($order->delivery_fee ?? 0));
    }

    /** Reversement d'une commande payée en ligne au restaurant (Jeko ou manuel). */
    public function orderPayoutEntry(Order $order, ?string $via = null): ?array
    {
        if ($this->normalizeMethod($order->payment_method) === 'cash') {
            return null; // l'argent n'a jamais transité par MenuPro
        }

        $amount = $this->orderPayoutAmount($order);
        if ($amount <= 0) {
            return null;
        }

        $manual = $order->payout_status === 'manual';
        $rate = $manual ? 0.0 : $this->payoutFeePercent();
        $fee = $this->fee($amount, $rate);
        $restaurant = $this->restaurant($order->restaurant_id);

        return array_merge($this->baseOrderEntry($order, $via), [
            'idempotency_key' => self::orderPayoutKey($order->id),
            'occurred_at' => $order->payout_at ?? now(),
            'type' => LedgerEntry::TYPE_PAYOUT_RESTAURANT,
            'direction' => 'out',
            'status' => LedgerEntry::STATUS_SUCCEEDED,
            'channel' => $manual ? 'back_office' : 'system',
            'method' => $manual ? 'other' : 'jeko',
            'method_detail' => $manual ? 'manual' : 'jeko_transfer',
            'payer_type' => 'menupro',
            'payer_name' => 'MenuPro',
            'payer_phone' => null,
            'beneficiary_type' => 'restaurant',
            'beneficiary_name' => $restaurant?->name,
            'gateway_reference' => $order->payout_reference,
            'gateway_reference_alt' => 'PAYOUT-ORDER-' . $order->id . '-' . $order->payment_reference,
            'gross_amount' => $amount,
            'gateway_fee' => $fee,
            'gateway_fee_rate' => $rate,
            'restaurant_share' => $amount,
            'net_menupro' => -$fee,
            'description' => ($manual ? 'Reversement manuel' : 'Reversement Jeko') . ' — commande #' . $order->reference,
        ]);
    }

    /** Reversement Jeko échoué (montant non compté). */
    public function orderPayoutFailedEntry(Order $order, ?string $via = null): ?array
    {
        if ($this->normalizeMethod($order->payment_method) === 'cash') {
            return null;
        }

        $entry = $this->orderPayoutEntry($order, $via);
        if (!$entry) {
            return null;
        }

        return array_merge($entry, [
            'idempotency_key' => self::orderPayoutFailedKey($order->id),
            'occurred_at' => $order->updated_at ?? now(),
            'status' => LedgerEntry::STATUS_FAILED,
            'gateway_fee' => 0,
            'restaurant_share' => 0,
            'net_menupro' => 0,
            'description' => 'Reversement Jeko échoué — commande #' . $order->reference,
        ]);
    }

    // =========================================================================
    // CONSTRUCTEURS — REMBOURSEMENTS
    // =========================================================================

    public function refundEntry(OrderRefund $refund, ?string $via = null): ?array
    {
        $order = Order::withoutGlobalScope('restaurant')->find($refund->order_id);
        $amount = (int) $refund->amount;

        if (!$order || $amount <= 0) {
            return null;
        }

        $method = $this->normalizeMethod($refund->metadata['original_payment_method'] ?? $order->payment_method);
        $status = match ($refund->status) {
            'completed', 'succeeded', 'refunded' => LedgerEntry::STATUS_SUCCEEDED,
            'failed', 'rejected', 'cancelled' => LedgerEntry::STATUS_FAILED,
            default => LedgerEntry::STATUS_PENDING,
        };
        $rate = $method === 'jeko' ? $this->payoutFeePercent() : 0.0;
        $fee = $this->fee($amount, $rate);

        // Part de commission abandonnée au prorata du montant remboursé
        $total = max(1, (int) $order->total);
        $commissionShare = (int) round(max(0, (int) $order->platform_commission) * min($amount, $total) / $total);

        return array_merge($this->baseOrderEntry($order, $via), [
            'idempotency_key' => 'refund:' . $refund->id,
            'occurred_at' => $refund->processed_at ?? $refund->created_at ?? now(),
            'type' => LedgerEntry::TYPE_REFUND_CUSTOMER,
            'direction' => 'out',
            'status' => $status,
            'source_type' => 'order_refund',
            'source_id' => $refund->id,
            'method' => $method,
            'method_detail' => $order->payment_method,
            'payer_type' => 'menupro',
            'payer_name' => 'MenuPro',
            'payer_phone' => null,
            'beneficiary_type' => 'customer',
            'beneficiary_name' => trim(($order->customer_name ?? '') . ' ' . ($order->customer_phone ?? '')) ?: null,
            'gateway_reference' => $refund->refund_reference ?: $refund->payment_reference,
            'gateway_reference_alt' => $order->payment_reference,
            'gross_amount' => $amount,
            'gateway_fee' => $fee,
            'gateway_fee_rate' => $rate,
            'menupro_commission' => -$commissionShare,
            'restaurant_share' => -max(0, $amount - $commissionShare),
            'net_menupro' => -$commissionShare - $fee,
            'description' => 'Remboursement — commande #' . $order->reference . ($refund->reason ? ' (' . mb_substr($refund->reason, 0, 120) . ')' : ''),
        ]);
    }

    // =========================================================================
    // CONSTRUCTEURS — ABONNEMENTS
    // =========================================================================

    /** L'abonnement correspond-il à un encaissement réel ? */
    public function isCollectedSubscription(Subscription $subscription): bool
    {
        return in_array($subscription->status, [SubscriptionStatus::ACTIVE, SubscriptionStatus::EXPIRED], true)
            && !$subscription->is_trial
            && (int) $subscription->amount_paid > 0
            && !empty($subscription->payment_reference);
    }

    /** Prolongation manuelle à 0 F (geste commercial) : tracée dans le journal, marquée « offert ». */
    public function isOfferedManualSubscription(Subscription $subscription): bool
    {
        return $subscription->payment_method === 'admin_manual'
            && (int) $subscription->amount_paid === 0
            && in_array($subscription->status, [SubscriptionStatus::ACTIVE, SubscriptionStatus::EXPIRED], true)
            && (bool) ($subscription->payment_metadata['offered'] ?? false);
    }

    public function subscriptionEntry(Subscription $subscription, ?string $via = null, bool $failed = false): ?array
    {
        $amount = (int) $subscription->amount_paid;
        $offered = !$failed && $this->isOfferedManualSubscription($subscription);
        if ($amount <= 0 && !$offered) {
            return null;
        }

        $restaurant = $this->restaurant($subscription->restaurant_id);
        $plan = $subscription->plan;
        $isStand = $this->isStandPlan($plan?->slug);
        $manual = $subscription->payment_method === 'admin_manual';
        $meta = $subscription->payment_metadata ?? [];
        // Paiement reçu hors plateforme (dépôt, espèces…) : moyen saisi par le super-admin, sans frais
        $manualVia = $manual ? (self::MANUAL_PAYMENT_METHODS[$meta['received_via'] ?? ''] ?? null) : null;
        $method = $manualVia['method'] ?? $this->normalizeMethod($subscription->payment_method);
        $rate = (!$failed && !$manual && $method === 'jeko') ? $this->collectionFeePercent() : 0.0;
        $fee = $this->fee($amount, $rate);

        $periodLabels = ['weekly' => 'hebdomadaire', 'monthly' => 'mensuel', 'quarterly' => 'trimestriel', 'semiannual' => 'semestriel', 'annual' => 'annuel'];
        $period = $periodLabels[$subscription->billing_period] ?? $subscription->billing_period;

        $alt = $meta['jeko_link_id'] ?? $meta['payment_id'] ?? $meta['transaction_id'] ?? null;

        return [
            'idempotency_key' => "subscription:{$subscription->id}:" . ($failed ? 'payment_failed' : 'payment'),
            'occurred_at' => $failed
                ? ($subscription->updated_at ?? now())
                : ($subscription->paid_at ?? $subscription->starts_at ?? $subscription->created_at ?? now()),
            'type' => $isStand ? LedgerEntry::TYPE_SUBSCRIPTION_STAND : LedgerEntry::TYPE_SUBSCRIPTION_RESTAURANT,
            'direction' => 'in',
            'status' => $failed ? LedgerEntry::STATUS_FAILED : LedgerEntry::STATUS_SUCCEEDED,
            'channel' => $manual ? 'back_office' : 'restaurant_space',
            'recorded_via' => $via ?? $this->currentVia(),
            'method' => $method,
            'method_detail' => $manual
                ? ($offered ? 'Offert — geste commercial' : ($manualVia['label'] ?? 'Hors plateforme') . ' — reçu hors plateforme')
                : $subscription->payment_method,
            'restaurant_id' => $subscription->restaurant_id,
            'restaurant_name' => $restaurant?->name,
            'restaurant_plan' => $plan?->slug,
            'restaurant_plan_label' => $plan?->name,
            'is_demo' => (bool) ($restaurant?->is_demo ?? false),
            'source_type' => 'subscription',
            'source_id' => $subscription->id,
            'order_id' => null,
            'subscription_id' => $subscription->id,
            'order_reference' => null,
            'payer_type' => 'restaurant',
            'payer_name' => $restaurant?->name,
            'payer_phone' => $restaurant?->phone,
            'beneficiary_type' => 'menupro',
            'beneficiary_name' => 'MenuPro',
            'gateway_reference' => $subscription->payment_reference,
            'gateway_reference_alt' => ($alt && $alt !== $subscription->payment_reference) ? $alt : null,
            'gross_amount' => $amount,
            'gateway_fee' => $fee,
            'gateway_fee_rate' => $rate,
            'menupro_commission' => $failed ? 0 : $amount,
            'restaurant_share' => 0,
            'driver_share' => 0,
            'net_menupro' => $failed ? 0 : $amount - $fee,
            'description' => trim('Abonnement ' . ($plan?->name ?? '') . ($period ? ' — ' . $period : ''))
                . ($offered ? ' — OFFERT (geste commercial)' : '')
                . ($manual && !$offered ? ' — paiement reçu hors plateforme' . (!empty($meta['reference']) ? ' (réf. ' . $meta['reference'] . ')' : '') : '')
                . ($manual && !empty($meta['reason']) ? ' — ' . mb_substr((string) $meta['reason'], 0, 120) : ''),
            'metadata' => [
                'billing_period' => $subscription->billing_period,
                'starts_at' => $subscription->starts_at?->toIso8601String(),
                'ends_at' => $subscription->ends_at?->toIso8601String(),
                'offered' => $offered,
                'received_via' => $meta['received_via'] ?? null,
                'admin_id' => $manual ? ($meta['admin_id'] ?? null) : null,
            ],
        ];
    }

    // =========================================================================
    // CONSTRUCTEURS — RETRAITS DE WALLET ET LIVREURS
    // =========================================================================

    /** Retrait de wallet restaurant (PayoutTransaction, Wave ou Jeko). Statuts terminaux uniquement. */
    public function payoutTransactionEntry(PayoutTransaction $payout, ?string $via = null): ?array
    {
        $succeeded = in_array($payout->status, ['succeeded', 'completed', 'success'], true);
        $failed = in_array($payout->status, ['failed', 'reversed'], true);

        if ((!$succeeded && !$failed) || (int) $payout->amount <= 0) {
            return null;
        }

        $amount = (int) $payout->amount;
        $method = $this->normalizeMethod($payout->gateway);
        $fee = (int) round((float) $payout->fee);
        $rate = 0.0;
        if ($succeeded && $fee === 0 && $method === 'jeko') {
            $rate = $this->payoutFeePercent();
            $fee = $this->fee($amount, $rate);
        } elseif ($amount > 0 && $fee > 0) {
            $rate = round($fee * 100 / $amount, 2);
        }
        if ($failed) {
            $fee = 0;
        }

        $restaurant = $this->restaurant($payout->restaurant_id);

        return [
            'idempotency_key' => 'payout_tx:' . $payout->id . ($failed ? ':failed' : ''),
            'occurred_at' => $payout->updated_at ?? now(),
            'type' => LedgerEntry::TYPE_PAYOUT_RESTAURANT,
            'direction' => 'out',
            'status' => $succeeded ? LedgerEntry::STATUS_SUCCEEDED : LedgerEntry::STATUS_FAILED,
            'channel' => 'system',
            'recorded_via' => $via ?? $this->currentVia(),
            'method' => $method,
            'method_detail' => $payout->gateway,
            'restaurant_id' => $payout->restaurant_id,
            'restaurant_name' => $restaurant?->name,
            'restaurant_plan' => $restaurant?->currentPlan?->slug,
            'restaurant_plan_label' => $restaurant?->currentPlan?->name,
            'is_demo' => (bool) ($restaurant?->is_demo ?? false),
            'source_type' => 'payout_transaction',
            'source_id' => $payout->id,
            'payer_type' => 'menupro',
            'payer_name' => 'MenuPro',
            'beneficiary_type' => 'restaurant',
            'beneficiary_name' => trim(($payout->recipient_name ?: $restaurant?->name) . ($payout->mobile ? ' ' . $payout->mobile : '')),
            'gateway_reference' => $payout->gateway_transaction_id ?: $payout->wave_payout_id,
            'gateway_reference_alt' => $payout->client_reference ?: $payout->idempotency_key,
            'gross_amount' => $amount,
            'gateway_fee' => $fee,
            'gateway_fee_rate' => $rate,
            'restaurant_share' => $succeeded ? $amount : 0,
            'net_menupro' => -$fee,
            'description' => ($failed ? 'Retrait de wallet échoué' : 'Retrait de wallet') . ($payout->payment_reason ? ' — ' . $payout->payment_reason : ''),
        ];
    }

    /** Répartition des frais de livraison entre le livreur et MenuPro (mouvement interne). */
    public function driverEarningEntry(DriverEarning $earning, ?string $via = null): ?array
    {
        if ((int) $earning->gross_amount <= 0) {
            return null;
        }

        $order = $earning->order_id ? Order::withoutGlobalScope('restaurant')->find($earning->order_id) : null;
        $driver = $earning->driver()->with('user:id,name')->first();
        $driverName = $driver?->user?->name ?? $driver?->name ?? 'Livreur';

        $entry = $order ? $this->baseOrderEntry($order, $via) : [
            'recorded_via' => $via ?? $this->currentVia(),
            'restaurant_id' => null,
            'restaurant_name' => null,
            'restaurant_plan' => null,
            'restaurant_plan_label' => null,
            'is_demo' => false,
            'order_id' => null,
            'subscription_id' => null,
            'order_reference' => null,
            'channel' => 'client_app',
        ];

        return array_merge($entry, [
            'idempotency_key' => 'driver_earning:' . $earning->id,
            'occurred_at' => $earning->created_at ?? now(),
            'type' => LedgerEntry::TYPE_DRIVER_COMMISSION,
            'direction' => 'int',
            'status' => LedgerEntry::STATUS_SUCCEEDED,
            'source_type' => 'driver_earning',
            'source_id' => $earning->id,
            'method' => $this->normalizeMethod($order?->payment_method ?? 'other'),
            'method_detail' => $order?->payment_method,
            'payer_type' => 'customer',
            'payer_name' => $order?->customer_name,
            'payer_phone' => $order?->customer_phone ?: null,
            'beneficiary_type' => 'driver',
            'beneficiary_name' => $driverName,
            'gateway_reference' => $earning->payment_reference,
            'gateway_reference_alt' => null,
            'gross_amount' => (int) $earning->gross_amount,
            'gateway_fee' => 0,
            'gateway_fee_rate' => 0,
            'menupro_commission' => (int) $earning->platform_cut,
            'restaurant_share' => 0,
            'driver_share' => (int) $earning->net_amount,
            'net_menupro' => (int) $earning->platform_cut,
            'description' => 'Frais de livraison : part livreur ' . number_format((int) $earning->net_amount, 0, ',', ' ')
                . ' F, commission MenuPro ' . number_format((int) $earning->platform_cut, 0, ',', ' ') . ' F',
        ]);
    }

    // =========================================================================
    // OUTILS
    // =========================================================================

    /** Champs communs aux lignes liées à une commande. */
    private function baseOrderEntry(Order $order, ?string $via): array
    {
        $restaurant = $this->restaurant($order->restaurant_id);

        return [
            'recorded_via' => $via ?? $this->currentVia(),
            'channel' => $this->orderChannel($order),
            'restaurant_id' => $order->restaurant_id,
            'restaurant_name' => $restaurant?->name,
            'restaurant_plan' => $restaurant?->currentPlan?->slug,
            'restaurant_plan_label' => $restaurant?->currentPlan?->name,
            'is_demo' => (bool) ($restaurant?->is_demo ?? false),
            'source_type' => 'order',
            'source_id' => $order->id,
            'order_id' => $order->id,
            'subscription_id' => null,
            'order_reference' => $order->reference,
            'menupro_commission' => 0,
            'restaurant_share' => 0,
            'driver_share' => 0,
            'gateway_fee' => 0,
            'gateway_fee_rate' => 0,
            'net_menupro' => 0,
            'metadata' => null,
        ];
    }

    public function restaurant(?int $id): ?Restaurant
    {
        if (!$id) {
            return null;
        }

        if (!array_key_exists($id, $this->restaurants)) {
            $this->restaurants[$id] = Restaurant::withTrashed()->with('currentPlan:id,name,slug')->find($id);
        }

        return $this->restaurants[$id];
    }

    /** Oublie le cache local (utile pour les tests et les longues reconstitutions). */
    public function flushCache(): void
    {
        $this->restaurants = [];
    }

    public function isStandPlan(?string $slug): bool
    {
        return $slug !== null && str_contains(strtolower($slug), 'stand');
    }

    public function normalizeMethod(?string $method): string
    {
        $method = strtolower((string) $method);

        return match (true) {
            $method === 'jeko' => 'jeko',
            $method === 'wave' => 'wave',
            $method === '' || in_array($method, self::CASH_METHODS, true) => 'cash',
            default => 'other',
        };
    }

    public function isPlatformDelivery(Order $order): bool
    {
        return $order->source === 'platform_web' && $order->type === OrderType::DELIVERY;
    }

    public function orderChannel(Order $order): string
    {
        if ($order->source === 'platform_web') {
            return 'client_app';
        }

        if (str_starts_with((string) $order->reference, 'POS-')) {
            return 'pos';
        }

        return 'public_menu';
    }

    public function orderType(Order $order): string
    {
        if ($order->source === 'platform_web') {
            return $order->type === OrderType::DELIVERY
                ? LedgerEntry::TYPE_ORDER_APP_DELIVERY
                : LedgerEntry::TYPE_ORDER_APP_PICKUP;
        }

        if ($this->isStandPlan($this->restaurant($order->restaurant_id)?->currentPlan?->slug)) {
            return LedgerEntry::TYPE_ORDER_STAND;
        }

        return $this->orderChannel($order) === 'pos'
            ? LedgerEntry::TYPE_ORDER_POS
            : LedgerEntry::TYPE_ORDER_ONLINE_MENU;
    }

    /**
     * Références passerelle d'une commande, la principale d'abord : identifiant de
     * transaction Jeko/Wave (orders.payment_reference), puis l'identifiant du lien de
     * paiement / checkout gardé dans payment_transactions.
     *
     * @return array{0: ?string, 1: ?string}
     */
    public function orderGatewayReferences(Order $order): array
    {
        $meta = $order->payment_metadata ?? [];
        $refs = [
            $order->payment_reference,
            $meta['jeko_transaction_id'] ?? null,
            $meta['transaction_id'] ?? null,
            $meta['jeko_link_id'] ?? null,
            $meta['id'] ?? null,
        ];

        if ($this->normalizeMethod($order->payment_method) !== 'cash') {
            // Pas de liste de colonnes : la table de production n'a pas toutes les colonnes
            // des migrations (wave_payment_id absente) ; une colonne manquante vaut null.
            $tx = PaymentTransaction::where('order_id', $order->id)
                ->latest('id')
                ->first();
            if ($tx) {
                array_push($refs, $tx->gateway_transaction_id, $tx->jeko_payment_id, $tx->wave_payment_id, $tx->wave_checkout_id);
            }
        }

        $refs = array_values(array_unique(array_filter(
            array_map(fn ($r) => is_scalar($r) ? trim((string) $r) : null, $refs),
            fn ($r) => $r !== null && $r !== ''
        )));

        return [$refs[0] ?? null, $refs[1] ?? null];
    }

    /**
     * D'où vient l'écriture : webhook, back-office, espace restaurant, app livreur…
     * Déduit de la requête HTTP en cours (pas de requête ⇒ tâche automatique).
     */
    public function currentVia(): string
    {
        try {
            if (!app()->bound('request')) {
                return 'system';
            }

            $request = request();
            $route = $request->route();

            if (!$route) {
                return 'system';
            }

            $path = $request->path();
            if ($request->is('livewire*') || str_contains((string) $route->getName(), 'livewire')) {
                $path = ltrim((string) parse_url((string) $request->header('referer'), PHP_URL_PATH), '/');
            }
            $name = (string) $route->getName();

            return match (true) {
                str_starts_with($path, 'webhooks') || str_contains($name, 'webhook') => 'webhook',
                str_starts_with($path, 'admin') || str_starts_with($name, 'super-admin.') => 'back_office',
                str_starts_with($path, 'api/v1/driver') || str_starts_with($path, 'livreur') => 'driver_app',
                str_starts_with($path, 'api/v1/client') => 'client_api',
                str_starts_with($path, 'dashboard') || str_starts_with($path, 'api/v1/restaurant') => 'restaurant',
                default => 'site',
            };
        } catch (\Throwable) {
            return 'system';
        }
    }
}
