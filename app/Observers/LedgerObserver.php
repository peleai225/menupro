<?php

namespace App\Observers;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\SubscriptionStatus;
use App\Models\DriverEarning;
use App\Models\LedgerEntry;
use App\Models\Order;
use App\Models\OrderRefund;
use App\Models\PayoutTransaction;
use App\Models\Subscription;
use App\Services\LedgerService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

/**
 * Alimentation automatique du journal des transactions.
 *
 * Branché sur les modèles plutôt que sur chaque contrôleur : tous les chemins qui
 * confirment un paiement (Order::markAsPaid, webhooks Jeko/Wave, polling, encaissement
 * espèces par le livreur ou le restaurant, caisse POS), activent un abonnement payé
 * (Subscription::activateExclusively), reversent ou remboursent, passent par un save()
 * du modèle concerné. L'écriture a lieu après validation de la transaction SQL et
 * n'interrompt jamais le flux (try/catch + log dans LedgerService).
 */
class LedgerObserver
{
    public function __construct(private LedgerService $ledger)
    {
    }

    /** Vrai pendant le traitement d'un événement « created » (et non « updated »). */
    private bool $isNew = false;

    public function created(Model $model): void
    {
        $this->handle($model, true);
    }

    public function updated(Model $model): void
    {
        $this->handle($model, false);
    }

    private function handle(Model $model, bool $isNew): void
    {
        $this->isNew = $isNew;

        try {
            match (true) {
                $model instanceof Order => $this->order($model),
                $model instanceof Subscription => $this->subscription($model),
                $model instanceof OrderRefund => $this->refund($model),
                $model instanceof PayoutTransaction => $this->payoutTransaction($model),
                $model instanceof DriverEarning => $this->driverEarning($model),
                default => null,
            };
        } catch (\Throwable $e) {
            Log::channel('payments')->error('Journal des transactions : observer en échec', [
                'model' => $model::class,
                'id' => $model->getKey(),
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function changed(Model $model, string $attribute): bool
    {
        // Pas de wasRecentlyCreated : il reste vrai sur l'instance après une mise à jour
        return $this->isNew || $model->wasChanged($attribute);
    }

    private function order(Order $order): void
    {
        $paymentChanged = $this->changed($order, 'payment_status');
        $isCash = $this->ledger->normalizeMethod($order->payment_method) === 'cash';

        // Encaissement confirmé (en ligne ou espèces)
        if ($paymentChanged && $order->payment_status === PaymentStatus::COMPLETED) {
            $this->ledger->writeAfterCommit(fn () => $this->ledger->orderPaymentEntry($order));
        }

        // Commande espèces terminée sans encaissement explicite : l'argent a été remis
        if ($isCash
            && $this->changed($order, 'status')
            && $order->status === OrderStatus::COMPLETED
            && $order->payment_status !== PaymentStatus::COMPLETED
            && $order->payment_status !== PaymentStatus::REFUNDED) {
            $this->ledger->writeAfterCommit(fn () => $this->ledger->orderPaymentEntry($order));
        }

        if ($paymentChanged && $order->payment_status === PaymentStatus::FAILED) {
            $this->ledger->writeAfterCommit(fn () => $this->ledger->orderPaymentFailedEntry($order));
        }

        if ($paymentChanged && $order->payment_status === PaymentStatus::REFUNDED) {
            $this->ledger->updateStatus(LedgerService::orderPaymentKey($order->id), LedgerEntry::STATUS_REFUNDED);
        }

        // Reversement au restaurant (commandes payées en ligne uniquement)
        if (!$isCash && $this->changed($order, 'payout_status')) {
            if (in_array($order->payout_status, LedgerService::PAYOUT_DONE, true)) {
                $this->ledger->writeAfterCommit(fn () => $this->ledger->orderPayoutEntry($order));
                $this->ledger->updateStatus(LedgerService::orderPaymentKey($order->id), LedgerEntry::STATUS_PAID_OUT);
            } elseif ($order->payout_status === 'failed') {
                $this->ledger->writeAfterCommit(fn () => $this->ledger->orderPayoutFailedEntry($order));
            }
        }
    }

    private function subscription(Subscription $subscription): void
    {
        $relevant = $this->isNew
            || $subscription->wasChanged(['status', 'paid_at', 'amount_paid', 'payment_reference']);

        if (!$relevant) {
            return;
        }

        if ($this->ledger->isCollectedSubscription($subscription)) {
            $this->ledger->writeAfterCommit(fn () => $this->ledger->subscriptionEntry($subscription));

            return;
        }

        // Paiement d'abonnement échoué : PENDING → CANCELLED (webhook Jeko d'échec)
        if ($subscription->wasChanged('status')
            && $subscription->status === SubscriptionStatus::CANCELLED
            && $subscription->getOriginal('status') === SubscriptionStatus::PENDING
            && (int) $subscription->amount_paid > 0) {
            $this->ledger->writeAfterCommit(fn () => $this->ledger->subscriptionEntry($subscription, failed: true));
        }
    }

    private function refund(OrderRefund $refund): void
    {
        if ($this->isNew) {
            $this->ledger->writeAfterCommit(fn () => $this->ledger->refundEntry($refund));
        } elseif ($refund->wasChanged('status')) {
            $entry = $this->ledger->refundEntry($refund);
            if ($entry) {
                $this->ledger->updateStatus($entry['idempotency_key'], $entry['status']);
            }
        }

        // Remboursement total effectué : l'encaissement passe en « remboursé »
        if ($this->changed($refund, 'status') && in_array($refund->status, ['completed', 'succeeded', 'refunded'], true)) {
            $order = Order::withoutGlobalScope('restaurant')->find($refund->order_id);
            if ($order && (int) $refund->amount >= (int) $order->total) {
                $this->ledger->updateStatus(LedgerService::orderPaymentKey($order->id), LedgerEntry::STATUS_REFUNDED);
            }
        }
    }

    private function payoutTransaction(PayoutTransaction $payout): void
    {
        if ($this->changed($payout, 'status')) {
            $this->ledger->writeAfterCommit(fn () => $this->ledger->payoutTransactionEntry($payout));
        }
    }

    private function driverEarning(DriverEarning $earning): void
    {
        if ($this->isNew) {
            $this->ledger->writeAfterCommit(fn () => $this->ledger->driverEarningEntry($earning));
        }
    }
}
